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
  #doctorIpdTable tfoot tr {
    background: #ffe4e6 !important;
    color: #9f1239 !important;
    border-top: 2px solid #f43f5e !important;
  }
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
                    <div style="min-width: 140px;">
                        <select name="budget_year" id="budget_year_select" class="form-select form-select-sm shadow-sm" title="เลือกปีงบประมาณ">
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

                <div class="text-muted small">
                    <i class="fa-solid fa-circle-info text-info me-1"></i>
                    แสดงข้อมูลแพทย์เฉพาะทางหมุนเวียนออกตรวจและดูแลผู้ป่วย ณ รพช. 6 แห่ง
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
                                            {{ $hv > 0 ? number_format($hv) : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ 3 + count($hospitalsList) }}" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                                        ยังไม่มีข้อมูลการออกตรวจในช่วงเวลาที่เลือก
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end fw-bold">รวมทั้งหมด</td>
                                    <td class="text-end fw-bolder">{{ number_format($colTotals['cumulative']) }}</td>
                                    <td class="text-end fw-bolder bg-primary bg-opacity-10 text-primary">{{ number_format($colTotals['period']) }}</td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <td class="text-end fw-bolder">{{ number_format($colTotals[$hcode]) }}</td>
                                    @endforeach
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
                                            {{ $ha > 0 ? number_format($ha) : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="text-end fw-bold text-dark">
                                        {{ number_format($row['inc'], 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ 6 + count($hospitalsList) }}" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                                        ยังไม่มีข้อมูลการจำหน่ายผู้ป่วยใน (IPD) ในช่วงเวลาที่เลือก
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end fw-bold">รวมทั้งหมด</td>
                                    <td class="text-end fw-bolder">{{ number_format($colIpdTotals['cumulative']) }}</td>
                                    <td class="text-end fw-bolder bg-danger bg-opacity-10 text-danger">{{ number_format($colIpdTotals['period']) }}</td>
                                    <td class="text-end fw-bolder">{{ number_format($colIpdTotals['los']) }}</td>
                                    <td class="text-end fw-bolder">{{ number_format($colIpdTotals['rw'], 4) }}</td>
                                    @foreach($hospitalsList as $hcode => $hname)
                                        <td class="text-end fw-bolder">{{ number_format($colIpdTotals[$hcode]) }}</td>
                                    @endforeach
                                    <td class="text-end fw-bolder text-dark">{{ number_format($colIpdTotals['inc'], 2) }}</td>
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
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/th.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/flatpickr-th-buddhist.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
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
});
</script>
@endpush
