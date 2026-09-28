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
	AN          string  `json:"an"`
	IsIPD       bool    `json:"is_ipd"`
	VstDate     string  `json:"vstdate"`
	VstTime     string  `json:"vsttime"`
	AdmDate     string  `json:"admdate"`
	AdmTime     string  `json:"admtime"`
	DchDate     string  `json:"dchdate"`
	DchTime     string  `json:"dchtime"`
	LOS         int     `json:"los"`
	WardName    string  `json:"ward_name"`
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
	DRG         string  `json:"drg"`
	RW          float64 `json:"rw"`
}

type VisitDetail struct {
	VN           string           `json:"vn"`
	AN           string           `json:"an"`
	IsIPD        bool             `json:"is_ipd"`
	AdmDate      string           `json:"admdate"`
	AdmTime      string           `json:"admtime"`
	DchDate      string           `json:"dchdate"`
	DchTime      string           `json:"dchtime"`
	LOS          int              `json:"los"`
	WardName     string           `json:"ward_name"`
	DchType      string           `json:"dch_type"`
	DchStatus    string           `json:"dch_status"`
	AdmDoctor    string           `json:"adm_doctor"`
	DchDoctor    string           `json:"dch_doctor"`
	ChartStatus  string           `json:"chart_status"`
	DRG          string           `json:"drg"`
	RW           float64          `json:"rw"`
	AdjRW        float64          `json:"adjrw"`
	TotalIncome  float64          `json:"total_income"`
	PaidMoney    float64          `json:"paid_money"`
	UcMoney      float64          `json:"uc_money"`
	LatencyMs    float64          `json:"latency_ms"`
	Medications  []PrescribedDrug `json:"medications"`
	NonDrugs     []NonDrugItem    `json:"non_drugs"`
	LabResults   []LabResultItem  `json:"lab_results"`
	Diagnoses    []DiagnosisItem  `json:"diagnoses"`
	Procedures   []ProcedureItem  `json:"procedures"`
	IpdDiagnoses []DiagnosisItem  `json:"ipd_diagnoses"`
}

