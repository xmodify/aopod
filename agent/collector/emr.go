package collector

import (
	"database/sql"
	"fmt"
	"strings"
	"time"
)

// PatientEMR contains full patient record summary
type PatientEMR struct {
	HN           string          `json:"hn"`
	CID          string          `json:"cid"`
	FullName     string          `json:"full_name"`
	Sex          string          `json:"sex"`
	Age          string          `json:"age"`
	Birthday     string          `json:"birthday"`
	BloodGroup   string          `json:"bloodgrp"`
	PttypeName   string          `json:"pttype"`
	Address      string          `json:"address"`
	HospitalCode string          `json:"hospital_code"`
	HospitalName string          `json:"hospital_name"`
	LatencyMs    float64         `json:"latency_ms"`
	Allergies    []DrugAllergy   `json:"allergies"`
	Clinics      []ChronicClinic `json:"clinics"`
	Visits       []VisitSummary  `json:"visits"`
	TotalVisits  int             `json:"total_visits_found"`
}

type DrugAllergy struct {
	Agent        string `json:"agent"`
	Symptom      string `json:"symptom"`
	ReportDate   string `json:"report_date"`
	Note         string `json:"note"`
	HospitalCode string `json:"hospital_code"`
	HospitalName string `json:"hospital_name"`
}

type ChronicClinic struct {
	ClinicName   string `json:"clinic_name"`
	BeginDate    string `json:"begin_date"`
	HospitalCode string `json:"hospital_code"`
	HospitalName string `json:"hospital_name"`
}

type VisitSummary struct {
	VN          string  `json:"vn"`
	VstDate     string  `json:"vstdate"`
	VstTime     string  `json:"vsttime"`
	Department  string  `json:"department"`
	Pdx         string  `json:"pdx"`
	PdxName     string  `json:"pdx_name"`
	CC          string  `json:"cc"`
	BPS         float64 `json:"bps"`
	BPD         float64 `json:"bpd"`
	Pulse       float64 `json:"pulse"`
	Temperature float64 `json:"temperature"`
	BW          float64 `json:"bw"`
	Height      float64 `json:"height"`
	BMI         float64 `json:"bmi"`
	PttypeName  string  `json:"pttype_name"`
	DoctorName  string  `json:"doctor_name"`
	HN          string  `json:"hn"`
	HospCode    string  `json:"hospital_code"`
	HospName    string  `json:"hospital_name"`
}

type VisitDetail struct {
	VN          string           `json:"vn"`
	LatencyMs   float64          `json:"latency_ms"`
	Medications []PrescribedDrug `json:"medications"`
	NonDrugs    []NonDrugItem    `json:"non_drugs"`
	LabResults  []LabResultItem  `json:"lab_results"`
	Diagnoses   []DiagnosisItem  `json:"diagnoses"`
	Procedures  []ProcedureItem  `json:"procedures"`
}

type PrescribedDrug struct {
	DrugName string  `json:"drug_name"`
	Qty      string  `json:"qty"`
	Units    string  `json:"units"`
	Usage1   string  `json:"usage1"`
	Usage2   string  `json:"usage2"`
	Usage3   string  `json:"usage3"`
	SpUse    string  `json:"sp_use"`
	SumPrice float64 `json:"sum_price"`
}

type NonDrugItem struct {
	ItemName  string  `json:"item_name"`
	Qty       string  `json:"qty"`
	Units     string  `json:"units"`
	UnitPrice float64 `json:"unit_price"`
	SumPrice  float64 `json:"sum_price"`
}

type LabResultItem struct {
	LabName     string `json:"lab_name"`
	LabResult   string `json:"lab_result"`
	LabUnit     string `json:"lab_unit"`
	NormalValue string `json:"normal_value"`
	OrderDate   string `json:"order_date"`
	OrderTime   string `json:"order_time"`
	LabGroup    string `json:"lab_group"`
}

type DiagnosisItem struct {
	DiagType     string `json:"diagtype"`
	Icd10        string `json:"icd10"`
	DiagName     string `json:"diag_name"`
	DiagTypeName string `json:"diagtype_name"`
}

type ProcedureItem struct {
	Icd9         string `json:"icd9"`
	ProcName     string `json:"proc_name"`
	DoctorName   string `json:"doctor_name"`
	ProcTypeName string `json:"proctype_name"`
}

