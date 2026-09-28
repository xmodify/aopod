@extends('layouts.admin')

@section('title', 'A-EMR - AOPOD')
@section('header_title', 'A-EMR')

@push('styles')
<style>
  .emr-search-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(6, 182, 212, 0.08) 100%);
    border: 1px solid rgba(6, 182, 212, 0.25);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  }

  .emr-input-group {
    border-radius: 16px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    transition: all 0.25s ease;
    overflow: hidden;
  }
  .emr-input-group:focus-within {
    border-color: #06b6d4;
    box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15);
  }
  .emr-input-group input {
    border: none !important;
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: 1.5px;
    box-shadow: none !important;
  }
  .emr-input-group input::placeholder {
    font-size: 1rem;
    font-weight: 400;
    letter-spacing: normal;
    color: #94a3b8;
  }

  .patient-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    border-radius: 20px;
    padding: 1.6rem 1.8rem;
    position: relative;
    overflow: hidden;
  }
  .patient-banner::after {
    content: "";
    position: absolute;
    top: -50%;
    right: -20%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, transparent 70%);
    border-radius: 50%;
  }

  .allergy-alert-box {
    background: #fff1f2;
    border: 1px solid #fecdd3;
    border-left: 5px solid #e11d48;
    border-radius: 12px;
    padding: 0.8rem 1rem;
  }

  /* Hospital Source Chip in Patient Banner */
  .hosp-source-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 9999px;
    padding: 4px 12px;
    color: #ffffff;
    font-size: 0.83rem;
    font-weight: 600;
  }
  .hosp-source-chip .hosp-name-text {
    color: #ffffff !important;
    font-weight: 600;
  }
  .hosp-source-chip .hosp-count-badge {
    background: #06b6d4;
    color: #0f172a;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 9999px;
  }

  /* Table styling matching vEMR / HOSxP */
  .vemr-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    overflow: hidden;
  }

  .vemr-table thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 0.9rem 0.8rem;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
  }

  .vemr-table tbody tr {
    transition: all 0.15s ease;
    cursor: pointer;
  }
  .vemr-table tbody tr:hover {
    background-color: #f0fdf4 !important;
  }
  .vemr-table tbody td {
    padding: 0.85rem 0.8rem;
    vertical-align: middle;
    font-size: 0.9rem;
  }

  /* IPD Row Styling (Pastel Warm Amber/Rose) */
  .vemr-row-ipd {
    background-color: #fff7ed !important;
    border-left: 4px solid #ea580c !important;
  }
  .vemr-row-ipd:hover {
    background-color: #ffedd5 !important;
  }

  .badge-hosp {
    font-size: 0.82rem;
    padding: 0.35rem 0.65rem;
    border-radius: 8px;
    font-weight: 600;
  }

  /* Modal styling matching Image 3 (RIMS style) - Compact & Screen-Fitting */
  .rims-modal-header {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
    color: #ffffff;
    padding: 0.85rem 1.25rem;
    border-radius: 16px 16px 0 0;
    transition: background 0.3s ease;
  }
  .rims-modal-header.header-ipd {
    background: linear-gradient(135deg, #c2410c 0%, #ea580c 60%, #f97316 100%) !important;
  }

  .modal-mode-pill {
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.3rem 0.85rem;
    border-radius: 9999px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
  }
  .modal-mode-pill:hover {
    background: #f8fafc;
    border-color: #94a3b8;
  }
  .modal-mode-pill.active-opd {
    background: #2563eb !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
  }
  .modal-mode-pill.active-ipd {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
    color: #ffffff !important;
    border-color: #ea580c !important;
    box-shadow: 0 2px 8px rgba(234, 88, 12, 0.3);
  }

  .badge-home-med {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.72rem;
    display: inline-flex;
    align-items: center;
    gap: 3px;
  }
  .badge-ipd-med {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.72rem;
    display: inline-flex;
    align-items: center;
    gap: 3px;
  }

  .rims-info-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.55rem 0.8rem;
    height: 100%;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
  }
  .rims-info-card-header {
    font-size: 0.82rem;
    font-weight: 700;
    color: #1e3a8a;
    border-bottom: 1.5px solid #eff6ff;
    padding-bottom: 0.25rem;
    margin-bottom: 0.4rem;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .rims-field-row {
    display: flex;
    margin-bottom: 0.18rem;
    font-size: 0.8rem;
    line-height: 1.3;
  }
  .rims-field-label {
    width: 105px;
    flex-shrink: 0;
    color: #64748b;
    font-weight: 500;
  }
  .rims-field-value {
    color: #0f172a;
    font-weight: 600;
    word-break: break-word;
  }

  .rims-tab-nav .nav-link {
    font-weight: 600;
    font-size: 0.82rem;
    padding: 0.35rem 0.8rem;
    border-radius: 8px;
    color: #475569;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    transition: all 0.2s;
  }
  .rims-tab-nav .nav-link.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
  }
  .rims-tab-nav .nav-link.active span.badge {
    background: #ffffff !important;
    color: #2563eb !important;
  }

  /* Modal Table Compact & Sticky Header */
  .modal-tab-table-container {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
  }
  .modal-table thead th {
    font-size: 0.8rem;
    padding: 0.4rem 0.55rem;
    background-color: #f8fafc;
    color: #475569;
    font-weight: 700;
    position: sticky;
    top: 0;
    z-index: 2;
    border-bottom: 1.5px solid #e2e8f0;
  }
  .modal-table tbody td {
    padding: 0.35rem 0.55rem;
    font-size: 0.82rem;
    vertical-align: middle;
  }
  .modal-pagination-bar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.3rem 0.65rem;
    margin-top: 0.45rem;
  }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- 1. Search Box -->
    <div class="col-12">
        <div class="emr-search-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0f172a;">
                        <i class="fa-solid fa-notes-medical text-info me-2"></i> A-EMR : สืบค้นประวัติการรักษา
                    </h4>
                    <p class="text-muted mb-0 small">
                        ระบบสืบค้นข้อมูลประวัติการรักษาผู้ป่วยข้ามโรงพยาบาลแบบกระจายศูนย์ (Federated On-Demand Query)
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-3">
                        <i class="fa-solid fa-shield-check me-1"></i> Zero-Storage Privacy
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-3">
                        <i class="fa-solid fa-bolt me-1"></i> Real-time Multi-Hospital
                    </span>
                </div>
            </div>

            <form id="emrSearchForm" class="row g-2 align-items-center" onsubmit="handleSearch(event)">
                <div class="col-12 col-md-9 col-lg-10">
                    <div class="emr-input-group d-flex align-items-center px-3 py-1">
                        <i class="fa-solid fa-id-card text-muted fs-4 me-2"></i>
                        <input type="text" id="cidInput" class="form-control" 
                               placeholder="กรอกเลขประจำตัวประชาชน 13 หลัก หรือ สแกน Smart Card" 
                               maxlength="17" 
                               autocomplete="off" 
                               required 
                               autofocus>
                        <button type="button" class="btn btn-link text-muted p-0 ms-2" onclick="clearCidInput()" title="ล้างข้อมูล">
                            <i class="fa-solid fa-xmark fs-5"></i>
                        </button>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-lg-2 d-grid">
                    <button type="submit" id="searchBtn" class="btn btn-primary py-2.5 fw-bold shadow-sm" style="border-radius: 14px; background: linear-gradient(135deg, #06b6d4 0%, #0d6efd 100%); border: none; font-size: 1.05rem;">
                        <span id="btnIcon"><i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา</span>
                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. State: Initial Guide -->
    <div id="initialState" class="col-12 text-center py-5">
        <div class="p-5 glass-card d-inline-block" style="max-width: 620px;">
            <div class="mb-3 text-info">
                <i class="fa-solid fa-heart-pulse" style="font-size: 4rem; opacity: 0.85;"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">พร้อมสำหรับการค้นหาประวัติการรักษา (A-EMR)</h5>
            <p class="text-secondary small mb-4">
                กรอกเลขบัตรประชาชน 13 หลักเพื่อดึงข้อมูลประวัติการรักษา, การแพ้ยา, โรคประจำตัว, และประวัติการมารับบริการ 20 ครั้งล่าสุดจากโรงพยาบาลได้ทันที
            </p>
            <div class="d-flex justify-content-center gap-3 text-start small text-muted">
                <div><i class="fa-solid fa-circle-check text-success me-1"></i> ค้นหาข้าม รพ. อัตโนมัติ</div>
                <div><i class="fa-solid fa-circle-check text-success me-1"></i> ดึงสดจาก opitemrece</div>
                <div><i class="fa-solid fa-circle-check text-success me-1"></i> ปลอดภัยตาม PDPA</div>
            </div>
        </div>
    </div>

    <!-- 3. State: Loading Skeleton -->
    <div id="loadingState" class="col-12 d-none">
        <div class="glass-card p-4 text-center py-5">
            <div class="spinner-grow text-info mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h5 class="fw-bold text-slate-800 mb-1">กำลังสืบค้นประวัติการรักษาจากโรงพยาบาลในเครือข่าย...</h5>
            <p class="text-muted small mb-0">เชื่อมต่อ Agent ประจำโรงพยาบาล และดึงข้อมูลแบบ On-demand</p>
        </div>
    </div>

    <!-- 4. State: Not Found Alert -->
    <div id="notFoundState" class="col-12 d-none">
        <div class="glass-card p-4 text-center border-warning border-opacity-50">
            <i class="fa-solid fa-triangle-exclamation text-warning mb-2" style="font-size: 2.5rem;"></i>
            <h5 class="fw-bold text-dark mb-1">ไม่พบข้อมูลประวัติการรักษา</h5>
            <p id="notFoundMsg" class="text-secondary small mb-0">ไม่พบประวัติผู้ป่วยด้วยเลขประจำตัวประชาชนนี้ในระบบโรงพยาบาล</p>
        </div>
    </div>

    <!-- 5. State: EMR Result Container -->
    <div id="emrResultContainer" class="col-12 d-none">
        <!-- Patient Banner (Demographics) -->
        <div class="patient-banner mb-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-info bg-opacity-20 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; font-size: 1.8rem; flex-shrink: 0;">
                            <i class="fa-solid fa-user-injured"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-white" id="resPatientName">-</h3>
                            <div class="text-slate-300 small mt-1">
                                HN: <span class="fw-bold text-info" id="resPatientHn">-</span> | 
                                CID: <span class="fw-bold" id="resPatientCid">-</span> |
                                วันเกิด: <span id="resPatientBirthday" class="text-slate-200">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-5 text-md-end">
                    <div class="d-flex flex-wrap justify-content-md-end gap-2">
                        <span class="badge" style="background: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; padding: 0.5rem 0.8rem; border-radius: 8px;">
                            เพศ: <span class="fw-bold text-info" id="resPatientSex">-</span>
                        </span>
                        <span class="badge" style="background: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; padding: 0.5rem 0.8rem; border-radius: 8px;">
                            อายุ: <span class="fw-bold text-info" id="resPatientAge">-</span>
                        </span>
                        <span class="badge" style="background: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; padding: 0.5rem 0.8rem; border-radius: 8px;">
                            กรุ๊ปเลือด: <span class="fw-bold text-danger" id="resPatientBlood">-</span>
                        </span>
                        <span class="badge" style="background: rgba(37, 99, 235, 0.45) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; padding: 0.5rem 0.8rem; border-radius: 8px;">
                            สิทธิ: <span class="fw-bold" id="resPatientPttype">-</span>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2 mt-2">
                        <div class="small text-slate-300">
                            <i class="fa-solid fa-network-wired text-info me-1"></i> แหล่งข้อมูล:
                        </div>
                        <div id="resHospitalsSummaryList" class="d-flex flex-wrap gap-1.5">
                            <!-- Populated dynamically by JS -->
                        </div>
                        <span class="badge" id="resLatency" style="background: rgba(34, 197, 94, 0.2) !important; color: #86efac !important; border: 1px solid rgba(34, 197, 94, 0.3) !important; padding: 5px 10px; border-radius: 9999px; font-weight: 600;">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drug Allergy & Chronic Diseases Summary (Horizontal Split) -->
        <div class="row g-3 mb-4">
            <!-- Allergies -->
            <div class="col-12 col-lg-6">
                <div class="glass-card p-3.5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-danger mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i> ประวัติการแพ้ยา (Drug Allergies)
                        </h6>
                        <span id="allergyCountBadge" class="badge bg-danger rounded-pill">0</span>
                    </div>
                    <div id="allergyContentList" class="mt-2">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>

            <!-- Chronic Clinics -->
            <div class="col-12 col-lg-6">
                <div class="glass-card p-3.5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-primary mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-stethoscope"></i> โรคประจำตัว / คลินิกเรื้อรัง (PMH / Chronic Conditions)
                        </h6>
                        <span id="clinicCountBadge" class="badge bg-primary rounded-pill">0</span>
                    </div>
                    <div id="clinicContentList" class="mt-2 d-flex flex-wrap gap-2">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Visit History Table (เหมือน vEMR ในรูปที่ 1) -->
        <div class="vemr-table-card">
            <div class="p-3.5 border-bottom bg-light">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-list-check text-primary"></i> ประวัติการมารับบริการ (Visit History)
                        </h5>
                        <small class="text-muted">คลิกที่แถวรายการเพื่อเปิดดูรายละเอียดประวัติการรักษา, รายการยา (opitemrece), ค่าบริการ, Lab, และหัตถการ</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold" id="totalVisitsBadge">
                        0 Visits
                    </span>
                </div>

                <!-- Hospital Filter Navigation -->
                <div class="d-flex flex-wrap align-items-center gap-2 pt-1 border-top" id="hospitalFilterContainer" style="display: none !important;">
                    <span class="small fw-bold text-muted me-1"><i class="fa-solid fa-filter"></i> กรองตาม รพ.:</span>
                    <div class="d-flex flex-wrap gap-1.5" id="hospitalFilterButtons">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table vemr-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">ลำดับ</th>
                            <th style="width: 150px;">โรงพยาบาล</th>
                            <th style="width: 105px;">HN / AN</th>
                            <th style="width: 165px;">วันที่ / เวลา (พ.ศ.)</th>
                            <th style="width: 155px;">แผนก / ตึกผู้ป่วย</th>
                            <th style="width: 225px;">แพทย์ผู้ตรวจ / แพทย์เจ้าของไข้</th>
                            <th>การวินิจฉัยหลัก (PDX)</th>
                            <th style="width: 160px;">สัญญาณชีพ (BP/PR/Temp)</th>
                            <th style="width: 38px;" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="vemrTableBody">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Visit Detail (โครงสร้าง Smart OPD / IPD Mode Switcher + 5 แท็บ) -->
