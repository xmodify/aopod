package collector

import (
	"database/sql"
	"fmt"
	"strings"

	"aopod-agent/config"
	"aopod-agent/lookups"
)

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
		IF(ma.cid IS NOT NULL, 'Y', '') AS moph_oapp
	FROM ovst ov
	LEFT JOIN vn_stat v ON v.vn = ov.vn
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
	WHERE ov.vstdate BETWEEN ? AND ?
) a
LEFT JOIN (
	SELECT appointment_date as d, COUNT(DISTINCT cid) as cnt
	FROM moph_appointment_list
	WHERE appointment_date BETWEEN ? AND ?
	GROUP BY appointment_date
) ma_booking ON ma_booking.d = a.vstdate
GROUP BY a.vstdate
ORDER BY a.vstdate`

type OpdRecord struct {
	Vstdate              string  `json:"vstdate"`
	HNTotal              int     `json:"hn_total"`
	VisitTotal           int     `json:"visit_total"`
	VisitTotalOp         int     `json:"visit_total_op"`
	VisitTotalPp         int     `json:"visit_total_pp"`
	VisitEndpoint        int     `json:"visit_endpoint"`
	VisitUcsIncup        int     `json:"visit_ucs_incup"`
	VisitUcsInprov       int     `json:"visit_ucs_inprov"`
	VisitUcsOutprov      int     `json:"visit_ucs_outprov"`
	VisitOfc             int     `json:"visit_ofc"`
	VisitBkk             int     `json:"visit_bkk"`
	VisitBmt             int     `json:"visit_bmt"`
	VisitSss             int     `json:"visit_sss"`
	VisitLgo             int     `json:"visit_lgo"`
	VisitFss             int     `json:"visit_fss"`
	VisitStp             int     `json:"visit_stp"`
	VisitPay             int     `json:"visit_pay"`
	VisitUcsHealthmed    int     `json:"visit_ucs_healthmed"`
	VisitHealthmed       int     `json:"visit_healthmed"`
	VisitDent            int     `json:"visit_dent"`
	VisitPhysic          int     `json:"visit_physic"`
	VisitAnc             int     `json:"visit_anc"`
	VisitTelehealth      int     `json:"visit_telehealth"`
	VisitMophOappBooking int     `json:"visit_moph_oapp_booking"`
	VisitMophOapp        int     `json:"visit_moph_oapp"`
	IncTotal             float64 `json:"inc_total"`
	IncLabTotal          float64 `json:"inc_lab_total"`
	IncDrugTotal         float64 `json:"inc_drug_total"`
	IncUcsIncup          float64 `json:"inc_ucs_incup"`
	IncLabUcsIncup       float64 `json:"inc_lab_ucs_incup"`
	IncDrugUcsIncup      float64 `json:"inc_drug_ucs_incup"`
	IncUcsInprov         float64 `json:"inc_ucs_inprov"`
	IncLabUcsInprov      float64 `json:"inc_lab_ucs_inprov"`
	IncDrugUcsInprov     float64 `json:"inc_drug_ucs_inprov"`
	IncUcsOutprov        float64 `json:"inc_ucs_outprov"`
	IncLabUcsOutprov     float64 `json:"inc_lab_ucs_outprov"`
	IncDrugUcsOutprov    float64 `json:"inc_drug_ucs_outprov"`
	IncOfc               float64 `json:"inc_ofc"`
	IncLabOfc            float64 `json:"inc_lab_ofc"`
	IncDrugOfc           float64 `json:"inc_drug_ofc"`
	IncBkk               float64 `json:"inc_bkk"`
	IncLabBkk            float64 `json:"inc_lab_bkk"`
	IncDrugBkk           float64 `json:"inc_drug_bkk"`
	IncBmt               float64 `json:"inc_bmt"`
	IncLabBmt            float64 `json:"inc_lab_bmt"`
	IncDrugBmt           float64 `json:"inc_drug_bmt"`
	IncSss               float64 `json:"inc_sss"`
	IncLabSss            float64 `json:"inc_lab_sss"`
	IncDrugSss           float64 `json:"inc_drug_sss"`
	IncLgo               float64 `json:"inc_lgo"`
	IncLabLgo            float64 `json:"inc_lab_lgo"`
	IncDrugLgo           float64 `json:"inc_drug_lgo"`
	IncFss               float64 `json:"inc_fss"`
	IncLabFss            float64 `json:"inc_lab_fss"`
	IncDrugFss           float64 `json:"inc_drug_fss"`
	IncStp               float64 `json:"inc_stp"`
	IncLabStp            float64 `json:"inc_lab_stp"`
	IncDrugStp           float64 `json:"inc_drug_stp"`
	IncPay               float64 `json:"inc_pay"`
	IncLabPay            float64 `json:"inc_lab_pay"`
	IncDrugPay           float64 `json:"inc_drug_pay"`
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

// CollectOPD queries HOSxP and returns aggregated OPD records for date range.
func CollectOPD(db *sql.DB, startDate, endDate string) ([]OpdRecord, error) {
	cfg := config.Get()
	hospcode := cfg.Hospital.Code

	// Build PP ICD-10 IN clause
	ppCodes := lookups.GetPPList()
	ppInClause := "'Z00', 'Z000', 'Z010'"
	if len(ppCodes) > 0 {
		quoted := make([]string, len(ppCodes))
		for i, c := range ppCodes {
			quoted[i] = fmt.Sprintf("'%s'", strings.ReplaceAll(c, "'", "''"))
		}
		ppInClause = strings.Join(quoted, ",")
	}

	query := GetOpdQuery(ppInClause, hospcode)

	paramCount := strings.Count(query, "?")
	args := make([]interface{}, paramCount)
	for i := 0; i < paramCount; i += 2 {
		args[i] = startDate
		if i+1 < paramCount {
			args[i+1] = endDate
		}
	}

	rows, err := db.Query(query, args...)
	if err != nil {
		return nil, fmt.Errorf("error executing OPD query: %w", err)
	}
	defer rows.Close()

	cols, err := rows.Columns()
	if err != nil {
		return nil, fmt.Errorf("error getting OPD columns: %w", err)
	}

	var records []OpdRecord
	for rows.Next() {
		var r OpdRecord
		scanArgs := make([]interface{}, len(cols))
		for i, col := range cols {
			switch strings.ToLower(col) {
			case "vstdate":
				scanArgs[i] = &r.Vstdate
			case "hn_total":
				scanArgs[i] = &r.HNTotal
			case "visit_total":
				scanArgs[i] = &r.VisitTotal
			case "visit_total_op":
				scanArgs[i] = &r.VisitTotalOp
			case "visit_total_pp":
				scanArgs[i] = &r.VisitTotalPp
			case "visit_endpoint":
				scanArgs[i] = &r.VisitEndpoint
			case "visit_ucs_incup":
				scanArgs[i] = &r.VisitUcsIncup
			case "visit_ucs_inprov":
				scanArgs[i] = &r.VisitUcsInprov
			case "visit_ucs_outprov":
				scanArgs[i] = &r.VisitUcsOutprov
			case "visit_ofc":
				scanArgs[i] = &r.VisitOfc
			case "visit_bkk":
				scanArgs[i] = &r.VisitBkk
			case "visit_bmt":
				scanArgs[i] = &r.VisitBmt
			case "visit_sss":
				scanArgs[i] = &r.VisitSss
			case "visit_lgo":
				scanArgs[i] = &r.VisitLgo
			case "visit_fss":
				scanArgs[i] = &r.VisitFss
			case "visit_stp":
				scanArgs[i] = &r.VisitStp
			case "visit_pay":
				scanArgs[i] = &r.VisitPay
			case "visit_ucs_healthmed":
				scanArgs[i] = &r.VisitUcsHealthmed
			case "visit_healthmed":
				scanArgs[i] = &r.VisitHealthmed
			case "visit_dent":
				scanArgs[i] = &r.VisitDent
			case "visit_physic":
				scanArgs[i] = &r.VisitPhysic
			case "visit_anc":
				scanArgs[i] = &r.VisitAnc
			case "visit_telehealth":
				scanArgs[i] = &r.VisitTelehealth
			case "visit_moph_oapp_booking":
				scanArgs[i] = &r.VisitMophOappBooking
			case "visit_moph_oapp":
				scanArgs[i] = &r.VisitMophOapp
			case "inc_total":
				scanArgs[i] = &r.IncTotal
			case "inc_lab_total":
				scanArgs[i] = &r.IncLabTotal
			case "inc_drug_total":
				scanArgs[i] = &r.IncDrugTotal
			case "inc_ucs_incup":
				scanArgs[i] = &r.IncUcsIncup
			case "inc_lab_ucs_incup":
				scanArgs[i] = &r.IncLabUcsIncup
			case "inc_drug_ucs_incup":
				scanArgs[i] = &r.IncDrugUcsIncup
			case "inc_ucs_inprov":
				scanArgs[i] = &r.IncUcsInprov
			case "inc_lab_ucs_inprov":
				scanArgs[i] = &r.IncLabUcsInprov
			case "inc_drug_ucs_inprov":
				scanArgs[i] = &r.IncDrugUcsInprov
			case "inc_ucs_outprov":
				scanArgs[i] = &r.IncUcsOutprov
			case "inc_lab_ucs_outprov":
				scanArgs[i] = &r.IncLabUcsOutprov
			case "inc_drug_ucs_outprov":
				scanArgs[i] = &r.IncDrugUcsOutprov
			case "inc_ofc":
				scanArgs[i] = &r.IncOfc
			case "inc_lab_ofc":
				scanArgs[i] = &r.IncLabOfc
			case "inc_drug_ofc":
				scanArgs[i] = &r.IncDrugOfc
			case "inc_bkk":
				scanArgs[i] = &r.IncBkk
			case "inc_lab_bkk":
				scanArgs[i] = &r.IncLabBkk
			case "inc_drug_bkk":
				scanArgs[i] = &r.IncDrugBkk
			case "inc_bmt":
				scanArgs[i] = &r.IncBmt
			case "inc_lab_bmt":
				scanArgs[i] = &r.IncLabBmt
			case "inc_drug_bmt":
				scanArgs[i] = &r.IncDrugBmt
			case "inc_sss":
				scanArgs[i] = &r.IncSss
			case "inc_lab_sss":
				scanArgs[i] = &r.IncLabSss
			case "inc_drug_sss":
				scanArgs[i] = &r.IncDrugSss
			case "inc_lgo":
				scanArgs[i] = &r.IncLgo
			case "inc_lab_lgo":
				scanArgs[i] = &r.IncLabLgo
			case "inc_drug_lgo":
				scanArgs[i] = &r.IncDrugLgo
			case "inc_fss":
				scanArgs[i] = &r.IncFss
			case "inc_lab_fss":
				scanArgs[i] = &r.IncLabFss
			case "inc_drug_fss":
				scanArgs[i] = &r.IncDrugFss
			case "inc_stp":
				scanArgs[i] = &r.IncStp
			case "inc_lab_stp":
				scanArgs[i] = &r.IncLabStp
			case "inc_drug_stp":
				scanArgs[i] = &r.IncDrugStp
			case "inc_pay":
				scanArgs[i] = &r.IncPay
			case "inc_lab_pay":
				scanArgs[i] = &r.IncLabPay
			case "inc_drug_pay":
				scanArgs[i] = &r.IncDrugPay
			default:
				var dummy interface{}
				scanArgs[i] = &dummy
			}
		}
		if err := rows.Scan(scanArgs...); err != nil {
			return nil, fmt.Errorf("error scanning OPD row: %w", err)
		}
		if len(r.Vstdate) >= 10 {
			r.Vstdate = r.Vstdate[:10]
		}
		records = append(records, r)
	}

	return records, nil
}
