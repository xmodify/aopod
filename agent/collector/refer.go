package collector

import (
	"database/sql"
	"fmt"
	"strings"

	"aopod-agent/config"
)

const defaultReferQuery = `SELECT 
	ov.vstdate,
	COUNT(DISTINCT CASE WHEN r_out.referout_inprov = 'Y' THEN ov.vn END) AS visit_referout_inprov,
	COUNT(DISTINCT CASE WHEN r_out.referout_outprov = 'Y' THEN ov.vn END) AS visit_referout_outprov,
	COUNT(DISTINCT CASE WHEN r_out_ipd.referout_inprov_ipd = 'Y' THEN ip.an END) AS visit_referout_inprov_ipd,
	COUNT(DISTINCT CASE WHEN r_out_ipd.referout_outprov_ipd = 'Y' THEN ip.an END) AS visit_referout_outprov_ipd,
	COUNT(DISTINCT CASE WHEN r_in.inprov = 'Y' AND ip.vn IS NULL THEN ov.vn END) AS visit_referin_inprov,
	COUNT(DISTINCT CASE WHEN r_in.outprov = 'Y' AND ip.vn IS NULL THEN ov.vn END) AS visit_referin_outprov,
	COUNT(DISTINCT CASE WHEN r_in.inprov = 'Y' AND ip.vn IS NOT NULL THEN ov.vn END) AS visit_referin_inprov_ipd,
	COUNT(DISTINCT CASE WHEN r_in.outprov = 'Y' AND ip.vn IS NOT NULL THEN ov.vn END) AS visit_referin_outprov_ipd,
	COALESCE(rb.visit_referback_inprov, 0) AS visit_referback_inprov,
	COALESCE(rb.visit_referback_outprov, 0) AS visit_referback_outprov
FROM ovst ov
LEFT JOIN ipt ip ON ip.vn = ov.vn
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
LEFT JOIN (
	SELECT DATE(reply_date_time) as d,
		COUNT(DISTINCT CASE WHEN lh.chwpart = (SELECT chwpart FROM opdconfig LIMIT 1) THEN vn END) as visit_referback_inprov,
		COUNT(DISTINCT CASE WHEN lh.chwpart != (SELECT chwpart FROM opdconfig LIMIT 1) THEN vn END) as visit_referback_outprov
	FROM refer_reply rr
	LEFT JOIN hospcode lh ON lh.hospcode = rr.dest_hospcode
	WHERE DATE(reply_date_time) BETWEEN ? AND ?
	GROUP BY DATE(reply_date_time)
) rb ON rb.d = ov.vstdate
WHERE ov.vstdate BETWEEN ? AND ?
GROUP BY ov.vstdate
ORDER BY ov.vstdate`

type ReferRecord struct {
	Vstdate                 string `json:"vstdate"`
	VisitReferoutInprov     int    `json:"visit_referout_inprov"`
	VisitReferoutOutprov    int    `json:"visit_referout_outprov"`
	VisitReferoutInprovIpd  int    `json:"visit_referout_inprov_ipd"`
	VisitReferoutOutprovIpd int    `json:"visit_referout_outprov_ipd"`
	VisitReferinInprov      int    `json:"visit_referin_inprov"`
	VisitReferinOutprov     int    `json:"visit_referin_outprov"`
	VisitReferinInprovIpd   int    `json:"visit_referin_inprov_ipd"`
	VisitReferinOutprovIpd  int    `json:"visit_referin_outprov_ipd"`
	VisitReferbackInprov    int    `json:"visit_referback_inprov"`
	VisitReferbackOutprov   int    `json:"visit_referback_outprov"`
}

// GetReferQuery returns the active Refer query with province hospcodes replaced.
func GetReferQuery(hospcode string) string {
	queriesMutex.RLock()
	q := currentQueries["refer"]
	provCodes := currentProvinceHospcodes
	queriesMutex.RUnlock()

	if q == "" {
		q = defaultReferQuery
	}

	if hospcode == "" {
		hospcode = config.Get().Hospital.Code
	}
	if provCodes == "" {
		provCodes = "'10703', '10985', '10986', '10987', '10988', '10989', '10990'"
	}

	q = strings.ReplaceAll(q, "{{HOSPCODE}}", hospcode)
	q = strings.ReplaceAll(q, "{{MAIN_HOSPCODE}}", hospcode)
	q = strings.ReplaceAll(q, "{{PROVINCE_HOSPCODES}}", provCodes)

	return q
}

// CollectRefer queries HOSxP and returns aggregated Refer records for date range.
func CollectRefer(db *sql.DB, startDate, endDate string) ([]ReferRecord, error) {
	cfg := config.Get()
	hospcode := cfg.Hospital.Code

	query := GetReferQuery(hospcode)

	rows, err := db.Query(query, startDate, endDate, startDate, endDate)
	if err != nil {
		return nil, fmt.Errorf("error executing Refer query: %w (hospcode: %s)", err, hospcode)
	}
	defer rows.Close()

	var records []ReferRecord
	for rows.Next() {
		var r ReferRecord
		err := rows.Scan(
			&r.Vstdate,
			&r.VisitReferoutInprov,
			&r.VisitReferoutOutprov,
			&r.VisitReferoutInprovIpd,
			&r.VisitReferoutOutprovIpd,
			&r.VisitReferinInprov,
			&r.VisitReferinOutprov,
			&r.VisitReferinInprovIpd,
			&r.VisitReferinOutprovIpd,
			&r.VisitReferbackInprov,
			&r.VisitReferbackOutprov,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning Refer row: %w", err)
		}
		if len(r.Vstdate) >= 10 {
			r.Vstdate = r.Vstdate[:10]
		}
		records = append(records, r)
	}

	return records, nil
}