<div class="modal fade" id="visitDetailModal" tabindex="-1" aria-labelledby="visitDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);">
            <!-- Modal Header (RIMS Blue / Orange Style) -->
            <div class="rims-modal-header d-flex align-items-center justify-content-between" id="rimsModalHeader">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 bg-white bg-opacity-20 text-white rounded-3 fs-4" id="modalHeaderIcon">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="visitDetailModalLabel">รายละเอียดการรักษาผู้ป่วยนอก (OPD)</h5>
                        <div class="text-white text-opacity-75 small mt-0.5" id="modalVisitMeta">
                            สืบค้นประวัติจากระบบ HOSxP โรงพยาบาลในเครือข่าย AOPOD
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 bg-slate-50" style="background-color: #f8fafc;">
                <!-- Status Banner -->
                <div class="alert alert-success d-flex align-items-center justify-content-between gap-2 mb-2 py-1 px-2.5 border-0 shadow-sm" style="border-radius: 10px; background: #dcfce7; color: #15803d;">
                    <div class="d-flex align-items-center gap-2 small fw-semibold">
                        <i class="fa-solid fa-circle-check fs-6"></i>
                        <span><strong>สถานะ:</strong> ดึงข้อมูลสำเร็จจากตาราง <code class="fw-bold text-dark">opitemrece</code> และฐานข้อมูล HOSxP แบบ Real-time</span>
                    </div>
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50" id="modalFetchBadge">⚡ Live Federated</span>
                </div>

                <!-- Mode Switcher (Visible only when visit is IPD / Admission) -->
                <div id="modalModeSwitcherContainer" class="d-none mb-2 p-1.5 bg-white rounded-3 border shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-bold text-muted me-1"><i class="fa-solid fa-layer-group"></i> มุมมองข้อมูล:</span>
                        <button type="button" class="modal-mode-pill active-ipd" id="modeBtnIpd" onclick="switchModalMode('IPD')">
                            <i class="fa-solid fa-bed-pulse me-1"></i> ข้อมูลการนอน รพ. (IPD)
                        </button>
                        <button type="button" class="modal-mode-pill" id="modeBtnOpd" onclick="switchModalMode('OPD')">
                            <i class="fa-solid fa-stethoscope me-1"></i> ข้อมูลผู้ป่วยนอก (OPD)
                        </button>
                    </div>
                    <div id="modalIpdLosSummary" class="badge" style="background:#ffedd5; color:#9a3412; border:1px solid #fed7aa; padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 9999px;">
                        <i class="fa-solid fa-bed-pulse me-1"></i> Admit Case
                    </div>
                </div>

                <!-- 3 Information Cards Grid -->
                <div class="row g-2 mb-2">
                    <!-- Column 1: ข้อมูลผู้ป่วย -->
                    <div class="col-12 col-md-4">
                        <div class="rims-info-card">
                            <div class="rims-info-card-header">
                                <i class="fa-solid fa-user-circle text-primary"></i> ข้อมูลผู้ป่วย
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">HN / AN:</div>
                                <div class="rims-field-value text-primary" id="mPtHn">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">CID:</div>
                                <div class="rims-field-value" id="mPtCid">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">ชื่อ - สกุล:</div>
                                <div class="rims-field-value text-dark" id="mPtName">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">สิทธิ์:</div>
                                <div class="rims-field-value" id="mPtPttype">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">เพศ / อายุ:</div>
                                <div class="rims-field-value" id="mPtSexAge">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">ประวัติแพ้ยา:</div>
                                <div class="rims-field-value" id="mPtAllergy">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">โรคประจำตัว:</div>
                                <div class="rims-field-value" id="mPtClinic">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: ข้อมูลทางคลินิก / การนอน รพ. (Dynamic OPD vs IPD) -->
                    <div class="col-12 col-md-4">
                        <div class="rims-info-card" id="cardClinicalInfo">
                            <div class="rims-info-card-header" id="cardClinicalHeader">
                                <i class="fa-solid fa-stethoscope text-primary"></i> ข้อมูลผู้ป่วยนอก (OPD)
                            </div>
                            
                            <!-- OPD View Fields -->
                            <div id="mViewOpdFields">
                                <div class="rims-field-row">
                                    <div class="rims-field-label">วันที่รับบริการ:</div>
                                    <div class="rims-field-value text-dark" id="mCliDate">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">โรงพยาบาล:</div>
                                    <div class="rims-field-value text-primary" id="mCliHospital">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">แผนก / ห้องตรวจ:</div>
                                    <div class="rims-field-value" id="mCliDep">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">แพทย์ผู้ตรวจ:</div>
                                    <div class="rims-field-value text-dark fw-bold" id="mCliDoctor">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">อาการสำคัญ (CC):</div>
                                    <div class="rims-field-value text-dark" id="mCliCC">-</div>
                                </div>
                            </div>

                            <!-- IPD View Fields (Admit / Dch / Ward / Doctor / Chart Summary) -->
                            <div id="mViewIpdFields" class="d-none">
                                <div class="rims-field-row">
                                    <div class="rims-field-label">วันที่ Admit:</div>
                                    <div class="rims-field-value text-danger fw-bold" id="mIpdAdmDate">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">วันที่ Discharge:</div>
                                    <div class="rims-field-value text-dark fw-bold" id="mIpdDchDate">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">จำนวนวันนอน:</div>
                                    <div class="rims-field-value text-primary fw-bold" id="mIpdLos">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">หอผู้ป่วย / ตึก:</div>
                                    <div class="rims-field-value text-dark fw-bold" id="mIpdWard">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">แพทย์ผู้รับ Admit:</div>
                                    <div class="rims-field-value text-dark" id="mIpdAdmDoctor">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">แพทย์สรุปชาร์จ:</div>
                                    <div class="rims-field-value text-primary fw-bold" id="mIpdDchDoctor">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">สถานะสรุปชาร์จ:</div>
                                    <div class="rims-field-value" id="mIpdChartStatus">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">DRG / RW:</div>
                                    <div class="rims-field-value" id="mIpdDrgRw">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">สถานะจำหน่าย:</div>
                                    <div class="rims-field-value text-muted" id="mIpdDchStatus">-</div>
                                </div>
                                <div class="rims-field-row">
                                    <div class="rims-field-label">อาการสำคัญ (CC):</div>
                                    <div class="rims-field-value text-dark" id="mIpdCC">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: สัญญาณชีพและการตรวจ -->
                    <div class="col-12 col-md-4">
                        <div class="rims-info-card">
                            <div class="rims-info-card-header">
                                <i class="fa-solid fa-heart-pulse text-danger"></i> สัญญาณชีพและการตรวจ
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">ความดันโลหิต:</div>
                                <div class="rims-field-value text-danger" id="mVitBP">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">ชีพจร (PR):</div>
                                <div class="rims-field-value" id="mVitPulse">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">อุณหภูมิ:</div>
                                <div class="rims-field-value" id="mVitTemp">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">น้ำหนัก / ส่วนสูง:</div>
                                <div class="rims-field-value" id="mVitBwHeight">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">ดัชนีมวลกาย:</div>
                                <div class="rims-field-value" id="mVitBMI">-</div>
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">Latency:</div>
                                <div class="rims-field-value text-success" id="mVitLatency">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Tabbed Details (แยก 5 แท็บ: ยา, ค่ารักษา, Lab, วินิจฉัย, หัตถการ) -->
                <div class="bg-white p-2.5 rounded-3 border shadow-sm">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 border-bottom pb-1.5">
                        <ul class="nav nav-pills rims-tab-nav mb-0" id="detailTab" role="tablist" style="gap: 6px;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active d-flex align-items-center gap-1.5" id="meds-tab" data-bs-toggle="tab" data-bs-target="#meds-pane" type="button" role="tab">
                                    <i class="fa-solid fa-pills text-success"></i> รายการยา 
                                    <span class="badge bg-secondary rounded-pill px-2" id="modalMedCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center gap-1.5" id="nondrug-tab" data-bs-toggle="tab" data-bs-target="#nondrug-pane" type="button" role="tab">
                                    <i class="fa-solid fa-file-invoice-dollar text-primary"></i> ค่ารักษาพยาบาล 
                                    <span class="badge bg-secondary rounded-pill px-2" id="modalNonDrugCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center gap-1.5" id="labs-tab" data-bs-toggle="tab" data-bs-target="#labs-pane" type="button" role="tab">
                                    <i class="fa-solid fa-flask-vial text-info"></i> Lab 
                                    <span class="badge bg-secondary rounded-pill px-2" id="modalLabCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center gap-1.5" id="diag-tab" data-bs-toggle="tab" data-bs-target="#diag-pane" type="button" role="tab">
                                    <i class="fa-solid fa-stethoscope text-warning"></i> การวินิจฉัยโรค 
                                    <span class="badge bg-secondary rounded-pill px-2" id="modalDiagCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center gap-1.5" id="proc-tab" data-bs-toggle="tab" data-bs-target="#proc-pane" type="button" role="tab">
                                    <i class="fa-solid fa-hand-holding-medical text-danger"></i> หัตถการ 
                                    <span class="badge bg-secondary rounded-pill px-2" id="modalProcCount">0</span>
                                </button>
                            </li>
                        </ul>
                        <!-- Quick Top-Right Pagination Bar -->
                        <div id="modalTopPaginationBox" class="d-flex align-items-center gap-1"></div>
                    </div>

                    <div class="tab-content" id="detailTabContent">
                        <!-- 1. Medications Table (Smart categorization for OPD and IPD) -->
                        <div class="tab-pane fade show active" id="meds-pane" role="tabpanel">
                            <div class="modal-tab-table-container">
                                <table class="table table-hover align-middle mb-0 modal-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 45px;" class="text-center">#</th>
                                            <th>ชื่อยา / เวชภัณฑ์</th>
                                            <th style="width: 140px;" class="text-center">จำนวนรวม</th>
                                            <th>วิธีใช้ / คำแนะนำ (Drug Usage)</th>
                                            <th style="width: 220px;">ช่วงวันที่ / คำสั่งพิเศษ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalMedTableBody">
                                        <!-- Filled by JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="modalMedPagination"></div>
                        </div>

                        <!-- 2. Non-Drug / Medical Service Fees Table (icode 3% & an_stat) -->
                        <div class="tab-pane fade" id="nondrug-pane" role="tabpanel">
                            <!-- IPD Chart Financial Summary (an_stat) -->
                            <div id="mIpdFinancialSummaryBox" class="d-none mb-2 p-2.5 rounded-3" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 1.5px solid #fdba74;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-uppercase" style="color: #9a3412; font-size: 0.8rem;">
                                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> สรุปค่ารักษาพยาบาลผู้ป่วยใน (an_stat Financial Summary)
                                    </span>
                                    <span class="badge" style="background: #ea580c; color: #fff; font-size: 0.75rem;">IPD an_stat</span>
                                </div>
                                <div class="row g-2 text-center">
                                    <div class="col-4 border-end" style="border-color: #fed7aa !important;">
                                        <div class="small text-muted" style="font-size: 0.75rem;">ค่ารักษาพยาบาลรวม</div>
                                        <div class="small fw-bold text-dark mt-0.5" id="mIpdIncome">0.00 บาท</div>
                                    </div>
                                    <div class="col-4 border-end" style="border-color: #fed7aa !important;">
                                        <div class="small text-muted" style="font-size: 0.75rem;">สิทธิเบิกได้ / เรียกเก็บ UC</div>
                                        <div class="small fw-bold text-success mt-0.5" id="mIpdUcMoney">0.00 บาท</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted" style="font-size: 0.75rem;">ชำระเงินเอง</div>
                                        <div class="small fw-bold text-danger mt-0.5" id="mIpdPaidMoney">0.00 บาท</div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-tab-table-container">
                                <table class="table table-hover align-middle mb-0 modal-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;" class="text-center">#</th>
                                            <th>รายการค่ารักษา / ค่าบริการ</th>
                                            <th style="width: 110px;" class="text-center">จำนวน</th>
                                            <th style="width: 100px;" class="text-center">หน่วย</th>
                                            <th style="width: 140px;" class="text-end">ราคา/หน่วย (บาท)</th>
                                            <th style="width: 140px;" class="text-end">รวมเงิน (บาท)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalNonDrugTableBody">
                                        <!-- Filled by JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="modalNonDrugPagination"></div>
                        </div>

                        <!-- 3. Labs Table -->
                        <div class="tab-pane fade" id="labs-pane" role="tabpanel">
                            <div class="modal-tab-table-container">
                                <table class="table table-hover align-middle mb-0 modal-table">
                                    <thead id="modalLabTableHead">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">#</th>
                                            <th>รายการตรวจ (Lab Test)</th>
                                            <th style="width: 160px;" class="text-center">ผลการตรวจ</th>
                                            <th style="width: 120px;" class="text-center">หน่วย</th>
                                            <th>ค่าอ้างอิงปกติ (Normal Range)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalLabTableBody">
                                        <!-- Filled by JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="modalLabPagination"></div>
                        </div>

                        <!-- 4. Diagnoses Table (ICD-10 OPD & IPD) -->
                        <div class="tab-pane fade" id="diag-pane" role="tabpanel">
                            <div class="modal-tab-table-container">
                                <table class="table table-hover align-middle mb-0 modal-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;" class="text-center">#</th>
                                            <th style="width: 130px;">รหัส ICD-10</th>
                                            <th>ชื่อโรค / ภาวะการวินิจฉัย</th>
                                            <th style="width: 240px;">ประเภทการวินิจฉัย</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalDiagTableBody">
                                        <!-- Filled by JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="modalDiagPagination"></div>
                        </div>

                        <!-- 5. Procedures Table (ICD-9) -->
                        <div class="tab-pane fade" id="proc-pane" role="tabpanel">
                            <div class="modal-tab-table-container">
                                <table class="table table-hover align-middle mb-0 modal-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;" class="text-center">#</th>
                                            <th style="width: 130px;">รหัสหัตถการ (ICD-9)</th>
                                            <th>ชื่อหัตถการ / การรักษา</th>
                                            <th style="width: 180px;">แพทย์ผู้ทำหัตถการ</th>
                                            <th style="width: 200px;">ประเภทหัตถการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalProcTableBody">
                                        <!-- Filled by JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="modalProcPagination"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-top px-4 py-2.5" style="border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-secondary px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px;">
                    <i class="fa-solid fa-xmark me-1"></i> ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // State memory for current loaded patient & visits
    let currentPatientData = null;

    const thaiMonthsShort = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

    function formatThaiDateTime(dateStr, timeStr) {
        if (!dateStr) return '-';
        const cleanDate = dateStr.substring(0, 10);
        const parts = cleanDate.split('-');
        if (parts.length === 3) {
            let year = parseInt(parts[0], 10);
            if (year < 2400) year += 543;
            const monthIndex = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);
            const mName = thaiMonthsShort[monthIndex] || parts[1];
            
            let timePart = '';
            if (timeStr) {
                timePart = ' ' + timeStr.substring(0, 5) + ' น.';
            }
            return `${day} ${mName} ${year}${timePart}`;
        }
        return dateStr;
    }

    function formatThaiDateShort(dateStr) {
        if (!dateStr) return '-';
        const cleanDate = dateStr.substring(0, 10);
        const parts = cleanDate.split('-');
        if (parts.length === 3) {
            let year = parseInt(parts[0], 10);
            if (year < 2400) year += 543;
            const yearShort = (year % 100).toString().padStart(2, '0');
            const monthIndex = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);
            const mName = thaiMonthsShort[monthIndex] || parts[1];
            return `${day} ${mName} ${yearShort}`;
        }
        return dateStr;
    }

    const cidInput = document.getElementById('cidInput');
    cidInput.addEventListener('input', function(e) {
        let val = this.value.replace(/\D/g, '');
        if (val.length > 13) val = val.substring(0, 13);
        this.value = val;
    });

    function clearCidInput() {
        cidInput.value = '';
        cidInput.focus();
    }

    async function handleSearch(e) {
        if (e) e.preventDefault();
        const cid = cidInput.value.replace(/\D/g, '');
        if (cid.length !== 13) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกเลข 13 หลัก',
                text: 'เลขประจำตัวประชาชนต้องครบถ้วน 13 หลัก',
                confirmButtonColor: '#06b6d4',
            });
            return;
        }

        // UI States
        document.getElementById('initialState').classList.add('d-none');
        document.getElementById('notFoundState').classList.add('d-none');
        document.getElementById('emrResultContainer').classList.add('d-none');
        document.getElementById('loadingState').classList.remove('d-none');

        document.getElementById('btnIcon').classList.add('d-none');
        document.getElementById('btnSpinner').classList.remove('d-none');
        document.getElementById('searchBtn').disabled = true;

        try {
            const response = await fetch("{{ route('manage.emr.search') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ cid })
            });

            const data = await response.json();
            document.getElementById('loadingState').classList.add('d-none');

            if (!data.success) {
                document.getElementById('notFoundState').classList.remove('d-none');
                document.getElementById('notFoundMsg').textContent = data.message || 'เกิดข้อผิดพลาดในการค้นหา';
                return;
            }

            if (!data.found) {
                document.getElementById('notFoundState').classList.remove('d-none');
                document.getElementById('notFoundMsg').textContent = data.message || 'ไม่พบข้อมูลผู้ป่วย';
                return;
            }

            currentPatientData = data;
            renderEmrData(data);
            document.getElementById('emrResultContainer').classList.remove('d-none');

        } catch (error) {
            document.getElementById('loadingState').classList.add('d-none');
            document.getElementById('notFoundState').classList.remove('d-none');
            document.getElementById('notFoundMsg').textContent = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้: ' + error.message;
        } finally {
            document.getElementById('btnIcon').classList.remove('d-none');
            document.getElementById('btnSpinner').classList.add('d-none');
            document.getElementById('searchBtn').disabled = false;
        }
    }

    // Color palette for hospitals to make each hospital visually distinct
    const hospitalColorPalette = [
        { bg: '#e0f2fe', text: '#0369a1', border: '#bae6fd', icon: 'fa-hospital' },         // Sky
        { bg: '#f3e8ff', text: '#7e22ce', border: '#e9d5ff', icon: 'fa-hospital-user' },    // Purple
        { bg: '#dcfce7', text: '#15803d', border: '#bbf7d0', icon: 'fa-house-medical' },    // Green
        { bg: '#ffedd5', text: '#c2410c', border: '#fed7aa', icon: 'fa-hospital' },         // Orange
        { bg: '#fce7f3', text: '#be185d', border: '#fbcfe8', icon: 'fa-hospital' },         // Pink
        { bg: '#ccfbf1', text: '#0f766e', border: '#99f6e4', icon: 'fa-hospital' },         // Teal
        { bg: '#fef3c7', text: '#b45309', border: '#fde68a', icon: 'fa-hospital' },         // Amber
    ];

    function getHospitalStyle(code, name) {
        let hash = 0;
        const str = (code || '') + (name || '');
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        const index = Math.abs(hash) % hospitalColorPalette.length;
        return hospitalColorPalette[index];
    }

    let activeHospitalFilter = 'ALL';

    function renderEmrData(data) {
        const pt = data.patient;
        document.getElementById('resPatientName').textContent = pt.full_name || '-';
        document.getElementById('resPatientHn').textContent = pt.hn || '-';
        document.getElementById('resPatientCid').textContent = pt.cid || '-';
        document.getElementById('resPatientBirthday').textContent = formatThaiDateTime(pt.birthday, '') || '-';
        document.getElementById('resPatientSex').textContent = pt.sex || '-';
        document.getElementById('resPatientAge').textContent = pt.age || '-';
        document.getElementById('resPatientBlood').textContent = pt.bloodgrp || '-';
        document.getElementById('resPatientPttype').textContent = pt.pttype || '-';
        document.getElementById('resLatency').textContent = `⚡ ${data.latency_ms} ms`;

        // Render Hospital Source Badges in Patient Banner
        const hospSummaryContainer = document.getElementById('resHospitalsSummaryList');
        hospSummaryContainer.innerHTML = '';
        
        const hospitalsList = data.hospitals && data.hospitals.length > 0 
            ? data.hospitals 
            : [{ code: data.hospital?.code, name: data.hospital?.name || 'โรงพยาบาลในเครือข่าย', visit_count: data.total_visits_found || 0 }];

        hospitalsList.forEach(h => {
            const hName = h.name || h.hospital_name || (data.hospital ? data.hospital.name : 'โรงพยาบาลในเครือข่าย');
            const chip = document.createElement('div');
            chip.className = 'hosp-source-chip';
            chip.innerHTML = `
                <i class="fa-solid fa-hospital text-info"></i>
                <span class="hosp-name-text">${escapeHtml(hName)}</span>
                <span class="hosp-count-badge">${h.visit_count || 0} ครั้ง</span>
            `;
            hospSummaryContainer.appendChild(chip);
        });

        // 1. Allergies (Aggregated from all hospitals)
        const allergyContainer = document.getElementById('allergyContentList');
        const allergyBadge = document.getElementById('allergyCountBadge');
        allergyContainer.innerHTML = '';
        if (data.allergies && data.allergies.length > 0) {
            allergyBadge.textContent = data.allergies.length;
            allergyBadge.className = 'badge bg-danger rounded-pill';
            data.allergies.forEach(al => {
                const div = document.createElement('div');
                div.className = 'allergy-alert-box mb-2';
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-bold text-danger fs-6"><i class="fa-solid fa-ban me-1"></i> ${escapeHtml(al.agent)}</div>
                        ${al.hospital_name ? `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="fa-solid fa-hospital me-1"></i>${escapeHtml(al.hospital_name)}</span>` : ''}
                    </div>
                    <div class="small text-dark"><strong>อาการแพ้:</strong> ${escapeHtml(al.symptom)}</div>
                    ${al.report_date ? `<div class="small text-muted mt-0.5">รายงานเมื่อ: ${formatThaiDateTime(al.report_date, '')}</div>` : ''}
                `;
                allergyContainer.appendChild(div);
            });
        } else {
            allergyBadge.textContent = '0';
            allergyBadge.className = 'badge bg-success rounded-pill';
            allergyContainer.innerHTML = `
                <div class="p-2.5 bg-light rounded-3 text-success text-center small fw-semibold">
                    <i class="fa-solid fa-circle-check me-1"></i> ไม่พบประวัติการแพ้ยาในระบบ
                </div>
            `;
        }

        // 2. Chronic Clinics (Aggregated from all hospitals)
        const clinicContainer = document.getElementById('clinicContentList');
        const clinicBadge = document.getElementById('clinicCountBadge');
        clinicContainer.innerHTML = '';
        if (data.clinics && data.clinics.length > 0) {
            clinicBadge.textContent = data.clinics.length;
            data.clinics.forEach(cl => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-semibold';
                badge.innerHTML = `<i class="fa-solid fa-notes-medical me-1"></i> ${escapeHtml(cl.clinic_name)} ${cl.hospital_name ? `<small class="text-muted">(${escapeHtml(cl.hospital_name)})</small>` : ''} ${cl.begin_date ? `<small class="text-muted">[${formatThaiDateTime(cl.begin_date, '')}]</small>` : ''}`;
                clinicContainer.appendChild(badge);
            });
        } else {
            clinicBadge.textContent = '0';
            clinicContainer.innerHTML = `
                <div class="p-2 bg-light rounded-3 text-muted text-center small w-100">
                    ไม่พบข้อมูลการขึ้นทะเบียนคลินิกโรคเรื้อรัง
                </div>
            `;
        }

        // Setup Hospital Filter Tabs (Only shown if > 1 hospital found)
        renderHospitalFilterButtons(data);

        // 3. Render Visit History Table
        activeHospitalFilter = 'ALL';
        renderVisitTable(data.visits || []);
    }

    function renderHospitalFilterButtons(data) {
        const filterContainer = document.getElementById('hospitalFilterContainer');
        const buttonsContainer = document.getElementById('hospitalFilterButtons');
        buttonsContainer.innerHTML = '';

        const hospitals = data.hospitals || [];
        // Only show filter bar if there are 2 or more DIFFERENT hospitals
        if (hospitals.length > 1) {
            filterContainer.style.setProperty('display', 'flex', 'important');

            // All Button
            const allBtn = document.createElement('button');
            allBtn.type = 'button';
            allBtn.className = 'btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold filter-hosp-btn active';
            allBtn.id = 'filterBtn-ALL';
            allBtn.innerHTML = `<i class="fa-solid fa-layer-group me-1"></i> ทุก รพ. (${data.total_visits_found || 0})`;
            allBtn.onclick = () => filterVisits('ALL');
            buttonsContainer.appendChild(allBtn);

            // Per hospital buttons
            hospitals.forEach(h => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-light border text-dark rounded-pill px-3 py-1 fw-semibold filter-hosp-btn';
                btn.id = `filterBtn-${h.code}`;
                btn.innerHTML = `<i class="fa-solid fa-hospital text-primary me-1"></i> ${escapeHtml(h.name)} (${h.visit_count || 0})`;
                btn.onclick = () => filterVisits(h.code);
                buttonsContainer.appendChild(btn);
            });
        } else {
            filterContainer.style.setProperty('display', 'none', 'important');
        }
    }

    function filterVisits(hospCode) {
        activeHospitalFilter = hospCode;
        document.querySelectorAll('.filter-hosp-btn').forEach(btn => {
            btn.classList.remove('btn-primary', 'active');
            btn.classList.add('btn-light', 'border', 'text-dark');
        });
        const currentBtn = document.getElementById(`filterBtn-${hospCode}`);
        if (currentBtn) {
            currentBtn.classList.add('btn-primary', 'active');
            currentBtn.classList.remove('btn-light', 'border', 'text-dark');
        }

        if (!currentPatientData || !currentPatientData.visits) return;

        let filtered = currentPatientData.visits;
        if (hospCode !== 'ALL') {
            filtered = currentPatientData.visits.filter(v => (v.hospital_code || '') === hospCode);
        }
        renderVisitTable(filtered);
    }

    let activeModalVisit = null;
    let activeModalDetail = null;
    let currentModalMode = 'IPD';

    function switchModalMode(mode) {
        currentModalMode = mode;
        const btnIpd = document.getElementById('modeBtnIpd');
        const btnOpd = document.getElementById('modeBtnOpd');
        const viewOpd = document.getElementById('mViewOpdFields');
        const viewIpd = document.getElementById('mViewIpdFields');
        const cardHeader = document.getElementById('cardClinicalHeader');
        const modalHeader = document.getElementById('rimsModalHeader');
        const headerIcon = document.getElementById('modalHeaderIcon');
        const modalTitle = document.getElementById('visitDetailModalLabel');

        if (btnIpd) btnIpd.className = 'modal-mode-pill';
        if (btnOpd) btnOpd.className = 'modal-mode-pill';

        if (mode === 'IPD') {
            if (btnIpd) btnIpd.className = 'modal-mode-pill active-ipd';
            viewOpd.classList.add('d-none');
            viewIpd.classList.remove('d-none');
            cardHeader.innerHTML = '<i class="fa-solid fa-bed-pulse text-danger"></i> ข้อมูลการนอน รพ. (IPD)';
            modalHeader.classList.add('header-ipd');
            headerIcon.innerHTML = '<i class="fa-solid fa-bed-pulse"></i>';
            modalTitle.textContent = 'รายละเอียดการรักษาผู้ป่วยใน (IPD / Admission)';
        } else {
            // OPD Mode
            if (btnOpd) btnOpd.className = 'modal-mode-pill active-opd';
            viewOpd.classList.remove('d-none');
            viewIpd.classList.add('d-none');
            cardHeader.innerHTML = '<i class="fa-solid fa-stethoscope text-primary"></i> ข้อมูลผู้ป่วยนอก (OPD)';
            modalHeader.classList.remove('header-ipd');
            headerIcon.innerHTML = '<i class="fa-solid fa-file-medical"></i>';
            modalTitle.textContent = 'รายละเอียดการรักษาผู้ป่วยนอก (OPD)';
        }

        if (activeModalDetail) {
            renderModalTabs(mode, activeModalDetail);
        }
    }

    function renderVisitTable(visits) {
        const tableBody = document.getElementById('vemrTableBody');
        const totalVisitsBadge = document.getElementById('totalVisitsBadge');
        tableBody.innerHTML = '';
        totalVisitsBadge.textContent = `${visits.length} Visits`;

        if (visits && visits.length > 0) {
            visits.forEach((v, index) => {
                const thaiDateFormatted = formatThaiDateTime(v.vstdate, v.vsttime);
                const hospName = v.hospital_name || (currentPatientData.hospital ? currentPatientData.hospital.name : 'โรงพยาบาลในเครือข่าย');
                const hospCode = v.hospital_code || (currentPatientData.hospital ? currentPatientData.hospital.code : '');
                const hStyle = getHospitalStyle(hospCode, hospName);
                const visitHn = v.hn || (currentPatientData.patient ? currentPatientData.patient.hn : '') || '-';
                const isIpd = Boolean(v.is_ipd || v.an);

                // Vital signs short summary
                let vitalsText = [];
                if (v.bps > 0 || v.bpd > 0) vitalsText.push(`BP: ${v.bps}/${v.bpd}`);
                if (v.pulse > 0) vitalsText.push(`PR: ${v.pulse}`);
                if (v.temperature > 0) vitalsText.push(`T: ${v.temperature}°C`);

                const tr = document.createElement('tr');
                if (isIpd) {
                    tr.className = 'vemr-row-ipd';
                }

                tr.onclick = function() {
                    openVisitDetailModal(v, index);
                };

                // Build Date Column
                let dateColHtml = `<div><i class="fa-regular fa-calendar text-muted me-1"></i>${thaiDateFormatted}</div>`;
                if (isIpd) {
                    if (v.dch_date) {
                        dateColHtml += `<div class="small text-muted mt-0.5"><i class="fa-solid fa-arrow-right-from-bracket me-1 text-secondary"></i>Dch: ${formatThaiDateTime(v.dch_date, v.dch_time)}</div>`;
                    } else {
                        dateColHtml += `<div class="small text-danger fw-bold mt-0.5"><i class="fa-solid fa-circle-dot me-1"></i>ยังนอน รพ.</div>`;
                    }
                }

                // Build Department / Ward Column
                let deptColHtml = '';
                if (isIpd) {
                    deptColHtml = `
                        <span class="badge" style="background:#ffedd5; color:#9a3412; border:1px solid #fed7aa; font-weight:600;">
                            <i class="fa-solid fa-bed-pulse me-1"></i> ${escapeHtml(v.ward_name || v.department || 'IPD')}
                        </span>
                        <div class="small fw-bold mt-1" style="color:#c2410c;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> นอน ${v.los || 1} วัน
                        </div>
                    `;
                } else {
                    deptColHtml = `
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold">
                            <i class="fa-solid fa-clinic-medical text-primary me-1"></i> ${escapeHtml(v.department || '-')}
                        </span>
                    `;
                }

                // Build Doctor Column (Expanded 225px width)
                let doctorColHtml = '';
                const docName = v.doctor_name || v.adm_doctor || '';
                if (docName) {
                    if (isIpd) {
                        doctorColHtml = `
                            <span class="fw-bold text-slate-800 d-inline-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-user-doctor" style="color:#ea580c;"></i> ${escapeHtml(docName)}
                            </span>
                            <div class="small text-muted mt-0.5">แพทย์เจ้าของไข้ / ผู้ตรวจ</div>
                        `;
                    } else {
                        doctorColHtml = `
                            <span class="fw-semibold text-slate-800 d-inline-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-user-doctor text-primary"></i> ${escapeHtml(docName)}
                            </span>
                        `;
                    }
                } else {
                    doctorColHtml = `<span class="text-muted small">-</span>`;
                }

                // Build PDX Column
                let pdxBadge = isIpd 
                    ? `<span class="badge" style="background:#fed7aa; color:#9a3412; font-weight:700; border:1px solid #fdba74; margin-right:4px;"><i class="fa-solid fa-bed-pulse me-1"></i>IPD</span>`
                    : `<span class="badge bg-light text-secondary border me-1">OPD</span>`;

                tr.innerHTML = `
                    <td class="text-center fw-bold text-muted">${index + 1}</td>
                    <td>
                        <span class="badge badge-hosp" style="background:${hStyle.bg}; color:${hStyle.text}; border: 1px solid ${hStyle.border};">
                            <i class="fa-solid ${hStyle.icon} me-1"></i> ${escapeHtml(hospName)}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${escapeHtml(visitHn)}</div>
                        ${isIpd && v.an ? `<div class="small fw-bold" style="color:#c2410c;"><i class="fa-solid fa-bed-pulse me-1"></i>AN: ${escapeHtml(v.an)}</div>` : ''}
                    </td>
                    <td class="fw-semibold text-slate-800">
                        ${dateColHtml}
                    </td>
                    <td>
                        ${deptColHtml}
                    </td>
                    <td>
                        ${doctorColHtml}
                    </td>
                    <td>
                        ${pdxBadge}
                        <span class="badge bg-warning bg-opacity-25 text-dark border border-warning px-2 py-1 rounded me-1 fw-bold">${escapeHtml(v.pdx)}</span>
                        <span class="fw-semibold text-slate-800">${escapeHtml(v.pdx_name || '-')}</span>
                        ${v.cc ? `<div class="small text-muted text-truncate mt-0.5" style="max-width: 250px;">CC: ${escapeHtml(v.cc)}</div>` : ''}
                    </td>
                    <td>
                        <small class="fw-semibold text-danger">${vitalsText.join(' | ') || '-'}</small>
                    </td>
                    <td class="text-center text-muted">
                        <i class="fa-solid fa-chevron-right text-primary opacity-75"></i>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        } else {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        ไม่พบประวัติการมารับบริการ
                    </td>
                </tr>
            `;
        }
    }

    async function openVisitDetailModal(visit, index) {
        if (!currentPatientData) return;
        activeModalVisit = visit;
        const pt = currentPatientData.patient;
        const hospName = visit.hospital_name || (currentPatientData.hospital ? currentPatientData.hospital.name : 'โรงพยาบาลในเครือข่าย');
        const hospCode = visit.hospital_code || (currentPatientData.hospital ? currentPatientData.hospital.code : '');
        const visitHn = visit.hn || pt.hn || '-';
        const isIpd = Boolean(visit.is_ipd || visit.an);
        const thaiDateFormatted = formatThaiDateTime(visit.vstdate, visit.vsttime);

        // 1. Fill Column 1 (Patient Info)
        document.getElementById('mPtHn').innerHTML = visit.an 
            ? `${escapeHtml(visitHn)} <span class="badge" style="background:#fed7aa; color:#9a3412; font-size:0.75rem;">AN: ${escapeHtml(visit.an)}</span>`
            : escapeHtml(visitHn);
        document.getElementById('mPtCid').textContent = pt.cid || '-';
        document.getElementById('mPtName').textContent = pt.full_name || '-';
        document.getElementById('mPtPttype').textContent = visit.pttype_name || pt.pttype || '-';
        document.getElementById('mPtSexAge').textContent = `${pt.sex} / ${pt.age}`;
        
        // Allergies preview
        if (currentPatientData.allergies && currentPatientData.allergies.length > 0) {
            const alNames = currentPatientData.allergies.map(a => a.agent).join(', ');
            document.getElementById('mPtAllergy').innerHTML = `<span class="badge bg-danger">${escapeHtml(alNames)}</span>`;
        } else {
            document.getElementById('mPtAllergy').innerHTML = `<span class="text-success small fw-semibold">ไม่แพ้ยา</span>`;
        }

        // Clinics preview
        if (currentPatientData.clinics && currentPatientData.clinics.length > 0) {
            const clNames = currentPatientData.clinics.map(c => c.clinic_name).join(', ');
            document.getElementById('mPtClinic').innerHTML = `<span class="badge bg-primary bg-opacity-10 text-primary border">${escapeHtml(clNames)}</span>`;
        } else {
            document.getElementById('mPtClinic').textContent = '-';
        }

        // 2. Fill Column 2 (Clinical & Admission Info)
        // OPD Fields
        document.getElementById('mCliDate').textContent = thaiDateFormatted;
        document.getElementById('mCliHospital').textContent = hospName;
        document.getElementById('mCliDep').textContent = visit.department || '-';
        document.getElementById('mCliDoctor').textContent = visit.doctor_name || '-';
        document.getElementById('mCliCC').textContent = visit.cc || '-';

        // IPD Fields
        const admDateStr = visit.adm_date ? formatThaiDateTime(visit.adm_date, visit.adm_time) : thaiDateFormatted;
        const dchDateStr = visit.dch_date ? formatThaiDateTime(visit.dch_date, visit.dch_time) : 'ยังไม่จำหน่าย (Admitted)';
        document.getElementById('mIpdAdmDate').textContent = admDateStr;
        document.getElementById('mIpdDchDate').textContent = dchDateStr;
        document.getElementById('mIpdLos').textContent = `${visit.los || 1} วัน`;
        document.getElementById('mIpdWard').textContent = visit.ward_name || visit.department || 'IPD';
        document.getElementById('mIpdAdmDoctor').textContent = visit.adm_doctor || '-';
        document.getElementById('mIpdDchDoctor').textContent = visit.doctor_name || '-';
        document.getElementById('mIpdChartStatus').innerHTML = '<span class="text-muted small">กำลังตรวจสอบ...</span>';
        document.getElementById('mIpdDrgRw').textContent = visit.drg ? `${visit.drg} (RW: ${visit.rw || 0})` : '-';
        document.getElementById('mIpdDchStatus').textContent = visit.dch_type || visit.dch_status || '-';
        document.getElementById('mIpdCC').textContent = visit.cc || '-';

        // Mode Switcher setup
        const modeSwitcherContainer = document.getElementById('modalModeSwitcherContainer');
        const ipdSummaryBadge = document.getElementById('modalIpdLosSummary');

        if (isIpd) {
            modeSwitcherContainer.classList.remove('d-none');
            ipdSummaryBadge.innerHTML = `<i class="fa-solid fa-bed-pulse me-1"></i> นอน รพ. ${visit.los || 1} วัน (${formatThaiDateTime(visit.adm_date || visit.vstdate, '')} - ${visit.dch_date ? formatThaiDateTime(visit.dch_date, '') : 'ปัจจุบัน'})`;
            switchModalMode('IPD');
        } else {
            modeSwitcherContainer.classList.add('d-none');
            switchModalMode('OPD');
        }

        // 3. Fill Column 3 (Vitals)
        document.getElementById('mVitBP').textContent = (visit.bps > 0 || visit.bpd > 0) ? `${visit.bps}/${visit.bpd} mmHg` : '-';
        document.getElementById('mVitPulse').textContent = visit.pulse > 0 ? `${visit.pulse} bpm` : '-';
        document.getElementById('mVitTemp').textContent = visit.temperature > 0 ? `${visit.temperature} °C` : '-';
        document.getElementById('mVitBwHeight').textContent = `${visit.bw > 0 ? visit.bw + ' kg' : '-'} / ${visit.height > 0 ? visit.height + ' cm' : '-'}`;
        document.getElementById('mVitBMI').textContent = visit.bmi > 0 ? `${visit.bmi} kg/m²` : '-';
        document.getElementById('mVitLatency').textContent = `⚡ กำลังเชื่อมต่อ...`;

        // Update modal sub-title
        const anMetaText = isIpd && visit.an ? ` | AN: <strong>${escapeHtml(visit.an)}</strong>` : '';
        document.getElementById('modalVisitMeta').innerHTML = `สืบค้นข้อมูลสดจากระบบ HOSxP <strong>${escapeHtml(hospName)}</strong> (VN: ${escapeHtml(visit.vn)}${anMetaText})`;

        // Reset Table Contents for all 5 Tabs
        document.getElementById('modalMedTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงรายการยาจาก opitemrece...</td></tr>';
        document.getElementById('modalNonDrugTableBody').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงค่ารักษาพยาบาล (icode 3%)...</td></tr>';
        document.getElementById('modalLabTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงผลตรวจ Lab...</td></tr>';
        document.getElementById('modalDiagTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงข้อมูลการวินิจฉัย...</td></tr>';
        document.getElementById('modalProcTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงข้อมูลหัตถการ (ICD-9)...</td></tr>';
        
        // Hide Financial Summary Box initially
        const finBox = document.getElementById('mIpdFinancialSummaryBox');
        if (finBox) finBox.classList.add('d-none');

        const modal = new bootstrap.Modal(document.getElementById('visitDetailModal'));
        modal.show();

        try {
            const response = await fetch("{{ route('manage.emr.visit-detail') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    vn: visit.vn,
                    hospital_code: hospCode 
                })
            });
            const data = await response.json();

            if (!data.success) {
                document.getElementById('modalMedTableBody').innerHTML = `<tr><td colspan="5" class="text-danger text-center py-3">${data.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล'}</td></tr>`;
                return;
            }

            activeModalDetail = data;
            document.getElementById('mVitLatency').textContent = `⚡ ${data.latency_ms} ms (ดึงสด)`;

            // Update IPD fields if returned in detail
            if (data.ward_name) document.getElementById('mIpdWard').textContent = data.ward_name;
            if (data.dch_status || data.dch_type) {
                document.getElementById('mIpdDchStatus').textContent = `${data.dch_type || ''} ${data.dch_status ? '(' + data.dch_status + ')' : ''}`.trim() || '-';
            }
            if (data.adm_doctor) document.getElementById('mIpdAdmDoctor').textContent = data.adm_doctor;
            if (data.dch_doctor) document.getElementById('mIpdDchDoctor').textContent = data.dch_doctor;
            
            // Chart summary status badge
            if (data.chart_status) {
                let cBadge = 'bg-secondary';
                if (data.chart_status.includes('สรุปชาร์จแล้ว')) cBadge = 'bg-success';
                else if (data.chart_status.includes('กำลังนอน')) cBadge = 'bg-info text-dark';
                else if (data.chart_status.includes('รอสรุป')) cBadge = 'bg-warning text-dark';
                document.getElementById('mIpdChartStatus').innerHTML = `<span class="badge ${cBadge} px-2 py-1">${escapeHtml(data.chart_status)}</span>`;
            } else {
                document.getElementById('mIpdChartStatus').textContent = '-';
            }

            // DRG / RW display
            if (data.drg || data.rw > 0) {
                document.getElementById('mIpdDrgRw').innerHTML = `
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1 fw-bold">DRG: ${escapeHtml(data.drg || '-')}</span>
                    <span class="badge bg-light text-dark border px-2 py-1">RW: ${Number(data.rw || 0).toFixed(4)}</span>
                    ${data.adjrw > 0 ? `<span class="badge bg-light text-muted border px-1.5 py-1 small">AdjRW: ${Number(data.adjrw).toFixed(4)}</span>` : ''}
                `;
            } else {
                document.getElementById('mIpdDrgRw').textContent = '-';
            }

            if (data.admdate) {
                document.getElementById('mIpdAdmDate').textContent = formatThaiDateTime(data.admdate, data.admtime);
            }
            if (data.dchdate) {
                document.getElementById('mIpdDchDate').textContent = formatThaiDateTime(data.dchdate, data.dchtime);
            }

            // Render tabs based on currently selected mode (IPD vs OPD vs ALL)
            renderModalTabs(currentModalMode, data);

        } catch (err) {
            document.getElementById('modalMedTableBody').innerHTML = `<tr><td colspan="5" class="text-danger text-center py-3">โหลดข้อมูลล้มเหลว: ${err.message}</td></tr>`;
        }
    }

    // Modal Tab Pagination State (10 items per page)
    let currentActiveTabKey = 'meds';
    let tabPagination = {
        meds: { page: 1, pageSize: 10, items: [] },
        nondrug: { page: 1, pageSize: 10, items: [] },
        labs: { page: 1, pageSize: 10, items: [] },
        diag: { page: 1, pageSize: 10, items: [] },
        proc: { page: 1, pageSize: 10, items: [] }
    };

    function changeModalTabPage(tabKey, newPage) {
        if (!tabPagination[tabKey]) return;
        const totalPages = Math.ceil(tabPagination[tabKey].items.length / tabPagination[tabKey].pageSize);
        if (newPage < 1 || newPage > totalPages) return;
        tabPagination[tabKey].page = newPage;
        renderTabContent(tabKey);
        updateTopPagination();
    }

    function updateTopPagination() {
        const topBox = document.getElementById('modalTopPaginationBox');
        if (!topBox) return;
        const currentData = tabPagination[currentActiveTabKey];
        if (!currentData || currentData.items.length <= currentData.pageSize) {
            topBox.innerHTML = '';
            return;
        }
        const totalPages = Math.ceil(currentData.items.length / currentData.pageSize);
        topBox.innerHTML = `
            <span class="small fw-bold text-muted me-1" style="font-size: 0.75rem;">
                หน้า ${currentData.page}/${totalPages}
            </span>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" ${currentData.page === 1 ? 'disabled' : ''} onclick="changeModalTabPage('${currentActiveTabKey}', ${currentData.page - 1})">
                    <i class="fa-solid fa-chevron-left" style="font-size:0.65rem;"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" ${currentData.page === totalPages ? 'disabled' : ''} onclick="changeModalTabPage('${currentActiveTabKey}', ${currentData.page + 1})">
                    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
                </button>
            </div>
        `;
    }

    // Bind tab switch event to keep top pagination in sync
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#detailTab button[data-bs-toggle="tab"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                if (target === '#meds-pane') currentActiveTabKey = 'meds';
                else if (target === '#nondrug-pane') currentActiveTabKey = 'nondrug';
                else if (target === '#labs-pane') currentActiveTabKey = 'labs';
                else if (target === '#diag-pane') currentActiveTabKey = 'diag';
                else if (target === '#proc-pane') currentActiveTabKey = 'proc';
                updateTopPagination();
            });
        });
    });

    function renderPaginationControls(tabKey, containerId, totalItems, currentPage, pageSize = 10) {
        const container = document.getElementById(containerId);
        if (!container) return;
        if (totalItems === 0) {
            container.innerHTML = '';
            if (tabKey === currentActiveTabKey) updateTopPagination();
            return;
        }
        const totalPages = Math.ceil(totalItems / pageSize);
        const startItem = (currentPage - 1) * pageSize + 1;
        const endItem = Math.min(currentPage * pageSize, totalItems);

        if (totalPages <= 1) {
            container.innerHTML = `
                <div class="modal-pagination-bar d-flex justify-content-between align-items-center text-muted small">
                    <span>แสดงทั้งหมด <strong class="text-dark">${totalItems}</strong> รายการ</span>
                </div>
            `;
            if (tabKey === currentActiveTabKey) updateTopPagination();
            return;
        }

        let pages = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else {
            pages.push(1);
            let start = Math.max(2, currentPage - 1);
            let end = Math.min(totalPages - 1, currentPage + 1);
            if (start > 2) pages.push('...');
            for (let i = start; i <= end; i++) pages.push(i);
            if (end < totalPages - 1) pages.push('...');
            pages.push(totalPages);
        }

        let pageBtnsHtml = `
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <button type="button" class="page-link py-0.5 px-2" onclick="changeModalTabPage('${tabKey}', ${currentPage - 1})" aria-label="Previous">
                        <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i>
                    </button>
                </li>
        `;

        pages.forEach(p => {
            if (p === '...') {
                pageBtnsHtml += `<li class="page-item disabled"><span class="page-link py-0.5 px-2 text-muted">…</span></li>`;
            } else {
                const isActive = (p === currentPage);
                pageBtnsHtml += `
                    <li class="page-item ${isActive ? 'active' : ''}">
                        <button type="button" class="page-link py-0.5 px-2 fw-semibold" onclick="changeModalTabPage('${tabKey}', ${p})">${p}</button>
                    </li>
                `;
            }
        });

        pageBtnsHtml += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <button type="button" class="page-link py-0.5 px-2" onclick="changeModalTabPage('${tabKey}', ${currentPage + 1})" aria-label="Next">
                        <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                    </button>
                </li>
            </ul>
        `;

        container.innerHTML = `
            <div class="modal-pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2 text-muted small">
                <div>แสดง <strong class="text-dark">${startItem} - ${endItem}</strong> จากทั้งหมด <strong class="text-dark">${totalItems}</strong> รายการ</div>
                <div>${pageBtnsHtml}</div>
            </div>
        `;

        if (tabKey === currentActiveTabKey) {
            updateTopPagination();
        }
    }

    function renderTabContent(tabKey) {
        if (tabKey === 'meds') renderMedsTab();
        else if (tabKey === 'nondrug') renderNonDrugTab();
        else if (tabKey === 'labs') renderLabsTab();
        else if (tabKey === 'diag') renderDiagTab();
        else if (tabKey === 'proc') renderProcTab();
    }

    function renderMedsTab() {
        const { page, pageSize, items } = tabPagination.meds;
        const medBody = document.getElementById('modalMedTableBody');
        const isOpd = (currentModalMode === 'OPD');
        if (items.length > 0) {
            medBody.innerHTML = '';
            const slice = items.slice((page - 1) * pageSize, page * pageSize);
            slice.forEach((m, i) => {
                const idx = (page - 1) * pageSize + i + 1;
                const tr = document.createElement('tr');
                let catBadge = '';
                if (m.med_category) {
                    if (m.med_category.includes('ยากลับบ้าน')) {
                        catBadge = `<span class="badge-home-med mb-1"><i class="fa-solid fa-house-medical"></i> ยากลับบ้าน</span>`;
                    } else if (m.med_category.includes('นอน รพ.')) {
                        catBadge = `<span class="badge-ipd-med mb-1"><i class="fa-solid fa-syringe"></i> ยาระหว่างนอน รพ.</span>`;
                    } else {
                        catBadge = `<span class="badge bg-primary bg-opacity-10 text-primary border mb-1"><i class="fa-solid fa-stethoscope"></i> ยา OPD</span>`;
                    }
                }

                // Build Periods / Dates column
                let periodsHtml = '';
                if (m.periods && m.periods.length > 0) {
                    if (m.periods.length === 1) {
                        const p = m.periods[0];
                        if (p.first_date && p.last_date && p.first_date !== p.last_date) {
                            periodsHtml += `<div class="small fw-semibold text-slate-700"><i class="fa-regular fa-calendar text-primary me-1"></i>${formatThaiDateTime(p.first_date, '')} - ${formatThaiDateTime(p.last_date, '')} <span class="badge bg-light text-dark border ms-1" style="font-size:0.7rem;">${p.qty} ${escapeHtml(m.units)}</span></div>`;
                        } else if (p.first_date) {
                            periodsHtml += `<div class="small fw-semibold text-slate-700"><i class="fa-regular fa-calendar text-primary me-1"></i>${formatThaiDateTime(p.first_date, '')} <span class="badge bg-light text-dark border ms-1" style="font-size:0.7rem;">${p.qty} ${escapeHtml(m.units)}</span></div>`;
                        }
                        if (p.sp_use) {
                            periodsHtml += `<div class="small text-primary fw-semibold mt-0.5"><i class="fa-solid fa-circle-info me-1"></i>${escapeHtml(p.sp_use)}</div>`;
                        }
                    } else {
                        periodsHtml = '<div class="d-flex flex-column gap-1">';
                        m.periods.forEach((p, pIdx) => {
                            let pDateText = '';
                            if (p.first_date && p.last_date && p.first_date !== p.last_date) {
                                pDateText = `${formatThaiDateShort(p.first_date)} - ${formatThaiDateShort(p.last_date)}`;
                            } else if (p.first_date) {
                                pDateText = `${formatThaiDateShort(p.first_date)}`;
                            }
                            periodsHtml += `
                                <div class="small fw-semibold text-slate-700 d-flex align-items-center justify-content-between gap-1 p-1 bg-light rounded border" style="font-size: 0.75rem;">
                                    <span><i class="fa-regular fa-calendar-check text-primary me-1"></i>${pDateText || `ครั้งที่ ${pIdx + 1}`}</span>
                                    <span class="badge bg-white text-dark border">${p.qty} ${escapeHtml(m.units)}</span>
                                </div>
                            `;
                        });
                        periodsHtml += '</div>';
                        if (m.sp_use) {
                            periodsHtml += `<div class="small text-primary fw-semibold mt-1"><i class="fa-solid fa-circle-info me-1"></i>${escapeHtml(m.sp_use)}</div>`;
                        }
                    }
                }
                if (!periodsHtml) periodsHtml = '<span class="text-muted small">-</span>';

                tr.innerHTML = `
                    <td class="text-muted fw-bold text-center">${idx}</td>
                    <td>
                        ${catBadge ? `<div>${catBadge}</div>` : ''}
                        <div class="fw-bold text-slate-800">${escapeHtml(m.drug_name)}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold">${m.total_qty} ${escapeHtml(m.units || '')}</span>
                    </td>
                    <td class="small text-muted">
                        <div class="fw-medium text-dark">${escapeHtml(m.usage1 || '')}</div>
                        ${m.usage2 ? `<div>${escapeHtml(m.usage2)}</div>` : ''}
                        ${m.usage3 ? `<div>${escapeHtml(m.usage3)}</div>` : ''}
                    </td>
                    <td>
                        ${periodsHtml}
                    </td>
                `;
                medBody.appendChild(tr);
            });
        } else {
            const noMedsText = isOpd
                ? '<i class="fa-solid fa-circle-info me-1"></i> ไม่มีรายการสั่งยาที่แผนกผู้ป่วยนอก (OPD) ก่อน Admit'
                : 'ไม่พบรายการสั่งยาในส่วนนี้';
            medBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">${noMedsText}</td></tr>`;
        }
        renderPaginationControls('meds', 'modalMedPagination', items.length, page, pageSize);
    }

    function renderNonDrugTab() {
        const { page, pageSize, items } = tabPagination.nondrug;
        const nonDrugBody = document.getElementById('modalNonDrugTableBody');
        if (items.length > 0) {
            nonDrugBody.innerHTML = '';
            const slice = items.slice((page - 1) * pageSize, page * pageSize);
            slice.forEach((nd, i) => {
                const idx = (page - 1) * pageSize + i + 1;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-muted fw-bold text-center">${idx}</td>
                    <td class="fw-bold text-slate-800">${escapeHtml(nd.item_name)}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold">${nd.qty}</span></td>
                    <td class="text-center text-muted small">${escapeHtml(nd.units || '-')}</td>
                    <td class="text-end text-muted">${Number(nd.unit_price || 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
                    <td class="text-end fw-bold text-primary">${Number(nd.sum_price || 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
                `;
                nonDrugBody.appendChild(tr);
            });
        } else {
            nonDrugBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">ไม่พบรายการค่ารักษาพยาบาลในส่วนนี้</td></tr>';
        }
        renderPaginationControls('nondrug', 'modalNonDrugPagination', items.length, page, pageSize);
    }

    function renderLabsTab() {
        const { page, pageSize, items, dates } = tabPagination.labs;
        const labHead = document.getElementById('modalLabTableHead');
        const labBody = document.getElementById('modalLabTableBody');
        const isOpd = (currentModalMode === 'OPD');
        const activeDates = dates || [];

        // Dynamic Lab Matrix Thead
        let theadHtml = `
            <tr>
                <th style="width: 45px;" class="text-center">#</th>
                <th style="min-width: 170px;">รายการตรวจ (Lab Test)</th>
        `;
        if (activeDates.length > 0) {
            activeDates.forEach(d => {
                theadHtml += `<th class="text-center" style="min-width: 90px; white-space: nowrap;"><i class="fa-regular fa-calendar text-primary me-1"></i>${formatThaiDateShort(d)}</th>`;
            });
        } else {
            theadHtml += `<th style="width: 150px;" class="text-center">ผลการตรวจ</th>`;
        }
        theadHtml += `
                <th style="width: 80px;" class="text-center">หน่วย</th>
                <th style="min-width: 120px;">ค่าอ้างอิงปกติ (Normal)</th>
            </tr>
        `;
        if (labHead) labHead.innerHTML = theadHtml;

        if (items.length > 0) {
            labBody.innerHTML = '';
            const slice = items.slice((page - 1) * pageSize, page * pageSize);
            slice.forEach((l, i) => {
                const idx = (page - 1) * pageSize + i + 1;
                const tr = document.createElement('tr');
                const catBadge = l.category === 'IPD'
                    ? '<span class="badge" style="background:#ffedd5; color:#9a3412; font-size:0.68rem; border:1px solid #fed7aa; margin-right:4px;">IPD</span>'
                    : '<span class="badge bg-light text-primary border" style="font-size:0.68rem; margin-right:4px;">OPD</span>';

                let rowHtml = `
                    <td class="text-muted fw-bold text-center">${idx}</td>
                    <td>
                        <div class="fw-bold text-dark">${catBadge}${escapeHtml(l.lab_name)}</div>
                        ${l.lab_group ? `<div class="small text-muted" style="font-size: 0.72rem;"><span class="badge bg-light text-secondary border mt-0.5">${escapeHtml(l.lab_group)}</span></div>` : ''}
                    </td>
                `;

                if (activeDates.length > 0) {
                    activeDates.forEach(d => {
                        const resList = l.results_by_date[d];
                        if (resList && resList.length > 0) {
                            if (resList.length === 1) {
                                rowHtml += `<td class="text-center fw-bold text-primary fs-6">${escapeHtml(resList[0].result)}</td>`;
                            } else {
                                const multiHtml = resList.map(r => `<div class="fw-bold text-primary"><span class="small text-muted me-0.5" style="font-size:0.68rem;">${r.time}:</span>${escapeHtml(r.result)}</div>`).join('');
                                rowHtml += `<td class="text-center">${multiHtml}</td>`;
                            }
                        } else {
                            rowHtml += `<td class="text-center text-muted small">-</td>`;
                        }
                    });
                } else {
                    rowHtml += `<td class="text-center text-muted">-</td>`;
                }

                rowHtml += `
                    <td class="text-center text-muted small">${escapeHtml(l.lab_unit || '-')}</td>
                    <td class="small text-muted">${escapeHtml(l.normal_value || '-')}</td>
                `;
                tr.innerHTML = rowHtml;
                labBody.appendChild(tr);
            });
        } else {
            const colSpan = (activeDates.length > 0 ? activeDates.length : 1) + 3;
            const noLabText = isOpd
                ? '<i class="fa-solid fa-circle-info me-1"></i> ไม่มีรายการตรวจ Lab ที่แผนกผู้ป่วยนอก (OPD)'
                : '<i class="fa-solid fa-circle-info me-1"></i> ไม่มีรายการตรวจ Lab ที่มีผลตรวจในส่วนนี้';
            labBody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center text-muted py-4">${noLabText}</td></tr>`;
        }
        renderPaginationControls('labs', 'modalLabPagination', items.length, page, pageSize);
    }

    function renderDiagTab() {
        const { page, pageSize, items } = tabPagination.diag;
        const diagBody = document.getElementById('modalDiagTableBody');
        const isIpd = (currentModalMode === 'IPD');
        if (items.length > 0) {
            diagBody.innerHTML = '';
            const slice = items.slice((page - 1) * pageSize, page * pageSize);
            slice.forEach((d, i) => {
                const idx = (page - 1) * pageSize + i + 1;
                const tr = document.createElement('tr');
                const badgeStyle = (d.diagtype_name || '').includes('IPD') || isIpd
                    ? 'background:#fed7aa; color:#9a3412;'
                    : 'background:#e0f2fe; color:#0369a1;';
                tr.innerHTML = `
                    <td class="text-muted fw-bold text-center">${idx}</td>
                    <td><span class="badge bg-warning bg-opacity-25 text-dark border border-warning px-2.5 py-1.5 fw-bold">${escapeHtml(d.icd10)}</span></td>
                    <td class="fw-semibold text-slate-800">${escapeHtml(d.diag_name)}</td>
                    <td><span class="badge" style="${badgeStyle}">${escapeHtml(d.diagtype_name || (isIpd ? 'IPD Diag' : 'OPD Diag'))}</span></td>
                `;
                diagBody.appendChild(tr);
            });
        } else {
            diagBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">ไม่มีข้อมูลการวินิจฉัยในส่วนนี้</td></tr>';
        }
        renderPaginationControls('diag', 'modalDiagPagination', items.length, page, pageSize);
    }

    function renderProcTab() {
        const { page, pageSize, items } = tabPagination.proc;
        const procBody = document.getElementById('modalProcTableBody');
        if (items.length > 0) {
            procBody.innerHTML = '';
            const slice = items.slice((page - 1) * pageSize, page * pageSize);
            slice.forEach((p, i) => {
                const idx = (page - 1) * pageSize + i + 1;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-muted fw-bold text-center">${idx}</td>
                    <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2.5 py-1.5 fw-bold">${escapeHtml(p.icd9)}</span></td>
                    <td class="fw-semibold text-slate-800">${escapeHtml(p.proc_name)}</td>
                    <td class="small text-dark">${escapeHtml(p.doctor_name || '-')}</td>
                    <td class="small text-muted">${escapeHtml(p.proctype_name || 'หัตถการ')}</td>
                `;
                procBody.appendChild(tr);
            });
        } else {
            procBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลหัตถการในส่วนนี้</td></tr>';
        }
        renderPaginationControls('proc', 'modalProcPagination', items.length, page, pageSize);
    }

    function renderModalTabs(mode, data) {
        if (!data) return;
        const isIpd = (mode === 'IPD');
        const isOpd = (mode === 'OPD');

        // 1. Financial Summary for IPD
        const finBox = document.getElementById('mIpdFinancialSummaryBox');
        if (isIpd && finBox && (data.total_income > 0 || data.uc_money > 0 || data.paid_money > 0)) {
            finBox.classList.remove('d-none');
            document.getElementById('mIpdIncome').textContent = Number(data.total_income || 0).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';
            document.getElementById('mIpdUcMoney').textContent = Number(data.uc_money || 0).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';
            document.getElementById('mIpdPaidMoney').textContent = Number(data.paid_money || 0).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';
        } else if (finBox) {
            finBox.classList.add('d-none');
        }

        // 2. Medications (Grouped by drug_name + med_category)
        let filteredMeds = data.medications || [];
        if (isIpd) {
            filteredMeds = filteredMeds.filter(m => (m.med_category || '').includes('ยากลับบ้าน') || (m.med_category || '').includes('นอน รพ.'));
        } else {
            filteredMeds = filteredMeds.filter(m => (m.med_category || '').includes('ผู้ป่วยนอก (OPD)'));
        }

        const groupedMedsMap = {};
        filteredMeds.forEach(m => {
            const drugKey = (m.drug_name || '').trim() + '||' + (m.med_category || '').trim();
            if (!groupedMedsMap[drugKey]) {
                groupedMedsMap[drugKey] = {
                    drug_name: m.drug_name,
                    med_category: m.med_category,
                    units: m.units || '',
                    usage1: m.usage1 || '',
                    usage2: m.usage2 || '',
                    usage3: m.usage3 || '',
                    sp_use: m.sp_use || '',
                    total_qty: 0,
                    periods: []
                };
            }
            groupedMedsMap[drugKey].total_qty += parseFloat(m.qty) || 0;
            groupedMedsMap[drugKey].periods.push({
                qty: m.qty,
                first_date: m.first_date,
                last_date: m.last_date,
                days_count: m.days_count || 1,
                sp_use: m.sp_use || ''
            });
        });

        const sortedMeds = Object.values(groupedMedsMap).sort((a, b) => {
            const isHomeA = (a.med_category || '').includes('ยากลับบ้าน') ? 0 : 1;
            const isHomeB = (b.med_category || '').includes('ยากลับบ้าน') ? 0 : 1;
            if (isHomeA !== isHomeB) return isHomeA - isHomeB;
            return (a.drug_name || '').localeCompare(b.drug_name || '');
        });

        tabPagination.meds = { page: 1, pageSize: 10, items: sortedMeds };
        document.getElementById('modalMedCount').textContent = sortedMeds.length;
        renderMedsTab();

        // 3. Non-Drug / Medical Services
        let filteredNonDrugs = data.non_drugs || [];
        if (isIpd) {
            filteredNonDrugs = filteredNonDrugs.filter(nd => (nd.category || '') === 'IPD' || (nd.category || '') === '');
        } else {
            filteredNonDrugs = filteredNonDrugs.filter(nd => (nd.category || '') === 'OPD' || (nd.category || '') === '');
        }
        tabPagination.nondrug = { page: 1, pageSize: 10, items: filteredNonDrugs };
        document.getElementById('modalNonDrugCount').textContent = filteredNonDrugs.length;
        renderNonDrugTab();

        // 4. Labs (Flowsheet Matrix: Grouped by unique test with multi-date columns)
        let validLabs = (data.lab_results || []).filter(l => {
            const res = (l.lab_result || '').trim();
            return res !== '' && res !== '-' && res !== 'null';
        });
        if (isIpd) {
            const ipdLabs = validLabs.filter(l => (l.category || '') === 'IPD');
            if (ipdLabs.length > 0) validLabs = ipdLabs;
        } else {
            const opdLabs = validLabs.filter(l => (l.category || '') === 'OPD');
            if (opdLabs.length > 0) validLabs = opdLabs;
        }

        // Extract all unique dates sorted ascending
        const labDates = [...new Set(validLabs.map(l => (l.order_date || '').substring(0, 10)).filter(d => d))].sort();

        // Group tests by lab_name
        const groupedTests = {};
        validLabs.forEach(l => {
            const key = (l.lab_name || 'Lab Test').trim();
            if (!groupedTests[key]) {
                groupedTests[key] = {
                    lab_name: l.lab_name,
                    lab_group: l.lab_group || '',
                    lab_unit: l.lab_unit || '',
                    normal_value: l.normal_value || '-',
                    category: l.category || 'OPD',
                    results_by_date: {}
                };
            }
            const d = (l.order_date || '').substring(0, 10);
            if (d) {
                if (!groupedTests[key].results_by_date[d]) {
                    groupedTests[key].results_by_date[d] = [];
                }
                groupedTests[key].results_by_date[d].push({
                    result: l.lab_result,
                    time: l.order_time ? l.order_time.substring(0, 5) : '',
                    order_date: l.order_date
                });
            }
        });

        const groupedLabList = Object.values(groupedTests).sort((a, b) => {
            if (a.lab_group !== b.lab_group) {
                return (a.lab_group || '').localeCompare(b.lab_group || '');
            }
            return (a.lab_name || '').localeCompare(b.lab_name || '');
        });

        tabPagination.labs = { page: 1, pageSize: 10, items: groupedLabList, dates: labDates };
        document.getElementById('modalLabCount').textContent = groupedLabList.length;
        renderLabsTab();

        // 5. Diagnoses
        let displayedDiags = [];
        if (isIpd) {
            displayedDiags = (data.ipd_diagnoses && data.ipd_diagnoses.length > 0) ? data.ipd_diagnoses : (data.diagnoses || []);
        } else {
            displayedDiags = data.diagnoses || [];
        }
        tabPagination.diag = { page: 1, pageSize: 10, items: displayedDiags };
        document.getElementById('modalDiagCount').textContent = displayedDiags.length;
        renderDiagTab();

        // 6. Procedures
        let filteredProcs = data.procedures || [];
        if (isIpd) {
            const ipdProcs = filteredProcs.filter(p => (p.category || '') === 'IPD');
            if (ipdProcs.length > 0) filteredProcs = ipdProcs;
        } else {
            const opdProcs = filteredProcs.filter(p => (p.category || '') === 'OPD');
            if (opdProcs.length > 0) filteredProcs = opdProcs;
        }
        tabPagination.proc = { page: 1, pageSize: 10, items: filteredProcs };
        document.getElementById('modalProcCount').textContent = filteredProcs.length;
        renderProcTab();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endpush
