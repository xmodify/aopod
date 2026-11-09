@extends('layouts.admin')

@section('title', 'บริการ OPOH - จำนวนที่แพทย์แต่ละคนไปตรวจ')
@section('header_title', 'บริการ OPOH (One Region One Province One Hospital)')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
<style>
  .flatpickr-calendar {
    font-family: 'Inter', 'Noto Sans Thai', sans-serif !important;
    border-radius: 16px !important;
    box-shadow: 0 15px 35px rgba(2, 132, 199, 0.2) !important;
    border: 1px solid rgba(2, 132, 199, 0.25) !important;
  }
  .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
  }
  .flatpickr-day:hover {
    background: rgba(2, 132, 199, 0.15) !important;
  }
  .btn-quick-range {
    font-size: 0.78rem;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.2s;
  }
  .table-visits thead th {
    background: #f0f7ff !important;
    color: #0369a1 !important;
    font-weight: 700 !important;
    text-align: center !important;
    vertical-align: middle !important;
    font-size: 0.85rem !important;
    padding: 10px 8px !important;
    border-bottom: 2px solid #bae6fd !important;
  }
  .table-visits tbody td {
    vertical-align: middle !important;
    padding: 8px 10px !important;
    font-size: 0.88rem !important;
  }
  .table-visits tfoot tr {
    background: #e0f2fe !important;
    font-weight: 800 !important;
    border-top: 2px solid #0284c7 !important;
    color: #0369a1 !important;
  }
  .nav-pills .nav-link {
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
  }
  .nav-pills .nav-link:hover {
    background: #f1f5f9;
  }
  .nav-pills .nav-link.active {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    color: #fff !important;
    border-color: #0284c7 !important;
  }
  .nav-pills .nav-link#pills-admin-ipd-tab.active {
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
    color: #fff !important;
    border-color: #e11d48 !important;
  }
  #doctorIpdTable thead th {
    background: #fff1f2 !important;
    color: #be123c !important;
    border-bottom: 2px solid #fecdd3 !important;
  }
  #doctorIpdTable tfoot tr.tr-aoph {
    background: #ffe4e6 !important;
    color: #9f1239 !important;
    border-top: 2px solid #f43f5e !important;
  }
  #doctorIpdTable tfoot tr.tr-grand,
  #doctorVisitsTable tfoot tr.tr-grand {
    background: #ecfdf5 !important;
    color: #065f46 !important;
    border-top: 1px dashed #10b981 !important;
  }
  #doctorVisitsTable tfoot tr.tr-aoph {
    background: #e0f2fe !important;
    color: #0369a1 !important;
    border-top: 2px solid #0284c7 !important;
  }

  /* Doctor Income Modal Segment Buttons */
  .doc-inc-type-btn {
    border: none !important;
    border-radius: 20px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #475569 !important;
    background: #f1f5f9 !important;
    padding: 6px 14px !important;
    transition: all 0.2s ease-in-out !important;
    cursor: pointer !important;
  }
  .doc-inc-type-btn:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
  }
  .doc-inc-type-btn.active[data-type="ALL"],
  .doc-inc-type-btn.active-all {
    background: #1e293b !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(30, 41, 59, 0.3) !important;
  }
  .doc-inc-type-btn.active[data-type="OPD"],
  .doc-inc-type-btn.active-opd {
    background: #0284c7 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35) !important;
  }
  .doc-inc-type-btn.active[data-type="IPD"],
  .doc-inc-type-btn.active-ipd {
    background: #e11d48 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(225, 29, 72, 0.35) !important;
  }

  /* Modal Table Styles */
  #doctorIncomeTable th,
  #doctorIncomeTable td {
    white-space: nowrap !important;
    vertical-align: middle !important;
    padding: 8px 12px !important;
  }
  .col-inc-total {
    min-width: 155px !important;
    padding-right: 18px !important;
  }

  /* ตัวเลขต่อ รพ. ที่กดดูรายเคสได้ */
  .btn-rec-cell {
    background: transparent;
    border: 0;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 700;
    color: #0369a1;
    text-decoration: underline dotted;
    text-underline-offset: 3px;
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .btn-rec-cell:hover { background: #0284c7; color: #fff; text-decoration: none; }
  .btn-rec-cell-ipd { color: #be123c; }
  .btn-rec-cell-ipd:hover { background: #e11d48; color: #fff; }
  #docRecTable th, #docRecTable td { white-space: nowrap; vertical-align: middle; font-size: 0.85rem; }
</style>
@endpush

@section('content')
{{-- 1. Summary Cards & Navigation --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="glass-card p-3 d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="fa-solid fa-user-doctor fs-4"></i>
            </div>
            <div>
                <span class="text-secondary small fw-semibold">แพทย์ทั้งหมดในระบบ</span>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalDoctors) }} <span class="fs-6 text-muted fw-normal">ท่าน</span></h3>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="glass-card p-3 d-flex align-items-center gap-3">
            <div class="p-3 bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="fa-solid fa-circle-check fs-4"></i>
            </div>
            <div>
                <span class="text-secondary small fw-semibold">สถานะเปิดใช้งาน</span>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($activeDoctors) }} <span class="fs-6 text-muted fw-normal">ท่าน</span></h3>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="glass-card p-3 d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="fa-solid fa-calendar-check fs-4 text-primary"></i>
            </div>
            <div class="flex-grow-1">
                <span class="text-secondary small fw-semibold d-block">ยอดช่วงที่เลือก (OPD / IPD)</span>
                <div class="d-flex align-items-baseline gap-2">
                    <h4 class="fw-bold mb-0 text-primary">{{ number_format($colTotals['period']) }} <span class="fs-6 text-muted fw-normal">ครั้ง</span></h4>
                    <span class="text-muted fw-bold">/</span>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($colIpdTotals['period']) }} <span class="fs-6 text-muted fw-normal">ราย</span></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 d-flex align-items-center">
        <a href="{{ route('web.opoh') }}" target="_blank" class="glass-card p-3 w-100 d-flex align-items-center justify-content-between text-decoration-none text-dark hover-shadow" style="transition: all 0.2s;">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-info bg-opacity-10 text-info rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="fa-solid fa-chart-pie fs-4"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark">แดชบอร์ด OPOH</span>
                    <span class="text-primary small fw-semibold">เปิดดูสถิติราย รพช. <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></span>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 2. Doctor Visit Service Statistics Card (Main Table) --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card p-4">
            {{-- Header: Title & Action Button --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-stethoscope text-primary me-2"></i>จำนวนที่แพทย์แต่ละคนไปตรวจ
                    </h5>
                    <span class="text-secondary small">
                        สถิติการออกตรวจของแพทย์เฉพาะทางจาก รพ.อำนาจเจริญ ณ โรงพยาบาลชุมชน 6 แห่ง (ช่วงที่เลือก: <strong class="text-primary">{{ $thaiDateRangeText }}</strong>)
                    </span>
                </div>
                <div>
                    <button type="button" class="btn btn-primary px-3 py-2 fw-bold text-white shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#doctorListManageModal" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #0284c7 100%); border: none;">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>จัดการรายชื่อแพทย์</span>
                    </button>
                </div>
            </div>

            {{-- Filter Form Toolbar: 1 แถว ชิดขวา รวมช่วงเวลารวดเร็วมาด้านหน้า --}}
            <div class="mb-3">
                <form method="GET" action="{{ route('manage.opoh.index') }}" id="manageOpohFilterForm" class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                    <input type="hidden" name="filter_type" id="filter_type" value="{{ $filter_type }}">

                    {{-- 1. ช่วงเวลารวดเร็ว (นำมาไว้ด้านหน้าสุด) --}}
                    <div class="d-flex align-items-center gap-1 me-1">
                        <span class="small text-muted me-1 d-none d-md-inline"><i class="fa-solid fa-bolt text-warning me-1"></i>เร็ว:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary btn-quick-range px-2.5 py-1 {{ $filter_type === 'week' ? 'active' : '' }}" data-range="week">
                                สัปดาห์นี้
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-quick-range px-2.5 py-1 {{ $filter_type === 'month' ? 'active' : '' }}" data-range="month">
                                เดือนนี้
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-quick-range px-2.5 py-1 {{ $filter_type === 'fiscal' ? 'active' : '' }}" data-range="fiscal">
                                ทั้งปีงบ {{ $budget_year }}
                            </button>
                        </div>
                    </div>

                    {{-- 2. เลือกปีงบ --}}
                    <div style="min-width: 185px;">
                        <select name="budget_year" id="budget_year_select" class="form-select form-select-sm shadow-sm fw-semibold" title="เลือกปีงบประมาณ">
                            @foreach($budget_year_select as $by)
                                <option value="{{ $by->LEAVE_YEAR_ID }}" {{ $budget_year == $by->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $by->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. ช่วงวันที่เริ่มต้น --}}
                    <div class="input-group input-group-sm shadow-sm" style="width: 135px;">
                        <span class="input-group-text bg-white text-primary px-2"><i class="fa-regular fa-calendar"></i></span>
                        <input type="text" id="start_date" name="start_date" class="form-control form-control-sm bg-white px-2" value="{{ $current_start }}" placeholder="วว/ดด/ปปปป" title="วันที่เริ่มต้น">
                    </div>

                    <span class="text-secondary small fw-bold">ถึง</span>

                    {{-- 4. ช่วงวันที่สิ้นสุด --}}
                    <div class="input-group input-group-sm shadow-sm" style="width: 135px;">
                        <span class="input-group-text bg-white text-primary px-2"><i class="fa-regular fa-calendar-check"></i></span>
                        <input type="text" id="end_date" name="end_date" class="form-control form-control-sm bg-white px-2" value="{{ $current_end }}" placeholder="วว/ดด/ปปปป" title="วันที่สิ้นสุด">
                    </div>

                    {{-- 5. กรองสาขา --}}
                    <div style="min-width: 160px;">
                        <select name="spclty_filter" id="spclty_filter" class="form-select form-select-sm shadow-sm">
                            <option value="">-- ทุกสาขา (7 สาขา) --</option>
                            @foreach($specialties as $code => $spec)
                                <option value="{{ $code }}" {{ $spclty_filter == $code ? 'selected' : '' }}>
                                    {{ $code }} - {{ $spec['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 6. ปุ่มค้นหา & รีเซ็ต --}}
                    <div class="d-flex align-items-center gap-1.5">
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1.5 fw-semibold rounded-2 shadow-sm d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>ค้นหา</span>
                        </button>
                        <a href="{{ route('manage.opoh.index') }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1.5 rounded-2 shadow-sm" title="คืนค่าเริ่มต้น">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- Nav Pills สลับดูข้อมูล: ผู้ป่วยนอก (OPD) / ผู้ป่วยใน (IPD) --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
                <ul class="nav nav-pills gap-2" id="adminOpohTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-3.5 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2" id="pills-admin-opd-tab" data-bs-toggle="pill" data-bs-target="#pills-admin-opd" type="button" role="tab" aria-controls="pills-admin-opd" aria-selected="true">
                            <i class="fa-solid fa-stethoscope"></i>
                            <span>สถิติผู้ป่วยนอก (OPD)</span>
                            <span class="badge bg-primary bg-opacity-25 text-white rounded-pill ms-1" style="font-size: 0.75rem;">{{ number_format($colTotals['cumulative']) }} ครั้ง</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3.5 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2 text-danger border border-danger border-opacity-25" id="pills-admin-ipd-tab" data-bs-toggle="pill" data-bs-target="#pills-admin-ipd" type="button" role="tab" aria-controls="pills-admin-ipd" aria-selected="false">
                            <i class="fa-solid fa-bed-pulse"></i>
                            <span>สถิติผู้ป่วยใน (IPD)</span>
                            <span class="badge bg-danger text-white rounded-pill ms-1" style="font-size: 0.75rem;">{{ number_format($colIpdTotals['cumulative']) }} ราย</span>
                        </button>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold shadow-sm d-flex align-items-center gap-1.5" onclick="exportMainDoctorTableToExcel()" title="ส่งออกตารางผลงานแพทย์เป็น Excel">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Excel ตารางผลงาน</span>
                    </button>
                    <div class="text-muted small d-none d-lg-inline">
                        <i class="fa-solid fa-circle-info text-info me-1"></i>
                        แพทย์เฉพาะทาง รพช. 6 แห่ง
                    </div>
                </div>
            </div>

            <div class="tab-content" id="adminOpohTabsContent">
                {{-- TAB 1: สถิติผู้ป่วยนอก (OPD) --}}
                <div class="tab-pane fade show active" id="pills-admin-opd" role="tabpanel" aria-labelledby="pills-admin-opd-tab">
                    {{-- Main Visits DataTable: เริ่มแสดง 20 รายการ (ไม่มี card ครอบ) --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-visits align-middle mb-0 w-100" id="doctorVisitsTable">
                            <thead>
                                <tr>
                                    <th class="text-start" style="min-width: 220px;">ชื่อแพทย์ (เลข ว.)</th>
                                    <th style="min-width: 100px;" class="text-end">ยอดสะสม</th>
                                    <th style="min-width: 110px;" class="text-end bg-primary bg-opacity-10 text-primary">ยอดตามช่วง</th>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <th style="min-width: 110px;" class="text-end">{{ $hname }}</th>
                                    @endforeach
                                    <th style="min-width: 130px;" class="text-end">ค่ารักษารวม (บาท)</th>
                                    <th style="min-width: 130px;" class="text-end bg-light border-start text-secondary">
                                        รพ.อำนาจเจริญ
                                        <div class="small fw-normal text-muted" style="font-size: 0.70rem;">(ตรวจ ณ แม่ข่าย)</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($docStats as $row)
                                @php
                                    $doc = $row['doctor'];
                                    $specColor = $specialties[$doc->spclty_code]['color'] ?? '#0284c7';
                                    $specIcon = $specialties[$doc->spclty_code]['icon'] ?? 'fa-user-doctor';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle text-center d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background-color: {{ $specColor }}15; color: {{ $specColor }};">
                                                <i class="fa-solid {{ $specIcon }} small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark leading-tight">{{ $doc->doctor_name }}</div>
                                                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                                    <span class="badge px-1.5 py-0.5 rounded-pill fw-semibold" style="background-color: {{ $specColor }}15; color: {{ $specColor }}; font-size: 0.72rem;">
                                                        {{ $doc->specialty_name }}
                                                    </span>
                                                    <small class="text-muted font-monospace">({{ $doc->licenseno ?: '-' }})</small>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold text-secondary">
                                        {{ number_format($row['cumulative']) }}
                                    </td>
                                    <td class="text-end fw-bold text-primary bg-primary bg-opacity-10">
                                        {{ number_format($row['period']) }}
                                    </td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        @php
                                             $hv = $row['hosp_visits'][$hcode] ?? 0;
                                        @endphp
                                        <td class="text-end {{ $hv > 0 ? 'fw-bold text-dark' : 'text-muted opacity-50' }}">
                                            @if($hv > 0)
                                                <button type="button" class="btn-rec-cell" onclick="openDoctorRecordsModal({{ $doc->id }}, '{{ addslashes($doc->doctor_name) }}', 'opd', '{{ $hcode }}', '{{ addslashes($hname) }}')" title="ดูรายเคสของแพทย์ท่านนี้ที่ {{ $hname }}">{{ number_format($hv) }}</button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-end">
                                        <div class="fw-bold text-dark">{{ number_format($row['inc'] ?? 0, 2) }}</div>
                                        <button type="button" class="btn btn-outline-success btn-xs rounded-pill px-2 py-0 mt-0.5 shadow-sm" style="font-size: 0.68rem;" onclick="openDoctorIncomeModal('{{ $doc->id }}', '{{ addslashes($doc->doctor_name) }}', 'OPD')" title="ดูรายละเอียด 16 หมวดค่ารักษา">
                                            <i class="fa-solid fa-list-check me-1"></i>16 หมวด
                                        </button>
                                    </td>
                                    <td class="text-end bg-light border-start {{ $row['amnat_visits'] > 0 ? 'fw-bold text-secondary' : 'text-muted opacity-50' }}">
                                        {{ $row['amnat_visits'] > 0 ? number_format($row['amnat_visits']) : '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ 5 + count($hospitalsList) }}" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                                        ยังไม่มีข้อมูลการออกตรวจในช่วงเวลาที่เลือก
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                {{-- แถวรวมที่ 1: รวมเฉพาะแพทย์ AOPH (22 ท่าน) --}}
                                <tr class="tr-aoph align-middle">
                                    <td class="text-start fw-bold">
                                        <i class="fa-solid fa-user-doctor me-1 text-primary"></i> รวมแพทย์ AOPH (22 ท่าน)
                                    </td>
                                    <td class="text-end fw-bolder text-secondary">{{ number_format($colTotals['cumulative']) }}</td>
                                    <td class="text-end fw-bolder bg-primary bg-opacity-10 text-primary">{{ number_format($colTotals['period']) }}</td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <td class="text-end fw-bolder">{{ number_format($colTotals[$hcode]) }}</td>
                                    @endforeach
                                    <td class="text-end">
                                        <div class="fw-bolder text-success">฿{{ number_format($colTotals['inc'] ?? 0, 2) }}</div>
                                        <button type="button" class="btn btn-outline-primary btn-xs rounded-pill px-2 py-0 mt-0.5 shadow-sm" style="font-size: 0.68rem;" onclick="openDoctorIncomeModal('AOPH', 'รวมแพทย์ AOPH (22 ท่าน)', 'OPD')" title="ดู 16 หมวดค่ารักษาเฉพาะแพทย์ AOPH">
                                            <i class="fa-solid fa-list-check me-1"></i>16 หมวด
                                        </button>
                                    </td>
                                    <td class="text-end fw-bolder bg-light border-start text-secondary">
                                        {{ number_format($colTotals['amnat']) }}
                                    </td>
                                </tr>
                                {{-- แถวรวมที่ 2: รวมทั้งหมด รพช. 6 แห่ง (ไม่แยกแพทย์) --}}
                                <tr class="tr-grand align-middle">
                                    <td class="text-start fw-bolder text-success">
                                        <i class="fa-solid fa-hospital me-1 text-success"></i> รวมทั้งหมด รพช. (ไม่แยกแพทย์)
                                    </td>
                                    <td class="text-end fw-bolder">{{ number_format($grandOpdTotals['cumulative']) }}</td>
                                    <td class="text-end fw-bolder bg-success bg-opacity-10 text-success">{{ number_format($grandOpdTotals['period']) }}</td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <td class="text-end fw-bolder">{{ number_format($grandOpdTotals[$hcode] ?? 0) }}</td>
                                    @endforeach
                                    <td class="text-end">
                                        <div class="fw-bolder text-success fs-6">฿{{ number_format($grandOpdTotals['inc'] ?? 0, 2) }}</div>
                                        <button type="button" class="btn btn-success btn-xs rounded-pill px-2.5 py-0.5 mt-0.5 text-white shadow-sm" style="font-size: 0.68rem;" onclick="openDoctorIncomeModal('ALL', 'ยอดรวมทั้งหมด รพช. 6 แห่ง (ไม่แยกแพทย์)', 'OPD')" title="ดู 16 หมวดค่ารักษาภาพรวมทั้งหมด (ไม่แยกแพทย์)">
                                            <i class="fa-solid fa-layer-group me-1"></i>16 หมวด (ภาพรวม)
                                        </button>
                                    </td>
                                    <td class="text-end fw-bolder bg-light border-start text-secondary">
                                        {{ number_format($grandOpdTotals['amnat']) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- TAB 2: สถิติผู้ป่วยใน (IPD) --}}
                <div class="tab-pane fade" id="pills-admin-ipd" role="tabpanel" aria-labelledby="pills-admin-ipd-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-visits align-middle mb-0 w-100" id="doctorIpdTable">
                            <thead>
                                <tr>
                                    <th class="text-start" style="min-width: 220px;">ชื่อแพทย์ (เลข ว.)</th>
                                    <th style="min-width: 90px;" class="text-end">สะสม (AN)</th>
                                    <th style="min-width: 100px;" class="text-end bg-danger bg-opacity-10 text-danger">ช่วงที่เลือก (AN)</th>
                                    <th style="min-width: 90px;" class="text-end">วันนอน (LOS)</th>
                                    <th style="min-width: 90px;" class="text-end">AdjRW รวม</th>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <th style="min-width: 100px;" class="text-end">{{ $hname }}</th>
                                    @endforeach
                                    <th style="min-width: 120px;" class="text-end">ค่ารักษารวม (บาท)</th>
                                    <th style="min-width: 130px;" class="text-end bg-light border-start text-secondary">
                                        รพ.อำนาจเจริญ
                                        <div class="small fw-normal text-muted" style="font-size: 0.70rem;">(ตรวจ ณ แม่ข่าย)</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($docIpdStats as $row)
                                @php
                                    $doc = $row['doctor'];
                                    $specColor = $specialties[$doc->spclty_code]['color'] ?? '#e11d48';
                                    $specIcon = $specialties[$doc->spclty_code]['icon'] ?? 'fa-user-doctor';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle text-center d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background-color: {{ $specColor }}15; color: {{ $specColor }};">
                                                <i class="fa-solid {{ $specIcon }} small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark leading-tight">{{ $doc->doctor_name }}</div>
                                                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                                    <span class="badge px-1.5 py-0.5 rounded-pill fw-semibold" style="background-color: {{ $specColor }}15; color: {{ $specColor }}; font-size: 0.72rem;">
                                                        {{ $doc->specialty_name }}
                                                    </span>
                                                    <small class="text-muted font-monospace">({{ $doc->licenseno ?: '-' }})</small>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold text-secondary">
                                        {{ number_format($row['cumulative']) }}
                                    </td>
                                    <td class="text-end fw-bold text-danger bg-danger bg-opacity-10">
                                        {{ number_format($row['period']) }}
                                    </td>
                                    <td class="text-end text-secondary">
                                        {{ number_format($row['los']) }}
                                    </td>
                                    <td class="text-end text-secondary">
                                        {{ number_format($row['rw'], 4) }}
                                    </td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        @php
                                            $ha = $row['hosp_ans'][$hcode] ?? 0;
                                        @endphp
                                        <td class="text-end {{ $ha > 0 ? 'fw-bold text-danger' : 'text-muted opacity-50' }}">
                                            @if($ha > 0)
                                                <button type="button" class="btn-rec-cell btn-rec-cell-ipd" onclick="openDoctorRecordsModal({{ $doc->id }}, '{{ addslashes($doc->doctor_name) }}', 'ipd', '{{ $hcode }}', '{{ addslashes($hname) }}')" title="ดูรายเคสของแพทย์ท่านนี้ที่ {{ $hname }}">{{ number_format($ha) }}</button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-end">
                                        <div class="fw-bold text-dark">{{ number_format($row['inc'], 2) }}</div>
                                        <button type="button" class="btn btn-outline-danger btn-xs rounded-pill px-2 py-0 mt-0.5 shadow-sm" style="font-size: 0.68rem;" onclick="openDoctorIncomeModal('{{ $doc->id }}', '{{ addslashes($doc->doctor_name) }}', 'IPD')" title="ดูรายละเอียด 16 หมวดค่ารักษา">
                                            <i class="fa-solid fa-list-check me-1"></i>16 หมวด
                                        </button>
                                    </td>
                                    <td class="text-end bg-light border-start {{ $row['amnat_ans'] > 0 ? 'fw-bold text-secondary' : 'text-muted opacity-50' }}">
                                        {{ $row['amnat_ans'] > 0 ? number_format($row['amnat_ans']) : '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ 7 + count($hospitalsList) }}" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                                        ยังไม่มีข้อมูลการจำหน่ายผู้ป่วยใน (IPD) ในช่วงเวลาที่เลือก
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                {{-- แถวรวมที่ 1: รวมเฉพาะแพทย์ AOPH (22 ท่าน) --}}
                                <tr class="tr-aoph align-middle">
                                    <td class="text-start fw-bold">
                                        <i class="fa-solid fa-user-doctor me-1 text-danger"></i> รวมแพทย์ AOPH (22 ท่าน)
                                    </td>
                                    <td class="text-end fw-bolder">{{ number_format($colIpdTotals['cumulative']) }}</td>
                                    <td class="text-end fw-bolder bg-danger bg-opacity-10 text-danger">{{ number_format($colIpdTotals['period']) }}</td>
                                    <td class="text-end fw-bolder">{{ number_format($colIpdTotals['los']) }}</td>
                                    <td class="text-end fw-bolder">{{ number_format($colIpdTotals['rw'], 4) }}</td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <td class="text-end fw-bolder">{{ number_format($colIpdTotals[$hcode]) }}</td>
                                    @endforeach
                                    <td class="text-end">
                                        <div class="fw-bolder text-danger">฿{{ number_format($colIpdTotals['inc'] ?? 0, 2) }}</div>
                                        <button type="button" class="btn btn-outline-danger btn-xs rounded-pill px-2 py-0 mt-0.5 shadow-sm" style="font-size: 0.68rem;" onclick="openDoctorIncomeModal('AOPH', 'รวมแพทย์ AOPH (22 ท่าน)', 'IPD')" title="ดู 16 หมวดค่ารักษาเฉพาะแพทย์ AOPH">
                                            <i class="fa-solid fa-list-check me-1"></i>16 หมวด
                                        </button>
                                    </td>
                                    <td class="text-end fw-bolder bg-light border-start text-secondary">
                                        {{ number_format($colIpdTotals['amnat']) }}
                                    </td>
                                </tr>
                                {{-- แถวรวมที่ 2: รวมทั้งหมด รพช. 6 แห่ง (ไม่แยกแพทย์) --}}
                                <tr class="tr-grand align-middle">
                                    <td class="text-start fw-bolder text-success">
                                        <i class="fa-solid fa-hospital me-1 text-success"></i> รวมทั้งหมด รพช. (ไม่แยกแพทย์)
                                    </td>
                                    <td class="text-end fw-bolder">{{ number_format($grandIpdTotals['cumulative']) }}</td>
                                    <td class="text-end fw-bolder bg-success bg-opacity-10 text-success">{{ number_format($grandIpdTotals['period']) }}</td>
                                    <td class="text-end fw-bolder">{{ number_format($grandIpdTotals['los']) }}</td>
                                    <td class="text-end fw-bolder">{{ number_format($grandIpdTotals['rw'], 4) }}</td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <td class="text-end fw-bolder">{{ number_format($grandIpdTotals[$hcode] ?? 0) }}</td>
                                    @endforeach
                                    <td class="text-end">
                                        <div class="fw-bolder text-success fs-6">฿{{ number_format($grandIpdTotals['inc'] ?? 0, 2) }}</div>
                                        <button type="button" class="btn btn-success btn-xs rounded-pill px-2.5 py-0.5 mt-0.5 text-white shadow-sm" style="font-size: 0.68rem;" onclick="openDoctorIncomeModal('ALL', 'ยอดรวมทั้งหมด รพช. 6 แห่ง (ไม่แยกแพทย์)', 'IPD')" title="ดู 16 หมวดค่ารักษาภาพรวมทั้งหมด (ไม่แยกแพทย์)">
                                            <i class="fa-solid fa-layer-group me-1"></i>16 หมวด (ภาพรวม)
                                        </button>
                                    </td>
                                    <td class="text-end fw-bolder bg-light border-start text-secondary">
                                        {{ number_format($grandIpdTotals['amnat']) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. Modal: จัดการรายชื่อแพทย์ (ชุดรูป 2 - มีตารางแพทย์ พร้อมปุ่ม เพิ่ม, แก้ไข, ลบ) --}}
<div class="modal fade" id="doctorListManageModal" tabindex="-1" aria-labelledby="doctorListManageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                        <i class="fa-solid fa-id-card-clip fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="doctorListManageModalLabel">ทำเนียบแพทย์เฉพาะทางออกตรวจ รพช.</h5>
                        <p class="text-muted small mb-0">จัดการรายชื่อแพทย์ เลข ว. เลขประจำตัวประชาชน และสาขาความเชี่ยวชาญสำหรับระบบตรวจสอบ</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                {{-- Action Bar: Search, Specialty Filter & Add button --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                        <div class="input-group input-group-sm" style="min-width: 240px; max-width: 320px;">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" id="rosterSearchInput" class="form-control" placeholder="ค้นหาแพทย์, ว., เลขบัตร...">
                        </div>
                        <div style="min-width: 180px;">
                            <select id="rosterSpecialtyFilter" class="form-select form-select-sm shadow-sm">
                                <option value="">-- กรองทุกสาขา (7 สาขา) --</option>
                                @foreach($specialties as $code => $spec)
                                    <option value="{{ $spec['name'] }}">{{ $code }} - {{ $spec['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary fw-bold text-white px-3 py-1.5 rounded-3 d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>เพิ่มแพทย์ท่านใหม่</span>
                        </button>
                    </div>
                </div>

                {{-- Doctor Roster DataTable --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="doctorRosterTable">
                        <thead class="bg-light">
                            <tr class="text-secondary" style="font-size: 0.88rem;">
                                <th style="width: 45px;" class="text-center">#</th>
                                <th style="min-width: 180px;">ชื่อ-นามสกุลแพทย์</th>
                                <th style="min-width: 140px;">สาขาเฉพาะทาง</th>
                                <th style="min-width: 110px;">เลขที่ใบอนุญาต (ว.)</th>
                                <th style="min-width: 140px;">เลขประจำตัวประชาชน (13 หลัก)</th>
                                <th style="min-width: 120px;">ตำแหน่ง</th>
                                <th style="width: 90px;" class="text-center">สถานะใช้งาน</th>
                                <th style="width: 100px;" class="text-center">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allDoctors as $index => $doc)
                            @php
                                $specColor = $specialties[$doc->spclty_code]['color'] ?? '#0284c7';
                                $specIcon = $specialties[$doc->spclty_code]['icon'] ?? 'fa-user-doctor';
                            @endphp
                            <tr id="doctor-row-{{ $doc->id }}" data-search="{{ mb_strtolower($doc->doctor_name . ' ' . $doc->licenseno . ' ' . $doc->cid . ' ' . $doc->specialty_name) }}">
                                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $doc->doctor_name }}</div>
                                    <small class="text-muted">รหัสสาขา: {{ $doc->spclty_code }}</small>
                                </td>
                                <td>
                                    <span class="badge px-2 py-1 rounded-pill fw-semibold" style="background-color: {{ $specColor }}15; color: {{ $specColor }}; font-size: 0.8rem;">
                                        <i class="fa-solid {{ $specIcon }} me-1"></i> {{ $doc->specialty_name }}
                                    </span>
                                </td>
                                <td>
                                    <code class="fw-semibold text-dark">{{ $doc->licenseno ?: '-' }}</code>
                                </td>
                                <td>
                                    <code class="text-secondary">{{ $doc->cid ?: '-' }}</code>
                                </td>
                                <td class="small text-secondary">{{ $doc->position ?: '-' }}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input doctor-toggle-btn" type="checkbox" role="switch"
                                               data-id="{{ $doc->id }}" {{ $doc->is_active === 'Y' ? 'checked' : '' }} title="สลับเปิด/ปิด การใช้งาน">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm shadow-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm edit-doctor-btn"
                                                data-id="{{ $doc->id }}"
                                                data-pname="{{ $doc->pname }}"
                                                data-fname="{{ $doc->fname }}"
                                                data-lname="{{ $doc->lname }}"
                                                data-spclty-code="{{ $doc->spclty_code }}"
                                                data-specialty-name="{{ $doc->specialty_name }}"
                                                data-licenseno="{{ $doc->licenseno }}"
                                                data-cid="{{ $doc->cid }}"
                                                data-position="{{ $doc->position }}"
                                                data-is-active="{{ $doc->is_active }}"
                                                title="แก้ไขข้อมูล">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-doctor-btn"
                                                data-id="{{ $doc->id }}"
                                                data-name="{{ $doc->doctor_name }}"
                                                title="ลบข้อมูล">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                                    ยังไม่มีข้อมูลแพทย์ในระบบ
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

{{-- 4. Modal เพิ่มแพทย์ใหม่ --}}
<div class="modal fade" id="addDoctorModal" tabindex="-1" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                        <i class="fa-solid fa-user-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="addDoctorModalLabel">เพิ่มแพทย์ท่านใหม่</h5>
                        <p class="text-muted small mb-0">บันทึกข้อมูลแพทย์เฉพาะทางสำหรับออกตรวจ รพช.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addDoctorForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-secondary mb-1">คำนำหน้า</label>
                            <input type="text" name="pname" class="form-control form-control-sm" placeholder="นพ., พญ.">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" name="fname" class="form-control form-control-sm" required placeholder="ชื่อจริง">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold text-secondary mb-1">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" name="lname" class="form-control form-control-sm" required placeholder="นามสกุล">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">สาขาเฉพาะทาง <span class="text-danger">*</span></label>
                            <select name="spclty_code" id="addDoctorSpcltySelect" class="form-select form-select-sm" required>
                                @foreach($specialties as $code => $spec)
                                    <option value="{{ $code }}" data-name="{{ $spec['name'] }}">{{ $code }} - {{ $spec['name'] }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="specialty_name" id="addDoctorSpcltyName" value="{{ reset($specialties)['name'] ?? 'อายุรกรรม' }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">ตำแหน่ง</label>
                            <input type="text" name="position" class="form-control form-control-sm" placeholder="เช่น นายแพทย์ชำนาญการ, อายุรแพทย์">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">เลขที่ใบอนุญาต (เลข ว.)</label>
                            <input type="text" name="licenseno" class="form-control form-control-sm" placeholder="เช่น ว29520">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">เลขประจำตัวประชาชน (13 หลัก)</label>
                            <input type="text" name="cid" class="form-control form-control-sm" maxlength="13" placeholder="ตัวเลข 13 หลัก">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="Y" id="addDoctorActive" checked>
                                <label class="form-check-label small fw-semibold text-dark" for="addDoctorActive">เปิดใช้งานทันที</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-3 rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 5. Modal แก้ไขข้อมูลแพทย์ --}}
<div class="modal fade" id="editDoctorModal" tabindex="-1" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-warning bg-opacity-10 text-warning rounded-3">
                        <i class="fa-solid fa-user-pen fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="editDoctorModalLabel">แก้ไขข้อมูลแพทย์</h5>
                        <p class="text-muted small mb-0">ปรับปรุงข้อมูลแพทย์ในระบบ</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editDoctorForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="doctor_id" id="editDoctorId">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-secondary mb-1">คำนำหน้า</label>
                            <input type="text" name="pname" id="editDoctorPname" class="form-control form-control-sm" placeholder="นพ., พญ.">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" name="fname" id="editDoctorFname" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold text-secondary mb-1">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" name="lname" id="editDoctorLname" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">สาขาเฉพาะทาง <span class="text-danger">*</span></label>
                            <select name="spclty_code" id="editDoctorSpcltySelect" class="form-select form-select-sm" required>
                                @foreach($specialties as $code => $spec)
                                    <option value="{{ $code }}" data-name="{{ $spec['name'] }}">{{ $code }} - {{ $spec['name'] }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="specialty_name" id="editDoctorSpcltyName">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">ตำแหน่ง</label>
                            <input type="text" name="position" id="editDoctorPosition" class="form-control form-control-sm">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">เลขที่ใบอนุญาต (เลข ว.)</label>
                            <input type="text" name="licenseno" id="editDoctorLicenseno" class="form-control form-control-sm">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">เลขประจำตัวประชาชน (13 หลัก)</label>
                            <input type="text" name="cid" id="editDoctorCid" class="form-control form-control-sm" maxlength="13">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="Y" id="editDoctorActive">
                                <label class="form-check-label small fw-semibold text-dark" for="editDoctorActive">เปิดใช้งานในระบบ</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-3 rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning px-4 rounded-3 fw-bold text-dark">
                        <i class="fa-solid fa-save me-1"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 6. Modal รายได้แยกตาม 16 หมวดค่ารักษาพยาบาลของแพทย์ --}}
<div class="modal fade" id="doctorIncomeModal" tabindex="-1" aria-labelledby="doctorIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-2 pt-4 px-4 align-items-start">
                <div class="d-flex align-items-start gap-3">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-file-invoice-dollar fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                รายได้ตาม 16 หมวดค่ารักษา
                            </span>
                            <span id="docIncomeSpecBadge" class="badge rounded-pill bg-light text-dark border px-2.5 py-1 fw-semibold">
                                -
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="docIncomeDoctorName">ชื่อแพทย์</h5>
                        <p class="text-muted small mb-0 mt-1">
                            ช่วงเวลา: <strong class="text-dark">{{ $thaiDateRangeText }}</strong> | เลข ว.: <span id="docIncomeLicense" class="font-monospace">-</span>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    {{-- Toggle OPD / IPD / ALL --}}
                    <div class="btn-group btn-group-sm shadow-sm p-1 rounded-pill bg-light border" role="group" id="docIncomeTypeGroup">
                        <button type="button" class="btn btn-sm px-3 fw-bold doc-inc-type-btn active active-all" data-type="ALL">
                            <i class="fa-solid fa-layer-group me-1"></i>ทั้งหมด
                        </button>
                        <button type="button" class="btn btn-sm px-3 fw-bold doc-inc-type-btn" data-type="OPD">
                            <i class="fa-solid fa-hospital-user me-1"></i>OPD
                        </button>
                        <button type="button" class="btn btn-sm px-3 fw-bold doc-inc-type-btn" data-type="IPD">
                            <i class="fa-solid fa-bed-pulse me-1"></i>IPD
                        </button>
                    </div>

                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" onclick="exportDoctorIncomeToExcel()" title="ส่งออกตาราง 16 หมวดเป็นไฟล์ Excel">
                        <i class="fa-solid fa-file-excel me-1"></i> Excel
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body px-4 py-3">
                {{-- Loader --}}
                <div id="docIncomeLoader" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted small fw-semibold">กำลังโหลดข้อมูล 16 หมวดค่ารักษา...</div>
                </div>

                {{-- Content Container --}}
                <div id="docIncomeContent" class="d-none">
                    {{-- 6 Rights Summary Row --}}
                    <div class="row g-2 mb-3" id="docIncomeCardsRow">
                        {{-- Injected dynamically --}}
                    </div>

                    <div class="table-responsive rounded-3 border shadow-sm">
                        <table class="table table-hover align-middle mb-0 w-100" id="doctorIncomeTable" style="font-size: 0.88rem;">
                            <thead class="table-light sticky-top">
                                <tr class="text-center align-middle">
                                    <th class="text-start" style="min-width: 260px;">กลุ่มหมวดค่ารักษา (drg_chrgitem)</th>
                                    <th style="min-width: 120px;" class="text-end">UC ในจังหวัด</th>
                                    <th style="min-width: 120px;" class="text-end">UC ต่างจังหวัด</th>
                                    <th style="min-width: 120px;" class="text-end">ข้าราชการ</th>
                                    <th style="min-width: 110px;" class="text-end">อปท.</th>
                                    <th style="min-width: 120px;" class="text-end">ประกันสังคม</th>
                                    <th style="min-width: 110px;" class="text-end">อื่นๆ/ชำระเงิน</th>
                                    <th style="min-width: 155px;" class="text-end bg-success bg-opacity-10 text-success fw-bolder col-inc-total">ยอดรวม (บาท)</th>
                                </tr>
                            </thead>
                            <tbody id="docIncomeTableBody">
                                {{-- Rows injected dynamically --}}
                            </tbody>
                            <tfoot id="docIncomeTableFoot" class="fw-bold text-end">
                                {{-- Footer totals injected dynamically --}}
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pb-4 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="fa-solid fa-circle-info text-info me-1"></i>ประมวลผลจากข้อมูลค่ารักษาพยาบาล (<span class="font-monospace">opitemrece</span>) เชื่อมโยงตามเลข ว. และรหัสแพทย์
                </span>
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

{{-- 7. Modal รายเคส (ข้อมูลระบุตัวผู้ป่วย - เฉพาะ allow_opoh) ดึงสดผ่าน agent ของ รพ. --}}
<div class="modal fade" id="doctorRecordsModal" tabindex="-1" aria-labelledby="doctorRecordsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="max-width: 96vw;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-2 pt-4 px-4 align-items-start">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span id="docRecTypeBadge" class="badge rounded-pill px-2.5 py-1 fw-bold">รายเคส</span>
                        <span id="docRecHospBadge" class="badge rounded-pill bg-light text-dark border px-2.5 py-1 fw-semibold">-</span>
                        <span class="badge rounded-pill bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1 fw-semibold">
                            <i class="fa-solid fa-user-shield me-1"></i>ข้อมูลผู้ป่วย (PDPA) - บันทึกการเข้าถึงทุกครั้ง
                        </span>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="docRecDoctorName">ชื่อแพทย์</h5>
                    <p class="text-muted small mb-0 mt-1">ช่วงเวลา: <strong class="text-dark" id="docRecRange">{{ $thaiDateRangeText }}</strong></p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div id="docRecAlert" class="alert alert-danger d-none py-2 small"></div>
                <div id="docRecWarn" class="alert alert-warning d-none py-2 small"></div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div id="docRecKpis" class="d-flex flex-wrap gap-2"></div>
                    <a id="docRecExportBtn" href="#" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm">
                        <i class="fa-solid fa-file-csv me-1"></i>ส่งออกทุกเคส (CSV)
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 w-100" id="docRecTable"></table>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="fa-solid fa-circle-info text-info me-1"></i>ดึงสดจาก HOSxP ของ รพ. ผ่าน AOPOD-Agent ไม่เก็บข้อมูลผู้ป่วยไว้บนเซิร์ฟเวอร์กลาง
                </span>
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/th.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/flatpickr-th-buddhist.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/xlsx.full.min.js') }}"></script>
<script>
$(document).ready(function() {
    // 1. Initialize Flatpickr Buddhist Calendar
    const startPicker = initThaiDatePicker('#start_date', {
        defaultDate: '{{ $current_start }}'
    });

    const endPicker = initThaiDatePicker('#end_date', {
        defaultDate: '{{ $current_end }}'
    });

    $('#budget_year_select').on('change', function() {
        $('#filter_type').val('fiscal');
        $('#manageOpohFilterForm').submit();
    });

    $('.btn-quick-range').on('click', function() {
        const range = $(this).data('range');
        $('#filter_type').val(range);

        let s = '';
        let e = '';

        if (range === 'week') {
            s = '{{ \Carbon\Carbon::now()->startOfWeek()->toDateString() }}';
            e = '{{ \Carbon\Carbon::now()->endOfWeek()->toDateString() }}';
        } else if (range === 'month') {
            s = '{{ \Carbon\Carbon::now()->startOfMonth()->toDateString() }}';
            e = '{{ \Carbon\Carbon::now()->endOfMonth()->toDateString() }}';
        } else if (range === 'fiscal') {
            s = '{{ $fiscal_start }}';
            e = '{{ $fiscal_end }}';
        }

        if (s && e) {
            startPicker.setDate(s, true);
            endPicker.setDate(e, true);
            $('#manageOpohFilterForm').submit();
        }
    });

    $('#start_date, #end_date').on('change', function() {
        $('#filter_type').val('custom');
    });

    // 2. Initialize DataTable for Doctor Visits Table: เริ่มแสดง 20 รายการ
    let opdVisitsTable = $('#doctorVisitsTable').DataTable({
        language: {
            url: "{{ asset('assets/vendor/datatables/th.json') }}"
        },
        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
        ordering: true,
        order: [[2, 'desc']] // เรียงลำดับจากยอดตามช่วงมากไปน้อยโดยเริ่มต้น
    });

    // 2.1 Initialize DataTable for Doctor IPD Table: เริ่มแสดง 20 รายการ
    let ipdVisitsTable = $('#doctorIpdTable').DataTable({
        language: {
            url: "{{ asset('assets/vendor/datatables/th.json') }}"
        },
        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
        ordering: true,
        order: [[2, 'desc']] // เรียงลำดับจากยอดตามช่วงมากไปน้อยโดยเริ่มต้น
    });

    // Adjust column widths when switching tabs
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
    });

    // 3. Initialize DataTable for Doctor Roster Modal Table: 10 รายการต่อหน้า
    let rosterTable = $('#doctorRosterTable').DataTable({
        language: {
            url: "{{ asset('assets/vendor/datatables/th.json') }}"
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
        ordering: true,
        order: [[0, 'asc']],
        dom: '<"d-none"f>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"<"small text-muted"i><"small"p>>',
        columnDefs: [
            { targets: [6, 7], orderable: false }
        ]
    });

    // 3.1 Search filter for Doctor Roster Modal Table
    $('#rosterSearchInput').on('keyup input', function() {
        rosterTable.search(this.value).draw();
    });

    // 3.2 Specialty Filter for Doctor Roster Modal Table (Column index 2: สาขาเฉพาะทาง)
    $('#rosterSpecialtyFilter').on('change', function() {
        const val = $(this).val();
        rosterTable.column(2).search(val ? val : '', false, false).draw();
    });

    // 3.3 Adjust columns on modal open
    $('#doctorListManageModal').on('shown.bs.modal', function() {
        rosterTable.columns.adjust().draw();
    });

    // Flag to track changes in roster
    let rosterChanged = false;

    // Helper to escape HTML characters
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 3.4 Live Reload Roster Table inside modal (No page reload)
    function reloadRosterTable() {
        $.ajax({
            url: "{{ route('manage.opoh.doctors.list') }}",
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.doctors) {
                    rosterChanged = true;
                    rosterTable.clear();

                    res.doctors.forEach(function(doc, idx) {
                        const rowNode = rosterTable.row.add([
                            `<div class="text-center text-muted fw-bold">${idx + 1}</div>`,
                            `<div>
                                <div class="fw-bold text-dark">${escapeHtml(doc.doctor_name)}</div>
                                <small class="text-muted">รหัสสาขา: ${escapeHtml(doc.spclty_code)}</small>
                            </div>`,
                            `<span class="badge px-2 py-1 rounded-pill fw-semibold" style="background-color: ${doc.spec_color}15; color: ${doc.spec_color}; font-size: 0.8rem;">
                                <i class="fa-solid ${doc.spec_icon} me-1"></i> ${escapeHtml(doc.specialty_name)}
                            </span>`,
                            `<code class="fw-semibold text-dark">${doc.licenseno ? escapeHtml(doc.licenseno) : '-'}</code>`,
                            `<code class="text-secondary">${doc.cid ? escapeHtml(doc.cid) : '-'}</code>`,
                            `<span class="small text-secondary">${doc.position ? escapeHtml(doc.position) : '-'}</span>`,
                            `<div class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input doctor-toggle-btn" type="checkbox" role="switch"
                                           data-id="${doc.id}" ${doc.is_active === 'Y' ? 'checked' : ''} title="สลับเปิด/ปิด การใช้งาน">
                                </div>
                            </div>`,
                            `<div class="text-center">
                                <div class="btn-group btn-group-sm shadow-sm">
                                    <button type="button" class="btn btn-outline-primary btn-sm edit-doctor-btn"
                                            data-id="${doc.id}"
                                            data-pname="${escapeHtml(doc.pname || '')}"
                                            data-fname="${escapeHtml(doc.fname || '')}"
                                            data-lname="${escapeHtml(doc.lname || '')}"
                                            data-spclty-code="${escapeHtml(doc.spclty_code || '')}"
                                            data-specialty-name="${escapeHtml(doc.specialty_name || '')}"
                                            data-licenseno="${escapeHtml(doc.licenseno || '')}"
                                            data-cid="${escapeHtml(doc.cid || '')}"
                                            data-position="${escapeHtml(doc.position || '')}"
                                            data-is-active="${doc.is_active || 'Y'}"
                                            title="แก้ไขข้อมูล">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-doctor-btn"
                                            data-id="${doc.id}"
                                            data-name="${escapeHtml(doc.doctor_name)}"
                                            title="ลบข้อมูล">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>`
                        ]).node();
                        $(rowNode).attr('id', 'doctor-row-' + doc.id);
                        $(rowNode).attr('data-search', `${doc.doctor_name} ${doc.licenseno || ''} ${doc.cid || ''} ${doc.specialty_name}`.toLowerCase());
                    });

                    rosterTable.draw(false);
                }
            },
            error: function(err) {
                console.error('Error reloading doctor roster:', err);
            }
        });
    }

    // Keep body.modal-open when child modals close
    $('#addDoctorModal, #editDoctorModal').on('hidden.bs.modal', function() {
        if ($('#doctorListManageModal').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // Refresh main view when user closes roster modal if any doctor was changed
    $('#doctorListManageModal').on('hidden.bs.modal', function() {
        if (rosterChanged) {
            location.reload();
        }
    });

    // 4. Specialty Select change updates hidden input
    $('#addDoctorSpcltySelect').on('change', function() {
        const selectedName = $(this).find('option:selected').data('name');
        $('#addDoctorSpcltyName').val(selectedName);
    });

    $('#editDoctorSpcltySelect').on('change', function() {
        const selectedName = $(this).find('option:selected').data('name');
        $('#editDoctorSpcltyName').val(selectedName);
    });

    // 5. Add Doctor Submit (AJAX live reload inside modal)
    $('#addDoctorForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);

        $.ajax({
            url: "{{ route('manage.opoh.doctors.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                btn.prop('disabled', false);
                if (res.success) {
                    const addModalEl = document.getElementById('addDoctorModal');
                    const addModal = bootstrap.Modal.getInstance(addModalEl) || new bootstrap.Modal(addModalEl);
                    addModal.hide();
                    $('#addDoctorForm')[0].reset();

                    reloadRosterTable();

                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                let msg = 'ไม่สามารถบันทึกข้อมูลได้';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    html: msg
                });
            }
        });
    });

    // 6. Edit Doctor Trigger
    $(document).on('click', '.edit-doctor-btn', function() {
        const btn = $(this).closest('.edit-doctor-btn');
        const id = btn.data('id');
        const pname = btn.data('pname');
        const fname = btn.data('fname');
        const lname = btn.data('lname');
        const spcltyCode = btn.data('spclty-code');
        const specialtyName = btn.data('specialty-name');
        const licenseno = btn.data('licenseno');
        const cid = btn.data('cid');
        const position = btn.data('position');
        const isActive = btn.data('is-active');

        $('#editDoctorId').val(id);
        $('#editDoctorPname').val(pname);
        $('#editDoctorFname').val(fname);
        $('#editDoctorLname').val(lname);
        $('#editDoctorSpcltySelect').val(spcltyCode);
        $('#editDoctorSpcltyName').val(specialtyName);
        $('#editDoctorLicenseno').val(licenseno);
        $('#editDoctorCid').val(cid);
        $('#editDoctorPosition').val(position);
        $('#editDoctorActive').prop('checked', isActive === 'Y');

        $('#editDoctorModal').modal('show');
    });

    // 7. Edit Doctor Submit (AJAX live reload inside modal)
    $('#editDoctorForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#editDoctorId').val();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);

        $.ajax({
            url: "{{ url('manage/opoh/doctors') }}/" + id,
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                btn.prop('disabled', false);
                if (res.success) {
                    const editModalEl = document.getElementById('editDoctorModal');
                    const editModal = bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl);
                    editModal.hide();

                    reloadRosterTable();

                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                let msg = 'ไม่สามารถแก้ไขข้อมูลได้';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                } else if (xhr.statusText) {
                    msg = `เกิดข้อผิดพลาด (${xhr.status}: ${xhr.statusText})`;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    html: msg
                });
            }
        });
    });

    // 8. Toggle Active Status (AJAX)
    $(document).on('change', '.doctor-toggle-btn', function() {
        const docId = $(this).data('id');
        rosterChanged = true;
        $.ajax({
            url: "{{ url('manage/opoh/doctors') }}/" + docId + "/toggle",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                Toast.fire({
                    icon: 'success',
                    title: res.message
                });
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเปลี่ยนสถานะได้', 'error');
            }
        });
    });

    // 9. Delete Doctor (AJAX live reload inside modal)
    $(document).on('click', '.delete-doctor-btn', function() {
        const btn = $(this).closest('.delete-doctor-btn');
        const docId = btn.data('id');
        const docName = btn.data('name');

        Swal.fire({
            title: `ต้องการลบข้อมูลแพทย์?`,
            text: `คุณต้องการลบ "${docName}" ออกจากทำเนียบแพทย์หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('manage/opoh/doctors') }}/" + docId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        reloadRosterTable();
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function() {
                        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถลบข้อมูลได้', 'error');
                    }
                });
            }
        });
    });

    // 10. Doctor 16 Income Categories Modal
    let currentDocIncomeId = null;
    let currentDocIncomeType = 'ALL';

    function updateDocIncomeBtnStyles(selectedType) {
        $('.doc-inc-type-btn').removeClass('active active-all active-opd active-ipd');
        const $target = $('.doc-inc-type-btn[data-type="' + selectedType + '"]');
        $target.addClass('active active-' + selectedType.toLowerCase());
    }

    // ==================== รายเคสของแพทย์ รายโรงพยาบาล (server-side DataTable ผ่าน agent) ====================
    const DOC_REC_URL = '{{ route('manage.opoh.doctor-records') }}';
    const DOC_REC_EXPORT_URL = '{{ route('manage.opoh.doctor-records.export') }}';
    let docRecDT = null;
    let docRecCtx = null;

    function docRecThaiDate(iso) {
        if (!iso || iso.length < 10) return iso || '';
        return iso.substr(8, 2) + '/' + iso.substr(5, 2) + '/' + (parseInt(iso.substr(0, 4), 10) + 543);
    }
    function docRecFmt(n, d) {
        n = Number(n || 0);
        return n.toLocaleString('th-TH', { minimumFractionDigits: d, maximumFractionDigits: d });
    }
    function docRecEsc(s) {
        return $('<div>').text(s == null ? '' : s).html();
    }

    window.openDoctorRecordsModal = function(doctorId, doctorName, type, hcode, hname) {
        const isIpd = (type === 'ipd');
        docRecCtx = { doctor_id: doctorId, type: type, hospcode: hcode,
                      start_date: '{{ $current_start }}', end_date: '{{ $current_end }}' };

        $('#docRecDoctorName').text(doctorName);
        $('#docRecHospBadge').html('<i class="fa-solid fa-hospital me-1"></i>' + docRecEsc(hname));
        $('#docRecRange').text(docRecThaiDate(docRecCtx.start_date) + ' ถึง ' + docRecThaiDate(docRecCtx.end_date));
        $('#docRecTypeBadge')
            .attr('class', 'badge rounded-pill px-2.5 py-1 fw-bold ' + (isIpd ? 'bg-danger' : 'bg-primary'))
            .text(isIpd ? 'ผู้ป่วยใน (IPD) รายเคส' : 'ผู้ป่วยนอก (OPD) รายเคส');
        $('#docRecAlert, #docRecWarn').addClass('d-none').text('');
        $('#docRecKpis').html('<span class="text-muted small"><span class="spinner-border spinner-border-sm me-1"></span>กำลังดึงข้อมูลจาก HOSxP ของ ' + docRecEsc(hname) + '...</span>');

        // คอลัมน์ (ที่ agent เรียงได้: date, key, hn, patient, pttype, pdx, adm, los, adjrw)
        const cols = [];
        if (isIpd) {
            cols.push({ data: 'key', name: 'key', title: 'AN' });
            cols.push({ data: 'hn', name: 'hn', title: 'HN' });
            cols.push({ data: 'patient_name', name: 'patient', title: 'ชื่อ-สกุล' });
            cols.push({ data: 'adm_date', name: 'adm', title: 'วันที่รับ', render: function(v) { return docRecThaiDate(v); } });
            cols.push({ data: 'date', name: 'date', title: 'วันที่จำหน่าย', render: function(v, t, r) { return docRecThaiDate(v) + (r.time ? ' ' + String(r.time).substr(0, 5) : ''); } });
        } else {
            cols.push({ data: 'date', name: 'date', title: 'วันที่-เวลา', render: function(v, t, r) { return docRecThaiDate(v) + (r.time ? ' ' + String(r.time).substr(0, 5) : ''); } });
            cols.push({ data: 'key', name: 'key', title: 'VN' });
            cols.push({ data: 'hn', name: 'hn', title: 'HN' });
            cols.push({ data: 'patient_name', name: 'patient', title: 'ชื่อ-สกุล' });
        }
        cols.push({ data: 'pttype', name: 'pttype', title: 'สิทธิ' });
        cols.push({ data: 'pdx', name: 'pdx', title: 'PDX', render: function(v, t, r) {
            return '<span class="font-monospace fw-bold" title="' + docRecEsc(r.diag_name) + '">' + docRecEsc(v) + '</span>';
        } });
        ['dx0', 'dx1', 'dx2', 'dx3', 'dx4', 'dx5'].forEach(function(k) {
            cols.push({ data: k, orderable: false, title: k.toUpperCase(), render: function(v) {
                return v ? '<span class="font-monospace">' + docRecEsc(v) + '</span>' : '<span class="text-muted opacity-50">-</span>';
            } });
        });
        if (isIpd) {
            cols.push({ data: 'los', name: 'los', title: 'วันนอน', className: 'text-end' });
            cols.push({ data: 'adjrw', name: 'adjrw', title: 'AdjRW', className: 'text-end', render: function(v) { return docRecFmt(v, 4); } });
        }
        cols.push({ data: 'total', name: 'total', className: 'text-end fw-bold text-success', title: 'ค่ารักษาทั้งหมด (บาท)', render: function(v) { return docRecFmt(v, 2); } });
        const totalColIdx = cols.length - 1;

        const dateColIdx = isIpd ? 4 : 0;

        if (docRecDT) { docRecDT.destroy(); docRecDT = null; }
        $('#docRecTable').empty();
        $('#docRecTable').append('<tfoot><tr class="table-light fw-bold" id="docRecFootRow"></tr></tfoot>');

        let needSummary = true;
        docRecDT = $('#docRecTable').DataTable({
            serverSide: true,
            processing: true,
            searchDelay: 600,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
            order: [[dateColIdx, 'desc']],
            columns: cols,
            language: { url: "{{ asset('assets/vendor/datatables/th.json') }}" },
            ajax: {
                url: DOC_REC_URL,
                type: 'GET',
                timeout: 90000,
                data: function(d) {
                    if (needSummary) { d.summary = 1; }
                    return $.extend(d, docRecCtx, { hospcode: hcode });
                },
                dataSrc: function(json) {
                    needSummary = false;
                    if (json.success === false) {
                        $('#docRecAlert').removeClass('d-none').text(json.message || 'ดึงข้อมูลไม่สำเร็จ');
                        $('#docRecKpis').empty();
                        needSummary = true;
                        return [];
                    }
                    $('#docRecAlert').addClass('d-none');
                    if (json.summary) { docRecRenderSummary(json, totalColIdx); }
                    return json.data || [];
                }
            }
        }).on('error.dt', function(e, settings, techNote, message) {
            needSummary = true;
            $('#docRecAlert').removeClass('d-none').text('ติดต่อเซิร์ฟเวอร์ไม่สำเร็จ หรือไม่มีสิทธิ์เข้าถึง (' + message + ')');
        });

        // ปุ่มส่งออก: ส่งคำค้นปัจจุบันไปด้วย
        const buildExport = function() {
            const q = $.param($.extend({}, docRecCtx, { hospcode: hcode, search: docRecDT ? docRecDT.search() : '' }));
            $('#docRecExportBtn').attr('href', DOC_REC_EXPORT_URL + '?' + q);
        };
        buildExport();
        $('#docRecTable').off('search.dt.rec').on('search.dt.rec', buildExport);

        bootstrap.Modal.getOrCreateInstance(document.getElementById('doctorRecordsModal')).show();
    };

    function docRecRenderSummary(json, totalColIdx) {
        const s = json.summary || { cases: 0, total: 0 };
        $('#docRecKpis').html(
            '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">จำนวนเคส: <strong>' + docRecFmt(s.cases, 0) + '</strong></span>' +
            '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">ค่ารักษารวม: <strong>฿' + docRecFmt(s.total, 2) + '</strong></span>'
        );

        // เทียบกระทบยอดกับตารางสรุปที่ agent ส่งมา
        if (json.expected_total !== undefined && Number(json.expected_total) !== Number(json.recordsTotal)) {
            $('#docRecWarn').removeClass('d-none').html(
                '<i class="fa-solid fa-triangle-exclamation me-1"></i>จำนวนเคสจาก HOSxP สด (<strong>' + docRecFmt(json.recordsTotal, 0) +
                '</strong>) ไม่ตรงกับตารางสรุป (<strong>' + docRecFmt(json.expected_total, 0) + '</strong>) - ข้อมูลที่ agent ส่งล่าสุดอาจยังไม่อัปเดต');
        } else {
            $('#docRecWarn').addClass('d-none');
        }

        // แถวรวมทั้งช่วง (ทุกเคส ไม่ใช่เฉพาะหน้าที่เห็น)
        $('#docRecFootRow').html(
            '<th colspan="' + totalColIdx + '" class="text-end">รวมทั้งช่วง (ทุกเคส ' + docRecFmt(s.cases, 0) + ')</th>' +
            '<th class="text-end text-success">' + docRecFmt(s.total, 2) + '</th>'
        );
    }

    window.openDoctorIncomeModal = function(doctorId, doctorName, defaultType = 'ALL') {
        currentDocIncomeId = doctorId;
        currentDocIncomeType = defaultType;

        $('#docIncomeDoctorName').text(doctorName);
        updateDocIncomeBtnStyles(defaultType);

        $('#docIncomeLoader').removeClass('d-none');
        $('#docIncomeContent').addClass('d-none');
        const modalEl = new bootstrap.Modal(document.getElementById('doctorIncomeModal'));
        modalEl.show();

        fetchDoctorIncomeData();
    };

    $('.doc-inc-type-btn').on('click', function() {
        const type = $(this).data('type');
        currentDocIncomeType = type;
        updateDocIncomeBtnStyles(type);
        fetchDoctorIncomeData();
    });

    function fetchDoctorIncomeData() {
        if (!currentDocIncomeId) return;
        $('#docIncomeLoader').removeClass('d-none');
        $('#docIncomeContent').addClass('d-none');

        $.ajax({
            url: "{{ url('manage/opoh/doctors') }}/" + currentDocIncomeId + "/income-categories",
            type: 'GET',
            data: {
                service_type: currentDocIncomeType,
                start_date: '{{ $current_start }}',
                end_date: '{{ $current_end }}'
            },
            success: function(res) {
                $('#docIncomeLoader').addClass('d-none');
                $('#docIncomeContent').removeClass('d-none');

                if (!res.success) {
                    Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                    return;
                }

                $('#docIncomeSpecBadge').text(res.doctor.specialty_name || '-');
                $('#docIncomeLicense').text(res.doctor.licenseno || '-');

                // Render Rows
                let tbodyHtml = '';
                res.categories.forEach(function(row) {
                    tbodyHtml += `
                        <tr>
                            <td class="fw-bold text-dark text-start">${row.drg_chrgitem_name}</td>
                            <td class="text-end ${row.ucs_incup > 0 ? 'text-dark' : 'text-muted opacity-50'}">${row.ucs_incup > 0 ? Number(row.ucs_incup).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end ${row.ucs_outprov > 0 ? 'text-dark' : 'text-muted opacity-50'}">${row.ucs_outprov > 0 ? Number(row.ucs_outprov).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end ${row.ofc > 0 ? 'text-dark' : 'text-muted opacity-50'}">${row.ofc > 0 ? Number(row.ofc).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end ${row.lgo > 0 ? 'text-dark' : 'text-muted opacity-50'}">${row.lgo > 0 ? Number(row.lgo).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end ${row.sss > 0 ? 'text-dark' : 'text-muted opacity-50'}">${row.sss > 0 ? Number(row.sss).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end ${row.other > 0 ? 'text-dark' : 'text-muted opacity-50'}">${row.other > 0 ? Number(row.other).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end col-inc-total fw-bold text-success bg-success bg-opacity-10">${Number(row.total).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>
                    `;
                });
                $('#docIncomeTableBody').html(tbodyHtml);

                // Render Foot
                const t = res.totals;
                const tfootHtml = `
                    <tr class="fw-bold align-middle bg-success bg-opacity-10" style="border-top: 2px solid #16a34a;">
                        <td class="text-center fw-bolder text-dark">รวมทั้งหมด (16 หมวด)</td>
                        <td class="text-end fw-bold">${Number(t.ucs_incup).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end fw-bold">${Number(t.ucs_outprov).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end fw-bold">${Number(t.ofc).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end fw-bold">${Number(t.lgo).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end fw-bold">${Number(t.sss).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end fw-bold">${Number(t.other).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end col-inc-total fw-bolder text-success fs-6 bg-success bg-opacity-25">฿${Number(t.total).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>
                `;
                $('#docIncomeTableFoot').html(tfootHtml);

                // Render 6 KPI Cards
                const grand = t.total > 0 ? t.total : 1;
                const rightsMap = [
                    { name: 'UC ในจังหวัด', val: t.ucs_incup, color: '#0284c7', icon: 'fa-id-card' },
                    { name: 'UC ต่างจังหวัด', val: t.ucs_outprov, color: '#06b6d4', icon: 'fa-plane-departure' },
                    { name: 'ข้าราชการ (OFC)', val: t.ofc, color: '#16a34a', icon: 'fa-building-columns' },
                    { name: 'อปท. (LGO)', val: t.lgo, color: '#d97706', icon: 'fa-landmark' },
                    { name: 'ประกันสังคม (SSS)', val: t.sss, color: '#9333ea', icon: 'fa-shield-halved' },
                    { name: 'ชำระเงิน/อื่นๆ', val: t.other, color: '#64748b', icon: 'fa-money-bill-wave' }
                ];
                let cardsHtml = '';
                rightsMap.forEach(function(r) {
                    const pct = ((r.val / grand) * 100).toFixed(1);
                    cardsHtml += `
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="card h-100 border rounded-3 p-2 shadow-sm bg-white" style="border-left: 4px solid ${r.color} !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge rounded-pill fw-bold" style="background-color: ${r.color}15; color: ${r.color}; font-size: 0.68rem;">
                                        <i class="fa-solid ${r.icon} me-1"></i>${r.name}
                                    </span>
                                    <span class="text-muted fw-bold" style="font-size: 0.70rem;">${pct}%</span>
                                </div>
                                <div class="mt-0.5">
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">฿${Number(r.val).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#docIncomeCardsRow').html(cardsHtml);
            },
            error: function() {
                $('#docIncomeLoader').addClass('d-none');
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อข้อมูลรายได้ของแพทย์ได้', 'error');
            }
        });
    }

    window.exportTableToExcel = function(tableId, filename, sheetName) {
        if (typeof XLSX === 'undefined') {
            alert('กรุณารอไลบรารี XLSX โหลดสักครู่');
            return;
        }
        const tableSelect = document.getElementById(tableId);
        if (!tableSelect) return;
        let cleanSheet = (sheetName || 'Sheet1').replace(/[:\\/?*\[\]]/g, '').substring(0, 31);
        const wb = XLSX.utils.table_to_book(tableSelect, { sheet: cleanSheet });
        let cleanFilename = (filename ? filename : 'excel_export').replace(/[\/\\:\*\?"<>\|]+/g, '_');
        const fname = cleanFilename + '.xlsx';
        XLSX.writeFile(wb, fname);
    };

    window.exportDoctorIncomeToExcel = function() {
        let rawDocName = $('#docIncomeDoctorName').text().trim() || 'แพทย์';
        let cleanDocName = rawDocName.replace(/[\s\.\/\\:\*\?"<>\|]+/g, '_').replace(/^_+|_+$/g, '');
        let typeText = currentDocIncomeType === 'ALL' ? 'รวมทั้งหมด_OPD_IPD' : (currentDocIncomeType === 'OPD' ? 'ผู้ป่วยนอก_OPD' : 'ผู้ป่วยใน_IPD');
        let dateText = '{{ $current_start }}_ถึง_{{ $current_end }}';

        let filename = 'รายงาน16หมวดค่ารักษา_' + cleanDocName + '_' + typeText + '_' + dateText;
        let sheetName = (cleanDocName.length > 25 ? cleanDocName.substring(0, 25) : cleanDocName);

        exportTableToExcel('doctorIncomeTable', filename, sheetName);
    };

    window.exportMainDoctorTableToExcel = function() {
        const isOpd = $('#pills-admin-opd-tab').hasClass('active');
        const tableId = isOpd ? 'doctorVisitsTable' : 'doctorIpdTable';
        const typeText = isOpd ? 'ผู้ป่วยนอก_OPD' : 'ผู้ป่วยใน_IPD';
        const dateText = '{{ $current_start }}_ถึง_{{ $current_end }}';

        let filename = 'รายงานสถิติผลงานแพทย์_AOPH_' + typeText + '_' + dateText;
        let sheetName = (isOpd ? 'สถิติ_OPD' : 'สถิติ_IPD');

        exportTableToExcel(tableId, filename, sheetName);
    };
});
</script>
@endpush
