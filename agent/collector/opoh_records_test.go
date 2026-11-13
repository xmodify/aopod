package collector

import (
	"database/sql"
	"testing"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

// ทดสอบกับฐานข้อมูล HOSxP ทดสอบในเครื่อง (ข้ามอัตโนมัติถ้าเชื่อมต่อไม่ได้)
// ตรวจว่ารายเคส (แบ่งหน้า) รวมกันแล้วตรงกับยอดที่ CollectOpohOPD / CollectOpohIPD สรุปไว้
func openRecordsTestDB(t *testing.T) *sql.DB {
	db, err := sql.Open("mysql", "rims:h10989@tcp(127.0.0.1:3306)/hosxe?charset=utf8mb4,utf8&parseTime=true&loc=Local")
	if err != nil {
		t.Skipf("skip: %v", err)
	}
	if err := db.Ping(); err != nil {
		t.Skipf("skip: database hosxe not reachable: %v", err)
	}
	return db
}

// pickBusyDoctor เลือกแพทย์ที่มีเคสมากที่สุดในช่วงวันที่ของฐานทดสอบ เพื่อให้การเทียบยอดมีความหมาย
func pickBusyDoctor(t *testing.T, db *sql.DB, ipd bool) (OpohDoctor, string, string) {
	q := `SELECT d.cid, d.licenseno, MIN(ov.vstdate), MAX(ov.vstdate)
		FROM ovst ov JOIN doctor d ON d.code = ov.doctor
		WHERE ov.vstdate >= DATE_SUB(CURDATE(), INTERVAL 120 DAY) AND (d.cid IS NOT NULL AND d.cid != '')
		GROUP BY d.cid, d.licenseno ORDER BY COUNT(*) DESC LIMIT 1`
	if ipd {
		q = `SELECT d.cid, d.licenseno, MIN(i.dchdate), MAX(i.dchdate)
		FROM ipt i JOIN doctor d ON ` + ipdDoctorMatchCond("i", "d") + `
		WHERE i.dchdate >= DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND (d.cid IS NOT NULL AND d.cid != '')
		GROUP BY d.cid, d.licenseno ORDER BY COUNT(DISTINCT i.an) DESC LIMIT 1`
	}
	var cid, lic, a, b sql.NullString
	if err := db.QueryRow(q).Scan(&cid, &lic, &a, &b); err != nil {
		t.Skipf("skip: ไม่พบแพทย์ในฐานทดสอบ: %v", err)
	}
	trim := func(s string) string {
		if len(s) >= 10 {
			return s[:10]
		}
		return s
	}
	return OpohDoctor{ID: 999, CID: cid.String, Licenseno: lic.String, SpcltyCode: "01", SpecialtyName: "ทดสอบ"}, trim(a.String), trim(b.String)
}

func TestDoctorRecords_OPD_MatchesSummary(t *testing.T) {
	db := openRecordsTestDB(t)
	defer db.Close()

	doc, start, end := pickBusyDoctor(t, db, false)

	summary, err := CollectOpohOPD(db, []OpohDoctor{doc}, start, end)
	if err != nil {
		t.Fatalf("CollectOpohOPD: %v", err)
	}
	want := 0
	for _, r := range summary {
		want += r.VisitTotal
	}

	t0 := time.Now()
	res, err := CollectOpohDoctorRecords(db, DoctorRecordsParams{
		Type: "opd", CID: doc.CID, Licenseno: doc.Licenseno,
		StartDate: start, EndDate: end, Limit: 50, Summary: true,
	})
	if err != nil {
		t.Fatalf("CollectOpohDoctorRecords: %v", err)
	}
	t.Logf("summary visits=%d, records_total=%d, page rows=%d, รายเคส+สรุป ใช้เวลา %v", want, res.RecordsTotal, len(res.Rows), time.Since(t0))
	if res.RecordsTotal != want {
		t.Errorf("records_total=%d ไม่ตรงกับ visit_total ของตารางสรุป=%d", res.RecordsTotal, want)
	}
	if res.Summary == nil || res.Summary.Cases != want {
		t.Errorf("summary ไม่ถูกต้อง: %+v", res.Summary)
	}
}

func TestDoctorRecords_IPD_MatchesSummary(t *testing.T) {
	db := openRecordsTestDB(t)
	defer db.Close()

	doc, start, end := pickBusyDoctor(t, db, true)

	summary, err := CollectOpohIPD(db, []OpohDoctor{doc}, start, end)
	if err != nil {
		t.Fatalf("CollectOpohIPD: %v", err)
	}
	want := 0
	for _, r := range summary {
		want += r.AnTotal
	}

	res, err := CollectOpohDoctorRecords(db, DoctorRecordsParams{
		Type: "ipd", CID: doc.CID, Licenseno: doc.Licenseno,
		StartDate: start, EndDate: end, Limit: 50, Summary: true,
	})
	if err != nil {
		t.Fatalf("CollectOpohDoctorRecords: %v", err)
	}
	t.Logf("summary an=%d, records_total=%d, page rows=%d", want, res.RecordsTotal, len(res.Rows))
	if res.RecordsTotal != want {
		t.Errorf("records_total=%d ไม่ตรงกับ an_total ของตารางสรุป=%d", res.RecordsTotal, want)
	}
}

func TestIPD_IncludesIptDoctorListOnlyCases(t *testing.T) {
	db := openRecordsTestDB(t)
	defer db.Close()

	// หาเคสที่มีแพทย์บันทึกเฉพาะใน ipt_doctor_list (ไม่ตรงกับ admdoctor/incharge_doctor/dch_doctor)
	var an, dch, cid, lic string
	err := db.QueryRow(`
		SELECT i.an, LEFT(i.dchdate, 10), COALESCE(d.cid, ''), COALESCE(d.licenseno, '')
		FROM ipt_doctor_list idl
		JOIN ipt i ON i.an = idl.an
		JOIN an_stat a ON a.an = i.an
		JOIN doctor d ON d.code = idl.doctor
		WHERE COALESCE(idl.active_doctor, '') != 'N'
		  AND i.dchdate IS NOT NULL
		  AND (d.cid IS NOT NULL AND d.cid != '')
		  AND idl.doctor != COALESCE(i.admdoctor, '')
		  AND idl.doctor != COALESCE(i.incharge_doctor, '')
		  AND idl.doctor != COALESCE(i.dch_doctor, '')
		ORDER BY i.dchdate DESC
		LIMIT 1
	`).Scan(&an, &dch, &cid, &lic)
	if err != nil {
		t.Skipf("skip: ไม่พบเคสตัวอย่างที่มีเฉพาะใน ipt_doctor_list: %v", err)
	}

	doc := OpohDoctor{ID: 999, CID: cid, Licenseno: lic, SpcltyCode: "01", SpecialtyName: "ทดสอบ"}

	// 1. CollectOpohIPD ต้องนับ AN นี้ด้วย
	sumRows, err := CollectOpohIPD(db, []OpohDoctor{doc}, dch, dch)
	if err != nil {
		t.Fatalf("CollectOpohIPD: %v", err)
	}
	sumAn := 0
	for _, r := range sumRows {
		sumAn += r.AnTotal
	}
	if sumAn == 0 {
		t.Errorf("CollectOpohIPD ไม่นับ AN %s ที่บันทึกเฉพาะใน ipt_doctor_list", an)
	}

	// 2. CollectOpohDoctorRecords ต้องเจอ AN นี้และยอดตรงกัน
	recRes, err := CollectOpohDoctorRecords(db, DoctorRecordsParams{
		Type: "ipd", CID: cid, Licenseno: lic,
		StartDate: dch, EndDate: dch, Limit: 200,
	})
	if err != nil {
		t.Fatalf("CollectOpohDoctorRecords: %v", err)
	}
	if recRes.RecordsTotal != sumAn {
		t.Errorf("CollectOpohDoctorRecords total=%d ไม่ตรงกับ CollectOpohIPD=%d", recRes.RecordsTotal, sumAn)
	}
	foundRec := false
	for _, r := range recRes.Rows {
		if r.Key == an {
			foundRec = true
			break
		}
	}
	if !foundRec {
		t.Errorf("CollectOpohDoctorRecords ไม่พบ AN %s จาก ipt_doctor_list", an)
	}

	// 3. CollectOpohIndivExport ต้องเจอ AN นี้พร้อม DoctorName
	indRes, err := CollectOpohIndivExport(db, IndivExportParams{
		Type: "ipd", StartDate: dch, EndDate: dch, Limit: 200,
		Doctors: []IndivDoctor{{CID: cid, Licenseno: lic}},
	})
	if err != nil {
		t.Fatalf("CollectOpohIndivExport: %v", err)
	}
	foundInd := false
	for _, r := range indRes.Rows {
		if r.Key == an {
			foundInd = true
			if r.DoctorName == "" {
				t.Errorf("CollectOpohIndivExport พบ AN %s แต่ DoctorName ว่าง", an)
			}
			break
		}
	}
	if !foundInd {
		t.Errorf("CollectOpohIndivExport ไม่พบ AN %s จาก ipt_doctor_list", an)
	}
}

func TestDoctorRecords_RejectsBadInput(t *testing.T) {
	if _, err := CollectOpohDoctorRecords(nil, DoctorRecordsParams{Type: "x", StartDate: "2025-10-01", EndDate: "2025-10-02"}); err == nil {
		t.Error("ควรปฏิเสธประเภทที่ไม่รู้จัก")
	}
	if _, err := CollectOpohDoctorRecords(nil, DoctorRecordsParams{Type: "opd"}); err == nil {
		t.Error("ควรปฏิเสธเมื่อไม่มีช่วงวันที่")
	}
}

func TestCollectOpohIPDHospitalSummary_OriginExclusionAndDedup(t *testing.T) {
	db := openRecordsTestDB(t)
	defer db.Close()

	doc, start, end := pickBusyDoctor(t, db, true)

	// 1. ยอดระดับโรงพยาบาล (ยังไม่กรองต้นสังกัด) ต้องเท่ากับ COUNT(DISTINCT i.an)
	doc.OriginHospcode = ""
	sumRows, err := CollectOpohIPDHospitalSummary(db, []OpohDoctor{doc}, "10989", start, end)
	if err != nil {
		t.Fatalf("CollectOpohIPDHospitalSummary: %v", err)
	}
	gotAn := 0
	for _, r := range sumRows {
		gotAn += r.AnTotal
	}

	matchWhere, _, _ := buildDoctorMatchClause([]OpohDoctor{doc})
	var wantAn int
	q := `SELECT COUNT(DISTINCT i.an) FROM ipt i JOIN an_stat a ON a.an = i.an
		WHERE i.dchdate BETWEEN ? AND ? AND EXISTS (SELECT 1 FROM doctor d WHERE ` + ipdDoctorMatchCond("i", "d") + ` AND (` + matchWhere + `))`
	if err := db.QueryRow(q, start, end).Scan(&wantAn); err != nil {
		t.Fatalf("count distinct AN: %v", err)
	}
	if gotAn != wantAn {
		t.Errorf("got hospital AnTotal=%d, want distinct AN=%d", gotAn, wantAn)
	}

	// 2. เมื่อแพทย์มี OriginHospcode ตรงกับ hospcode ("10989") ต้องถูกกรองออกทั้งหมด
	doc.OriginHospcode = "10989"
	excludedRows, err := CollectOpohIPDHospitalSummary(db, []OpohDoctor{doc}, "10989", start, end)
	if err != nil {
		t.Fatalf("CollectOpohIPDHospitalSummary excluded: %v", err)
	}
	if len(excludedRows) != 0 {
		t.Errorf("expected 0 rows when doctor OriginHospcode matches hospcode, got %d", len(excludedRows))
	}
}

func TestCollectOpohIndivExport_IPDNoDuplicateAN(t *testing.T) {
	db := openRecordsTestDB(t)
	defer db.Close()

	doc, start, end := pickBusyDoctor(t, db, true)
	res, err := CollectOpohIndivExport(db, IndivExportParams{
		Type: "ipd", StartDate: start, EndDate: end, Limit: 200,
		Doctors: []IndivDoctor{{CID: doc.CID, Licenseno: doc.Licenseno}},
	})
	if err != nil {
		t.Fatalf("CollectOpohIndivExport: %v", err)
	}
	seen := make(map[string]bool)
	for _, r := range res.Rows {
		if seen[r.Key] {
			t.Errorf("พบ AN ซ้ำใน export indiv: %s", r.Key)
		}
		seen[r.Key] = true
	}
}
