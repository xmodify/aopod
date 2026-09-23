package collector

import (
	"database/sql"
	"fmt"
	"strings"

	"aopod-agent/config"
)

const defaultOperationQuery = `SELECT 
	o.request_date AS vstdate,
	COUNT(DISTINCT o.operation_id) AS visit_operation
FROM operation_list o
WHERE o.request_date BETWEEN ? AND ?
GROUP BY o.request_date
ORDER BY o.request_date`

type OperationRecord struct {
	Vstdate        string `json:"vstdate"`
	VisitOperation int    `json:"visit_operation"`
}

// GetOperationQuery returns the active Operation query.
func GetOperationQuery() string {
	queriesMutex.RLock()
	defer queriesMutex.RUnlock()
	if q, ok := currentQueries["operation"]; ok && strings.TrimSpace(q) != "" {
		return q
	}
	return defaultOperationQuery
}

// CollectOperation queries HOSxP and returns aggregated Operation records for date range.
func CollectOperation(db *sql.DB, startDate, endDate string) ([]OperationRecord, error) {
	cfg := config.Get()
	hospcode := cfg.Hospital.Code

	query := GetOperationQuery()

	rows, err := db.Query(query, startDate, endDate)
	if err != nil {
		return nil, fmt.Errorf("error executing Operation query: %w (hospcode: %s)", err, hospcode)
	}
	defer rows.Close()

	var records []OperationRecord
	for rows.Next() {
		var r OperationRecord
		err := rows.Scan(
			&r.Vstdate,
			&r.VisitOperation,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning Operation row: %w", err)
		}
		if len(r.Vstdate) >= 10 {
			r.Vstdate = r.Vstdate[:10]
		}
		records = append(records, r)
	}

	return records, nil
}
