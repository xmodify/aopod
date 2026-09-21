package collector

import (
	"strings"
	"testing"
)

func TestGetOpdQueryReplacements(t *testing.T) {
	// Set custom province hospcodes
	provCodes := []string{"10703", "10985", "10986", "10987", "10988", "10989", "10990"}
	SetProvinceHospcodes(provCodes)

	ppInClause := "'Z00', 'Z000', 'Z010', 'Z300'"
	hospcode := "10989"

	q := GetOpdQuery(ppInClause, hospcode)

	// Check that tags are properly replaced and none remain unreplaced
	if strings.Contains(q, "{{PP_ICD10_LIST}}") {
		t.Errorf("Expected {{PP_ICD10_LIST}} to be replaced, but found in query")
	}
	if strings.Contains(q, "{{HOSPCODE}}") {
		t.Errorf("Expected {{HOSPCODE}} to be replaced, but found in query")
	}
	if strings.Contains(q, "{{PROVINCE_HOSPCODES}}") {
		t.Errorf("Expected {{PROVINCE_HOSPCODES}} to be replaced, but found in query")
	}

	// Check expected values exist in query
	if !strings.Contains(q, "hospmain = '10989'") {
		t.Errorf("Expected query to contain \"hospmain = '10989'\"")
	}
	if !strings.Contains(q, "hospmain != '10989' AND hospmain IN ('10703', '10985', '10986', '10987', '10988', '10989', '10990')") {
		t.Errorf("Expected query to contain in-province condition")
	}
	if !strings.Contains(q, "hospmain NOT IN ('10703', '10985', '10986', '10987', '10988', '10989', '10990')") {
		t.Errorf("Expected query to contain out-of-province condition")
	}
}

func TestUpdateRemoteQueries(t *testing.T) {
	customSql := "SELECT 'custom_opd_test' AS test_field"
	newVersion := "v_test_9999"

	changed := UpdateRemoteQueries(map[string]string{
		"opd": customSql,
	}, newVersion)

	if !changed {
		t.Errorf("Expected UpdateRemoteQueries to return true on change")
	}

	if GetQueriesVersion() != newVersion {
		t.Errorf("Expected version %s, got %s", newVersion, GetQueriesVersion())
	}

	// Reset back
	UpdateRemoteQueries(map[string]string{
		"opd": defaultOpdQuery,
	}, "initial_default")
}