type PrescribedDrug struct {
	DrugName    string  `json:"drug_name"`
	Qty         string  `json:"qty"`
	Units       string  `json:"units"`
	Usage1      string  `json:"usage1"`
	Usage2      string  `json:"usage2"`
	Usage3      string  `json:"usage3"`
	SpUse       string  `json:"sp_use"`
	SumPrice    float64 `json:"sum_price"`
	MedCategory string  `json:"med_category"` // "ยากลับบ้าน (Home Meds)" or "ยาระหว่างนอน รพ."
	FirstDate   string  `json:"first_date"`
	LastDate    string  `json:"last_date"`
	DaysCount   int     `json:"days_count"`
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

	// 4. 20 Recent Visits (Both OPD and IPD with Admission details & Chart Summary)
	visitRows, err := db.Query(`
		SELECT 
			o.vn, 
			COALESCE(o.an, ''),
			o.vstdate, 
			o.vsttime,
			COALESCE(ipt.admdate, ''),
			COALESCE(ipt.admtime, ''),
			COALESCE(ipt.dchdate, ''),
			COALESCE(ipt.dchtime, ''),
			COALESCE(w.name, ''),
			COALESCE(d.department, 'แผนกตรวจทั่วไป'),
			COALESCE(ans.pdx, COALESCE(v.pdx, COALESCE(id_pdx.icd10, ''))),
			COALESCE(icd_ans.name, COALESCE(icd.name, COALESCE(icd_ipd.name, ''))),
			COALESCE(s.cc, ''),
			COALESCE(s.bps, 0),
			COALESCE(s.bpd, 0),
			COALESCE(s.pulse, 0),
			COALESCE(s.temperature, 0),
			COALESCE(s.bw, 0),
			COALESCE(s.height, 0),
			COALESCE(s.bmi, 0),
			COALESCE(pt.name, ''),
			COALESCE(doc_dch.name, COALESCE(doc.name, COALESCE(doc_v.name, COALESCE(doc_adm.name, '')))),
			COALESCE(ans.drg, ''),
			COALESCE(ans.rw, 0)
		FROM ovst o
		LEFT JOIN ipt ipt ON ipt.an = o.an
		LEFT JOIN an_stat ans ON ans.an = o.an
		LEFT JOIN ward w ON w.ward = ipt.ward
		LEFT JOIN vn_stat v ON v.vn = o.vn
		LEFT JOIN iptdiag id_pdx ON id_pdx.an = o.an AND id_pdx.diagtype = '1'
		LEFT JOIN opdscreen s ON s.vn = o.vn
		LEFT JOIN kskdepartment d ON d.depcode = COALESCE(o.main_dep, o.cur_dep)
		LEFT JOIN icd101 icd_ans ON icd_ans.code = ans.pdx
		LEFT JOIN icd101 icd ON icd.code = v.pdx
		LEFT JOIN icd101 icd_ipd ON icd_ipd.code = id_pdx.icd10
		LEFT JOIN pttype pt ON pt.pttype = o.pttype
		LEFT JOIN doctor doc ON doc.code = o.doctor
		LEFT JOIN doctor doc_v ON doc_v.code = v.dx_doctor
		LEFT JOIN doctor doc_adm ON doc_adm.code = ipt.adm_doctor
		LEFT JOIN doctor doc_dch ON doc_dch.code = COALESCE(ans.dch_doctor, COALESCE(ans.dx_doctor, ipt.dch_doctor))
		WHERE o.hn = ?
		ORDER BY o.vstdate DESC, o.vsttime DESC
		LIMIT 20
	`, hn)
	if err == nil {
		defer visitRows.Close()
		for visitRows.Next() {
			var v VisitSummary
			var an, admDate, admTime, dchDate, dchTime, wardName string
			if err := visitRows.Scan(
				&v.VN, &an, &v.VstDate, &v.VstTime,
				&admDate, &admTime, &dchDate, &dchTime, &wardName,
				&v.Department, &v.Pdx, &v.PdxName, &v.CC,
				&v.BPS, &v.BPD, &v.Pulse, &v.Temperature,
				&v.BW, &v.Height, &v.BMI, &v.PttypeName, &v.DoctorName,
				&v.DRG, &v.RW,
			); err == nil {
				if len(v.VstDate) > 10 {
					v.VstDate = v.VstDate[:10]
				}
				v.AN = an
				v.IsIPD = (an != "")
				v.AdmDate = admDate
				v.AdmTime = admTime
				v.DchDate = dchDate
				v.DchTime = dchTime
				v.WardName = wardName

				// Calculate Length of stay if IPD
				if v.IsIPD && admDate != "" {
					if dchDate != "" {
						tAdm, e1 := time.Parse("2006-01-02", admDate[:min(10, len(admDate))])
						tDch, e2 := time.Parse("2006-01-02", dchDate[:min(10, len(dchDate))])
						if e1 == nil && e2 == nil {
							days := int(tDch.Sub(tAdm).Hours()/24) + 1
							if days < 1 {
								days = 1
							}
							v.LOS = days
						}
					} else {
						v.LOS = 1 // Currently admitted
					}
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

// CollectVisitDetail queries medications, non-drugs, labs, diagnoses, and procedures for a specific VN or AN.
func CollectVisitDetail(db *sql.DB, vn string) (*VisitDetail, error) {
	startTime := time.Now()
	cleanVN := strings.TrimSpace(vn)

	detail := &VisitDetail{
		VN:           cleanVN,
		Medications:  make([]PrescribedDrug, 0),
		NonDrugs:     make([]NonDrugItem, 0),
		LabResults:   make([]LabResultItem, 0),
		Diagnoses:    make([]DiagnosisItem, 0),
		Procedures:   make([]ProcedureItem, 0),
		IpdDiagnoses: make([]DiagnosisItem, 0),
	}

	// Check if this visit has an AN or is an AN
	var an string
	_ = db.QueryRow(`
		SELECT COALESCE(o.an, '') 
		FROM ovst o 
		WHERE o.vn = ? 
		LIMIT 1
	`, cleanVN).Scan(&an)

	if an == "" {
		_ = db.QueryRow(`
			SELECT COALESCE(an, '') 
			FROM ipt 
			WHERE an = ? OR vn = ? 
			LIMIT 1
		`, cleanVN, cleanVN).Scan(&an)
	}

	if an != "" {
		detail.AN = an
		detail.IsIPD = true

		// Query IPD Admission Info & Chart Summary (an_stat + ipt)
		var admDoctor, dchDoctor, dchStatus, dchType, drg string
		var rw, adjrw, income, rcptMoney, ucMoney float64

		_ = db.QueryRow(`
			SELECT 
				ipt.admdate,
				ipt.admtime,
				COALESCE(ipt.dchdate, ''),
				COALESCE(ipt.dchtime, ''),
				COALESCE(w.name, ''),
				COALESCE(doc_adm.name, ''),
				COALESCE(doc_dch.name, ''),
				COALESCE(ds.name, ''),
				COALESCE(dt.name, ''),
				COALESCE(ans.drg, ''),
				COALESCE(ans.rw, 0),
				COALESCE(ans.adjrw, 0),
				COALESCE(ans.income, 0),
				COALESCE(ans.rcpt_money, 0),
				COALESCE(ans.uc_money, 0)
			FROM ipt
			LEFT JOIN an_stat ans ON ans.an = ipt.an
			LEFT JOIN ward w ON w.ward = ipt.ward
			LEFT JOIN doctor doc_adm ON doc_adm.code = ipt.adm_doctor
			LEFT JOIN doctor doc_dch ON doc_dch.code = COALESCE(ans.dch_doctor, COALESCE(ans.dx_doctor, ipt.dch_doctor))
			LEFT JOIN dchstts ds ON ds.dchstts = ipt.dchstts
			LEFT JOIN dchtype dt ON dt.dchtype = ipt.dchtype
			WHERE ipt.an = ?
			LIMIT 1
		`, an).Scan(
			&detail.AdmDate, &detail.AdmTime,
			&detail.DchDate, &detail.DchTime,
			&detail.WardName, &admDoctor, &dchDoctor,
			&dchStatus, &dchType,
			&drg, &rw, &adjrw, &income, &rcptMoney, &ucMoney,
		)

		detail.AdmDoctor = admDoctor
		detail.DchDoctor = dchDoctor
		detail.DchStatus = dchStatus
		detail.DchType = dchType
		detail.DRG = drg
		detail.RW = rw
		detail.AdjRW = adjrw
		detail.TotalIncome = income
		detail.PaidMoney = rcptMoney
		detail.UcMoney = ucMoney

		if detail.AdmDate != "" {
			if detail.DchDate != "" {
				tAdm, e1 := time.Parse("2006-01-02", detail.AdmDate[:min(10, len(detail.AdmDate))])
				tDch, e2 := time.Parse("2006-01-02", detail.DchDate[:min(10, len(detail.DchDate))])
				if e1 == nil && e2 == nil {
					days := int(tDch.Sub(tAdm).Hours()/24) + 1
					if days < 1 {
						days = 1
					}
					detail.LOS = days
				}
			} else {
				detail.LOS = 1
			}
		}

		// Determine Chart Summary Status
		if detail.DchDate == "" {
			detail.ChartStatus = "กำลังนอนรักษาตัวใน รพ. (Admitted)"
		} else if dchDoctor != "" || drg != "" || rw > 0 {
			detail.ChartStatus = "สรุปชาร์จแล้ว (Chart Summarized)"
		} else {
			detail.ChartStatus = "จำหน่ายแล้ว (รอสรุปชาร์จ)"
		}

		// Query IPD Diagnoses from iptdiag
		ipdDiagRows, err := db.Query(`
			SELECT 
				id.diagtype, 
				id.icd10, 
				COALESCE(i.name, ''),
				CASE 
					WHEN id.diagtype = '1' THEN 'Principal Diagnosis (โรคหลัก)'
					WHEN id.diagtype = '2' THEN 'Comorbidity (โรคร่วม)'
					WHEN id.diagtype = '3' THEN 'Complication (โรคแทรก)'
					WHEN id.diagtype = '4' THEN 'Other (โรคอื่น)'
					WHEN id.diagtype = '5' THEN 'External Cause (สาเหตุภายนอก)'
					ELSE 'อื่นๆ'
				END AS diagtype_name
			FROM iptdiag id
			LEFT JOIN icd101 i ON i.code = id.icd10
			WHERE id.an = ?
			ORDER BY id.diagtype ASC
		`, an)
		if err == nil {
			defer ipdDiagRows.Close()
			for ipdDiagRows.Next() {
				var d DiagnosisItem
				if err := ipdDiagRows.Scan(&d.DiagType, &d.Icd10, &d.DiagName, &d.DiagTypeName); err == nil {
					detail.IpdDiagnoses = append(detail.IpdDiagnoses, d)
				}
			}
		}

		// Query IPD Procedures from iptoprt
		ipdProcRows, err := db.Query(`
			SELECT 
				iop.icd9,
				COALESCE(i9.name, ''),
				COALESCE(d.name, ''),
				CASE 
					WHEN iop.op_type = '1' THEN 'Principal Procedure (หัตถการหลัก IPD)'
					WHEN iop.op_type = '2' THEN 'Secondary Procedure (หัตถการรอง IPD)'
					ELSE 'หัตถการ IPD'
				END AS proctype_name
			FROM iptoprt iop
			LEFT JOIN icd9cm1 i9 ON i9.code = iop.icd9
			LEFT JOIN doctor d ON d.code = iop.doctor
			WHERE iop.an = ?
			ORDER BY iop.op_type ASC
		`, an)
		if err == nil {
			defer ipdProcRows.Close()
			for ipdProcRows.Next() {
				var p ProcedureItem
				if err := ipdProcRows.Scan(&p.Icd9, &p.ProcName, &p.DoctorName, &p.ProcTypeName); err == nil {
					detail.Procedures = append(detail.Procedures, p)
				}
			}
		}
	}

	// 1. Medications: Smart grouped by drug, supporting Home Meds vs In-Hospital doses without restrictive icode filter
	medRows, err := db.Query(`
		SELECT 
			d.name,
			SUM(op.qty) AS qty,
			COALESCE(d.units, ''),
			COALESCE(du.name1, ''),
			COALESCE(du.name2, ''),
			COALESCE(du.name3, ''),
			COALESCE(op.sp_use, COALESCE(sp.name1, '')),
			COALESCE(SUM(op.sum_price), 0),
			CASE 
				WHEN op.item_type = 'H' THEN 'ยากลับบ้าน (Home Meds)'
				WHEN op.an IS NOT NULL AND op.an != '' THEN 'ยาระหว่างนอน รพ.'
				ELSE 'ยาผู้ป่วยนอก (OPD)'
			END AS med_category,
			COALESCE(MIN(op.vstdate), ''),
			COALESCE(MAX(op.vstdate), ''),
			COUNT(DISTINCT op.vstdate) AS days_count
		FROM opitemrece op
		JOIN drugitems d ON d.icode = op.icode
		LEFT JOIN drugusage du ON du.drugusage = op.drugusage
		LEFT JOIN sp_use sp ON sp.sp_use = op.sp_use
		WHERE (op.vn = ? OR (op.an IS NOT NULL AND op.an != '' AND op.an = ?))
		GROUP BY d.icode, med_category, du.drugusage, op.sp_use, sp.name1
		ORDER BY CASE WHEN med_category = 'ยากลับบ้าน (Home Meds)' THEN 1 WHEN med_category = 'ยาผู้ป่วยนอก (OPD)' THEN 2 ELSE 3 END ASC, d.name ASC
	`, cleanVN, an)
	if err == nil {
		defer medRows.Close()
		for medRows.Next() {
			var m PrescribedDrug
			var firstDate, lastDate string
			if err := medRows.Scan(
				&m.DrugName, &m.Qty, &m.Units,
				&m.Usage1, &m.Usage2, &m.Usage3,
				&m.SpUse, &m.SumPrice, &m.MedCategory,
				&firstDate, &lastDate, &m.DaysCount,
			); err == nil {
				if len(firstDate) > 10 {
					firstDate = firstDate[:10]
				}
				if len(lastDate) > 10 {
					lastDate = lastDate[:10]
				}
				m.FirstDate = firstDate
				m.LastDate = lastDate
				detail.Medications = append(detail.Medications, m)
			}
		}
	}

	// 2. Non-Drug / Medical Services
	ndRows, err := db.Query(`
		SELECT 
			nd.name,
			SUM(op.qty) AS qty,
			COALESCE(nd.unit, ''),
			COALESCE(op.unitprice, 0),
			COALESCE(SUM(op.sum_price), 0)
		FROM opitemrece op
		JOIN nondrugitems nd ON nd.icode = op.icode
		WHERE (op.vn = ? OR (op.an IS NOT NULL AND op.an != '' AND op.an = ?))
		GROUP BY nd.icode, nd.name, nd.unit, op.unitprice
		ORDER BY nd.name ASC
	`, cleanVN, an)
	if err == nil {
		defer ndRows.Close()
		for ndRows.Next() {
			var nd NonDrugItem
			if err := ndRows.Scan(&nd.ItemName, &nd.Qty, &nd.Units, &nd.UnitPrice, &nd.SumPrice); err == nil {
				detail.NonDrugs = append(detail.NonDrugs, nd)
			}
		}
	}

	// 3. Lab Results (Only tests with actual recorded results)
	labRows, err := db.Query(`
		SELECT 
			COALESCE(i.lab_items_name, 'Lab item'),
			COALESCE(lo.lab_order_result, ''),
			COALESCE(i.lab_items_unit, ''),
			COALESCE(i.lab_items_normal_value, '-'),
			COALESCE(lh.order_date, ''),
			COALESCE(lh.order_time, ''),
			COALESCE(lh.form_name, 'ผลตรวจทั่วไป')
		FROM lab_order lo
		JOIN lab_head lh ON lh.lab_order_number = lo.lab_order_number
		LEFT JOIN lab_items i ON i.lab_items_code = lo.lab_items_code
		WHERE (lh.vn = ? OR (lh.an IS NOT NULL AND lh.an != '' AND lh.an = ?))
		  AND lo.lab_order_result IS NOT NULL 
		  AND TRIM(lo.lab_order_result) != '' 
		  AND TRIM(lo.lab_order_result) != '-'
		ORDER BY lh.order_date DESC, lh.order_time DESC, i.lab_items_name ASC
	`, cleanVN, an)
	if err == nil {
		defer labRows.Close()
		for labRows.Next() {
			var l LabResultItem
			if err := labRows.Scan(&l.LabName, &l.LabResult, &l.LabUnit, &l.NormalValue, &l.OrderDate, &l.OrderTime, &l.LabGroup); err == nil {
				cleanRes := strings.TrimSpace(l.LabResult)
				if cleanRes == "" || cleanRes == "-" {
					continue
				}
				if len(l.OrderDate) > 10 {
					l.OrderDate = l.OrderDate[:10]
				}
				detail.LabResults = append(detail.LabResults, l)
			}
		}
	}

	// 4. OPD Diagnoses (ICD-10 letter-prefixed)
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
	`, cleanVN)
	if err == nil {
		defer diagRows.Close()
		for diagRows.Next() {
			var d DiagnosisItem
			if err := diagRows.Scan(&d.DiagType, &d.Icd10, &d.DiagName, &d.DiagTypeName); err == nil {
				detail.Diagnoses = append(detail.Diagnoses, d)
			}
		}
	}

	// 5. Procedures (ICD-9 numeric-only from ovstdiag)
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
		WHERE (od.vn = ? OR (od.an IS NOT NULL AND od.an != '' AND od.an = ?)) AND (od.icd10 REGEXP '^[0-9]')
		ORDER BY od.diagtype ASC
	`, cleanVN, an)
	if err == nil {
		defer procRows.Close()
		seenProcs := make(map[string]bool)
		for _, p := range detail.Procedures {
			seenProcs[p.Icd9] = true
		}
		for procRows.Next() {
			var p ProcedureItem
			if err := procRows.Scan(&p.Icd9, &p.ProcName, &p.DoctorName, &p.ProcTypeName); err == nil {
				if !seenProcs[p.Icd9] {
					seenProcs[p.Icd9] = true
					detail.Procedures = append(detail.Procedures, p)
				}
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
