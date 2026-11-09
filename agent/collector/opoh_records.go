package collector

import (
	"database/sql"
	"fmt"
	"strings"
)

// DoctorRecordsParams เป็นพารามิเตอร์ของงาน "opoh_doctor_records" ที่เซิร์ฟเวอร์ส่งมา
// (แพทย์ 1 คน ใน รพ. ของ agent นี้ ช่วงวันที่หนึ่ง)
type DoctorRecordsParams struct {
	Type      string `json:"type"`       // "opd" | "ipd"
	CID       string `json:"cid"`        // เลขบัตรประชาชนแพทย์ (ใน HOSxP doctor.cid)
	Licenseno string `json:"licenseno"`  // เลข ว. (doctor.licenseno)
	StartDate string `json:"start_date"` // YYYY-MM-DD
	EndDate   string `json:"end_date"`   // YYYY-MM-DD
	Offset    int    `json:"offset"`
	Limit     int    `json:"limit"`
	Search    string `json:"search"`
	OrderCol  string `json:"order_col"` // ชื่อคอลัมน์ที่อนุญาตเท่านั้น (whitelist)
	OrderDir  string `json:"order_dir"` // "asc" | "desc"
	Summary   bool   `json:"summary"`   // true = คำนวณยอดรวมทั้งช่วงด้วย
}

// DoctorRecordRow เป็น 1 เคส (OPD = 1 VN จาก vn_stat, IPD = 1 AN จาก an_stat)
// ใช้เฉพาะคอลัมน์ของ vn_stat / an_stat (pdx, dx0-dx5, income) จึงไม่ต้อง join รายการค่ารักษา
type DoctorRecordRow struct {
	Key         string  `json:"key"` // VN หรือ AN
	HN          string  `json:"hn"`
	Date        string  `json:"date"` // OPD: vstdate, IPD: dchdate
	Time        string  `json:"time"` // OPD: vsttime, IPD: dchtime
	AdmDate     string  `json:"adm_date,omitempty"`
	PatientName string  `json:"patient_name"`
	Pttype      string  `json:"pttype"`
	Pdx         string  `json:"pdx"`
	DiagName    string  `json:"diag_name"`
	Dx0         string  `json:"dx0"`
	Dx1         string  `json:"dx1"`
	Dx2         string  `json:"dx2"`
	Dx3         string  `json:"dx3"`
	Dx4         string  `json:"dx4"`
	Dx5         string  `json:"dx5"`
	Los         int     `json:"los,omitempty"`
	AdjRW       float64 `json:"adjrw,omitempty"`
	Total       float64 `json:"total"` // ค่ารักษาทั้งหมด (income)
}

type DoctorRecordsSummary struct {
	Cases int     `json:"cases"`
	Total float64 `json:"total"`
}

type DoctorRecordsResult struct {
	Type            string                `json:"type"`
	RecordsTotal    int                   `json:"records_total"`    // ทั้งหมดของแพทย์ในช่วงวันที่ (ไม่รวม search)
	RecordsFiltered int                   `json:"records_filtered"` // หลัง search
	Rows            []DoctorRecordRow     `json:"rows"`
	Summary         *DoctorRecordsSummary `json:"summary,omitempty"`
}

// เงื่อนไขตัดเคสฟอกไต ให้ตรงกับ CollectOpohOPD เพื่อให้ยอดตรงกับตารางสรุป
const opdDialysisExclusion = `
	  AND NOT EXISTS (
		SELECT 1 FROM opitemrece opr_x
		JOIN nondrugitems ndi_x ON ndi_x.icode = opr_x.icode
		WHERE opr_x.vn = ov.vn
		  AND (ndi_x.nhso_adp_code = '71641' OR ndi_x.icd9cm IN ('3995', '39.95'))
	  )
	  AND NOT EXISTS (
		SELECT 1 FROM doctor_operation d_op_x
		WHERE d_op_x.vn = ov.vn AND d_op_x.icd9 IN ('3995', '39.95')
	  )
	  AND NOT EXISTS (
		SELECT 1 FROM ovstdiag od_x
		WHERE od_x.vn = ov.vn AND od_x.icd10 IN ('3995', '39.95')
	  )`

