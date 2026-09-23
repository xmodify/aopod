package collector

import (
	"database/sql"
	"fmt"
	"strings"

	"aopod-agent/config"
)

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

type IpdRecord struct {
	Dchdate      string  `json:"dchdate"`
	AnTotal      int     `json:"an_total"`
	Admdate      int     `json:"admdate"`
	BedOccupancy float64 `json:"bed_occupancy"`
	ActiveBed    float64 `json:"active_bed"`
	CMI          float64 `json:"cmi"`
	AdjRW        float64 `json:"adjrw"`
	IncTotal     float64 `json:"inc_total"`
	IncLabTotal  float64 `json:"inc_lab_total"`
	IncDrugTotal float64 `json:"inc_drug_total"`
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

// CollectIPD queries HOSxP and returns aggregated IPD records for date range.
func CollectIPD(db *sql.DB, startDate, endDate string) ([]IpdRecord, error) {
	cfg := config.Get()
	hospcode := cfg.Hospital.Code

	var bedQty int
	_ = db.QueryRow("SELECT COALESCE(SUM(bed_count), 30) FROM ward WHERE ward_active = 'Y'").Scan(&bedQty)
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
