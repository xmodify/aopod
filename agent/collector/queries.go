package collector

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strings"
	"sync"

	"aopod-agent/config"
)

var (
	queriesMutex             sync.RWMutex
	currentQueries           map[string]string
	currentVersion           string
	currentProvinceHospcodes string = "'10703', '10985', '10986', '10987', '10988', '10989', '10990'"
)

type QueryCacheFile struct {
	Version string            `json:"version"`
	Queries map[string]string `json:"queries"`
}

const defaultOpdQuery = `SELECT 
	a.vstdate,
	COUNT(DISTINCT a.hn) AS hn_total,
	COUNT(a.vn) AS visit_total,
	SUM(CASE WHEN a.diagtype = 'OP' THEN 1 ELSE 0 END) AS visit_total_op,
	SUM(CASE WHEN a.diagtype = 'PP' THEN 1 ELSE 0 END) AS visit_total_pp,
	SUM(CASE WHEN a.endpoint = 'Y' THEN 1 ELSE 0 END) AS visit_endpoint,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN 1 ELSE 0 END) AS visit_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN 1 ELSE 0 END) AS visit_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN 1 ELSE 0 END) AS visit_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_sss,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_fss,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_stp,
	SUM(CASE WHEN (a.paidst IN ('01','03') OR a.hipdata_code IN ('A1','A9')) THEN 1 ELSE 0 END) AS visit_pay,
	COUNT(DISTINCT CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.healthmed = 'Y' THEN a.vn END) AS visit_ucs_healthmed,
	COUNT(DISTINCT CASE WHEN a.healthmed = 'Y' THEN a.vn END) AS visit_healthmed,
	COUNT(DISTINCT CASE WHEN a.dent = 'Y' THEN a.vn END) AS visit_dent,
	COUNT(DISTINCT CASE WHEN a.physic = 'Y' THEN a.vn END) AS visit_physic,
	COUNT(DISTINCT CASE WHEN a.anc = 'Y' THEN a.vn END) AS visit_anc,
	COUNT(DISTINCT CASE WHEN a.telehealth = 'Y' THEN a.vn END) AS visit_telehealth,
	COALESCE(ma_booking.cnt, 0) AS visit_moph_oapp_booking,
	COUNT(DISTINCT CASE WHEN a.moph_oapp = 'Y' THEN a.cid END) AS visit_moph_oapp,
	COUNT(DISTINCT CASE WHEN a.referout_inprov = 'Y' THEN a.vn END) AS visit_referout_inprov,
	COUNT(DISTINCT CASE WHEN a.referout_outprov = 'Y' THEN a.vn END) AS visit_referout_outprov,
	COUNT(DISTINCT CASE WHEN a.referout_inprov_ipd = 'Y' THEN a.vn END) AS visit_referout_inprov_ipd,
	COUNT(DISTINCT CASE WHEN a.referout_outprov_ipd = 'Y' THEN a.vn END) AS visit_referout_outprov_ipd,
	COUNT(DISTINCT CASE WHEN a.referin_inprov = 'Y' THEN a.vn END) AS visit_referin_inprov,
	COUNT(DISTINCT CASE WHEN a.referin_outprov = 'Y' THEN a.vn END) AS visit_referin_outprov,
	COUNT(DISTINCT CASE WHEN a.referin_inprov_ipd = 'Y' THEN a.vn END) AS visit_referin_inprov_ipd,
	COUNT(DISTINCT CASE WHEN a.referin_outprov_ipd = 'Y' THEN a.vn END) AS visit_referin_outprov_ipd,
	COALESCE(rb.visit_referback_inprov, 0) AS visit_referback_inprov,
	COALESCE(rb.visit_referback_outprov, 0) AS visit_referback_outprov,
	COALESCE(op.visit_operation, 0) AS visit_operation,
	SUM(a.income) AS inc_total, 
	SUM(a.inc03) AS inc_lab_total, 
	SUM(a.inc12) AS inc_drug_total,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN a.income ELSE 0 END) AS inc_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN a.inc03 ELSE 0 END) AS inc_lab_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN a.inc12 ELSE 0 END) AS inc_drug_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN a.income ELSE 0 END) AS inc_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN a.inc03 ELSE 0 END) AS inc_lab_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN a.inc12 ELSE 0 END) AS inc_drug_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN a.income ELSE 0 END) AS inc_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN a.inc03 ELSE 0 END) AS inc_lab_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN a.inc12 ELSE 0 END) AS inc_drug_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_sss,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_sss,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_sss,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_fss,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_fss,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_fss,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_stp,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_stp,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_stp,
	SUM(CASE WHEN (a.hipdata_code IN ('A1','A9') OR a.paidst IN ('01','03')) THEN a.income ELSE 0 END) AS inc_pay,
	SUM(CASE WHEN (a.hipdata_code IN ('A1','A9') OR a.paidst IN ('01','03')) THEN a.inc03 ELSE 0 END) AS inc_lab_pay,
	SUM(CASE WHEN (a.hipdata_code IN ('A1','A9') OR a.paidst IN ('01','03')) THEN a.inc12 ELSE 0 END) AS inc_drug_pay
FROM (
	SELECT 
		ov.vstdate, ov.vn, ov.hn, v.cid,
		IF(v.pdx IN ({{PP_ICD10_LIST}}) OR v.pdx LIKE 'Z%', 'PP', 'OP') AS diagtype,
		p.hipdata_code, p.paidst,
		COALESCE(v.income, 0) AS income,
		COALESCE(v.inc03, 0) AS inc03,
		COALESCE(v.inc12, 0) AS inc12,
		IF(vp.is_ep = 'Y', 'Y', '') AS endpoint,
		COALESCE(vp.is_incup, '') AS incup,
		COALESCE(vp.is_inprov, '') AS inprov,
		COALESCE(vp.is_outprov, '') AS outprov,
		IF(dt.vn IS NOT NULL, 'Y', '') AS dent,
		IF(pl.vn IS NOT NULL, 'Y', '') AS physic,
		IF(hm.vn IS NOT NULL, 'Y', '') AS healthmed,
		IF(anc.vn IS NOT NULL, 'Y', '') AS anc,
		IF(oi.ovstist = '08', 'Y', '') AS telehealth,
		IF(ma.cid IS NOT NULL, 'Y', '') AS moph_oapp,
		COALESCE(r_out.referout_inprov, '') AS referout_inprov,
		COALESCE(r_out.referout_outprov, '') AS referout_outprov,
		COALESCE(r_out_ipd.referout_inprov_ipd, '') AS referout_inprov_ipd,
		COALESCE(r_out_ipd.referout_outprov_ipd, '') AS referout_outprov_ipd,
		IF(r_in.inprov = 'Y' AND ip.vn IS NULL, 'Y', '') AS referin_inprov,
		IF(r_in.outprov = 'Y' AND ip.vn IS NULL, 'Y', '') AS referin_outprov,
		IF(r_in.inprov = 'Y' AND ip.vn IS NOT NULL, 'Y', '') AS referin_inprov_ipd,
		IF(r_in.outprov = 'Y' AND ip.vn IS NOT NULL, 'Y', '') AS referin_outprov_ipd
	FROM ovst ov
	LEFT JOIN vn_stat v ON v.vn = ov.vn
	LEFT JOIN ipt ip ON ip.vn = ov.vn
	LEFT JOIN pttype p ON p.pttype = ov.pttype
	LEFT JOIN ovstist oi ON oi.ovstist = ov.ovstist
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN hospmain = '{{HOSPCODE}}' THEN 'Y' END) AS is_incup,
			MAX(CASE WHEN hospmain != '{{HOSPCODE}}' AND hospmain IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS is_inprov,
			MAX(CASE WHEN hospmain NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS is_outprov,
			MAX(CASE WHEN auth_code LIKE 'EP%' THEN 'Y' END) AS is_ep
		FROM visit_pttype
		GROUP BY vn
	) vp ON vp.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM dtmain) dt ON dt.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM physic_list) pl ON pl.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM health_med_service) hm ON hm.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM person_anc_service) anc ON anc.vn = ov.vn
	LEFT JOIN (
		SELECT DISTINCT cid, appointment_date
		FROM moph_appointment_list
	) ma ON ma.cid = v.cid AND ma.appointment_date = ov.vstdate
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN refer_hospcode IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_inprov,
			MAX(CASE WHEN refer_hospcode NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_outprov
		FROM referout
		GROUP BY vn
	) r_out ON r_out.vn = ov.vn
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN refer_hospcode IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_inprov_ipd,
			MAX(CASE WHEN refer_hospcode NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_outprov_ipd
		FROM referout
		GROUP BY vn
	) r_out_ipd ON r_out_ipd.vn = ip.an
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN refer_hospcode IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS inprov,
			MAX(CASE WHEN refer_hospcode NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS outprov
		FROM referin
		GROUP BY vn
	) r_in ON r_in.vn = ov.vn
	WHERE ov.vstdate BETWEEN ? AND ?
) a
LEFT JOIN (
	SELECT DATE(reply_date_time) as d,
		COUNT(DISTINCT CASE WHEN lh.chwpart = (SELECT chwpart FROM opdconfig LIMIT 1) THEN vn END) as visit_referback_inprov,
		COUNT(DISTINCT CASE WHEN lh.chwpart != (SELECT chwpart FROM opdconfig LIMIT 1) THEN vn END) as visit_referback_outprov
	FROM referin_reply rr
	LEFT JOIN hospcode lh ON lh.hospcode = rr.reply_hospcode
	WHERE DATE(reply_date_time) BETWEEN ? AND ?
	GROUP BY DATE(reply_date_time)
) rb ON rb.d = a.vstdate
LEFT JOIN (
	SELECT o.operation_request_date as d, COUNT(DISTINCT o.operation_id) as visit_operation
	FROM operation_list o
	WHERE o.operation_request_date BETWEEN ? AND ?
	GROUP BY o.operation_request_date
) op ON op.d = a.vstdate
LEFT JOIN (
	SELECT appointment_date as d, COUNT(DISTINCT cid) as cnt
	FROM moph_appointment_list
	WHERE appointment_date BETWEEN ? AND ?
	GROUP BY appointment_date
) ma_booking ON ma_booking.d = a.vstdate
GROUP BY a.vstdate
ORDER BY a.vstdate`

