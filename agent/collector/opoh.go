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

type OpohDiagnosisItem struct {
	ICD10     string `json:"icd10"`
	DiagName  string `json:"diag_name"`
	CaseTotal int    `json:"case_total"`
}

type OpohOpdRecord struct {
	Vstdate         string              `json:"vstdate"`
	SpcltyCode      string              `json:"spclty_code"`
	SpecialtyName   string              `json:"specialty_name"`
	DoctorID        int64               `json:"doctor_id"`
	Licenseno       string              `json:"licenseno"`
	HnTotal         int                 `json:"hn_total"`
	VisitTotal      int                 `json:"visit_total"`
	VisitUCS        int                 `json:"visit_ucs"`
	VisitOFC        int                 `json:"visit_ofc"`
	VisitLGO        int                 `json:"visit_lgo"`
	VisitSSS        int                 `json:"visit_sss"`
	VisitOther      int                 `json:"visit_other"`
	VisitTelemed    int                 `json:"visit_telemed"`
	VisitUCSIncup   int                 `json:"visit_ucs_incup"`
	VisitUCSOutprov int                 `json:"visit_ucs_outprov"`
	IncTotal        float64             `json:"inc_total"`
	IncDrugTotal    float64             `json:"inc_drug_total"`
	IncLabTotal     float64             `json:"inc_lab_total"`
	IncUCSIncup     float64             `json:"inc_ucs_incup"`
	IncUCSOutprov   float64             `json:"inc_ucs_outprov"`
	IncOFC          float64             `json:"inc_ofc"`
	IncLGO          float64             `json:"inc_lgo"`
	IncSSS          float64             `json:"inc_sss"`
	IncOther        float64             `json:"inc_other"`
	ReferToOrigin   int                 `json:"refer_to_origin"`
	AdmitIPD        int                 `json:"admit_ipd"`
	Diagnoses       []OpohDiagnosisItem `json:"diagnoses,omitempty"`
}

type OpohIpdRecord struct {
	Dchdate           string              `json:"dchdate"`
	SpcltyCode        string              `json:"spclty_code"`
	SpecialtyName     string              `json:"specialty_name"`
	DoctorID          int64               `json:"doctor_id"`
	Licenseno         string              `json:"licenseno"`
	AnTotal           int                 `json:"an_total"`
	AdmdateTotal      int                 `json:"admdate_total"`
	AdjrwTotal        float64             `json:"adjrw_total"`
	OperationTotal    int                 `json:"operation_total"`
	IncTotal          float64             `json:"inc_total"`
	AnUCSIncup        int                 `json:"an_ucs_incup"`
	AnUCSOutprov      int                 `json:"an_ucs_outprov"`
	AnOFC             int                 `json:"an_ofc"`
	AnLGO             int                 `json:"an_lgo"`
	AnSSS             int                 `json:"an_sss"`
	AnOther           int                 `json:"an_other"`
	AdmdateUCSIncup   int                 `json:"admdate_ucs_incup"`
	AdmdateUCSOutprov int                 `json:"admdate_ucs_outprov"`
	AdmdateOFC        int                 `json:"admdate_ofc"`
	AdmdateLGO        int                 `json:"admdate_lgo"`
	AdmdateSSS        int                 `json:"admdate_sss"`
	AdmdateOther      int                 `json:"admdate_other"`
	AdjrwUCSIncup     float64             `json:"adjrw_ucs_incup"`
	AdjrwUCSOutprov   float64             `json:"adjrw_ucs_outprov"`
	AdjrwOFC          float64             `json:"adjrw_ofc"`
	AdjrwLGO          float64             `json:"adjrw_lgo"`
	AdjrwSSS          float64             `json:"adjrw_sss"`
	AdjrwOther        float64             `json:"adjrw_other"`
	IncUCSIncup       float64             `json:"inc_ucs_incup"`
	IncUCSOutprov     float64             `json:"inc_ucs_outprov"`
	IncOFC            float64             `json:"inc_ofc"`
	IncLGO            float64             `json:"inc_lgo"`
	IncSSS            float64             `json:"inc_sss"`
	IncOther          float64             `json:"inc_other"`
	Diagnoses         []OpohDiagnosisItem `json:"diagnoses,omitempty"`
}