// doctorMatchSQL สร้างเงื่อนไขจับคู่แพทย์จาก cid / licenseno ด้วย placeholder
func doctorMatchSQL(alias, cid, lic string) (string, []interface{}, error) {
	cid = strings.TrimSpace(cid)
	lic = strings.TrimSpace(lic)
	var parts []string
	var args []interface{}
	if cid != "" {
		parts = append(parts, alias+".cid = ?")
		args = append(args, cid)
	}
	if lic != "" {
		parts = append(parts, alias+".licenseno = ?")
		args = append(args, lic)
	}
	if len(parts) == 0 {
		return "", nil, fmt.Errorf("ไม่มีเลขบัตรประชาชนหรือเลข ว. ของแพทย์")
	}
	return "(" + strings.Join(parts, " OR ") + ")", args, nil
}

func nullStr(s sql.NullString) string {
	if s.Valid {
		return s.String
	}
	return ""
}

func trimDate(s string) string {
	if len(s) >= 10 {
		return s[:10]
	}
	return s
}

func likeEscape(s string) string {
	r := strings.NewReplacer(`\`, `\\`, `%`, `\%`, `_`, `\_`)
	return "%" + r.Replace(strings.TrimSpace(s)) + "%"
}

// CollectOpohDoctorRecords ดึงรายเคส (server-side paging) ของแพทย์ 1 คน
func CollectOpohDoctorRecords(db *sql.DB, p DoctorRecordsParams) (*DoctorRecordsResult, error) {
	if p.Type != "opd" && p.Type != "ipd" {
		return nil, fmt.Errorf("ประเภทบริการไม่ถูกต้อง")
	}
	if p.StartDate == "" || p.EndDate == "" {
		return nil, fmt.Errorf("ต้องระบุช่วงวันที่")
	}
	if p.Limit <= 0 || p.Limit > 1000 {
		p.Limit = 50
	}
	if p.Offset < 0 {
		p.Offset = 0
	}
	if p.Type == "opd" {
		return collectDoctorRecordsOPD(db, p)
	}
	return collectDoctorRecordsIPD(db, p)
}

// addSearch ต่อเงื่อนไขค้นหา (placeholder ทั้งหมด) แล้วคืน where/args ใหม่
// alias: ตาราง stat (v หรือ a), hnCol/keyCol: คอลัมน์ HN และ VN/AN
func addSearch(where string, args []interface{}, search, hnCol, keyCol, stat string) (string, []interface{}) {
	s := strings.TrimSpace(search)
	if s == "" {
		return where, args
	}
	like := likeEscape(s)
	conds := []string{
		hnCol + " LIKE ?", keyCol + " LIKE ?", "pt.fname LIKE ?", "pt.lname LIKE ?",
		"CONCAT(COALESCE(pt.fname,''),' ',COALESCE(pt.lname,'')) LIKE ?",
		"ic.name LIKE ?", "p.name LIKE ?",
		stat + ".pdx LIKE ?",
		stat + ".dx0 LIKE ?", stat + ".dx1 LIKE ?", stat + ".dx2 LIKE ?",
		stat + ".dx3 LIKE ?", stat + ".dx4 LIKE ?", stat + ".dx5 LIKE ?",
	}
	out := append([]interface{}{}, args...)
	for range conds {
		out = append(out, like)
	}
	return where + " AND (" + strings.Join(conds, " OR ") + ")", out
}

func orderDir(d string) string {
	if strings.EqualFold(d, "desc") {
		return "DESC"
	}
	return "ASC"
}

// ---------------------------------------------------------------- OPD

func collectDoctorRecordsOPD(db *sql.DB, p DoctorRecordsParams) (*DoctorRecordsResult, error) {
	docWhere, docArgs, err := doctorMatchSQL("d", p.CID, p.Licenseno)
	if err != nil {
		return nil, err
	}

	baseFrom := `FROM ovst ov
		JOIN vn_stat v ON v.vn = ov.vn
		JOIN doctor d ON d.code = ov.doctor
		JOIN patient pt ON pt.hn = ov.hn
		LEFT JOIN pttype p ON p.pttype = ov.pttype
		LEFT JOIN icd101 ic ON ic.code = v.pdx`
	baseWhere := `WHERE ov.vstdate BETWEEN ? AND ? AND ` + docWhere + opdDialysisExclusion
	baseArgs := append([]interface{}{p.StartDate, p.EndDate}, docArgs...)

	res := &DoctorRecordsResult{Type: "opd", Rows: []DoctorRecordRow{}}

	// รวมทั้งหมด (ไม่รวม search) + ยอดค่ารักษารวม ใน query เดียว
	var total sql.NullFloat64
	if err := db.QueryRow("SELECT COUNT(ov.vn), COALESCE(SUM(v.income),0) "+baseFrom+" "+baseWhere, baseArgs...).Scan(&res.RecordsTotal, &total); err != nil {
		return nil, fmt.Errorf("นับจำนวนเคส OPD ไม่สำเร็จ: %w", err)
	}
	if p.Summary {
		res.Summary = &DoctorRecordsSummary{Cases: res.RecordsTotal, Total: total.Float64}
	}

	pageWhere, pageArgs := addSearch(baseWhere, baseArgs, p.Search, "ov.hn", "ov.vn", "v")
	if strings.TrimSpace(p.Search) != "" {
		if err := db.QueryRow("SELECT COUNT(ov.vn) "+baseFrom+" "+pageWhere, pageArgs...).Scan(&res.RecordsFiltered); err != nil {
			return nil, fmt.Errorf("นับจำนวนเคส OPD (ค้นหา) ไม่สำเร็จ: %w", err)
		}
	} else {
		res.RecordsFiltered = res.RecordsTotal
	}

	orderCols := map[string]string{
		"date":    "ov.vstdate %[1]s, ov.vsttime %[1]s",
		"hn":      "ov.hn %s",
		"patient": "pt.fname %[1]s, pt.lname %[1]s",
		"pttype":  "p.name %s",
		"pdx":     "v.pdx %s",
		"key":     "ov.vn %s",
		"total":   "v.income %s",
	}
	tpl, ok := orderCols[p.OrderCol]
	if !ok {
		tpl = orderCols["date"]
	}
	orderBy := fmt.Sprintf(tpl, orderDir(p.OrderDir)) + ", ov.vn ASC"

	pageSQL := `SELECT ov.vn, ov.hn, ov.vstdate, ov.vsttime,
			TRIM(CONCAT(COALESCE(pt.pname,''), COALESCE(pt.fname,''), ' ', COALESCE(pt.lname,''))) AS patient_name,
			p.name AS pttype_name, v.pdx, ic.name AS diag_name,
			v.dx0, v.dx1, v.dx2, v.dx3, v.dx4, v.dx5, COALESCE(v.income, 0) ` +
		baseFrom + " " + pageWhere + " ORDER BY " + orderBy + " LIMIT ? OFFSET ?"
	args := append(append([]interface{}{}, pageArgs...), p.Limit, p.Offset)

	rows, err := db.Query(pageSQL, args...)
	if err != nil {
		return nil, fmt.Errorf("ดึงรายเคส OPD ไม่สำเร็จ: %w", err)
	}
	defer rows.Close()
	for rows.Next() {
		var vn, hn, vdate, vtime, pname, pttype, pdx, dname, d0, d1, d2, d3, d4, d5 sql.NullString
		var income float64
		if err := rows.Scan(&vn, &hn, &vdate, &vtime, &pname, &pttype, &pdx, &dname, &d0, &d1, &d2, &d3, &d4, &d5, &income); err != nil {
			return nil, err
		}
		res.Rows = append(res.Rows, DoctorRecordRow{
			Key: nullStr(vn), HN: nullStr(hn), Date: trimDate(nullStr(vdate)), Time: nullStr(vtime),
			PatientName: nullStr(pname), Pttype: nullStr(pttype), Pdx: nullStr(pdx), DiagName: nullStr(dname),
			Dx0: nullStr(d0), Dx1: nullStr(d1), Dx2: nullStr(d2), Dx3: nullStr(d3), Dx4: nullStr(d4), Dx5: nullStr(d5),
			Total: income,
		})
	}
	return res, rows.Err()
}

// ---------------------------------------------------------------- IPD

func collectDoctorRecordsIPD(db *sql.DB, p DoctorRecordsParams) (*DoctorRecordsResult, error) {
	docWhere, docArgs, err := doctorMatchSQL("d", p.CID, p.Licenseno)
	if err != nil {
		return nil, err
	}

	// จับคู่แพทย์เหมือน CollectOpohIPD (admdoctor / dch_doctor / incharge_doctor) แต่ใช้ EXISTS กันนับซ้ำ
	baseFrom := `FROM ipt i
		JOIN an_stat a ON a.an = i.an
		JOIN patient pt ON pt.hn = i.hn
		LEFT JOIN pttype p ON p.pttype = i.pttype
		LEFT JOIN icd101 ic ON ic.code = a.pdx`
	baseWhere := `WHERE i.dchdate BETWEEN ? AND ?
		AND EXISTS (SELECT 1 FROM doctor d
			WHERE (d.code = i.admdoctor OR d.code = i.dch_doctor OR d.code = i.incharge_doctor)
			  AND ` + docWhere + `)`
	baseArgs := append([]interface{}{p.StartDate, p.EndDate}, docArgs...)

	res := &DoctorRecordsResult{Type: "ipd", Rows: []DoctorRecordRow{}}

	var total sql.NullFloat64
	if err := db.QueryRow("SELECT COUNT(i.an), COALESCE(SUM(a.income),0) "+baseFrom+" "+baseWhere, baseArgs...).Scan(&res.RecordsTotal, &total); err != nil {
		return nil, fmt.Errorf("นับจำนวนเคส IPD ไม่สำเร็จ: %w", err)
	}
	if p.Summary {
		res.Summary = &DoctorRecordsSummary{Cases: res.RecordsTotal, Total: total.Float64}
	}

	pageWhere, pageArgs := addSearch(baseWhere, baseArgs, p.Search, "i.hn", "i.an", "a")
	if strings.TrimSpace(p.Search) != "" {
		if err := db.QueryRow("SELECT COUNT(i.an) "+baseFrom+" "+pageWhere, pageArgs...).Scan(&res.RecordsFiltered); err != nil {
			return nil, fmt.Errorf("นับจำนวนเคส IPD (ค้นหา) ไม่สำเร็จ: %w", err)
		}
	} else {
		res.RecordsFiltered = res.RecordsTotal
	}

	orderCols := map[string]string{
		"date":    "i.dchdate %[1]s, i.dchtime %[1]s",
		"adm":     "i.regdate %[1]s, i.regtime %[1]s",
		"hn":      "i.hn %s",
		"patient": "pt.fname %[1]s, pt.lname %[1]s",
		"pttype":  "p.name %s",
		"pdx":     "a.pdx %s",
		"key":     "i.an %s",
		"los":     "COALESCE(a.admdate, a.los, 1) %s",
		"adjrw":   "COALESCE(a.rw, i.adjrw, 0) %s",
		"total":   "a.income %s",
	}
	tpl, ok := orderCols[p.OrderCol]
	if !ok {
		tpl = orderCols["date"]
	}
	orderBy := fmt.Sprintf(tpl, orderDir(p.OrderDir)) + ", i.an ASC"

	pageSQL := `SELECT i.an, i.hn, i.regdate, i.dchdate, i.dchtime,
			TRIM(CONCAT(COALESCE(pt.pname,''), COALESCE(pt.fname,''), ' ', COALESCE(pt.lname,''))) AS patient_name,
			p.name AS pttype_name, a.pdx, ic.name AS diag_name,
			a.dx0, a.dx1, a.dx2, a.dx3, a.dx4, a.dx5,
			COALESCE(a.admdate, a.los, 1) AS los, COALESCE(a.rw, i.adjrw, 0) AS adjrw, COALESCE(a.income, 0) ` +
		baseFrom + " " + pageWhere + " ORDER BY " + orderBy + " LIMIT ? OFFSET ?"
	args := append(append([]interface{}{}, pageArgs...), p.Limit, p.Offset)

	rows, err := db.Query(pageSQL, args...)
	if err != nil {
		return nil, fmt.Errorf("ดึงรายเคส IPD ไม่สำเร็จ: %w", err)
	}
	defer rows.Close()
	for rows.Next() {
		var an, hn, reg, dch, dtime, pname, pttype, pdx, dname, d0, d1, d2, d3, d4, d5 sql.NullString
		var los int
		var adjrw, income float64
		if err := rows.Scan(&an, &hn, &reg, &dch, &dtime, &pname, &pttype, &pdx, &dname, &d0, &d1, &d2, &d3, &d4, &d5, &los, &adjrw, &income); err != nil {
			return nil, err
		}
		res.Rows = append(res.Rows, DoctorRecordRow{
			Key: nullStr(an), HN: nullStr(hn), AdmDate: trimDate(nullStr(reg)),
			Date: trimDate(nullStr(dch)), Time: nullStr(dtime),
			PatientName: nullStr(pname), Pttype: nullStr(pttype), Pdx: nullStr(pdx), DiagName: nullStr(dname),
			Dx0: nullStr(d0), Dx1: nullStr(d1), Dx2: nullStr(d2), Dx3: nullStr(d3), Dx4: nullStr(d4), Dx5: nullStr(d5),
			Los: los, AdjRW: adjrw, Total: income,
		})
	}
	return res, rows.Err()
}
