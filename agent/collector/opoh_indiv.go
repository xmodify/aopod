package collector

import (
	"database/sql"
	"fmt"
	"sort"
	"strings"
)

// IndivDoctor คือแพทย์ที่อยู่ในรายชื่อ OPOH (ส่งมาจากเซิร์ฟเวอร์) ใช้จับคู่กับ doctor ใน HOSxP ด้วย cid / ว.
type IndivDoctor struct {
	CID       string `json:"cid"`
	Licenseno string `json:"licenseno"`
}

// IndivExportParams เป็นพารามิเตอร์ของงาน "opoh_indiv_export" (รายเคสทุกแพทย์ใน รพ. แบ่งก้อน)
type IndivExportParams struct {
	Type         string        `json:"type"` // "opd" | "ipd"
	StartDate    string        `json:"start_date"`
	EndDate      string        `json:"end_date"`
	Offset       int           `json:"offset"`
	Limit        int           `json:"limit"`
	HospitalName string        `json:"hospital_name"`
	Doctors      []IndivDoctor `json:"doctors"`
}

type IndivCategory struct {
	ID   int    `json:"id"`
	Name string `json:"name"`
}

type IndivRow struct {
	Key          string             `json:"key"`
	HN           string             `json:"hn"`
	Date         string             `json:"date"`
	Time         string             `json:"time"`
	AdmDate      string             `json:"adm_date,omitempty"`
	PatientName  string             `json:"patient_name"`
	Pttype       string             `json:"pttype"`
	Pdx          string             `json:"pdx"`
	DiagName     string             `json:"diag_name"`
	Dx0          string             `json:"dx0"`
	Dx1          string             `json:"dx1"`
	Dx2          string             `json:"dx2"`
	Dx3          string             `json:"dx3"`
	Dx4          string             `json:"dx4"`
	Dx5          string             `json:"dx5"`
	Los          int                `json:"los,omitempty"`
	AdjRW        float64            `json:"adjrw,omitempty"`
	DoctorName   string             `json:"doctor_name"`
	HospitalName string             `json:"hospital_name"`
	Total        float64            `json:"total"`
	Cats         map[string]float64 `json:"cats"` // drg_chrgitem_id -> ยอดรวมของเคสนี้
}

type IndivExportResult struct {
	Type         string          `json:"type"`
	RecordsTotal int             `json:"records_total"`
	Categories   []IndivCategory `json:"categories"`
	Rows         []IndivRow      `json:"rows"`
}

// indivDoctorWhere สร้างเงื่อนไข (alias.cid IN (...) OR alias.licenseno IN (...))
func indivDoctorWhere(alias string, docs []IndivDoctor) (string, []interface{}, error) {
	var cids, lics []interface{}
	seenC, seenL := map[string]bool{}, map[string]bool{}
	for _, d := range docs {
		if c := strings.TrimSpace(d.CID); c != "" && !seenC[c] {
			seenC[c] = true
			cids = append(cids, c)
		}
		if l := strings.TrimSpace(d.Licenseno); l != "" && !seenL[l] {
			seenL[l] = true
			lics = append(lics, l)
		}
	}
	var parts []string
	var args []interface{}
	ph := func(n int) string { return strings.TrimSuffix(strings.Repeat("?,", n), ",") }
	if len(cids) > 0 {
		parts = append(parts, alias+".cid IN ("+ph(len(cids))+")")
		args = append(args, cids...)
	}
	if len(lics) > 0 {
		parts = append(parts, alias+".licenseno IN ("+ph(len(lics))+")")
		args = append(args, lics...)
	}
	if len(parts) == 0 {
		return "", nil, fmt.Errorf("ไม่มีรายชื่อแพทย์ที่มีเลขบัตรประชาชนหรือเลข ว.")
	}
	return "(" + strings.Join(parts, " OR ") + ")", args, nil
}

// CollectOpohIndivExport ดึงรายเคสของแพทย์ทุกคนในรายชื่อ พร้อมยอดแยกตามหมวด drg_chrgitem
func CollectOpohIndivExport(db *sql.DB, p IndivExportParams) (*IndivExportResult, error) {
	if p.Type != "opd" && p.Type != "ipd" {
		return nil, fmt.Errorf("ประเภทบริการไม่ถูกต้อง")
	}
	if p.StartDate == "" || p.EndDate == "" {
		return nil, fmt.Errorf("ต้องระบุช่วงวันที่")
	}
	if p.Limit <= 0 || p.Limit > 1000 {
		p.Limit = 500
	}
	if p.Offset < 0 {
		p.Offset = 0
	}

	res := &IndivExportResult{Type: p.Type, Rows: []IndivRow{}}

	cats, err := loadIndivCategories(db)
	if err != nil {
		return nil, err
	}
	res.Categories = cats

	if p.Type == "opd" {
		err = collectIndivOPD(db, p, res)
	} else {
		err = collectIndivIPD(db, p, res)
	}
	if err != nil {
		return nil, err
	}
	if err := fillIndivCategories(db, p.Type, res); err != nil {
		return nil, err
	}
	return res, nil
}