const defaultIpdQuery = `SELECT 
	dchdate,
	COUNT(DISTINCT an) AS an_total,
	COALESCE(SUM(admdate), 0) AS admdate,
	ROUND((COALESCE(SUM(a.admdate), 0) * 100) / (? * CASE 
		WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) THEN DAY(CURDATE()) 
		ELSE DAY(LAST_DAY(a.dchdate)) 
	END), 2) AS bed_occupancy,
	ROUND((COALESCE(SUM(a.admdate), 0) / CASE 
		WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) THEN DAY(CURDATE()) 
		ELSE DAY(LAST_DAY(a.dchdate)) 
	END), 2) AS active_bed,
	ROUND(COALESCE(SUM(adjrw), 0) / COUNT(DISTINCT an), 2) AS cmi,
	ROUND(COALESCE(SUM(adjrw), 0), 5) AS adjrw,
	COALESCE(SUM(income), 0) AS inc_total,
	COALESCE(SUM(inc03), 0) AS inc_lab_total,
	COALESCE(SUM(inc12), 0) AS inc_drug_total
FROM (
	SELECT a.dchdate, a.an, a.admdate, i.adjrw, a.income, a.inc03, a.inc12
	FROM ipt i
	LEFT JOIN an_stat a ON a.an = i.an
	LEFT JOIN pttype p ON p.pttype = a.pttype
	WHERE a.dchdate BETWEEN ? AND ?
	  AND a.pdx NOT IN ('Z290', 'Z208')
	GROUP BY a.an
) AS a
GROUP BY dchdate
ORDER BY dchdate`

