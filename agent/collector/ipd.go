package collector

import (
	"database/sql"
	"fmt"

	"aopod-agent/config"
)

type IpdRecord struct {
	Dchdate       string  `json:"dchdate"`
	AnTotal       int     `json:"an_total"`
	Admdate       int     `json:"admdate"`
	BedOccupancy  float64 `json:"bed_occupancy"`
	ActiveBed     float64 `json:"active_bed"`
	CMI           float64 `json:"cmi"`
	AdjRW         float64 `json:"adjrw"`
	IncTotal      float64 `json:"inc_total"`
	IncLabTotal   float64 `json:"inc_lab_total"`
	IncDrugTotal  float64 `json:"inc_drug_total"`
}

// CollectIPD queries HOSxP and returns aggregated IPD records for date range.
func CollectIPD(db *sql.DB, startDate, endDate string) ([]IpdRecord, error) {
	cfg := config.Get()
	hospcode := cfg.Hospital.Code
	bedQty := cfg.Hospital.BedQty
	if bedQty <= 0 {
		bedQty = 30
	}

	query := GetIpdQuery()

	rows, err := db.Query(query, bedQty, startDate, endDate)
	if err != nil {
		return nil, fmt.Errorf("error executing IPD query: %w (hospcode: %s)", err, hospcode)
	}
	defer rows.Close()

	var records []IpdRecord
	for rows.Next() {
		var r IpdRecord
		err := rows.Scan(
			&r.Dchdate,
			&r.AnTotal,
			&r.Admdate,
			&r.BedOccupancy,
			&r.ActiveBed,
			&r.CMI,
			&r.AdjRW,
			&r.IncTotal,
			&r.IncLabTotal,
			&r.IncDrugTotal,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning IPD row: %w", err)
		}
		if len(r.Dchdate) >= 10 {
			r.Dchdate = r.Dchdate[:10]
		}
		records = append(records, r)
	}

	return records, nil
}