// loadIndivCategories อ่านรายการหมวดจริงจาก drg_chrgitem (ไม่ hard-code 16) และเติมหมวด 19 (อื่นๆ/ไม่ได้จับคู่) ถ้ายังไม่มี
func loadIndivCategories(db *sql.DB) ([]IndivCategory, error) {
	rows, err := db.Query(`SELECT drg_chrgitem_id, drg_chrgitem_name FROM drg_chrgitem ORDER BY drg_chrgitem_id`)
	if err != nil {
		return nil, fmt.Errorf("อ่านหมวดค่ารักษา drg_chrgitem ไม่สำเร็จ: %w", err)
	}
	defer rows.Close()
	var out []IndivCategory
	has19 := false
	for rows.Next() {
		var id int
		var name sql.NullString
		if err := rows.Scan(&id, &name); err != nil {
			return nil, err
		}
		if id == 19 {
			has19 = true
		}
		out = append(out, IndivCategory{ID: id, Name: nullStr(name)})
	}
	if err := rows.Err(); err != nil {
		return nil, err
	}
	if !has19 {
		out = append(out, IndivCategory{ID: 19, Name: "อื่นๆ / ไม่ได้จัดหมวด"})
	}
	sort.Slice(out, func(i, j int) bool { return out[i].ID < out[j].ID })
	return out, nil
}

func collectIndivOPD(db *sql.DB, p IndivExportParams, res *IndivExportResult) error {
	docWhere, docArgs, err := indivDoctorWhere("d", p.Doctors)
	if err != nil {
		return err
	}
	from := `FROM ovst ov
		JOIN vn_stat v ON v.vn = ov.vn
		JOIN doctor d ON d.code = ov.doctor
		JOIN patient pt ON pt.hn = ov.hn
		LEFT JOIN pttype p ON p.pttype = ov.pttype
		LEFT JOIN icd101 ic ON ic.code = v.pdx`
	where := `WHERE ov.vstdate BETWEEN ? AND ? AND ` + docWhere + opdDialysisExclusion
	args := append([]interface{}{p.StartDate, p.EndDate}, docArgs...)

	if err := db.QueryRow("SELECT COUNT(ov.vn) "+from+" "+where, args...).Scan(&res.RecordsTotal); err != nil {
		return fmt.Errorf("นับจำนวนเคส OPD ไม่สำเร็จ: %w", err)
	}

	q := `SELECT ov.vn, ov.hn, ov.vstdate, ov.vsttime,
			TRIM(CONCAT(COALESCE(pt.pname,''), COALESCE(pt.fname,''), ' ', COALESCE(pt.lname,''))),
			p.name, v.pdx, ic.name, v.dx0, v.dx1, v.dx2, v.dx3, v.dx4, v.dx5, d.name, COALESCE(v.income,0) ` +
		from + " " + where + " ORDER BY ov.vstdate, ov.vsttime, ov.vn LIMIT ? OFFSET ?"
	rows, err := db.Query(q, append(append([]interface{}{}, args...), p.Limit, p.Offset)...)
	if err != nil {
		return fmt.Errorf("ดึงรายเคส OPD ไม่สำเร็จ: %w", err)
	}
	defer rows.Close()
	for rows.Next() {
		var vn, hn, vdate, vtime, pname, pttype, pdx, dname, d0, d1, d2, d3, d4, d5, doc sql.NullString
		var income float64
		if err := rows.Scan(&vn, &hn, &vdate, &vtime, &pname, &pttype, &pdx, &dname, &d0, &d1, &d2, &d3, &d4, &d5, &doc, &income); err != nil {
			return err
		}
		res.Rows = append(res.Rows, IndivRow{
			Key: nullStr(vn), HN: nullStr(hn), Date: trimDate(nullStr(vdate)), Time: nullStr(vtime),
			PatientName: nullStr(pname), Pttype: nullStr(pttype), Pdx: nullStr(pdx), DiagName: nullStr(dname),
			Dx0: nullStr(d0), Dx1: nullStr(d1), Dx2: nullStr(d2), Dx3: nullStr(d3), Dx4: nullStr(d4), Dx5: nullStr(d5),
			DoctorName: nullStr(doc), HospitalName: p.HospitalName, Total: income, Cats: map[string]float64{},
		})
	}
	return rows.Err()
}