type OpohOperationRecord struct {
	OperationDate  string  `json:"operation_date"`
	SpcltyCode     string  `json:"spclty_code"`
	SpecialtyName  string  `json:"specialty_name"`
	DoctorID       int64   `json:"doctor_id"`
	Licenseno      string  `json:"licenseno"`
	ICD9           string  `json:"icd9"`
	OperationName  string  `json:"operation_name"`
	OpType         string  `json:"op_type"`
	CaseTotal      int     `json:"case_total"`
	IncTotal       float64 `json:"inc_total"`
	AdjrwTotal     float64 `json:"adjrw_total"`
	CaseUCSIncup   int     `json:"case_ucs_incup"`
	CaseUCSOutprov int     `json:"case_ucs_outprov"`
	CaseOFC        int     `json:"case_ofc"`
	CaseLGO        int     `json:"case_lgo"`
	CaseSSS        int     `json:"case_sss"`
	CaseOther      int     `json:"case_other"`
	IncUCSIncup    float64 `json:"inc_ucs_incup"`
	IncUCSOutprov  float64 `json:"inc_ucs_outprov"`
	IncOFC         float64 `json:"inc_ofc"`
	IncLGO         float64 `json:"inc_lgo"`
	IncSSS         float64 `json:"inc_sss"`
	IncOther       float64 `json:"inc_other"`
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

// matchDoctor resolves an OpohDoctor from CID or License
func matchDoctor(cid, lic string, cidMap, licMap map[string]OpohDoctor) (OpohDoctor, bool) {
	cid = strings.TrimSpace(cid)
	lic = strings.TrimSpace(lic)
	if cid != "" {
		if d, ok := cidMap[cid]; ok {
			return d, true
		}
	}
	if lic != "" {
		if d, ok := licMap[lic]; ok {
			return d, true
		}
	}
	return OpohDoctor{}, false
}

// CollectOpohOPD queries HOSxP for outpatient visits under OPOH specialist doctors
func CollectOpohOPD(db *sql.DB, doctors []OpohDoctor, startDate, endDate string) ([]OpohOpdRecord, error) {
	if len(doctors) == 0 {
		return nil, nil
	}

	matchWhere, cidMap, licMap := buildDoctorMatchClause(doctors)

	// 1. Collect OPD Diagnoses (Excluding hemodialysis visits: nhso_adp_code = 71641 or ICD-9 = 3995)
	diagQuery := fmt.Sprintf(`SELECT 
		ov.vstdate,
		COALESCE(d.cid, '') AS cid,
		COALESCE(d.licenseno, '') AS licenseno,
		v.pdx,
		COALESCE(i.name, v.pdx) AS diag_name,
		COUNT(v.vn) AS case_total
	FROM ovst ov
	JOIN vn_stat v ON v.vn = ov.vn
	JOIN doctor d ON d.code = ov.doctor
	LEFT JOIN icd101 i ON i.code = v.pdx
	WHERE ov.vstdate BETWEEN ? AND ?
	  AND v.pdx IS NOT NULL AND v.pdx != ''
	  AND NOT EXISTS (
		SELECT 1 
		FROM opitemrece opr 
		JOIN nondrugitems ndi ON ndi.icode = opr.icode 
		WHERE opr.vn = ov.vn 
		  AND (ndi.nhso_adp_code = '71641' OR ndi.icd9cm IN ('3995', '39.95'))
	  )
	  AND NOT EXISTS (
		SELECT 1 
		FROM doctor_operation d_op 
		WHERE d_op.vn = ov.vn 
		  AND d_op.icd9 IN ('3995', '39.95')
	  )
	  AND NOT EXISTS (
		SELECT 1 
		FROM ovstdiag od 
		WHERE od.vn = ov.vn 
		  AND od.icd10 IN ('3995', '39.95')
	  )
	  AND (%s)
	GROUP BY ov.vstdate, d.cid, d.licenseno, v.pdx, i.name
	ORDER BY ov.vstdate ASC, case_total DESC`, matchWhere)

	diagRows, err := db.Query(diagQuery, startDate, endDate)
	diagMap := make(map[string][]OpohDiagnosisItem)
	if err == nil {
		for diagRows.Next() {
			var dDate, dCid, dLic, dPdx, dName string
			var dTotal int
			if scanErr := diagRows.Scan(&dDate, &dCid, &dLic, &dPdx, &dName, &dTotal); scanErr == nil {
				if len(dDate) >= 10 {
					dDate = dDate[:10]
				}
				key := fmt.Sprintf("%s|%s|%s", dDate, strings.TrimSpace(dCid), strings.TrimSpace(dLic))
				diagMap[key] = append(diagMap[key], OpohDiagnosisItem{
					ICD10:     dPdx,
					DiagName:  dName,
					CaseTotal: dTotal,
				})
			}
		}
		diagRows.Close()
	}

	// 2. Query OPD Summary
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
		SUM(CASE WHEN ot.export_code = '5' THEN 1 ELSE 0 END) AS visit_telemed,
		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR v.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN 1 ELSE 0 END) AS visit_ucs_incup,
		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR v.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN 1 ELSE 0 END) AS visit_ucs_outprov,
		COALESCE(SUM(v.income), 0) AS inc_total,
		COALESCE(SUM(v.inc12), 0) AS inc_drug_total,
		COALESCE(SUM(v.inc03), 0) AS inc_lab_total,
		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR v.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN v.income ELSE 0 END), 0) AS inc_ucs_incup,
		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR v.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN v.income ELSE 0 END), 0) AS inc_ucs_outprov,
		COALESCE(SUM(CASE WHEN p.hipdata_code = 'OFC' THEN v.income ELSE 0 END), 0) AS inc_ofc,
		COALESCE(SUM(CASE WHEN p.hipdata_code = 'LGO' THEN v.income ELSE 0 END), 0) AS inc_lgo,
		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN v.income ELSE 0 END), 0) AS inc_sss,
		COALESCE(SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN v.income ELSE 0 END), 0) AS inc_other,
		COUNT(DISTINCT CASE WHEN ro.refer_hospcode = '10703' THEN ov.vn END) AS refer_to_origin,
		COUNT(DISTINCT CASE WHEN ipt.an IS NOT NULL THEN ov.vn END) AS admit_ipd
	FROM ovst ov
	JOIN vn_stat v ON v.vn = ov.vn
	JOIN doctor d ON d.code = ov.doctor
	LEFT JOIN ovstist ot ON ot.ovstist = ov.ovstist
	LEFT JOIN pttype p ON p.pttype = ov.pttype
	LEFT JOIN hospcode hc ON hc.hospcode = v.hospmain
	LEFT JOIN referout ro ON ro.vn = ov.vn
	LEFT JOIN ipt ON ipt.vn = ov.vn
	WHERE ov.vstdate BETWEEN ? AND ?
	  AND NOT EXISTS (
		SELECT 1 
		FROM opitemrece opr 
		JOIN nondrugitems ndi ON ndi.icode = opr.icode 
		WHERE opr.vn = ov.vn 
		  AND (ndi.nhso_adp_code = '71641' OR ndi.icd9cm IN ('3995', '39.95'))
	  )
	  AND NOT EXISTS (
		SELECT 1 
		FROM doctor_operation d_op 
		WHERE d_op.vn = ov.vn 
		  AND d_op.icd9 IN ('3995', '39.95')
	  )
	  AND NOT EXISTS (
		SELECT 1 
		FROM ovstdiag od 
		WHERE od.vn = ov.vn 
		  AND od.icd10 IN ('3995', '39.95')
	  )
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
			&r.VisitTelemed,
			&r.VisitUCSIncup,
			&r.VisitUCSOutprov,
			&r.IncTotal,
			&r.IncDrugTotal,
			&r.IncLabTotal,
			&r.IncUCSIncup,
			&r.IncUCSOutprov,
			&r.IncOFC,
			&r.IncLGO,
			&r.IncSSS,
			&r.IncOther,
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

		matchedDoc, found := matchDoctor(cid, lic, cidMap, licMap)
		if found {
			r.DoctorID = matchedDoc.ID
			r.SpcltyCode = matchedDoc.SpcltyCode
			r.SpecialtyName = matchedDoc.SpecialtyName
			r.Licenseno = matchedDoc.Licenseno
		} else {
			r.SpcltyCode = "01"
			r.SpecialtyName = "ไม่ระบุสาขา"
			r.Licenseno = strings.TrimSpace(lic)
		}

		// Attach diagnoses
		key := fmt.Sprintf("%s|%s|%s", r.Vstdate, strings.TrimSpace(cid), strings.TrimSpace(lic))
		if items, ok := diagMap[key]; ok {
			r.Diagnoses = items
		}

		records = append(records, r)
	}

	// Check if any doctor had visits in ovst on dates where ALL visits were excluded as dialysis.
	// Emitting a record with VisitTotal: 0 allows the central server to delete old ghost records.
	seenDoctorDates := make(map[string]bool)
	for _, rec := range records {
		seenDoctorDates[fmt.Sprintf("%s|%d", rec.Vstdate, rec.DoctorID)] = true
	}

	zeroCheckQuery := fmt.Sprintf(`SELECT 
		ov.vstdate,
		COALESCE(d.cid, '') AS cid,
		COALESCE(d.licenseno, '') AS licenseno
	FROM ovst ov
	JOIN doctor d ON d.code = ov.doctor
	WHERE ov.vstdate BETWEEN ? AND ?
	  AND (%s)
	GROUP BY ov.vstdate, d.cid, d.licenseno`, matchWhere)

	if zRows, err := db.Query(zeroCheckQuery, startDate, endDate); err == nil {
		defer zRows.Close()
		for zRows.Next() {
			var zDate, zCid, zLic string
			if err := zRows.Scan(&zDate, &zCid, &zLic); err == nil {
				if len(zDate) >= 10 {
					zDate = zDate[:10]
				}
				if doc, found := matchDoctor(zCid, zLic, cidMap, licMap); found {
					dKey := fmt.Sprintf("%s|%d", zDate, doc.ID)
					if !seenDoctorDates[dKey] {
						records = append(records, OpohOpdRecord{
							Vstdate:       zDate,
							DoctorID:      doc.ID,
							Licenseno:     doc.Licenseno,
							SpcltyCode:    doc.SpcltyCode,
							SpecialtyName: doc.SpecialtyName,
							VisitTotal:    0,
							HnTotal:       0,
						})
						seenDoctorDates[dKey] = true
					}
				}
			}
		}
	}

	return records, nil
}