// CollectPatientEMR searches patient demographics, allergies, clinics, and 20 recent visits by CID.
func CollectPatientEMR(db *sql.DB, cid string) (*PatientEMR, error) {
	startTime := time.Now()
	cleanCID := strings.TrimSpace(cid)

	// Get Hospital Info
	hcode, hname := GetHospitalInfo(db)

	// 1. Patient Demographics
	var hn, fname, lname, sex, birthday, bloodgrp, pttypeName, informaddr, moopart string
	err := db.QueryRow(`
		SELECT 
			p.hn, p.fname, p.lname, COALESCE(p.sex, ''), COALESCE(p.birthday, ''), 
			COALESCE(p.bloodgrp, ''), COALESCE(pt.name, ''),
			COALESCE(p.informaddr, ''), COALESCE(p.moopart, '')
		FROM patient p
		LEFT JOIN pttype pt ON pt.pttype = p.pttype
		WHERE p.cid = ?
		LIMIT 1
	`, cleanCID).Scan(&hn, &fname, &lname, &sex, &birthday, &bloodgrp, &pttypeName, &informaddr, &moopart)

	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil // Patient not found in this hospital
		}
		return nil, fmt.Errorf("query patient error: %w", err)
	}

	// Calculate age
	ageStr := calculateAge(birthday)
	sexStr := "ชาย"
	if sex == "2" {
		sexStr = "หญิง"
	} else if sex != "1" {
		sexStr = "-"
	}

	addrStr := strings.TrimSpace(fmt.Sprintf("%s ม.%s", informaddr, moopart))
	if addrStr == "ม." || addrStr == "" {
		addrStr = "-"
	}

	emr := &PatientEMR{
		HN:           hn,
		CID:          cleanCID,
		FullName:     fmt.Sprintf("%s %s", fname, lname),
		Sex:          sexStr,
		Age:          ageStr,
		Birthday:     birthday,
		BloodGroup:   bloodgrp,
		PttypeName:   pttypeName,
		Address:      addrStr,
		HospitalCode: hcode,
		HospitalName: hname,
		Allergies:    make([]DrugAllergy, 0),
		Clinics:      make([]ChronicClinic, 0),
		Visits:       make([]VisitSummary, 0),
	}

	// 2. Drug Allergies
	allergyRows, err := db.Query(`
		SELECT 
			a.agent, 
			COALESCE(a.symptom, '-'), 
			COALESCE(a.report_date, ''), 
			COALESCE(a.note, '')
		FROM opd_allergy a
		WHERE a.hn = ?
		ORDER BY a.report_date DESC
	`, hn)
	if err == nil {
		defer allergyRows.Close()
		for allergyRows.Next() {
			var al DrugAllergy
			if err := allergyRows.Scan(&al.Agent, &al.Symptom, &al.ReportDate, &al.Note); err == nil {
				al.HospitalCode = hcode
				al.HospitalName = hname
				emr.Allergies = append(emr.Allergies, al)
			}
		}
	}

	// 3. Chronic Clinics
	clinicRows, err := db.Query(`
		SELECT 
			c.name, 
			COALESCE(cm.begin_date, '')
		FROM clinicmember cm
		JOIN clinic c ON c.clinic = cm.clinic
		WHERE cm.hn = ?
		ORDER BY cm.begin_date DESC
	`, hn)
	if err == nil {
		defer clinicRows.Close()
		for clinicRows.Next() {
			var cl ChronicClinic
			if err := clinicRows.Scan(&cl.ClinicName, &cl.BeginDate); err == nil {
				cl.HospitalCode = hcode
				cl.HospitalName = hname
				emr.Clinics = append(emr.Clinics, cl)
			}
		}
	}

	// 4. 20 Recent Visits
	visitRows, err := db.Query(`
		SELECT 
			o.vn, 
			o.vstdate, 
			o.vsttime,
			COALESCE(d.department, 'แผนกตรวจทั่วไป'),
			COALESCE(v.pdx, ''),
			COALESCE(icd.name, ''),
			COALESCE(s.cc, ''),
			COALESCE(s.bps, 0),
			COALESCE(s.bpd, 0),
			COALESCE(s.pulse, 0),
			COALESCE(s.temperature, 0),
			COALESCE(s.bw, 0),
			COALESCE(s.height, 0),
			COALESCE(s.bmi, 0),
			COALESCE(pt.name, ''),
			COALESCE(doc.name, doc_v.name, '')
		FROM ovst o
		LEFT JOIN vn_stat v ON v.vn = o.vn
		LEFT JOIN opdscreen s ON s.vn = o.vn
		LEFT JOIN kskdepartment d ON d.depcode = COALESCE(o.main_dep, o.cur_dep)
		LEFT JOIN icd101 icd ON icd.code = v.pdx
		LEFT JOIN pttype pt ON pt.pttype = o.pttype
		LEFT JOIN doctor doc ON doc.code = o.doctor
		LEFT JOIN doctor doc_v ON doc_v.code = v.dx_doctor
		WHERE o.hn = ?
		ORDER BY o.vstdate DESC, o.vsttime DESC
		LIMIT 20
	`, hn)
	if err == nil {
		defer visitRows.Close()
		for visitRows.Next() {
			var v VisitSummary
			if err := visitRows.Scan(
				&v.VN, &v.VstDate, &v.VstTime, &v.Department,
				&v.Pdx, &v.PdxName, &v.CC,
				&v.BPS, &v.BPD, &v.Pulse, &v.Temperature,
				&v.BW, &v.Height, &v.BMI, &v.PttypeName, &v.DoctorName,
			); err == nil {
				if len(v.VstDate) > 10 {
					v.VstDate = v.VstDate[:10]
				}
				v.HN = hn
				v.HospCode = hcode
				v.HospName = hname
				emr.Visits = append(emr.Visits, v)
			}
		}
	}

	emr.TotalVisits = len(emr.Visits)
	emr.LatencyMs = float64(time.Since(startTime).Microseconds()) / 1000.0

	return emr, nil
}

