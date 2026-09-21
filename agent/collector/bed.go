package collector

import (
	"database/sql"
	"fmt"

	"aopod-agent/config"
)

type HospitalConfigRecord struct {
	Hospcode string `json:"hospcode"`
	BedQty   int    `json:"bed_qty"`
	BedUse   int    `json:"bed_use"`
}

type IpdBedDepRecord struct {
	BedCode string `json:"bed_code"`
	BedQty  int    `json:"bed_qty"`
	BedUse  int    `json:"bed_use"`
}

// CollectHospitalBed queries total active beds and occupied beds in real-time.
func CollectHospitalBed(db *sql.DB) ([]HospitalConfigRecord, error) {
	cfg := config.Get()
	hospcode := cfg.Hospital.Code
	configBedQty := cfg.Hospital.BedQty

	query := GetBedTotalQuery()

	var bedQty, bedUse int
	err := db.QueryRow(query, configBedQty).Scan(&bedQty, &bedUse)
	if err != nil {
		// Fallback simple active inpatient count
		_ = db.QueryRow("SELECT COUNT(*) FROM ipt WHERE confirm_discharge = 'N'").Scan(&bedUse)
		bedQty = configBedQty
	}

	if bedQty <= 0 {
		bedQty = configBedQty
	}

	return []HospitalConfigRecord{
		{
			Hospcode: hospcode,
			BedQty:   bedQty,
			BedUse:   bedUse,
		},
	}, nil
}

// CollectIpdBedDepartment queries occupied & total beds grouped by department/export_code.
func CollectIpdBedDepartment(db *sql.DB) ([]IpdBedDepRecord, error) {
	query := GetBedDepQuery()

	rows, err := db.Query(query)
	if err != nil {
		return nil, fmt.Errorf("error querying bed department: %w", err)
	}
	defer rows.Close()

	var records []IpdBedDepRecord
	for rows.Next() {
		var r IpdBedDepRecord
		if err := rows.Scan(&r.BedCode, &r.BedQty, &r.BedUse); err != nil {
			return nil, err
		}
		records = append(records, r)
	}

	return records, nil
}