func collectIndivIPD(db *sql.DB, p IndivExportParams, res *IndivExportResult) error {
	docWhere, docArgs, err := indivDoctorWhere("d", p.Doctors)
	if err != nil {
		return err
	}
	// ชื่อแพทย์: เลือกแพทย์ในรายชื่อคนแรกที่ตรงกับ admdoctor / dch_doctor / incharge_doctor
	docNameSub := `(SELECT d.name FROM doctor d
			WHERE d.code IN (i.dch_doctor, i.incharge_doctor, i.admdoctor) AND ` + docWhere + ` LIMIT 1)`
	from := `FROM ipt i
		JOIN an_stat a ON a.an = i.an
		JOIN patient pt ON pt.hn = i.hn
		LEFT JOIN pttype p ON p.pttype = i.pttype
		LEFT JOIN icd101 ic ON ic.code = a.pdx`
	where := `WHERE i.dchdate BETWEEN ? AND ?
		AND EXISTS (SELECT 1 FROM doctor d
			WHERE (d.code = i.admdoctor OR d.code = i.dch_doctor OR d.code = i.incharge_doctor)
			  AND ` + docWhere + `)`
	whereArgs := append([]interface{}{p.StartDate, p.EndDate}, docArgs...)

	if err := db.QueryRow("SELECT COUNT(i.an) "+from+" "+where, whereArgs...).Scan(&res.RecordsTotal); err != nil {
		return fmt.Errorf("นับจำนวนเคส IPD ไม่สำเร็จ: %w", err)
	}

	q := `SELECT i.an, i.hn, i.regdate, i.dchdate, i.dchtime,
			TRIM(CONCAT(COALESCE(pt.pname,''), COALESCE(pt.fname,''), ' ', COALESCE(pt.lname,''))),
			p.name, a.pdx, ic.name, a.dx0, a.dx1, a.dx2, a.dx3, a.dx4, a.dx5,
			COALESCE(a.admdate, a.los, 1), COALESCE(a.rw, i.adjrw, 0), ` + docNameSub + `, COALESCE(a.income,0) ` +
		from + " " + where + " ORDER BY i.dchdate, i.dchtime, i.an LIMIT ? OFFSET ?"
	// placeholder เรียงตามข้อความ: docNameSub (docArgs) ก่อน แล้ว where (วันที่ + docArgs)
	qArgs := append([]interface{}{}, docArgs...)
	qArgs = append(qArgs, whereArgs...)
	qArgs = append(qArgs, p.Limit, p.Offset)

	rows, err := db.Query(q, qArgs...)
	if err != nil {
		return fmt.Errorf("ดึงรายเคส IPD ไม่สำเร็จ: %w", err)
	}
	defer rows.Close()
	for rows.Next() {
		var an, hn, reg, dch, dtime, pname, pttype, pdx, dname, d0, d1, d2, d3, d4, d5, doc sql.NullString
		var los int
		var adjrw, income float64
		if err := rows.Scan(&an, &hn, &reg, &dch, &dtime, &pname, &pttype, &pdx, &dname, &d0, &d1, &d2, &d3, &d4, &d5, &los, &adjrw, &doc, &income); err != nil {
			return err
		}
		res.Rows = append(res.Rows, IndivRow{
			Key: nullStr(an), HN: nullStr(hn), AdmDate: trimDate(nullStr(reg)),
			Date: trimDate(nullStr(dch)), Time: nullStr(dtime),
			PatientName: nullStr(pname), Pttype: nullStr(pttype), Pdx: nullStr(pdx), DiagName: nullStr(dname),
			Dx0: nullStr(d0), Dx1: nullStr(d1), Dx2: nullStr(d2), Dx3: nullStr(d3), Dx4: nullStr(d4), Dx5: nullStr(d5),
			Los: los, AdjRW: adjrw, DoctorName: nullStr(doc), HospitalName: p.HospitalName, Total: income,
			Cats: map[string]float64{},
		})
	}
	return rows.Err()
}

// fillIndivCategories รวมยอดค่ารักษาของเคสในก้อนนี้ตามหมวด drg_chrgitem
// opitemrece.income -> income.income -> income.drg_chrgitem_id (ไม่จับคู่ = หมวด 19)
func fillIndivCategories(db *sql.DB, typ string, res *IndivExportResult) error {
	if len(res.Rows) == 0 {
		return nil
	}
	idx := make(map[string]*IndivRow, len(res.Rows))
	keys := make([]interface{}, 0, len(res.Rows))
	for i := range res.Rows {
		idx[res.Rows[i].Key] = &res.Rows[i]
		keys = append(keys, res.Rows[i].Key)
	}
	keyCol, anCond := "opr.vn", "opr.an IS NULL"
	if typ == "ipd" {
		keyCol, anCond = "opr.an", "opr.an IS NOT NULL"
	}
	q := `SELECT ` + keyCol + `, COALESCE(i.drg_chrgitem_id, 19), COALESCE(SUM(opr.sum_price), 0)
		FROM opitemrece opr
		LEFT JOIN income i ON i.income = opr.income
		WHERE ` + keyCol + ` IN (` + strings.TrimSuffix(strings.Repeat("?,", len(keys)), ",") + `) AND ` + anCond + `
		GROUP BY ` + keyCol + `, COALESCE(i.drg_chrgitem_id, 19)`
	rows, err := db.Query(q, keys...)
	if err != nil {
		return fmt.Errorf("ดึงยอดตามหมวดค่ารักษาไม่สำเร็จ: %w", err)
	}
	defer rows.Close()
	for rows.Next() {
		var key string
		var cid int
		var amt float64
		if err := rows.Scan(&key, &cid, &amt); err != nil {
			return err
		}
		if r := idx[key]; r != nil {
			r.Cats[fmt.Sprintf("%d", cid)] += amt
		}
	}
	return rows.Err()
}
