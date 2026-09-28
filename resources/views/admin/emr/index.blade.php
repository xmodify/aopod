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

  .badge-hosp {
    font-size: 0.82rem;
    padding: 0.35rem 0.65rem;
    border-radius: 8px;
    font-weight: 600;
  }

  /* Modal styling matching Image 3 (RIMS style) */
  .rims-modal-header {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
    color: #ffffff;
    padding: 1.25rem 1.75rem;
    border-radius: 20px 20px 0 0;
  }

  .rims-info-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.1rem;
    height: 100%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  }
  .rims-info-card-header {
    font-size: 0.92rem;
    font-weight: 700;
    color: #1e3a8a;
    border-bottom: 2px solid #eff6ff;
    padding-bottom: 0.5rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .rims-field-row {
    display: flex;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
    line-height: 1.4;
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
    font-size: 0.88rem;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    color: #475569;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    transition: all 0.2s;
  }
  .rims-tab-nav .nav-link.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
  }
  .rims-tab-nav .nav-link.active span.badge {
    background: #ffffff !important;
    color: #2563eb !important;
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
                            <th style="width: 155px;">โรงพยาบาล</th>
                            <th style="width: 100px;">HN</th>
                            <th style="width: 155px;">วันที่ / เวลา (พ.ศ.)</th>
                            <th style="width: 145px;">แผนก / ห้องตรวจ</th>
                            <th style="width: 160px;">แพทย์ผู้ตรวจ</th>
                            <th>การวินิจฉัยหลัก (PDX)</th>
                            <th style="width: 175px;">สัญญาณชีพ (BP/PR/Temp)</th>
                            <th style="width: 35px;" class="text-center"></th>
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

<!-- Modal: Visit Detail (ออกแบบโครงสร้าง 3 คอลัมน์ + 5 แท็บตามรูปที่ 3) -->
<div class="modal fade" id="visitDetailModal" tabindex="-1" aria-labelledby="visitDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);">
            <!-- Modal Header (RIMS Blue Style) -->
            <div class="rims-modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 bg-white bg-opacity-20 text-white rounded-3 fs-4">
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

            <div class="modal-body p-4 bg-slate-50" style="background-color: #f8fafc;">
                <!-- Status Banner -->
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4 py-2.5 px-3 border-0 shadow-sm" style="border-radius: 12px; background: #dcfce7; color: #15803d;">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                    <div class="small fw-semibold">
                        <strong>สถานะ:</strong> ดึงข้อมูลสำเร็จจากตาราง <code class="fw-bold text-dark">opitemrece</code> และฐานข้อมูล HOSxP ของโรงพยาบาลต้นทางแบบ Real-time
                    </div>
                </div>

                <!-- 3 Information Cards Grid (ตามรูปที่ 3) -->
                <div class="row g-3 mb-4">
                    <!-- Column 1: ข้อมูลผู้ป่วย -->
                    <div class="col-12 col-md-4">
                        <div class="rims-info-card">
                            <div class="rims-info-card-header">
                                <i class="fa-solid fa-user-circle text-primary"></i> ข้อมูลผู้ป่วย
                            </div>
                            <div class="rims-field-row">
                                <div class="rims-field-label">HN:</div>
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

                    <!-- Column 2: ข้อมูลทางคลินิก -->
                    <div class="col-12 col-md-4">
                        <div class="rims-info-card">
                            <div class="rims-info-card-header">
                                <i class="fa-solid fa-stethoscope text-primary"></i> ข้อมูลทางคลินิก
                            </div>
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
                <div class="bg-white p-3.5 rounded-4 border shadow-sm">
                    <ul class="nav nav-pills rims-tab-nav mb-3 border-bottom pb-2" id="detailTab" role="tablist" style="gap: 8px;">
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

                    <div class="tab-content" id="detailTabContent">
                        <!-- 1. Medications Table (icode 1%) -->
                        <div class="tab-pane fade show active" id="meds-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">#</th>
                                            <th>ชื่อยา / เวชภัณฑ์</th>
                                            <th style="width: 120px;" class="text-center">จำนวน</th>
                                            <th>วิธีใช้ / คำแนะนำ (Drug Usage)</th>
                                            <th style="width: 180px;">คำสั่งพิเศษ (Sp Use)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalMedTableBody">
                                        <!-- Filled by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 2. Non-Drug / Medical Service Fees Table (icode 3%) -->
                        <div class="tab-pane fade" id="nondrug-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
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
                        </div>

                        <!-- 3. Labs Table -->
                        <div class="tab-pane fade" id="labs-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
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
                        </div>

                        <!-- 4. Diagnoses Table (ICD-10) -->
                        <div class="tab-pane fade" id="diag-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
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
                        </div>

                        <!-- 5. Procedures Table (ICD-9) -->
                        <div class="tab-pane fade" id="proc-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
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
                const visitHn = v.hn || currentPatientData.patient.hn || '-';

                // Vital signs short summary
                let vitalsText = [];
                if (v.bps > 0 || v.bpd > 0) vitalsText.push(`BP: ${v.bps}/${v.bpd}`);
                if (v.pulse > 0) vitalsText.push(`PR: ${v.pulse}`);
                if (v.temperature > 0) vitalsText.push(`T: ${v.temperature}°C`);

                const tr = document.createElement('tr');
                tr.onclick = function() {
                    openVisitDetailModal(v, index);
                };

                tr.innerHTML = `
                    <td class="text-center fw-bold text-muted">${index + 1}</td>
                    <td>
                        <span class="badge badge-hosp" style="background:${hStyle.bg}; color:${hStyle.text}; border: 1px solid ${hStyle.border};">
                            <i class="fa-solid ${hStyle.icon} me-1"></i> ${escapeHtml(hospName)}
                        </span>
                    </td>
                    <td class="fw-bold text-dark">${escapeHtml(visitHn)}</td>
                    <td class="fw-semibold text-slate-800">
                        <i class="fa-regular fa-calendar text-muted me-1"></i> ${thaiDateFormatted}
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold">${escapeHtml(v.department)}</span>
                    </td>
                    <td>
                        ${v.doctor_name ? `<span class="fw-semibold text-slate-800 d-inline-flex align-items-center gap-1.5"><i class="fa-solid fa-user-doctor text-primary"></i> ${escapeHtml(v.doctor_name)}</span>` : '<span class="text-muted small">-</span>'}
                    </td>
                    <td>
                        <span class="badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-50 px-2 py-1 rounded me-1">${escapeHtml(v.pdx)}</span>
                        <span class="fw-semibold text-slate-800">${escapeHtml(v.pdx_name || '-')}</span>
                        ${v.cc ? `<div class="small text-muted text-truncate mt-0.5" style="max-width: 240px;">CC: ${escapeHtml(v.cc)}</div>` : ''}
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
        const pt = currentPatientData.patient;
        const hospName = visit.hospital_name || (currentPatientData.hospital ? currentPatientData.hospital.name : 'โรงพยาบาลในเครือข่าย');
        const hospCode = visit.hospital_code || (currentPatientData.hospital ? currentPatientData.hospital.code : '');
        const visitHn = visit.hn || pt.hn || '-';
        const thaiDateFormatted = formatThaiDateTime(visit.vstdate, visit.vsttime);

        // 1. Fill Column 1 (Patient Info)
        document.getElementById('mPtHn').textContent = visitHn;
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

        // 2. Fill Column 2 (Clinical Info)
        document.getElementById('mCliDate').textContent = thaiDateFormatted;
        document.getElementById('mCliHospital').textContent = hospName;
        document.getElementById('mCliDep').textContent = visit.department || '-';
        document.getElementById('mCliDoctor').textContent = visit.doctor_name || '-';
        document.getElementById('mCliCC').textContent = visit.cc || '-';

        // 3. Fill Column 3 (Vitals)
        document.getElementById('mVitBP').textContent = (visit.bps > 0 || visit.bpd > 0) ? `${visit.bps}/${visit.bpd} mmHg` : '-';
        document.getElementById('mVitPulse').textContent = visit.pulse > 0 ? `${visit.pulse} bpm` : '-';
        document.getElementById('mVitTemp').textContent = visit.temperature > 0 ? `${visit.temperature} °C` : '-';
        document.getElementById('mVitBwHeight').textContent = `${visit.bw > 0 ? visit.bw + ' kg' : '-'} / ${visit.height > 0 ? visit.height + ' cm' : '-'}`;
        document.getElementById('mVitBMI').textContent = visit.bmi > 0 ? `${visit.bmi} kg/m²` : '-';
        document.getElementById('mVitLatency').textContent = `⚡ กำลังเชื่อมต่อ...`;

        // Update modal sub-title
        document.getElementById('modalVisitMeta').innerHTML = `สืบค้นข้อมูลสดจากระบบ HOSxP <strong>${escapeHtml(hospName)}</strong> (VN: ${escapeHtml(visit.vn)})`;

        // Reset Table Contents for all 5 Tabs
        document.getElementById('modalMedTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงรายการยาจาก opitemrece (icode 1%)...</td></tr>';
        document.getElementById('modalNonDrugTableBody').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงค่ารักษาพยาบาล (icode 3%)...</td></tr>';
        document.getElementById('modalLabTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงผลตรวจ Lab...</td></tr>';
        document.getElementById('modalDiagTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงข้อมูลการวินิจฉัย...</td></tr>';
        document.getElementById('modalProcTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังดึงข้อมูลหัตถการ (ICD-9)...</td></tr>';
        
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
                document.getElementById('modalMedTableBody').innerHTML = `<tr><td colspan="5" class="text-danger text-center py-3">${data.message || 'เกิดข้อผิดพลาด'}</td></tr>`;
                return;
            }

            document.getElementById('mVitLatency').textContent = `⚡ ${data.latency_ms} ms (ดึงสด)`;

            // 1. Medications (icode 1% from opitemrece)
            const medBody = document.getElementById('modalMedTableBody');
            document.getElementById('modalMedCount').textContent = data.medications ? data.medications.length : 0;
            if (data.medications && data.medications.length > 0) {
                medBody.innerHTML = '';
                data.medications.forEach((m, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-muted fw-bold text-center">${idx + 1}</td>
                        <td class="fw-bold text-slate-800">${escapeHtml(m.drug_name)}</td>
                        <td class="text-center"><span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold">${m.qty} ${escapeHtml(m.units)}</span></td>
                        <td class="small text-muted">${escapeHtml(m.usage1)} ${escapeHtml(m.usage2)} ${escapeHtml(m.usage3)}</td>
                        <td class="small text-primary fw-semibold">${escapeHtml(m.sp_use || '-')}</td>
                    `;
                    medBody.appendChild(tr);
                });
            } else {
                medBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">ไม่พบรายการสั่งยาในครั้งนี้</td></tr>';
            }

            // 2. Non-Drug / Medical Services (icode 3% from opitemrece)
            const nonDrugBody = document.getElementById('modalNonDrugTableBody');
            document.getElementById('modalNonDrugCount').textContent = data.non_drugs ? data.non_drugs.length : 0;
            if (data.non_drugs && data.non_drugs.length > 0) {
                nonDrugBody.innerHTML = '';
                data.non_drugs.forEach((nd, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-muted fw-bold text-center">${idx + 1}</td>
                        <td class="fw-bold text-slate-800">${escapeHtml(nd.item_name)}</td>
                        <td class="text-center"><span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold">${nd.qty}</span></td>
                        <td class="text-center text-muted small">${escapeHtml(nd.units || '-')}</td>
                        <td class="text-end text-muted">${Number(nd.unit_price || 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
                        <td class="text-end fw-bold text-primary">${Number(nd.sum_price || 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
                    `;
                    nonDrugBody.appendChild(tr);
                });
            } else {
                nonDrugBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">ไม่พบรายการค่ารักษาพยาบาลในครั้งนี้</td></tr>';
            }

            // 3. Labs
            const labBody = document.getElementById('modalLabTableBody');
            document.getElementById('modalLabCount').textContent = data.lab_results ? data.lab_results.length : 0;
            if (data.lab_results && data.lab_results.length > 0) {
                labBody.innerHTML = '';
                data.lab_results.forEach((l, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-muted fw-bold text-center">${idx + 1}</td>
                        <td class="fw-bold text-dark">${escapeHtml(l.lab_name)}</td>
                        <td class="text-center fw-bold text-primary fs-6">${escapeHtml(l.lab_result)}</td>
                        <td class="text-center text-muted small">${escapeHtml(l.lab_unit)}</td>
                        <td class="small text-muted">${escapeHtml(l.normal_value)}</td>
                    `;
                    labBody.appendChild(tr);
                });
            } else {
                labBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">ไม่มีรายการตรวจ Lab ในครั้งนี้</td></tr>';
            }

            // 4. Diagnoses (ICD-10)
            const diagBody = document.getElementById('modalDiagTableBody');
            document.getElementById('modalDiagCount').textContent = data.diagnoses ? data.diagnoses.length : 0;
            if (data.diagnoses && data.diagnoses.length > 0) {
                diagBody.innerHTML = '';
                data.diagnoses.forEach((d, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-muted fw-bold text-center">${idx + 1}</td>
                        <td><span class="badge bg-warning bg-opacity-25 text-dark border border-warning px-2.5 py-1.5 fw-bold">${escapeHtml(d.icd10)}</span></td>
                        <td class="fw-semibold text-slate-800">${escapeHtml(d.diag_name)}</td>
                        <td class="small text-muted">${escapeHtml(d.diagtype_name)}</td>
                    `;
                    diagBody.appendChild(tr);
                });
            } else {
                diagBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">ไม่มีข้อมูลการวินิจฉัย</td></tr>';
            }

            // 5. Procedures (ICD-9)
            const procBody = document.getElementById('modalProcTableBody');
            document.getElementById('modalProcCount').textContent = data.procedures ? data.procedures.length : 0;
            if (data.procedures && data.procedures.length > 0) {
                procBody.innerHTML = '';
                data.procedures.forEach((p, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-muted fw-bold text-center">${idx + 1}</td>
                        <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2.5 py-1.5 fw-bold">${escapeHtml(p.icd9)}</span></td>
                        <td class="fw-semibold text-slate-800">${escapeHtml(p.proc_name)}</td>
                        <td class="small text-dark">${escapeHtml(p.doctor_name || '-')}</td>
                        <td class="small text-muted">${escapeHtml(p.proctype_name || 'หัตถการ')}</td>
                    `;
                    procBody.appendChild(tr);
                });
            } else {
                procBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลหัตถการในครั้งนี้</td></tr>';
            }

        } catch (err) {
            document.getElementById('modalMedTableBody').innerHTML = `<tr><td colspan="5" class="text-danger text-center py-3">โหลดข้อมูลล้มเหลว: ${err.message}</td></tr>`;
        }
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
