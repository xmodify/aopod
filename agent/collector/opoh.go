package collector

import (
	"crypto/tls"
	"database/sql"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"sync"
	"time"

	"aopod-agent/config"
)

// OpohDoctor represents an active specialist doctor from central roster
type OpohDoctor struct {
	ID            int64  `json:"id"`
	DoctorName    string `json:"doctor_name"`
	Fname         string `json:"fname"`
	Lname         string `json:"lname"`
	CID           string `json:"cid"`
	Licenseno     string `json:"licenseno"`
	SpcltyCode    string `json:"spclty_code"`
	SpecialtyName string `json:"specialty_name"`
}

type OpohDoctorsApiResponse struct {
	Status   string       `json:"status"`
	Count    int          `json:"count"`
	Cids     []string     `json:"cids"`
	Licenses []string     `json:"licenses"`
	Names    []string     `json:"names"`
	Data     []OpohDoctor `json:"data"`
}

var (
	opohDoctorsLock sync.RWMutex
	cachedDoctors   []OpohDoctor
)

func getOpohCachePath() string {
	return filepath.Join(config.GetConfigDir(), "opoh_doctors_cache.json")
}

// FetchOpohDoctors retrieves the active doctor roster from central server with local cache fallback
func FetchOpohDoctors(serverURL, token string) ([]OpohDoctor, error) {
	opohDoctorsLock.Lock()
	defer opohDoctorsLock.Unlock()

	cleanURL := strings.TrimRight(serverURL, "/")
	reqURL := fmt.Sprintf("%s/api/agent/opoh-doctors", cleanURL)

	client := &http.Client{
		Timeout: 15 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("GET", reqURL, nil)
	if err == nil {
		if token != "" {
			req.Header.Set("Authorization", "Bearer "+token)
		}
		req.Header.Set("Accept", "application/json")

		resp, err := client.Do(req)
		if err == nil && resp.StatusCode == http.StatusOK {
			body, _ := io.ReadAll(resp.Body)
			resp.Body.Close()

			var apiResp OpohDoctorsApiResponse
			if err := json.Unmarshal(body, &apiResp); err == nil && len(apiResp.Data) > 0 {
				cachedDoctors = apiResp.Data
				_ = os.WriteFile(getOpohCachePath(), body, 0644)
				return cachedDoctors, nil
			}
		}
	}

	// Memory cache fallback
	if len(cachedDoctors) > 0 {
		return cachedDoctors, nil
	}

	// Disk cache fallback
	if data, err := os.ReadFile(getOpohCachePath()); err == nil {
		var apiResp OpohDoctorsApiResponse
		if err := json.Unmarshal(data, &apiResp); err == nil && len(apiResp.Data) > 0 {
			cachedDoctors = apiResp.Data
			return cachedDoctors, nil
		}
	}

	return nil, fmt.Errorf("unable to load OPOH doctors from server or disk cache")
}

type OpohOpdRecord struct {
	Vstdate        string  `json:"vstdate"`
	SpcltyCode     string  `json:"spclty_code"`
	SpecialtyName  string  `json:"specialty_name"`
	DoctorID       int64   `json:"doctor_id"`
	Licenseno      string  `json:"licenseno"`
	HnTotal        int     `json:"hn_total"`
	VisitTotal     int     `json:"visit_total"`
	VisitUCS       int     `json:"visit_ucs"`
	VisitOFC       int     `json:"visit_ofc"`
	VisitLGO       int     `json:"visit_lgo"`
	VisitSSS       int     `json:"visit_sss"`
	VisitOther     int     `json:"visit_other"`
	IncTotal       float64 `json:"inc_total"`
	IncDrugTotal   float64 `json:"inc_drug_total"`
	IncLabTotal    float64 `json:"inc_lab_total"`
	ReferToOrigin  int     `json:"refer_to_origin"`
	AdmitIPD       int     `json:"admit_ipd"`
}

type OpohIpdRecord struct {
	Dchdate        string  `json:"dchdate"`
	SpcltyCode     string  `json:"spclty_code"`
	SpecialtyName  string  `json:"specialty_name"`
	DoctorID       int64   `json:"doctor_id"`
	Licenseno      string  `json:"licenseno"`
	AnTotal        int     `json:"an_total"`
	AdmdateTotal   int     `json:"admdate_total"`
	AdjrwTotal     float64 `json:"adjrw_total"`
	OperationTotal int     `json:"operation_total"`
	IncTotal       float64 `json:"inc_total"`
}

// buildDoctorMatchClause creates SQL WHERE conditions strictly for doctor CIDs and licenses (2-tier matching)
func buildDoctorMatchClause(doctors []OpohDoctor) (string, map[string]OpohDoctor, map[string]OpohDoctor) {
	cidMap := make(map[string]OpohDoctor)
	licMap := make(map[string]OpohDoctor)
	var cids []string
	var lics []string

	for _, d := range doctors {
		// Tier 1: CID 13 digits
		cleanCid := strings.TrimSpace(d.CID)
		if cleanCid != "" {
			cidMap[cleanCid] = d
			cids = append(cids, fmt.Sprintf("'%s'", cleanCid))
		}

		// Tier 2: License number (วxxxx)
		cleanLic := strings.TrimSpace(d.Licenseno)
		if cleanLic != "" {
			licMap[cleanLic] = d
			lics = append(lics, fmt.Sprintf("'%s'", cleanLic))
			// Also match variant without 'ว'
			if strings.HasPrefix(cleanLic, "ว") {
				noV := strings.TrimPrefix(cleanLic, "ว")
				licMap[noV] = d
				lics = append(lics, fmt.Sprintf("'%s'", noV))
			}
		}
	}

	var clauses []string
	if len(cids) > 0 {
		clauses = append(clauses, fmt.Sprintf("d.cid IN (%s)", strings.Join(cids, ",")))
	}
	if len(lics) > 0 {
		clauses = append(clauses, fmt.Sprintf("d.licenseno IN (%s)", strings.Join(lics, ",")))
	}

	if len(clauses) == 0 {
		return "1=0", cidMap, licMap
	}

	return strings.Join(clauses, " OR "), cidMap, licMap
}

// CollectOpohOPD queries HOSxP for outpatient visits under OPOH specialist doctors
func CollectOpohOPD(db *sql.DB, doctors []OpohDoctor, startDate, endDate string) ([]OpohOpdRecord, error) {
	if len(doctors) == 0 {
		return nil, nil
	}

	matchWhere, cidMap, licMap := buildDoctorMatchClause(doctors)

	query := fmt.Sprintf(`SELECT 
		ov.vstdate,
		COALESCE(d.cid, '') AS cid,
		COALESCE(d.licenseno, '') AS licenseno,
		COUNT(DISTINCT ov.hn) AS hn_total,
		COUNT(ov.vn) AS visit_total,
		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') THEN 1 ELSE 0 END) AS visit_ucs,
		SUM(CASE WHEN p.hipdata_code = 'OFC' THEN 1 ELSE 0 END) AS visit_ofc,
		SUM(CASE WHEN p.hipdata_code = 'LGO' THEN 1 ELSE 0 END) AS visit_lgo,
		SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN 1 ELSE 0 END) AS visit_sss,
		SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN 1 ELSE 0 END) AS visit_other,
		COALESCE(SUM(v.income), 0) AS inc_total,
		COALESCE(SUM(v.inc12), 0) AS inc_drug_total,
		COALESCE(SUM(v.inc03), 0) AS inc_lab_total,
		COUNT(DISTINCT CASE WHEN ro.refer_hospcode = '10703' THEN ov.vn END) AS refer_to_origin,
		COUNT(DISTINCT CASE WHEN ipt.an IS NOT NULL THEN ov.vn END) AS admit_ipd
	FROM ovst ov
	JOIN vn_stat v ON v.vn = ov.vn
	JOIN doctor d ON d.code = ov.doctor
	LEFT JOIN pttype p ON p.pttype = ov.pttype
	LEFT JOIN referout ro ON ro.vn = ov.vn
	LEFT JOIN ipt ON ipt.vn = ov.vn
	WHERE ov.vstdate BETWEEN ? AND ?
	  AND (%s)
	GROUP BY ov.vstdate, d.cid, d.licenseno
	ORDER BY ov.vstdate ASC`, matchWhere)

	rows, err := db.Query(query, startDate, endDate)
	if err != nil {
		return nil, fmt.Errorf("error querying OPOH OPD: %w", err)
	}
	defer rows.Close()

	var records []OpohOpdRecord
	for rows.Next() {
		var r OpohOpdRecord
		var cid, lic string
		var vstDateRaw string

		err := rows.Scan(
			&vstDateRaw,
			&cid,
			&lic,
			&r.HnTotal,
			&r.VisitTotal,
			&r.VisitUCS,
			&r.VisitOFC,
			&r.VisitLGO,
			&r.VisitSSS,
			&r.VisitOther,
			&r.IncTotal,
			&r.IncDrugTotal,
			&r.IncLabTotal,
			&r.ReferToOrigin,
			&r.AdmitIPD,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning OPOH OPD row: %w", err)
		}

		if len(vstDateRaw) >= 10 {
			r.Vstdate = vstDateRaw[:10]
		} else {
			r.Vstdate = vstDateRaw
		}

		// Match doctor: 1. CID 13 หลัก -> 2. เลข ว. (d.licenseno)
		cid = strings.TrimSpace(cid)
		lic = strings.TrimSpace(lic)
		var matchedDoc OpohDoctor
		var found bool

		if cid != "" {
			matchedDoc, found = cidMap[cid]
		}
		if !found && lic != "" {
			matchedDoc, found = licMap[lic]
		}

		if found {
			r.DoctorID = matchedDoc.ID
			r.SpcltyCode = matchedDoc.SpcltyCode
			r.SpecialtyName = matchedDoc.SpecialtyName
			r.Licenseno = matchedDoc.Licenseno
		} else {
			r.SpcltyCode = "01"
			r.SpecialtyName = "ไม่ระบุสาขา"
			r.Licenseno = lic
		}

		records = append(records, r)
	}

	return records, nil
}

// CollectOpohIPD queries HOSxP for inpatient admissions/discharges under OPOH specialist doctors
func CollectOpohIPD(db *sql.DB, doctors []OpohDoctor, startDate, endDate string) ([]OpohIpdRecord, error) {
	if len(doctors) == 0 {
		return nil, nil
	}

	matchWhere, cidMap, licMap := buildDoctorMatchClause(doctors)

	query := fmt.Sprintf(`SELECT 
		i.dchdate,
		COALESCE(d.cid, '') AS cid,
		COALESCE(d.licenseno, '') AS licenseno,
		COUNT(DISTINCT i.an) AS an_total,
		SUM(COALESCE(a.admdate, a.los, 1)) AS admdate_total,
		SUM(COALESCE(a.rw, i.adjrw, 0)) AS adjrw_total,
		COUNT(DISTINCT op.operation_id) AS operation_total,
		COALESCE(SUM(a.income), 0) AS inc_total
	FROM ipt i
	JOIN an_stat a ON a.an = i.an
	JOIN doctor d ON (d.code = i.admdoctor OR d.code = i.dch_doctor OR d.code = i.incharge_doctor)
	LEFT JOIN operation_list op ON op.an = i.an
	WHERE i.dchdate BETWEEN ? AND ?
	  AND (%s)
	GROUP BY i.dchdate, d.cid, d.licenseno
	ORDER BY i.dchdate ASC`, matchWhere)

	rows, err := db.Query(query, startDate, endDate)
	if err != nil {
		return nil, fmt.Errorf("error querying OPOH IPD: %w", err)
	}
	defer rows.Close()

	var records []OpohIpdRecord
	for rows.Next() {
		var r OpohIpdRecord
		var cid, lic string
		var dchDateRaw string

		err := rows.Scan(
			&dchDateRaw,
			&cid,
			&lic,
			&r.AnTotal,
			&r.AdmdateTotal,
			&r.AdjrwTotal,
			&r.OperationTotal,
			&r.IncTotal,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning OPOH IPD row: %w", err)
		}

		if len(dchDateRaw) >= 10 {
			r.Dchdate = dchDateRaw[:10]
		} else {
			r.Dchdate = dchDateRaw
		}

		// Match doctor: 1. CID 13 หลัก -> 2. เลข ว. (d.licenseno)
		cid = strings.TrimSpace(cid)
		lic = strings.TrimSpace(lic)
		var matchedDoc OpohDoctor
		var found bool

		if cid != "" {
			matchedDoc, found = cidMap[cid]
		}
		if !found && lic != "" {
			matchedDoc, found = licMap[lic]
		}

		if found {
			r.DoctorID = matchedDoc.ID
			r.SpcltyCode = matchedDoc.SpcltyCode
			r.SpecialtyName = matchedDoc.SpecialtyName
			r.Licenseno = matchedDoc.Licenseno
		} else {
			r.SpcltyCode = "01"
			r.SpecialtyName = "ไม่ระบุสาขา"
			r.Licenseno = lic
		}

		records = append(records, r)
	}

	return records, nil
}

