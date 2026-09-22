package collector

import (
	"database/sql"
	"fmt"

	"aopod-agent/config"
)

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