const defaultBedTotalQuery = `SELECT 
	COALESCE((SELECT SUM(bed_count) FROM ward WHERE ward_active = 'Y'), ?) AS bed_qty,
	COALESCE(COUNT(DISTINCT b.bedno), 0) AS bed_use
FROM ipt i 
INNER JOIN iptadm ia ON ia.an = i.an
LEFT JOIN bedno b ON b.bedno = ia.bedno
WHERE i.confirm_discharge = 'N' 
  AND (b.export_code IS NOT NULL AND b.export_code <> '')`

const defaultBedDepQuery = `SELECT 
	IFNULL(b.export_code, 0) AS bed_code,
	IFNULL(COUNT(DISTINCT b.bedno), 0) AS bed_qty,
	IFNULL(b1.bed_use, 0) AS bed_use
FROM bedno b
LEFT JOIN (
	SELECT b.export_code, COUNT(DISTINCT b.bedno) AS bed_use
	FROM ipt i
	INNER JOIN iptadm ia ON ia.an = i.an
	LEFT JOIN bedno b ON b.bedno = ia.bedno
	WHERE b.export_code IS NOT NULL 
	  AND b.export_code <> ''
	  AND i.confirm_discharge = 'N'
	GROUP BY b.export_code
) b1 ON b1.export_code = b.export_code
WHERE b.export_code IS NOT NULL 
  AND b.export_code <> ''
GROUP BY b.export_code 
ORDER BY b.export_code`

