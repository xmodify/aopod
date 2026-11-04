package collector

import (
	"database/sql"
	"fmt"
	"testing"

	_ "github.com/go-sql-driver/mysql"
)

func TestCollectOpohOPD_ExcludesDialysis(t *testing.T) {
	// Connect to local test database hosxe
	db, err := sql.Open("mysql", "rims:h10989@tcp(127.0.0.1:3306)/hosxe?charset=utf8mb4,utf8&parseTime=true&loc=Local")
	if err != nil {
		t.Fatalf("failed to connect to db: %v", err)
	}
	defer db.Close()

	if err := db.Ping(); err != nil {
		t.Skipf("skipping test: database hosxe not reachable: %v", err)
	}

	// Test with doctor 258 who has known dialysis visits in hosxe
	testDocs := []OpohDoctor{
		{
			ID:            999,
			DoctorName:    "น.ส.รัศญากร มารัตน์",
			CID:           "3341800035642",
			Licenseno:     "พ65086",
			SpcltyCode:    "01",
			SpecialtyName: "อายุรกรรม",
		},
	}

	startDate := "2020-01-01"
	endDate := "2020-09-03"

	records, err := CollectOpohOPD(db, testDocs, startDate, endDate)
	if err != nil {
		t.Fatalf("CollectOpohOPD error: %v", err)
	}

	totalVisits := 0
	for _, r := range records {
		totalVisits += r.VisitTotal
	}

	fmt.Printf("CollectOpohOPD returned %d days of records, total visits = %d\n", len(records), totalVisits)

	// In test_db.php earlier, we verified:
	// Total visits before filter: 5060
	// Dialysis visits (nhso_adp_code = 71641): 1843
	// Non-dialysis visits: 3217
	if totalVisits != 3217 {
		t.Errorf("Expected exactly 3217 non-dialysis visits, got %d", totalVisits)
	} else {
		fmt.Printf("SUCCESS: Exactly 3217 non-dialysis visits returned! (Dialysis visits 1843 were excluded)\n")
	}
}
