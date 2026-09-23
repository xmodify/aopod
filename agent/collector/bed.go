package collector

import (
	"database/sql"
	"fmt"
	"strings"
)

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

// IpdBedDepRecord represents occupied & total beds grouped by department/export_code.
type IpdBedDepRecord struct {
	BedCode string `json:"bed_code"`
	BedQty  int    `json:"bed_qty"`
	BedUse  int    `json:"bed_use"`
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

// CollectIpdBedDepartment queries occupied & total beds grouped by department/export_code directly from HOSxP.
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