// CollectOpohIPD queries HOSxP for inpatient admissions/discharges under OPOH specialist doctors
func CollectOpohIPD(db *sql.DB, doctors []OpohDoctor, startDate, endDate string) ([]OpohIpdRecord, error) {
	if len(doctors) == 0 {
		return nil, nil
	}

	matchWhere, cidMap, licMap := buildDoctorMatchClause(doctors)

	// 1. Collect IPD Diagnoses
	diagQuery := fmt.Sprintf(`SELECT 
		i.dchdate,
		COALESCE(d.cid, '') AS cid,
		COALESCE(d.licenseno, '') AS licenseno,
		a.pdx,
		COALESCE(ic.name, a.pdx) AS diag_name,
		COUNT(DISTINCT i.an) AS case_total
	FROM ipt i
	JOIN an_stat a ON a.an = i.an
	JOIN doctor d ON (d.code = i.admdoctor OR d.code = i.dch_doctor OR d.code = i.incharge_doctor)
	LEFT JOIN icd101 ic ON ic.code = a.pdx
	WHERE i.dchdate BETWEEN ? AND ?
	  AND a.pdx IS NOT NULL AND a.pdx != ''
	  AND (%s)
	GROUP BY i.dchdate, d.cid, d.licenseno, a.pdx, ic.name
	ORDER BY i.dchdate ASC, case_total DESC`, matchWhere)

	diagRows, err := db.Query(diagQuery, startDate, endDate)
	diagMap := make(map[string][]OpohDiagnosisItem)
	if err == nil {
		for diagRows.Next() {
			var dDate, dCid, dLic, dPdx, dName string
			var dTotal int
			if scanErr := diagRows.Scan(&dDate, &dCid, &dLic, &dPdx, &dName, &dTotal); scanErr == nil {
				if len(dDate) >= 10 {
					dDate = dDate[:10]
				}
				key := fmt.Sprintf("%s|%s|%s", dDate, strings.TrimSpace(dCid), strings.TrimSpace(dLic))
				diagMap[key] = append(diagMap[key], OpohDiagnosisItem{
					ICD10:     dPdx,
					DiagName:  dName,
					CaseTotal: dTotal,
				})
			}
		}
		diagRows.Close()
	}

	// 2. Query IPD Summary
	query := fmt.Sprintf(`SELECT 
		i.dchdate,
		COALESCE(d.cid, '') AS cid,
		COALESCE(d.licenseno, '') AS licenseno,
		COUNT(DISTINCT i.an) AS an_total,
		SUM(COALESCE(a.admdate, a.los, 1)) AS admdate_total,
		SUM(COALESCE(a.rw, i.adjrw, 0)) AS adjrw_total,
		COUNT(DISTINCT op.operation_id) AS operation_total,
		COALESCE(SUM(a.income), 0) AS inc_total,

		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN 1 ELSE 0 END) AS an_ucs_incup,
		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN 1 ELSE 0 END) AS an_ucs_outprov,
		SUM(CASE WHEN p.hipdata_code = 'OFC' THEN 1 ELSE 0 END) AS an_ofc,
		SUM(CASE WHEN p.hipdata_code = 'LGO' THEN 1 ELSE 0 END) AS an_lgo,
		SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN 1 ELSE 0 END) AS an_sss,
		SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN 1 ELSE 0 END) AS an_other,

		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN COALESCE(a.admdate, a.los, 1) ELSE 0 END) AS admdate_ucs_incup,
		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN COALESCE(a.admdate, a.los, 1) ELSE 0 END) AS admdate_ucs_outprov,
		SUM(CASE WHEN p.hipdata_code = 'OFC' THEN COALESCE(a.admdate, a.los, 1) ELSE 0 END) AS admdate_ofc,
		SUM(CASE WHEN p.hipdata_code = 'LGO' THEN COALESCE(a.admdate, a.los, 1) ELSE 0 END) AS admdate_lgo,
		SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN COALESCE(a.admdate, a.los, 1) ELSE 0 END) AS admdate_sss,
		SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN COALESCE(a.admdate, a.los, 1) ELSE 0 END) AS admdate_other,

		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN COALESCE(a.rw, i.adjrw, 0) ELSE 0 END) AS adjrw_ucs_incup,
		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN COALESCE(a.rw, i.adjrw, 0) ELSE 0 END) AS adjrw_ucs_outprov,
		SUM(CASE WHEN p.hipdata_code = 'OFC' THEN COALESCE(a.rw, i.adjrw, 0) ELSE 0 END) AS adjrw_ofc,
		SUM(CASE WHEN p.hipdata_code = 'LGO' THEN COALESCE(a.rw, i.adjrw, 0) ELSE 0 END) AS adjrw_lgo,
		SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN COALESCE(a.rw, i.adjrw, 0) ELSE 0 END) AS adjrw_sss,
		SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN COALESCE(a.rw, i.adjrw, 0) ELSE 0 END) AS adjrw_other,

		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN a.income ELSE 0 END), 0) AS inc_ucs_incup,
		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (hc.chwpart = '37' OR ip.hospmain IN ('10703','10985','10986','10987','10988','10989','10990')) THEN a.income ELSE 0 END), 0) AS inc_ucs_outprov,
		COALESCE(SUM(CASE WHEN p.hipdata_code = 'OFC' THEN a.income ELSE 0 END), 0) AS inc_ofc,
		COALESCE(SUM(CASE WHEN p.hipdata_code = 'LGO' THEN a.income ELSE 0 END), 0) AS inc_lgo,
		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN a.income ELSE 0 END), 0) AS inc_sss,
		COALESCE(SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN a.income ELSE 0 END), 0) AS inc_other
	FROM ipt i
	JOIN an_stat a ON a.an = i.an
	JOIN doctor d ON (d.code = i.admdoctor OR d.code = i.dch_doctor OR d.code = i.incharge_doctor)
	LEFT JOIN pttype p ON p.pttype = i.pttype
	LEFT JOIN ipt_pttype ip ON ip.an = i.an AND ip.pttype_number = 1
	LEFT JOIN hospcode hc ON hc.hospcode = ip.hospmain
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
			&r.AnUCSIncup,
			&r.AnUCSOutprov,
			&r.AnOFC,
			&r.AnLGO,
			&r.AnSSS,
			&r.AnOther,
			&r.AdmdateUCSIncup,
			&r.AdmdateUCSOutprov,
			&r.AdmdateOFC,
			&r.AdmdateLGO,
			&r.AdmdateSSS,
			&r.AdmdateOther,
			&r.AdjrwUCSIncup,
			&r.AdjrwUCSOutprov,
			&r.AdjrwOFC,
			&r.AdjrwLGO,
			&r.AdjrwSSS,
			&r.AdjrwOther,
			&r.IncUCSIncup,
			&r.IncUCSOutprov,
			&r.IncOFC,
			&r.IncLGO,
			&r.IncSSS,
			&r.IncOther,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning OPOH IPD row: %w", err)
		}

		if len(dchDateRaw) >= 10 {
			r.Dchdate = dchDateRaw[:10]
		} else {
			r.Dchdate = dchDateRaw
		}

		matchedDoc, found := matchDoctor(cid, lic, cidMap, licMap)
		if found {
			r.DoctorID = matchedDoc.ID
			r.SpcltyCode = matchedDoc.SpcltyCode
			r.SpecialtyName = matchedDoc.SpecialtyName
			r.Licenseno = matchedDoc.Licenseno
		} else {
			r.SpcltyCode = "01"
			r.SpecialtyName = "ไม่ระบุสาขา"
			r.Licenseno = strings.TrimSpace(lic)
		}

		// Attach diagnoses
		key := fmt.Sprintf("%s|%s|%s", r.Dchdate, strings.TrimSpace(cid), strings.TrimSpace(lic))
		if items, ok := diagMap[key]; ok {
			r.Diagnoses = items
		}

		records = append(records, r)
	}

	return records, nil
}

// CollectOpohOperations queries HOSxP for surgical operations & procedures (Tab 3: Surgery)
func CollectOpohOperations(db *sql.DB, doctors []OpohDoctor, startDate, endDate string) ([]OpohOperationRecord, error) {
	if len(doctors) == 0 {
		return nil, nil
	}

	matchWhere, cidMap, licMap := buildDoctorMatchClause(doctors)

	query := fmt.Sprintf(`SELECT 
		ol.operation_date,
		COALESCE(od.icdcode, '') AS icd9,
		COALESCE(ol.operation_name, '') AS operation_name,
		CASE WHEN ol.an IS NOT NULL AND ol.an != '' THEN 'IPD' ELSE 'ODS' END AS op_type,
		COALESCE(d.cid, '') AS cid,
		COALESCE(d.licenseno, '') AS licenseno,
		COUNT(DISTINCT ol.operation_id) AS case_total,
		COALESCE(SUM(od.total_price), 0) AS inc_total,
		ROUND(COALESCE(SUM(COALESCE(a.rw, i.adjrw, 0)), 0), 4) AS adjrw_total,

		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR COALESCE(ip.hospmain, v.hospmain) IN ('10703','10985','10986','10987','10988','10989','10990')) THEN 1 ELSE 0 END) AS case_ucs_incup,
		SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR COALESCE(ip.hospmain, v.hospmain) IN ('10703','10985','10986','10987','10988','10989','10990')) THEN 1 ELSE 0 END) AS case_ucs_outprov,
		SUM(CASE WHEN p.hipdata_code = 'OFC' THEN 1 ELSE 0 END) AS case_ofc,
		SUM(CASE WHEN p.hipdata_code = 'LGO' THEN 1 ELSE 0 END) AS case_lgo,
		SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN 1 ELSE 0 END) AS case_sss,
		SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN 1 ELSE 0 END) AS case_other,

		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR COALESCE(ip.hospmain, v.hospmain) IN ('10703','10985','10986','10987','10988','10989','10990')) THEN od.total_price ELSE 0 END), 0) AS inc_ucs_incup,
		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('UCS','WEL','DIS') AND NOT (v.pttype_in_chwpart = 'Y' OR hc.chwpart = '37' OR COALESCE(ip.hospmain, v.hospmain) IN ('10703','10985','10986','10987','10988','10989','10990')) THEN od.total_price ELSE 0 END), 0) AS inc_ucs_outprov,
		COALESCE(SUM(CASE WHEN p.hipdata_code = 'OFC' THEN od.total_price ELSE 0 END), 0) AS inc_ofc,
		COALESCE(SUM(CASE WHEN p.hipdata_code = 'LGO' THEN od.total_price ELSE 0 END), 0) AS inc_lgo,
		COALESCE(SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') THEN od.total_price ELSE 0 END), 0) AS inc_sss,
		COALESCE(SUM(CASE WHEN (p.hipdata_code NOT IN ('UCS','WEL','DIS','OFC','LGO','SSS','SSI') OR p.paidst IN ('01','03')) THEN od.total_price ELSE 0 END), 0) AS inc_other
	FROM operation_list ol
	LEFT JOIN operation_detail od ON od.operation_id = ol.operation_id
	LEFT JOIN ipt i ON i.an = ol.an
	LEFT JOIN an_stat a ON a.an = ol.an
	LEFT JOIN ovst ov ON ov.vn = ol.vn
	LEFT JOIN vn_stat v ON v.vn = ol.vn
	LEFT JOIN ipt_pttype ip ON ip.an = ol.an AND ip.pttype_number = 1
	LEFT JOIN pttype p ON p.pttype = COALESCE(i.pttype, ov.pttype)
	LEFT JOIN hospcode hc ON hc.hospcode = COALESCE(ip.hospmain, v.hospmain)
	JOIN doctor d ON (d.code = ol.request_doctor OR d.code = od.doctor)
	WHERE ol.operation_date BETWEEN ? AND ?
	  AND (%s)
	GROUP BY ol.operation_date, od.icdcode, ol.operation_name, op_type, d.cid, d.licenseno
	ORDER BY ol.operation_date ASC`, matchWhere)

	rows, err := db.Query(query, startDate, endDate)
	if err != nil {
		return nil, fmt.Errorf("error querying OPOH Operations: %w", err)
	}
	defer rows.Close()

	var records []OpohOperationRecord
	for rows.Next() {
		var r OpohOperationRecord
		var cid, lic string
		var opDateRaw string

		err := rows.Scan(
			&opDateRaw,
			&r.ICD9,
			&r.OperationName,
			&r.OpType,
			&cid,
			&lic,
			&r.CaseTotal,
			&r.IncTotal,
			&r.AdjrwTotal,
			&r.CaseUCSIncup,
			&r.CaseUCSOutprov,
			&r.CaseOFC,
			&r.CaseLGO,
			&r.CaseSSS,
			&r.CaseOther,
			&r.IncUCSIncup,
			&r.IncUCSOutprov,
			&r.IncOFC,
			&r.IncLGO,
			&r.IncSSS,
			&r.IncOther,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning OPOH Operation row: %w", err)
		}

		if len(opDateRaw) >= 10 {
			r.OperationDate = opDateRaw[:10]
		} else {
			r.OperationDate = opDateRaw
		}

		if strings.TrimSpace(r.OperationName) == "" {
			if r.ICD9 != "" {
				r.OperationName = "Procedure ICD9: " + r.ICD9
			} else {
				r.OperationName = "การผ่าตัด/หัตถการ"
			}
		}

		matchedDoc, found := matchDoctor(cid, lic, cidMap, licMap)
		if found {
			r.DoctorID = matchedDoc.ID
			r.SpcltyCode = matchedDoc.SpcltyCode
			r.SpecialtyName = matchedDoc.SpecialtyName
			r.Licenseno = matchedDoc.Licenseno
		} else {
			r.SpcltyCode = "02"
			r.SpecialtyName = "ศัลยกรรมทั่วไป"
			r.Licenseno = strings.TrimSpace(lic)
		}

		records = append(records, r)
	}

	return records, nil
}