// CollectVisitDetail queries medications, non-drugs, labs, diagnoses, and procedures for a specific VN.
func CollectVisitDetail(db *sql.DB, vn string) (*VisitDetail, error) {
	startTime := time.Now()
	detail := &VisitDetail{
		VN:          vn,
		Medications: make([]PrescribedDrug, 0),
		NonDrugs:    make([]NonDrugItem, 0),
		LabResults:  make([]LabResultItem, 0),
		Diagnoses:   make([]DiagnosisItem, 0),
		Procedures:  make([]ProcedureItem, 0),
	}

	// 1. Medications (icode LIKE '1%')
	medRows, err := db.Query(`
		SELECT 
			d.name,
			op.qty,
			COALESCE(d.units, ''),
			COALESCE(du.name1, ''),
			COALESCE(du.name2, ''),
			COALESCE(du.name3, ''),
			COALESCE(op.sp_use, ''),
			COALESCE(op.sum_price, 0)
		FROM opitemrece op
		JOIN drugitems d ON d.icode = op.icode
		LEFT JOIN drugusage du ON du.drugusage = op.drugusage
		WHERE op.vn = ? AND op.icode LIKE '1%'
		ORDER BY op.item_no ASC, d.name ASC
	`, vn)
	if err == nil {
		defer medRows.Close()
		for medRows.Next() {
			var m PrescribedDrug
			if err := medRows.Scan(&m.DrugName, &m.Qty, &m.Units, &m.Usage1, &m.Usage2, &m.Usage3, &m.SpUse, &m.SumPrice); err == nil {
				detail.Medications = append(detail.Medications, m)
			}
		}
	}

	// 2. Non-Drug / Medical Services (icode LIKE '3%' OR NOT LIKE '1%')
	ndRows, err := db.Query(`
		SELECT 
			nd.name,
			op.qty,
			COALESCE(nd.unit, ''),
			COALESCE(op.unitprice, 0),
			COALESCE(op.sum_price, 0)
		FROM opitemrece op
		JOIN nondrugitems nd ON nd.icode = op.icode
		WHERE op.vn = ? AND (op.icode LIKE '3%' OR op.icode NOT LIKE '1%')
		ORDER BY op.item_no ASC, nd.name ASC
	`, vn)
	if err == nil {
		defer ndRows.Close()
		for ndRows.Next() {
			var nd NonDrugItem
			if err := ndRows.Scan(&nd.ItemName, &nd.Qty, &nd.Units, &nd.UnitPrice, &nd.SumPrice); err == nil {
				detail.NonDrugs = append(detail.NonDrugs, nd)
			}
		}
	}

	// 3. Lab Results
	labRows, err := db.Query(`
		SELECT 
			COALESCE(i.lab_items_name, 'Lab item'),
			COALESCE(lo.lab_order_result, '-'),
			COALESCE(i.lab_items_unit, ''),
			COALESCE(i.lab_items_normal_value, '-'),
			lh.order_date,
			lh.order_time,
			COALESCE(lh.form_name, 'ผลตรวจทั่วไป')
		FROM lab_order lo
		JOIN lab_head lh ON lh.lab_order_number = lo.lab_order_number
		LEFT JOIN lab_items i ON i.lab_items_code = lo.lab_items_code
		WHERE lh.vn = ?
		ORDER BY lh.order_date DESC, lh.order_time DESC, i.lab_items_name ASC
	`, vn)
	if err == nil {
		defer labRows.Close()
		for labRows.Next() {
			var l LabResultItem
			if err := labRows.Scan(&l.LabName, &l.LabResult, &l.LabUnit, &l.NormalValue, &l.OrderDate, &l.OrderTime, &l.LabGroup); err == nil {
				if len(l.OrderDate) > 10 {
					l.OrderDate = l.OrderDate[:10]
				}
				detail.LabResults = append(detail.LabResults, l)
			}
		}
	}

	// 4. Diagnoses (ICD-10 letter-prefixed)
	diagRows, err := db.Query(`
		SELECT 
			od.diagtype, 
			od.icd10, 
			COALESCE(i.name, ''),
			CASE 
				WHEN od.diagtype = '1' THEN 'Principal Diagnosis (โรคหลัก)'
				WHEN od.diagtype = '2' THEN 'Comorbidity (โรคร่วม)'
				WHEN od.diagtype = '3' THEN 'Complication (โรคแทรก)'
				WHEN od.diagtype = '4' THEN 'Other (โรคอื่น)'
				WHEN od.diagtype = '5' THEN 'External Cause (สาเหตุภายนอก)'
				ELSE 'อื่นๆ'
			END AS diagtype_name
		FROM ovstdiag od
		LEFT JOIN icd101 i ON i.code = od.icd10
		WHERE od.vn = ? AND (od.icd10 REGEXP '^[A-Za-z]')
		ORDER BY od.diagtype ASC
	`, vn)
	if err == nil {
		defer diagRows.Close()
		for diagRows.Next() {
			var d DiagnosisItem
			if err := diagRows.Scan(&d.DiagType, &d.Icd10, &d.DiagName, &d.DiagTypeName); err == nil {
				detail.Diagnoses = append(detail.Diagnoses, d)
			}
		}
	}

	// 5. Procedures (ICD-9 numeric-only from ovstdiag joined with icd9cm1 / icd101)
	procRows, err := db.Query(`
		SELECT 
			od.icd10, 
			COALESCE(i9.name, COALESCE(i10.name, '')),
			COALESCE(d.name, ''),
			CASE 
				WHEN od.diagtype = '1' THEN 'Principal Procedure (หัตถการหลัก)'
				WHEN od.diagtype = '2' THEN 'Secondary Procedure (หัตถการรอง)'
				ELSE 'หัตถการอื่น'
			END AS proctype_name
		FROM ovstdiag od
		LEFT JOIN icd9cm1 i9 ON i9.code = od.icd10
		LEFT JOIN icd101 i10 ON i10.code = od.icd10
		LEFT JOIN doctor d ON d.code = od.doctor
		WHERE od.vn = ? AND (od.icd10 REGEXP '^[0-9]')
		ORDER BY od.diagtype ASC
	`, vn)
	if err == nil {
		defer procRows.Close()
		for procRows.Next() {
			var p ProcedureItem
			if err := procRows.Scan(&p.Icd9, &p.ProcName, &p.DoctorName, &p.ProcTypeName); err == nil {
				detail.Procedures = append(detail.Procedures, p)
			}
		}
	}

	detail.LatencyMs = float64(time.Since(startTime).Microseconds()) / 1000.0
	return detail, nil
}

// GetHospitalInfo gets hospital code & name from HOSxP opdconfig
func GetHospitalInfo(db *sql.DB) (string, string) {
	var hcode, hname string
	err := db.QueryRow("SELECT hospitalcode, hospitalname FROM opdconfig LIMIT 1").Scan(&hcode, &hname)
	if err != nil {
		return "10989", "โรงพยาบาล"
	}
	return hcode, hname
}

func calculateAge(birthday string) string {
	if birthday == "" {
		return "-"
	}
	t, err := time.Parse("2006-01-02", birthday[:min(10, len(birthday))])
	if err != nil {
		return "-"
	}
	now := time.Now()
	years := now.Year() - t.Year()
	if now.YearDay() < t.YearDay() {
		years--
	}
	if years < 0 {
		years = 0
	}
	return fmt.Sprintf("%d ปี", years)
}

func min(a, b int) int {
	if a < b {
		return a
	}
	return b
}
