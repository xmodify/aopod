package collector

import (
	"database/sql"
	"fmt"
	"strings"

	"aopod-agent/config"
	"aopod-agent/lookups"
)

type OpdRecord struct {
	Vstdate               string  `json:"vstdate"`
	HNTotal               int     `json:"hn_total"`
	VisitTotal            int     `json:"visit_total"`
	VisitTotalOp          int     `json:"visit_total_op"`
	VisitTotalPp          int     `json:"visit_total_pp"`
	VisitEndpoint         int     `json:"visit_endpoint"`
	VisitUcsIncup         int     `json:"visit_ucs_incup"`
	VisitUcsInprov        int     `json:"visit_ucs_inprov"`
	VisitUcsOutprov       int     `json:"visit_ucs_outprov"`
	VisitOfc              int     `json:"visit_ofc"`
	VisitBkk              int     `json:"visit_bkk"`
	VisitBmt              int     `json:"visit_bmt"`
	VisitSss              int     `json:"visit_sss"`
	VisitLgo              int     `json:"visit_lgo"`
	VisitFss              int     `json:"visit_fss"`
	VisitStp              int     `json:"visit_stp"`
	VisitPay              int     `json:"visit_pay"`
	VisitDent             int     `json:"visit_dent"`
	VisitPhysic           int     `json:"visit_physic"`
	VisitHealthmed        int     `json:"visit_healthmed"`
	VisitUcsHealthmed     int     `json:"visit_ucs_healthmed"`
	VisitAnc              int     `json:"visit_anc"`
	VisitTelehealth       int     `json:"visit_telehealth"`
	VisitMophOappBooking  int     `json:"visit_moph_oapp_booking"`
	VisitMophOapp         int     `json:"visit_moph_oapp"`
	VisitOperation        int     `json:"visit_operation"`
	VisitReferoutInprov   int     `json:"visit_referout_inprov"`
	VisitReferoutOutprov  int     `json:"visit_referout_outprov"`
	VisitReferoutInprovIpd int    `json:"visit_referout_inprov_ipd"`
	VisitReferoutOutprovIpd int   `json:"visit_referout_outprov_ipd"`
	VisitReferinInprov    int     `json:"visit_referin_inprov"`
	VisitReferinOutprov   int     `json:"visit_referin_outprov"`
	VisitReferinInprovIpd int     `json:"visit_referin_inprov_ipd"`
	VisitReferinOutprovIpd int    `json:"visit_referin_outprov_ipd"`
	VisitReferbackInprov  int     `json:"visit_referback_inprov"`
	VisitReferbackOutprov int     `json:"visit_referback_outprov"`
	IncTotal              float64 `json:"inc_total"`
	IncLabTotal           float64 `json:"inc_lab_total"`
	IncDrugTotal          float64 `json:"inc_drug_total"`
	IncUcsIncup           float64 `json:"inc_ucs_incup"`
	IncLabUcsIncup        float64 `json:"inc_lab_ucs_incup"`
	IncDrugUcsIncup       float64 `json:"inc_drug_ucs_incup"`
	IncUcsInprov          float64 `json:"inc_ucs_inprov"`
	IncLabUcsInprov       float64 `json:"inc_lab_ucs_inprov"`
	IncDrugUcsInprov      float64 `json:"inc_drug_ucs_inprov"`
	IncUcsOutprov         float64 `json:"inc_ucs_outprov"`
	IncLabUcsOutprov      float64 `json:"inc_lab_ucs_outprov"`
	IncDrugUcsOutprov     float64 `json:"inc_drug_ucs_outprov"`
	IncOfc                float64 `json:"inc_ofc"`
	IncLabOfc             float64 `json:"inc_lab_ofc"`
	IncDrugOfc            float64 `json:"inc_drug_ofc"`
	IncBkk                float64 `json:"inc_bkk"`
	IncLabBkk             float64 `json:"inc_lab_bkk"`
	IncDrugBkk            float64 `json:"inc_drug_bkk"`
	IncBmt                float64 `json:"inc_bmt"`
	IncLabBmt             float64 `json:"inc_lab_bmt"`
	IncDrugBmt            float64 `json:"inc_drug_bmt"`
	IncSss                float64 `json:"inc_sss"`
	IncLabSss             float64 `json:"inc_lab_sss"`
	IncDrugSss            float64 `json:"inc_drug_sss"`
	IncLgo                float64 `json:"inc_lgo"`
	IncLabLgo             float64 `json:"inc_lab_lgo"`
	IncDrugLgo            float64 `json:"inc_drug_lgo"`
	IncFss                float64 `json:"inc_fss"`
	IncLabFss             float64 `json:"inc_lab_fss"`
	IncDrugFss            float64 `json:"inc_drug_fss"`
	IncStp                float64 `json:"inc_stp"`
	IncLabStp             float64 `json:"inc_lab_stp"`
	IncDrugStp            float64 `json:"inc_drug_stp"`
	IncPay                float64 `json:"inc_pay"`
	IncLabPay             float64 `json:"inc_lab_pay"`
	IncDrugPay            float64 `json:"inc_drug_pay"`
}

