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
		FROM ipt i JOIN doctor d ON (d.code = i.admdoctor OR d.code = i.dch_doctor OR d.code = i.incharge_doctor)
		WHERE i.dchdate >= DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND (d.cid IS NOT NULL AND d.cid != '')
		GROUP BY d.cid, d.licenseno ORDER BY COUNT(*) DESC LIMIT 1`
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

func TestDoctorRecords_RejectsBadInput(t *testing.T) {
	if _, err := CollectOpohDoctorRecords(nil, DoctorRecordsParams{Type: "x", StartDate: "2025-10-01", EndDate: "2025-10-02"}); err == nil {
		t.Error("ควรปฏิเสธประเภทที่ไม่รู้จัก")
	}
	if _, err := CollectOpohDoctorRecords(nil, DoctorRecordsParams{Type: "opd"}); err == nil {
		t.Error("ควรปฏิเสธเมื่อไม่มีช่วงวันที่")
	}
}