func init() {
	currentQueries = map[string]string{
		"opd":       defaultOpdQuery,
		"ipd":       defaultIpdQuery,
		"bed_total": defaultBedTotalQuery,
		"bed_dep":   defaultBedDepQuery,
	}
	currentVersion = "initial_default"
	InitQueriesCache()
}

func getCacheFilePath() string {
	return filepath.Join(config.GetConfigDir(), "queries_cache.json")
}

// InitQueriesCache loads cached queries from disk if present.
func InitQueriesCache() {
	path := getCacheFilePath()
	if data, err := os.ReadFile(path); err == nil {
		var cache QueryCacheFile
		if err := json.Unmarshal(data, &cache); err == nil && len(cache.Queries) > 0 {
			queriesMutex.Lock()
			for k, v := range cache.Queries {
				if strings.TrimSpace(v) != "" {
					currentQueries[k] = v
				}
			}
			currentVersion = cache.Version
			queriesMutex.Unlock()
		}
	}
}

// UpdateRemoteQueries updates the in-memory queries dynamically and saves to disk cache.
func UpdateRemoteQueries(queries map[string]string, version string) bool {
	if len(queries) == 0 {
		return false
	}

	queriesMutex.Lock()
	defer queriesMutex.Unlock()

	if version != "" && version == currentVersion {
		return false // Already latest version
	}

	changed := false
	for k, v := range queries {
		if strings.TrimSpace(v) != "" {
			currentQueries[k] = v
			changed = true
		}
	}

	if version != "" {
		currentVersion = version
	}

	// Persist to local disk cache for offline startup
	cache := QueryCacheFile{
		Version: currentVersion,
		Queries: currentQueries,
	}
	if data, err := json.MarshalIndent(cache, "", "  "); err == nil {
		_ = os.WriteFile(getCacheFilePath(), data, 0644)
	}

	return changed
}

// SetProvinceHospcodes updates the in-province hospital codes list.
func SetProvinceHospcodes(codes []string) {
	if len(codes) == 0 {
		return
	}
	quoted := make([]string, len(codes))
	for i, c := range codes {
		clean := strings.TrimSpace(c)
		quoted[i] = fmt.Sprintf("'%s'", strings.ReplaceAll(clean, "'", "''"))
	}
	queriesMutex.Lock()
	currentProvinceHospcodes = strings.Join(quoted, ", ")
	queriesMutex.Unlock()
}

// GetOpdQuery returns the formatted OPD query with variables replaced.
func GetOpdQuery(ppInClause string, hospcode string) string {
	queriesMutex.RLock()
	q := currentQueries["opd"]
	provCodes := currentProvinceHospcodes
	queriesMutex.RUnlock()

	if q == "" {
		q = defaultOpdQuery
	}

	if ppInClause == "" {
		ppInClause = "'Z00', 'Z000', 'Z010'"
	}
	if hospcode == "" {
		hospcode = config.Get().Hospital.Code
	}
	if provCodes == "" {
		provCodes = "'10703', '10985', '10986', '10987', '10988', '10989', '10990'"
	}

	q = strings.ReplaceAll(q, "{{PP_ICD10_LIST}}", ppInClause)
	q = strings.ReplaceAll(q, "{{HOSPCODE}}", hospcode)
	q = strings.ReplaceAll(q, "{{MAIN_HOSPCODE}}", hospcode)
	q = strings.ReplaceAll(q, "{{PROVINCE_HOSPCODES}}", provCodes)

	return q
}

// GetIpdQuery returns the active IPD query.
func GetIpdQuery() string {
	queriesMutex.RLock()
	defer queriesMutex.RUnlock()
	if q, ok := currentQueries["ipd"]; ok && strings.TrimSpace(q) != "" {
		return q
	}
	return defaultIpdQuery
}

// GetBedTotalQuery returns the active Bed Total query.
func GetBedTotalQuery() string {
	queriesMutex.RLock()
	defer queriesMutex.RUnlock()
	if q, ok := currentQueries["bed_total"]; ok && strings.TrimSpace(q) != "" {
		return q
	}
	return defaultBedTotalQuery
}

// GetBedDepQuery returns the active Bed Department query.
func GetBedDepQuery() string {
	queriesMutex.RLock()
	defer queriesMutex.RUnlock()
	if q, ok := currentQueries["bed_dep"]; ok && strings.TrimSpace(q) != "" {
		return q
	}
	return defaultBedDepQuery
}

// GetQueriesVersion returns current active queries version.
func GetQueriesVersion() string {
	queriesMutex.RLock()
	defer queriesMutex.RUnlock()
	return currentVersion
}