// CollectOPD queries HOSxP and returns aggregated OPD records for date range.
func CollectOPD(db *sql.DB, startDate, endDate string) ([]OpdRecord, error) {
	cfg := config.Get()
	hospcode := cfg.Hospital.Code

	// Build PP ICD-10 IN clause
	ppCodes := lookups.GetPPList()
	ppInClause := "'Z00', 'Z000', 'Z010'"
	if len(ppCodes) > 0 {
		quoted := make([]string, len(ppCodes))
		for i, c := range ppCodes {
			quoted[i] = fmt.Sprintf("'%s'", strings.ReplaceAll(c, "'", "''"))
		}
		ppInClause = strings.Join(quoted, ",")
	}

	query := GetOpdQuery(ppInClause, hospcode)

	rows, err := db.Query(query, startDate, endDate, startDate, endDate, startDate, endDate, startDate, endDate)
	if err != nil {
		return nil, fmt.Errorf("error executing OPD query: %w", err)
	}
	defer rows.Close()

	var records []OpdRecord
	for rows.Next() {
		var r OpdRecord
		err := rows.Scan(
			&r.Vstdate,
			&r.HNTotal,
			&r.VisitTotal,
			&r.VisitTotalOp,
			&r.VisitTotalPp,
			&r.VisitEndpoint,
			&r.VisitUcsIncup,
			&r.VisitUcsInprov,
			&r.VisitUcsOutprov,
			&r.VisitOfc,
			&r.VisitBkk,
			&r.VisitBmt,
			&r.VisitSss,
			&r.VisitLgo,
			&r.VisitFss,
			&r.VisitStp,
			&r.VisitPay,
			&r.VisitUcsHealthmed,
			&r.VisitHealthmed,
			&r.VisitDent,
			&r.VisitPhysic,
			&r.VisitAnc,
			&r.VisitTelehealth,
			&r.VisitMophOappBooking,
			&r.VisitMophOapp,
			&r.VisitReferoutInprov,
			&r.VisitReferoutOutprov,
			&r.VisitReferoutInprovIpd,
			&r.VisitReferoutOutprovIpd,
			&r.VisitReferinInprov,
			&r.VisitReferinOutprov,
			&r.VisitReferinInprovIpd,
			&r.VisitReferinOutprovIpd,
			&r.VisitReferbackInprov,
			&r.VisitReferbackOutprov,
			&r.VisitOperation,
			&r.IncTotal,
			&r.IncLabTotal,
			&r.IncDrugTotal,
			&r.IncUcsIncup,
			&r.IncLabUcsIncup,
			&r.IncDrugUcsIncup,
			&r.IncUcsInprov,
			&r.IncLabUcsInprov,
			&r.IncDrugUcsInprov,
			&r.IncUcsOutprov,
			&r.IncLabUcsOutprov,
			&r.IncDrugUcsOutprov,
			&r.IncOfc,
			&r.IncLabOfc,
			&r.IncDrugOfc,
			&r.IncBkk,
			&r.IncLabBkk,
			&r.IncDrugBkk,
			&r.IncBmt,
			&r.IncLabBmt,
			&r.IncDrugBmt,
			&r.IncSss,
			&r.IncLabSss,
			&r.IncDrugSss,
			&r.IncLgo,
			&r.IncLabLgo,
			&r.IncDrugLgo,
			&r.IncFss,
			&r.IncLabFss,
			&r.IncDrugFss,
			&r.IncStp,
			&r.IncLabStp,
			&r.IncDrugStp,
			&r.IncPay,
			&r.IncLabPay,
			&r.IncDrugPay,
		)
		if err != nil {
			return nil, fmt.Errorf("error scanning OPD row: %w", err)
		}
		if len(r.Vstdate) >= 10 {
			r.Vstdate = r.Vstdate[:10]
		}
		records = append(records, r)
	}

	return records, nil
}
