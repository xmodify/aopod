@extends('layouts.app')

@section('title', 'ยอดสะสมผู้รับบริการแยกรายสาขา (OPOH) | AOPOD')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
<style>
  .opoh-glass-card {
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(2, 132, 199, 0.2);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(2, 132, 199, 0.08);
  }
  .opoh-stat-card {
    border-radius: 18px;
    transition: all 0.25s ease-in-out;
    border: 1px solid rgba(0,0,0,0.06);
    background: #ffffff;
  }
  .opoh-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(2, 132, 199, 0.15);
  }
  .table-opoh thead th {
    background: #f0f7ff !important;
    color: #0369a1 !important;
    font-weight: 700 !important;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 12px 10px !important;
    font-size: 0.9rem !important;
    border-bottom: 2px solid #bae6fd !important;
  }
  .table-opoh tbody td {
    vertical-align: middle !important;
    padding: 10px 12px !important;
    font-size: 0.9rem !important;
  }
  .tr-opoh-total {
    background: #e0f2fe !important;
    font-weight: 800 !important;
    border-top: 2px solid #0284c7 !important;
    color: #0369a1 !important;
  }

  /* === Clickable Scheme Cards & Table Rows === */
  .opoh-right-card {
    cursor: pointer;
    transition: all 0.25s ease-in-out;
  }
  .opoh-right-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(2, 132, 199, 0.16) !important;
    border-color: #38bdf8 !important;
  }
  .opoh-right-card .opoh-view-badge {
    opacity: 0.75;
    transition: opacity 0.2s ease;
  }
  .opoh-right-card:hover .opoh-view-badge {
    opacity: 1;
    text-decoration: underline;
  }
  .opoh-right-row {
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
  }
  .opoh-right-row:hover {
    background-color: #f0f9ff !important;
  }
  .opoh-right-row:hover .opoh-view-pill {
    background-color: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
  }
  .opoh-view-pill {
    transition: all 0.2s ease;
  }
  .tr-modal-community-total {
    background: #e0f2fe !important;
    font-weight: 800 !important;
    border-top: 2px solid #38bdf8 !important;
    color: #0369a1 !important;
  }
  .tr-modal-amnat-total {
    background: #ede9fe !important;
    font-weight: 800 !important;
    border-top: 1px solid #c4b5fd !important;
    color: #5b21b6 !important;
  }
  .tr-modal-grand-total {
    background: #dcfce7 !important;
    font-weight: 800 !important;
    border-top: 2px solid #22c55e !important;
    color: #15803d !important;
  }

  /* === Flatpickr Thai Buddhist Custom Styling === */
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
  .flatpickr-current-month .cur-month {
    font-weight: 700 !important;
  }
  .btn-quick-range {
    font-size: 0.78rem;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.2s;
  }
  .top-rank-badge {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 800;
  }
</style>

<div class="container-fluid px-3 px-xl-5 py-4">

  {{-- ==================== GLOBAL HEADER: 3 แท็บ (ซ้าย) + ตัวกรองช่วงเวลา (ขวา) แถวเดียวกัน ==================== --}}
  <div class="opoh-glass-card px-3 py-2.5 mb-4 shadow-sm">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      
      {{-- ฝั่งซ้าย: 3 แท็บเรียงเป็นแถวเดียวกัน ไม่ตกบรรทัด --}}
      <div class="d-flex align-items-center" style="min-width: 0; max-width: 100%;">
        <ul class="nav nav-pills gap-2 flex-nowrap overflow-auto pb-1" style="max-width: 100%; scrollbar-width: thin;" id="opohServiceTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active_tab === 'opd' ? 'active' : '' }} px-3 py-1.5 rounded-pill fw-bold d-flex align-items-center gap-1.5 text-nowrap" id="pills-opd-tab" data-bs-toggle="pill" data-bs-target="#pills-opd" type="button" role="tab" aria-controls="pills-opd" aria-selected="{{ $active_tab === 'opd' ? 'true' : 'false' }}">
              <i class="fa-solid fa-stethoscope"></i>
              <span>ผู้ป่วยนอก (OPD)</span>
              <span class="badge {{ $active_tab === 'opd' ? 'bg-white text-primary' : 'bg-primary bg-opacity-25 text-primary' }} rounded-pill ms-1" style="font-size: 0.72rem; font-weight: 700;">{{ number_format($sumCumulative) }} ครั้ง</span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active_tab === 'ipd' ? 'active' : '' }} px-3 py-1.5 rounded-pill fw-bold d-flex align-items-center gap-1.5 text-nowrap" id="pills-ipd-tab" data-bs-toggle="pill" data-bs-target="#pills-ipd" type="button" role="tab" aria-controls="pills-ipd" aria-selected="{{ $active_tab === 'ipd' ? 'true' : 'false' }}">
              <i class="fa-solid fa-bed-pulse"></i>
              <span>ผู้ป่วยใน (IPD)</span>
              <span class="badge {{ $active_tab === 'ipd' ? 'bg-white text-danger' : 'bg-danger text-white' }} rounded-pill ms-1" style="font-size: 0.72rem; font-weight: 700;">{{ number_format($ipdSumCumulative) }} ราย</span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active_tab === 'operation' ? 'active' : '' }} px-3 py-1.5 rounded-pill fw-bold d-flex align-items-center gap-1.5 text-nowrap" id="pills-op-tab" data-bs-toggle="pill" data-bs-target="#pills-op" type="button" role="tab" aria-controls="pills-op" aria-selected="{{ $active_tab === 'operation' ? 'true' : 'false' }}">
              <i class="fa-solid fa-scissors"></i>
              <span>ผ่าตัด</span>
              <span class="badge text-white rounded-pill ms-1" style="background-color: {{ $active_tab === 'operation' ? 'rgba(255,255,255,0.3)' : '#8b5cf6' }}; font-size: 0.72rem; font-weight: 700;">{{ number_format($opSumCumulative) }} เคส</span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active_tab === 'income' ? 'active' : '' }} px-3 py-1.5 rounded-pill fw-bold d-flex align-items-center gap-1.5 text-nowrap" id="pills-income-tab" data-bs-toggle="pill" data-bs-target="#pills-income" type="button" role="tab" aria-controls="pills-income" aria-selected="{{ $active_tab === 'income' ? 'true' : 'false' }}">
              <i class="fa-solid fa-file-invoice-dollar"></i>
              <span>หมวดค่ารักษา</span>
              <span class="badge {{ $active_tab === 'income' ? 'bg-white text-success' : 'bg-success bg-opacity-25 text-success' }} rounded-pill ms-1" style="font-size: 0.72rem; font-weight: 700;">฿{{ number_format($incomeCumulativeGrandTotal) }}</span>
            </button>
          </li>
        </ul>
      </div>

      {{-- ฝั่งขวา: Global Filter Controls แถวเดียวเรียบเนียน --}}
      <form method="GET" action="{{ route('web.opoh') }}" id="opohFilterForm" class="d-flex align-items-center flex-wrap gap-2 mb-0 ms-auto">
        <input type="hidden" name="active_tab" id="active_tab" value="{{ $active_tab }}">
        <input type="hidden" name="filter_type" id="filter_type" value="{{ $filter_type }}">

        {{-- เลือกปีงบ --}}
        <div style="width: 170px; max-width: 100%;">
          <select name="budget_year" id="budget_year_select" class="form-select form-select-sm shadow-sm" title="ปีงบประมาณ">
            @foreach($budget_year_select as $by)
              <option value="{{ $by->LEAVE_YEAR_ID }}" {{ $budget_year == $by->LEAVE_YEAR_ID ? 'selected' : '' }}>
                {{ $by->LEAVE_YEAR_NAME }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- วันที่เริ่มต้น --}}
        <div class="input-group input-group-sm shadow-sm" style="width: 125px;">
          <span class="input-group-text bg-white text-primary px-2"><i class="fa-regular fa-calendar"></i></span>
          <input type="text" id="start_date" name="start_date" class="form-control form-control-sm bg-white px-1 text-center" value="{{ $current_start }}" placeholder="วว/ดด/ปป" title="วันที่เริ่มต้น">
        </div>

        <span class="text-secondary small fw-bold">ถึง</span>

        {{-- วันที่สิ้นสุด --}}
        <div class="input-group input-group-sm shadow-sm" style="width: 125px;">
          <span class="input-group-text bg-white text-primary px-2"><i class="fa-regular fa-calendar"></i></span>
          <input type="text" id="end_date" name="end_date" class="form-control form-control-sm bg-white px-1 text-center" value="{{ $current_end }}" placeholder="วว/ดด/ปป" title="วันที่สิ้นสุด">
        </div>

        {{-- ปุ่มค้นหา & คืนค่า --}}
        <button type="submit" class="btn btn-sm btn-primary px-2.5 py-1.5 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-1" title="ค้นหา">
          <i class="fa-solid fa-magnifying-glass"></i>
          <span>ค้นหา</span>
        </button>

        <a href="{{ route('web.opoh', ['budget_year' => $budget_year, 'filter_type' => 'week', 'active_tab' => $active_tab]) }}" class="btn btn-sm btn-outline-secondary px-2 py-1.5 rounded-3" title="คืนค่าเริ่มต้น">
          <i class="fa-solid fa-rotate-left"></i>
        </a>

        {{-- Quick Presets (ปุ่มลัดสัปดาห์นี้ / เดือนนี้ / ทั้งปี) ต่อท้ายในแถวเดียวกัน --}}
        <div class="d-flex align-items-center gap-1 border-start ps-2 ms-1">
          <button type="button" class="btn btn-sm btn-outline-primary btn-quick-range {{ $filter_type === 'week' ? 'active' : '' }}" data-range="week" style="font-size: 0.76rem; padding: 3px 8px;">
            สัปดาห์นี้
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary btn-quick-range {{ $filter_type === 'month' ? 'active' : '' }}" data-range="month" style="font-size: 0.76rem; padding: 3px 8px;">
            เดือนนี้
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary btn-quick-range {{ $filter_type === 'fiscal' ? 'active' : '' }}" data-range="fiscal" style="font-size: 0.76rem; padding: 3px 8px;">
            ทั้งปีงบ {{ $budget_year }}
          </button>
        </div>
      </form>

    </div>
  </div>

  <div class="tab-content" id="opohServiceTabsContent">
    
    {{-- ==================== 1. TAB OPD (ผู้ป่วยนอก) ==================== --}}
    <div class="tab-pane fade {{ $active_tab === 'opd' ? 'show active' : '' }}" id="pills-opd" role="tabpanel" aria-labelledby="pills-opd-tab">
      
      {{-- 1. ยอดสะสมผู้รับบริการแยกรายสาขา (OPD) --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
              <i class="fa-solid fa-chart-pie fs-3 text-primary"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">OPOH • OPD</span>
                <span class="text-secondary small fw-semibold">สถิติด้านการแพทย์และสาธารณสุข จังหวัดอำนาจเจริญ</span>
              </div>
              <h4 class="fw-bold mb-0 text-dark">
                One Province One Hospital (OPOH) - ผู้ป่วยนอก (OPD)
              </h4>
              <div class="text-muted small mt-1">
                <i class="fa-solid fa-circle-info text-primary me-1"></i>ยอดสะสม: นับตั้งแต่ ต.ค. 2568 ถึงปัจจุบัน
              </div>
            </div>
          </div>
          {{-- Onsite vs Telemed Quick Badges --}}
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 fw-bold">
              <i class="fa-solid fa-user-doctor me-1"></i>Onsite: {{ number_format(max(0, ($fiscalSummary->visit_total ?? 0) - ($fiscalSummary->visit_telemed ?? 0))) }} ครั้ง
            </span>
            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1.5 fw-bold">
              <i class="fa-solid fa-headset me-1"></i>Telemed: {{ number_format(($fiscalSummary->visit_telemed ?? 0)) }} ครั้ง
            </span>
          </div>
        </div>

        {{-- 7 Specialty Cards Inside Card 1 (OPD) --}}
        <div class="row g-3 pt-2">
          @foreach($specialties as $code => $spec)
          @php
            $val = $specialtyStats[$code] ?? 0;
          @endphp
          <div class="col-6 col-md-4 col-xl">
            <div class="opoh-stat-card p-3 h-100 position-relative overflow-hidden shadow-sm">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fw-bold text-secondary text-truncate small" title="{{ $spec['name'] }}">
                  {{ $spec['name'] }}
                </span>
                <i class="fa-solid {{ $spec['icon'] }} fs-4 ms-2" style="color: {{ $spec['color'] }}; opacity: 0.85;"></i>
              </div>
              <div class="d-flex align-items-baseline gap-1 mt-1">
                <h3 class="fw-bolder mb-0 text-dark" style="color: {{ $spec['color'] }} !important;">
                  {{ number_format($val) }}
                </h3>
                <span class="text-muted" style="font-size: 0.75rem;">ครั้ง</span>
              </div>
              <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $sumCumulative > 0 ? ($val / $sumCumulative) * 100 : 0 }}%; background-color: {{ $spec['color'] }};"></div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- 2. แถวกราฟคู่ 7 : 5 (ซ้าย Bar Chart 12 เดือน / ขวา Top 5 อันดับโรค OPD) --}}
      <div class="row g-4 mb-4">
        {{-- ด้านซ้าย (7): กราฟแท่งแนวโน้ม 12 เดือน --}}
        <div class="col-12 col-xl-7">
          <div class="opoh-glass-card p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-chart-column text-primary"></i>
                  <span>แนวโน้มการจัดบริการแพทย์เฉพาะทางรายเดือน (ปีงบ {{ $budget_year }})</span>
                </h5>
              </div>
              <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
                <i class="fa-solid fa-chart-column me-1"></i>รายเดือน
              </span>
            </div>
            <div style="height: 290px; position: relative;">
              <canvas id="opdMonthlyChartCanvas"></canvas>
            </div>
          </div>
        </div>

        {{-- ด้านขวา (5): 5 อันดับโรคผู้ป่วยนอก --}}
        <div class="col-12 col-xl-5">
          <div class="opoh-glass-card p-4 h-100 d-flex flex-column">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-ranking-star text-warning"></i>
                  <span>5 อันดับโรคผู้ป่วยนอก</span>
                </h5>
              </div>
              <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
                5 อันดับ
              </span>
            </div>

            <div class="flex-grow-1 d-flex flex-column justify-content-around">
              @php
                $opdTotalDiagCases = $topOpdDiagnoses->sum('total');
              @endphp

              @forelse($topOpdDiagnoses as $index => $diag)
              @php
                $pct = $opdTotalDiagCases > 0 ? round(($diag->total / $opdTotalDiagCases) * 100, 1) : 0;
                $rankColors = ['#ef4444', '#f97316', '#eab308', '#3b82f6', '#64748b'];
                $curColor = $rankColors[$index] ?? '#64748b';
              @endphp
              <div class="mb-2.5">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 78%;">
                    <span class="top-rank-badge text-white" style="background-color: {{ $curColor }};">{{ $index + 1 }}</span>
                    <strong class="text-dark small font-monospace">{{ $diag->icd10 }}</strong>
                    <span class="text-muted small text-truncate" title="{{ $diag->diag_name }}">{{ $diag->diag_name }}</span>
                  </div>
                  <div class="text-end">
                    <strong class="small text-dark">{{ number_format($diag->total) }}</strong>
                    <span class="text-muted" style="font-size: 0.72rem;">ครั้ง ({{ $pct }}%)</span>
                  </div>
                </div>
                <div class="progress" style="height: 6px; border-radius: 6px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background-color: {{ $curColor }};"></div>
                </div>
              </div>
              @empty
              <div class="text-center py-4 my-auto">
                <i class="fa-solid fa-notes-medical fs-1 text-muted opacity-25 mb-2"></i>
                <div class="text-muted small fw-semibold">กำลังรอข้อมูลสรุปโรคจาก Agent ประจำ รพ.</div>
                <span class="text-secondary" style="font-size: 0.75rem;">ระบบจะแสดง 5 อันดับโรคหลักทันทีที่ Agent ส่งรอบถัดไป</span>
              </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      {{-- 3. สถิติการรับบริการแพทย์เฉพาะทางแยกรายสาขา --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-hospital text-primary"></i>
              <span>สถิติการรับบริการแพทย์เฉพาะทางแยกรายสาขา</span>
            </h5>
            <span class="text-muted small">
              ช่วงวันที่เลือก: <strong class="text-primary">{{ $thaiDateRangeText }}</strong> | รวมทั้งจังหวัด: <strong class="text-primary">{{ number_format($sumCumulative) }}</strong> ครั้ง
            </span>
          </div>
          <button type="button" onclick="exportTableToExcel('opohOpdSpecTable', 'OPOH_OPD_แยกรายสาขา_{{ $current_start }}_ถึง_{{ $current_end }}', 'OPD')" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm align-self-start" style="font-size: 0.78rem;" title="ส่งออกตารางนี้เป็น Excel (.xlsx)">
            <i class="fa-solid fa-file-excel me-1"></i> Excel
          </button>
        </div>

        {{-- ตารางสถิติแยกรายสาขาเฉพาะทาง (OPD) --}}
        <div class="table-responsive rounded-4 border">
          <table id="opohOpdSpecTable" class="table table-hover table-opoh align-middle mb-0">
            <thead>
              <tr>
                <th class="text-start" style="min-width: 220px;">สาขาเฉพาะทาง (7 สาขาหลัก)</th>
                <th style="min-width: 110px;" class="text-end" title="นับตั้งแต่ ต.ค. 2568 ถึงปัจจุบัน">ยอดสะสม<div class="fw-normal text-muted" style="font-size: 0.68rem;">ต.ค. 68 – ปัจจุบัน</div></th>
                <th style="min-width: 110px;" class="text-end bg-primary bg-opacity-10 text-primary">ช่วงวันที่เลือก</th>
                @foreach($hospitalsList as $hcode => $hname)
                <th style="min-width: 110px;" class="text-end">{{ $hname }}</th>
                @endforeach
                <th style="min-width: 120px;" class="text-end">ค่าบริการรวม</th>
                <th style="min-width: 115px;" class="text-end bg-light border-start text-secondary">
                  {{ $hubHospitalName }}
                </th>
              </tr>
            </thead>
            <tbody>
              @forelse($specialtyRows as $row)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center" style="background-color: {{ $row['color'] }}; width: 34px; height: 34px;">
                      <i class="fa-solid {{ $row['icon'] }} fs-6"></i>
                    </div>
                    <div>
                      <strong class="d-block text-dark">{{ $row['name'] }}</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">รหัสสาขา: {{ $row['code'] }}</span>
                    </div>
                  </div>
                </td>
                <td class="text-end fw-bold text-dark">{{ number_format($row['cumulative']) }}</td>
                <td class="text-end fw-bold bg-primary bg-opacity-10 text-primary">{{ number_format($row['period']) }}</td>
                @foreach($hospitalsList as $hcode => $hname)
                <td class="text-end {{ ($row['hosp_visits'][$hcode] ?? 0) > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                  {{ number_format($row['hosp_visits'][$hcode] ?? 0) }}
                </td>
                @endforeach
                <td class="text-end fw-bold text-dark">฿{{ number_format($row['inc'], 2) }}</td>
                <td class="text-end fw-bold bg-light border-start text-secondary">
                  {{ number_format($row['amnat_visits']) }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="{{ count($hospitalsList) + 5 }}" class="text-center py-4 text-muted">
                  ไม่พบข้อมูลบริการในช่วงเวลาที่เลือก
                </td>
              </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="tr-opoh-total">
                <td class="text-end fw-bold">ยอดรวม OPOH (รพช. {{ count($hospitalsList) }} แห่ง)</td>
                <td class="text-end fw-bolder text-dark fs-5">{{ number_format($specColTotals['cumulative']) }}</td>
                <td class="text-end fw-bolder bg-primary bg-opacity-10 text-primary fs-6">{{ number_format($specColTotals['period']) }}</td>
                @foreach($hospitalsList as $hcode => $hname)
                <td class="text-end fw-bolder">{{ number_format($specColTotals[$hcode]) }}</td>
                @endforeach
                <td class="text-end fw-bolder text-dark">฿{{ number_format($specColTotals['inc'], 2) }}</td>
                <td class="text-end fw-bolder bg-light border-start text-secondary">
                  {{ number_format($specColTotals['amnat']) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- 4. สรุปสิทธิการรักษาและการเงิน (OPD - 6 สิทธิมาตรฐาน) --}}
      <div class="opoh-glass-card p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-id-card-clip text-primary"></i>
              <span>ข้อมูลการให้บริการและมูลค่าการรักษาจำแนกตามสิทธิการรักษา (OPD)</span>
            </h5>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="text-end">
              <span class="text-muted small d-block">มูลค่ารวมทั้งหมด</span>
              <strong class="text-dark fs-5">฿{{ number_format($summary->inc_total ?? 0, 2) }}</strong>
              <span class="text-muted ms-1" style="font-size: 0.75rem;">(ค่ายา ฿{{ number_format($summary->inc_drug_total ?? 0, 2) }})</span>
            </div>
          </div>
        </div>

        <div class="row g-3">
          @foreach($rightsConfig as $rKey => $rCfg)
          @php
            $rData = $opdRightsRows[$rKey] ?? ['visits' => 0, 'inc' => 0];
            $rPct = ($opdVisitsTotal > 0) ? round(($rData['visits'] / $opdVisitsTotal) * 100, 1) : 0;
          @endphp
          <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="p-3 rounded-4 border h-100 bg-white shadow-sm position-relative opoh-right-card"
                 onclick="openRightBreakdownModal('opd', '{{ $rKey }}')"
                 role="button"
                 title="คลิกเพื่อดูสถิติแยกรายโรงพยาบาล">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge rounded-pill px-2 py-0.5" style="background-color: {{ $rCfg['bg'] }}; color: {{ $rCfg['color'] }}; font-size: 0.72rem; font-weight: 700;">
                  <i class="fa-solid {{ $rCfg['icon'] }} me-1"></i>{{ $rCfg['badge'] }}
                </span>
                <span class="text-muted fw-bold" style="font-size: 0.75rem;">{{ $rPct }}%</span>
              </div>
              <div class="text-secondary small fw-bold text-truncate" title="{{ $rCfg['name'] }}">
                {{ $rCfg['name'] }}
              </div>
              <div class="d-flex align-items-baseline gap-1 mt-1">
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($rData['visits']) }}</h4>
                <span class="text-muted" style="font-size: 0.72rem;">ครั้ง</span>
              </div>
              <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted" style="font-size: 0.7rem;">มูลค่าค่ารักษา</span>
                <span class="fw-bold text-dark" style="font-size: 0.78rem;">฿{{ number_format($rData['inc'], 2) }}</span>
              </div>
              <div class="text-end mt-1.5">
                <span class="text-primary opoh-view-badge" style="font-size: 0.68rem; font-weight: 600;">
                  <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>แยกราย รพ.
                </span>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

    </div>


    {{-- ==================== 2. TAB IPD (ผู้ป่วยใน) ==================== --}}
    <div class="tab-pane fade {{ $active_tab === 'ipd' ? 'show active' : '' }}" id="pills-ipd" role="tabpanel" aria-labelledby="pills-ipd-tab">
      
      {{-- 1. ยอดสะสมผู้ป่วยในแยกรายสาขา (IPD) --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-4">
              <i class="fa-solid fa-bed-pulse fs-3 text-danger"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">OPOH • IPD</span>
                <span class="text-secondary small fw-semibold">สถิติด้านการแพทย์และสาธารณสุข จังหวัดอำนาจเจริญ</span>
              </div>
              <h4 class="fw-bold mb-0 text-dark">
                One Province One Hospital (OPOH) - ผู้ป่วยใน (IPD)
              </h4>
              <div class="text-muted small mt-1">
                <i class="fa-solid fa-circle-info text-danger me-1"></i>ยอดสะสม: นับตั้งแต่ ต.ค. 2568 ถึงปัจจุบัน
              </div>
            </div>
          </div>
        </div>

        {{-- 7 Specialty Cards Inside Card 1 (IPD) --}}
        <div class="row g-3 pt-2">
          @foreach($specialties as $code => $spec)
          @php
            $ipdVal = $ipdSpecialtyStats[$code] ?? 0;
          @endphp
          <div class="col-6 col-md-4 col-xl">
            <div class="opoh-stat-card p-3 h-100 position-relative overflow-hidden shadow-sm">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fw-bold text-secondary text-truncate small" title="{{ $spec['name'] }}">
                  {{ $spec['name'] }}
                </span>
                <i class="fa-solid {{ $spec['icon'] }} fs-4 ms-2" style="color: {{ $spec['color'] }}; opacity: 0.85;"></i>
              </div>
              <div class="d-flex align-items-baseline gap-1 mt-1">
                <h3 class="fw-bolder mb-0 text-dark" style="color: {{ $spec['color'] }} !important;">
                  {{ number_format($ipdVal) }}
                </h3>
                <span class="text-muted" style="font-size: 0.75rem;">ราย (AN)</span>
              </div>
              <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $ipdSumCumulative > 0 ? ($ipdVal / $ipdSumCumulative) * 100 : 0 }}%; background-color: {{ $spec['color'] }};"></div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- 2. แถวกราฟคู่ 7 : 5 (ซ้าย Bar Chart 12 เดือน / ขวา Top 5 อันดับโรค IPD) --}}
      <div class="row g-4 mb-4">
        {{-- ด้านซ้าย (7): กราฟแท่งแนวโน้ม 12 เดือน IPD --}}
        <div class="col-12 col-xl-7">
          <div class="opoh-glass-card p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-chart-column text-danger"></i>
                  <span>แนวโน้มผู้ป่วยใน (IPD) แพทย์เฉพาะทางรายเดือน (ปีงบ {{ $budget_year }})</span>
                </h5>
              </div>
              <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
                <i class="fa-solid fa-chart-column me-1"></i>รายเดือน
              </span>
            </div>
            <div style="height: 290px; position: relative;">
              <canvas id="ipdMonthlyChartCanvas"></canvas>
            </div>
          </div>
        </div>

        {{-- ด้านขวา (5): 5 อันดับโรคผู้ป่วยใน --}}
        <div class="col-12 col-xl-5">
          <div class="opoh-glass-card p-4 h-100 d-flex flex-column">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-ranking-star text-danger"></i>
                  <span>5 อันดับโรคผู้ป่วยใน</span>
                </h5>
              </div>
              <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small fw-semibold">
                5 อันดับ
              </span>
            </div>

            <div class="flex-grow-1 d-flex flex-column justify-content-around">
              @php
                $ipdTotalDiagCases = $topIpdDiagnoses->sum('total');
              @endphp

              @forelse($topIpdDiagnoses as $index => $diag)
              @php
                $pct = $ipdTotalDiagCases > 0 ? round(($diag->total / $ipdTotalDiagCases) * 100, 1) : 0;
                $rankColors = ['#ef4444', '#f97316', '#eab308', '#3b82f6', '#64748b'];
                $curColor = $rankColors[$index] ?? '#64748b';
              @endphp
              <div class="mb-2.5">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 78%;">
                    <span class="top-rank-badge text-white" style="background-color: {{ $curColor }};">{{ $index + 1 }}</span>
                    <strong class="text-dark small font-monospace">{{ $diag->icd10 }}</strong>
                    <span class="text-muted small text-truncate" title="{{ $diag->diag_name }}">{{ $diag->diag_name }}</span>
                  </div>
                  <div class="text-end">
                    <strong class="small text-dark">{{ number_format($diag->total) }}</strong>
                    <span class="text-muted" style="font-size: 0.72rem;">ราย ({{ $pct }}%)</span>
                  </div>
                </div>
                <div class="progress" style="height: 6px; border-radius: 6px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background-color: {{ $curColor }};"></div>
                </div>
              </div>
              @empty
              <div class="text-center py-4 my-auto">
                <i class="fa-solid fa-bed-pulse fs-1 text-muted opacity-25 mb-2"></i>
                <div class="text-muted small fw-semibold">กำลังรอข้อมูลสรุปโรคผู้ป่วยในจาก Agent ประจำ รพ.</div>
                <span class="text-secondary" style="font-size: 0.75rem;">ระบบจะแสดง 5 อันดับโรคหลักทันทีที่ Agent ส่งรอบถัดไป</span>
              </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      {{-- 3. สถิติการรับบริการผู้ป่วยในแพทย์เฉพาะทางแยกรายสาขา --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-bed text-danger"></i>
              <span>สถิติการรับบริการผู้ป่วยในแพทย์เฉพาะทางแยกรายสาขา</span>
            </h5>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
              <span class="text-muted small">
                ช่วงวันที่เลือก: <strong class="text-primary">{{ $thaiDateRangeText }}</strong>
              </span>
              <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                สะสมรวม: <strong class="fs-6">{{ number_format($ipdSumCumulative) }}</strong> ราย (AN)
              </span>
              <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                วันนอนรวม: <strong>{{ number_format($ipdSumLos) }}</strong> วัน
              </span>
              <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                AdjRW รวม: <strong>{{ number_format($ipdSumRw, 4) }}</strong>
              </span>
            </div>
          </div>
          <button type="button" onclick="exportTableToExcel('opohIpdSpecTable', 'OPOH_IPD_แยกรายสาขา_{{ $current_start }}_ถึง_{{ $current_end }}', 'IPD')" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm align-self-start" style="font-size: 0.78rem;" title="ส่งออกตารางนี้เป็น Excel (.xlsx)">
            <i class="fa-solid fa-file-excel me-1"></i> Excel
          </button>
        </div>

        {{-- ตารางสถิติ IPD Matrix --}}
        <div class="table-responsive rounded-4 border">
          <table id="opohIpdSpecTable" class="table table-hover table-opoh align-middle mb-0">
            <thead>
              <tr>
                <th class="text-start" style="min-width: 220px;">สาขาเฉพาะทาง (7 สาขาหลัก)</th>
                <th style="min-width: 90px;" class="text-end" title="นับตั้งแต่ ต.ค. 2568 ถึงปัจจุบัน">สะสมรวม<div class="fw-normal text-muted" style="font-size: 0.68rem;">ต.ค. 68 – ปัจจุบัน</div></th>
                <th style="min-width: 90px;" class="text-end bg-danger bg-opacity-10 text-danger">ช่วงที่เลือก</th>
                <th style="min-width: 85px;" class="text-end">วันนอน</th>
                <th style="min-width: 95px;" class="text-end">AdjRW</th>
                <th style="min-width: 80px;" class="text-end">CMI</th>
                @foreach($hospitalsList as $hcode => $hname)
                <th style="min-width: 105px;" class="text-end">{{ $hname }}</th>
                @endforeach
                <th style="min-width: 120px;" class="text-end">ค่ารักษาพยาบาล</th>
                <th style="min-width: 110px;" class="text-end bg-light border-start text-secondary">
                  {{ $hubHospitalName }}
                </th>
              </tr>
            </thead>
            <tbody>
              @forelse($ipdSpecialtyRows as $row)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center" style="background-color: {{ $row['color'] }}; width: 34px; height: 34px;">
                      <i class="fa-solid {{ $row['icon'] }} fs-6"></i>
                    </div>
                    <div>
                      <strong class="d-block text-dark">{{ $row['name'] }}</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">รหัสสาขา: {{ $row['code'] }}</span>
                    </div>
                  </div>
                </td>
                <td class="text-end fw-bold text-dark">{{ number_format($row['cumulative']) }}</td>
                <td class="text-end fw-bold bg-danger bg-opacity-10 text-danger">{{ number_format($row['period']) }}</td>
                <td class="text-end fw-bold text-info">{{ number_format($row['los']) }}</td>
                <td class="text-end fw-bold text-primary">{{ number_format($row['rw'], 4) }}</td>
                <td class="text-end font-monospace small fw-bold text-secondary">{{ $row['period'] > 0 ? number_format($row['rw'] / $row['period'], 4) : '-' }}</td>
                @foreach($hospitalsList as $hcode => $hname)
                <td class="text-end {{ ($row['hosp_ans'][$hcode] ?? 0) > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                  {{ number_format($row['hosp_ans'][$hcode] ?? 0) }}
                </td>
                @endforeach
                <td class="text-end fw-bold text-dark">฿{{ number_format($row['inc'], 2) }}</td>
                <td class="text-end fw-bold bg-light border-start text-secondary">
                  {{ number_format($row['amnat_ans']) }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="{{ count($hospitalsList) + 8 }}" class="text-center py-4 text-muted">
                  ไม่พบข้อมูลผู้ป่วยในในช่วงเวลาที่เลือก
                </td>
              </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="tr-opoh-total">
                <td class="text-end fw-bold">ยอดรวม OPOH (รพช. {{ count($hospitalsList) }} แห่ง)</td>
                <td class="text-end fw-bolder text-dark fs-5">{{ number_format($ipdSpecColTotals['cumulative']) }}</td>
                <td class="text-end fw-bolder bg-danger bg-opacity-10 text-danger fs-6">{{ number_format($ipdSpecColTotals['period']) }}</td>
                <td class="text-end fw-bolder">{{ number_format($ipdSpecColTotals['los']) }}</td>
                <td class="text-end fw-bolder text-primary">{{ number_format($ipdSpecColTotals['rw'], 4) }}</td>
                <td class="text-end fw-bolder text-secondary font-monospace">
                  {{ $ipdSpecColTotals['period'] > 0 ? number_format($ipdSpecColTotals['rw'] / $ipdSpecColTotals['period'], 4) : '0.0000' }}
                </td>
                @foreach($hospitalsList as $hcode => $hname)
                <td class="text-end fw-bolder">{{ number_format($ipdSpecColTotals[$hcode]) }}</td>
                @endforeach
                <td class="text-end fw-bolder text-dark">฿{{ number_format($ipdSpecColTotals['inc'], 2) }}</td>
                <td class="text-end fw-bolder bg-light border-start text-secondary">
                  {{ number_format($ipdSpecColTotals['amnat']) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- 4. สรุปการจัดสรรและชดเชยค่าบริการผู้ป่วยในตามสิทธิการรักษา (IPD DRG & AdjRW Clearing Matrix) --}}
      <div class="opoh-glass-card p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-scale-balanced text-danger"></i>
              <span>ข้อมูลการจัดสรรและชดเชยค่าบริการผู้ป่วยในตามสิทธิการรักษา (IPD DRG & AdjRW Matrix)</span>
            </h5>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
              <i class="fa-solid fa-calculator me-1"></i>DRG Clearing
            </span>
          </div>
        </div>

        {{-- 5 KPI Mini Summary Cards (ยอดไม่นับซ้ำราย AN และกรอง รพ.ต้นสังกัด) --}}
        <div class="row g-3 mb-3">
          <div class="col-6 col-md-4 col-xl">
            <div class="p-3 rounded-4 border bg-white shadow-sm text-center h-100">
              <span class="text-muted small fw-bold">ผู้ป่วยใน (ANs)</span>
              <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($ipdAnsTotal) }}</h3>
              <span class="text-muted" style="font-size: 0.72rem;">ราย</span>
            </div>
          </div>
          <div class="col-6 col-md-4 col-xl">
            <div class="p-3 rounded-4 border bg-white shadow-sm text-center h-100">
              <span class="text-muted small fw-bold">วันนอน (LOS)</span>
              <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($ipdLosTotal) }}</h3>
              <span class="text-muted" style="font-size: 0.72rem;">วัน</span>
            </div>
          </div>
          <div class="col-6 col-md-4 col-xl">
            <div class="p-3 rounded-4 border bg-white shadow-sm text-center h-100">
              <span class="text-muted small fw-bold">AdjRW รวม</span>
              <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($ipdRwTotal, 4) }}</h3>
              <span class="text-muted" style="font-size: 0.72rem;">AdjRW</span>
            </div>
          </div>
          <div class="col-6 col-md-4 col-xl">
            <div class="p-3 rounded-4 border bg-white shadow-sm text-center h-100">
              <span class="text-muted small fw-bold">CMI เฉลี่ยรวม</span>
              <h3 class="fw-bold mb-0 mt-1" style="color: #8b5cf6;">
                {{ $ipdAnsTotal > 0 ? number_format($ipdRwTotal / $ipdAnsTotal, 4) : '0.0000' }}
              </h3>
              <span class="text-muted" style="font-size: 0.72rem;">AdjRW ÷ AN</span>
            </div>
          </div>
          <div class="col-12 col-md-4 col-xl">
            <div class="p-3 rounded-4 border bg-white shadow-sm text-center h-100">
              <span class="text-muted small fw-bold">มูลค่าค่ารักษารวม</span>
              <h3 class="fw-bold text-success mb-0 mt-1">฿{{ number_format($ipdIncTotal, 2) }}</h3>
              <span class="text-muted" style="font-size: 0.72rem;">บาท</span>
            </div>
          </div>
        </div>

        {{-- IPD Rights Table --}}
        <div class="table-responsive rounded-4 border">
          <table class="table table-hover table-opoh align-middle mb-0">
            <thead>
              <tr>
                <th class="text-start" style="min-width: 220px;">สิทธิการรักษา (6 สิทธิมาตรฐาน)</th>
                <th style="min-width: 100px;" class="text-end">ผู้ป่วยใน (AN)</th>
                <th style="min-width: 90px;" class="text-end">วันนอน (LOS)</th>
                <th style="min-width: 120px;" class="text-end bg-danger bg-opacity-10 text-danger">AdjRW รวม</th>
                <th style="min-width: 100px;" class="text-end">CMI เฉลี่ย</th>
                <th style="min-width: 120px;" class="text-end text-secondary">อัตราจ่าย (Base Rate)</th>
                <th style="min-width: 130px;" class="text-end bg-success bg-opacity-10 text-success">ประมาณการชดเชย DRG</th>
                <th style="min-width: 130px;" class="text-end">มูลค่าค่ารักษาจริง</th>
              </tr>
            </thead>
            <tbody>
              @php
                $totEstComp = 0;
              @endphp
              @foreach($rightsConfig as $rKey => $rCfg)
              @php
                $rData = $ipdRightsRows[$rKey] ?? ['ans' => 0, 'los' => 0, 'adjrw' => 0, 'cmi' => 0, 'inc' => 0];
                $estComp = ($rCfg['rate'] > 0) ? ($rData['adjrw'] * $rCfg['rate']) : 0;
                $totEstComp += $estComp;
              @endphp
              <tr class="opoh-right-row"
                  onclick="openRightBreakdownModal('ipd', '{{ $rKey }}')"
                  role="button"
                  title="คลิกเพื่อดูสถิติแยกรายโรงพยาบาล">
                <td>
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $rCfg['bg'] }}; color: {{ $rCfg['color'] }}; font-size: 0.72rem; font-weight: 700;">
                        <i class="fa-solid {{ $rCfg['icon'] }} me-1"></i>{{ $rCfg['badge'] }}
                      </span>
                      <div>
                        <strong class="d-block text-dark small">{{ $rCfg['name'] }}</strong>
                        <span class="text-muted" style="font-size: 0.7rem;">{{ $rCfg['sub'] }}</span>
                      </div>
                    </div>
                    <span class="badge bg-light text-primary border rounded-pill px-2 py-1 opoh-view-pill" style="font-size: 0.7rem;">
                      <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>แยกราย รพ.
                    </span>
                  </div>
                </td>
                <td class="text-end fw-bold {{ $rData['ans'] > 0 ? 'text-dark' : 'text-muted' }}">{{ number_format($rData['ans']) }}</td>
                <td class="text-end {{ $rData['los'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">{{ number_format($rData['los']) }}</td>
                <td class="text-end fw-bolder bg-danger bg-opacity-10 text-danger">{{ number_format($rData['adjrw'], 4) }}</td>
                <td class="text-end font-monospace small {{ $rData['cmi'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">{{ number_format($rData['cmi'], 4) }}</td>
                <td class="text-end text-muted small">
                  @if($rCfg['rate'] > 0)
                    ฿{{ number_format($rCfg['rate']) }}/RW
                  @else
                    ตามจริง
                  @endif
                </td>
                <td class="text-end fw-bold bg-success bg-opacity-10 text-success">
                  @if($estComp > 0)
                    ฿{{ number_format($estComp, 2) }}
                  @else
                    -
                  @endif
                </td>
                <td class="text-end fw-bold text-dark">฿{{ number_format($rData['inc'], 2) }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="tr-opoh-total">
                <td class="text-end fw-bold">ยอดรวมช่วงเวลาที่เลือก (รพช. {{ count($hospitalsList) }} แห่ง)</td>
                <td class="text-end fw-bolder text-dark fs-6">{{ number_format($ipdAnsTotal) }}</td>
                <td class="text-end fw-bolder text-dark">{{ number_format($ipdLosTotal) }}</td>
                <td class="text-end fw-bolder bg-danger bg-opacity-10 text-danger fs-6">{{ number_format($ipdRwTotal, 4) }}</td>
                <td class="text-end fw-bolder text-dark font-monospace">
                  {{ $ipdAnsTotal > 0 ? number_format($ipdRwTotal / $ipdAnsTotal, 4) : '0.0000' }}
                </td>
                <td class="text-end text-muted small">-</td>
                <td class="text-end fw-bolder bg-success bg-opacity-10 text-success">฿{{ number_format($totEstComp, 2) }}</td>
                <td class="text-end fw-bolder text-dark">฿{{ number_format($ipdIncTotal, 2) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

    </div>


    {{-- ==================== 3. TAB SURGERY (ผ่าตัดและหัตถการ) ==================== --}}
    <div class="tab-pane fade {{ $active_tab === 'operation' ? 'show active' : '' }}" id="pills-op" role="tabpanel" aria-labelledby="pills-op-tab">
      
      {{-- 1. ยอดสะสมการผ่าตัดแยกรายสาขาศัลยศาสตร์ --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="p-3 rounded-4" style="background-color: rgba(139, 92, 246, 0.1);">
              <i class="fa-solid fa-hand-holding-medical fs-3" style="color: #8b5cf6;"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge text-white rounded-pill px-2.5 py-1 fw-bold" style="background-color: #8b5cf6;">OPOH • บริการผ่าตัด</span>
                <span class="text-secondary small fw-semibold">สถิติด้านการแพทย์และสาธารณสุข จังหวัดอำนาจเจริญ</span>
              </div>
              <h4 class="fw-bold mb-0 text-dark">
                One Province One Hospital (OPOH) - การผ่าตัดและหัตถการ
              </h4>
              <div class="text-muted small mt-1">
                <i class="fa-solid fa-circle-info me-1" style="color: #8b5cf6;"></i>ยอดสะสม: นับตั้งแต่ ต.ค. 2568 ถึงปัจจุบัน
              </div>
            </div>
          </div>
          {{-- IPD vs OPD Badges --}}
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1.5 fw-bold">
              <i class="fa-solid fa-bed me-1"></i>ผู้ป่วยใน: {{ number_format($opCumSummary->total_ipd ?? 0) }} เคส
            </span>
            <span class="badge bg-purple bg-opacity-10 border rounded-pill px-3 py-1.5 fw-bold" style="color: #8b5cf6; border-color: rgba(139, 92, 246, 0.25) !important;">
              <i class="fa-solid fa-person-walking me-1"></i>ผู้ป่วยนอก: {{ number_format($opCumSummary->total_ods ?? 0) }} เคส
            </span>
          </div>
        </div>

        {{-- 4 Surgical Specialty Cards --}}
        <div class="row g-3 pt-2">
          @foreach($surgicalSpecialties as $code => $spec)
          @php
            $specRow = collect($opSpecialtyRows)->firstWhere('code', $code);
            $opVal = $specRow['cumulative'] ?? 0;
          @endphp
          <div class="col-6 col-md-4 col-xl">
            <div class="opoh-stat-card p-3 h-100 position-relative overflow-hidden shadow-sm">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fw-bold text-secondary text-truncate small" title="{{ $spec['name'] }}">
                  {{ $spec['name'] }}
                </span>
                <i class="fa-solid {{ $spec['icon'] }} fs-4 ms-2" style="color: {{ $spec['color'] }}; opacity: 0.85;"></i>
              </div>
              <div class="d-flex align-items-baseline gap-1 mt-1">
                <h3 class="fw-bolder mb-0 text-dark" style="color: {{ $spec['color'] }} !important;">
                  {{ number_format($opVal) }}
                </h3>
                <span class="text-muted" style="font-size: 0.75rem;">เคส</span>
              </div>
              <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" role="progressbar" style="width: {{ ($opCumSummary->total ?? 0) > 0 ? ($opVal / $opCumSummary->total) * 100 : 0 }}%; background-color: {{ $spec['color'] }};"></div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- 2. แถวกราฟคู่ 7 : 5 (ซ้าย Bar Chart 12 เดือน / ขวา Top 5 การผ่าตัดและหัตถการ) --}}
      <div class="row g-4 mb-4">
        {{-- ด้านซ้าย (7): กราฟแท่งแนวโน้มการผ่าตัด 12 เดือน --}}
        <div class="col-12 col-xl-7">
          <div class="opoh-glass-card p-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-chart-column text-purple" style="color: #8b5cf6;"></i>
                  <span>แนวโน้มการผ่าตัดและหัตถการรายเดือน (ปีงบ {{ $budget_year }})</span>
                </h5>
              </div>
              <span class="badge text-white px-2.5 py-1 rounded-pill small fw-semibold" style="background-color: #8b5cf6;">
                <i class="fa-solid fa-chart-column me-1"></i>รายเดือน
              </span>
            </div>
            <div style="height: 290px; position: relative;">
              <canvas id="opMonthlyChartCanvas"></canvas>
            </div>
          </div>
        </div>

        {{-- ด้านขวา (5): 5 อันดับการผ่าตัดและหัตถการ --}}
        <div class="col-12 col-xl-5">
          <div class="opoh-glass-card p-4 h-100 d-flex flex-column">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-scissors" style="color: #8b5cf6;"></i>
                  <span>5 อันดับการผ่าตัดและหัตถการ</span>
                </h5>
                <span class="text-muted small">ช่วงเวลาที่เลือก</span>
              </div>
              <span class="badge text-white px-2.5 py-1 rounded-pill small fw-semibold" style="background-color: #8b5cf6;">
                5 อันดับ
              </span>
            </div>

            <div class="flex-grow-1 d-flex flex-column justify-content-around">
              @php
                $opTotalProcedureCases = $topOperations->sum('total');
              @endphp

              @forelse($topOperations as $index => $op)
              @php
                $pct = $opTotalProcedureCases > 0 ? round(($op->total / $opTotalProcedureCases) * 100, 1) : 0;
                $rankColors = ['#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#64748b'];
                $curColor = $rankColors[$index] ?? '#8b5cf6';
              @endphp
              <div class="mb-2.5">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 78%;">
                    <span class="top-rank-badge text-white" style="background-color: {{ $curColor }};">{{ $index + 1 }}</span>
                    <strong class="text-dark small font-monospace">{{ $op->icd9 ?: 'ICD-9' }}</strong>
                    <span class="text-muted small text-truncate" title="{{ $op->operation_name }}">{{ $op->operation_name }}</span>
                  </div>
                  <div class="text-end">
                    <strong class="small text-dark">{{ number_format($op->total) }}</strong>
                    <span class="text-muted" style="font-size: 0.72rem;">เคส ({{ $pct }}%)</span>
                  </div>
                </div>
                <div class="progress" style="height: 6px; border-radius: 6px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background-color: {{ $curColor }};"></div>
                </div>
              </div>
              @empty
              <div class="text-center py-4 my-auto">
                <i class="fa-solid fa-syringe fs-1 text-muted opacity-25 mb-2"></i>
                <div class="text-muted small fw-semibold">กำลังรอข้อมูลการผ่าตัดจาก Agent ประจำ รพ.</div>
                <span class="text-secondary" style="font-size: 0.75rem;">ระบบจะแสดง 5 อันดับหัตถการทันทีที่ Agent ส่งรอบถัดไป</span>
              </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      {{-- 3. สถิติการผ่าตัดแยกรายสาขา (Surgery Matrix) --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-hospital-user text-purple" style="color: #8b5cf6;"></i>
              <span>สถิติการผ่าตัดและหัตถการแยกรายสาขา</span>
            </h5>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
              <span class="text-muted small">
                ช่วงวันที่เลือก: <strong class="text-primary">{{ $thaiDateRangeText }}</strong>
              </span>
              <span class="badge text-white px-2.5 py-1 rounded-pill fw-semibold" style="background-color: #8b5cf6;">
                รวมช่วงที่เลือก: <strong>{{ number_format($opSummary->total ?? 0) }}</strong> เคส
              </span>
              <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                ผู้ป่วยใน: <strong>{{ number_format($opSummary->total_ipd ?? 0) }}</strong> เคส
              </span>
              <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                ผู้ป่วยนอก: <strong>{{ number_format($opSummary->total_ods ?? 0) }}</strong> เคส
              </span>
            </div>
          </div>
          <button type="button" onclick="exportTableToExcel('opohOpSpecTable', 'OPOH_ผ่าตัด_แยกรายสาขา_{{ $current_start }}_ถึง_{{ $current_end }}', 'ผ่าตัด')" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm align-self-start" style="font-size: 0.78rem;" title="ส่งออกตารางนี้เป็น Excel (.xlsx)">
            <i class="fa-solid fa-file-excel me-1"></i> Excel
          </button>
        </div>

        {{-- ตารางสถิติ Surgery Matrix --}}
        <div class="table-responsive rounded-4 border">
          <table id="opohOpSpecTable" class="table table-hover table-opoh align-middle mb-0">
            <thead>
              <tr>
                <th class="text-start" style="min-width: 220px;">สาขา</th>
                <th style="min-width: 110px;" class="text-end text-white" style="background-color: #8b5cf6 !important;">ช่วงที่เลือก</th>
                @foreach($hospitalsList as $hcode => $hname)
                <th style="min-width: 110px;" class="text-end">{{ $hname }}</th>
                @endforeach
                <th style="min-width: 120px;" class="text-end bg-light border-start text-secondary">
                  {{ $hubHospitalName }}
                </th>
              </tr>
            </thead>
            <tbody>
              @forelse($opSpecialtyRows as $row)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center" style="background-color: {{ $row['color'] }}; width: 34px; height: 34px;">
                      <i class="fa-solid {{ $row['icon'] }} fs-6"></i>
                    </div>
                    <div>
                      <strong class="d-block text-dark">{{ $row['name'] }}</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">รหัสสาขา: {{ $row['code'] }}</span>
                    </div>
                  </div>
                </td>
                <td class="text-end fw-bold fs-6" style="color: #8b5cf6;">{{ number_format($row['period']) }}</td>
                @foreach($hospitalsList as $hcode => $hname)
                <td class="text-end {{ ($row['hosp_cases'][$hcode] ?? 0) > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                  {{ number_format($row['hosp_cases'][$hcode] ?? 0) }}
                </td>
                @endforeach
                <td class="text-end fw-bold bg-light border-start text-secondary">
                  {{ number_format($row['amnat_cases']) }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="{{ count($hospitalsList) + 3 }}" class="text-center py-4 text-muted">
                  ไม่พบข้อมูลการผ่าตัดในช่วงเวลาที่เลือก
                </td>
              </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="tr-opoh-total">
                <td class="text-end fw-bold">ยอดรวมผ่าตัด OPOH (รพช. {{ count($hospitalsList) }} แห่ง)</td>
                <td class="text-end fw-bolder fs-6" style="color: #8b5cf6;">{{ number_format($opSpecColTotals['period']) }}</td>
                @foreach($hospitalsList as $hcode => $hname)
                <td class="text-end fw-bolder">{{ number_format($opSpecColTotals[$hcode]) }}</td>
                @endforeach
                <td class="text-end fw-bolder bg-light border-start text-secondary">
                  {{ number_format($opSpecColTotals['amnat']) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- 4. สรุปบริการผ่าตัดและหัตถการจำแนกตามสิทธิการรักษา (6 สิทธิมาตรฐาน) --}}
      <div class="opoh-glass-card p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-hand-holding-medical" style="color: #8b5cf6;"></i>
              <span>ข้อมูลการผ่าตัดและหัตถการจำแนกตามสิทธิการรักษา (Surgery)</span>
            </h5>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="text-end">
              <span class="text-muted small d-block">มูลค่าค่าบริการผ่าตัดรวม</span>
              <strong class="text-dark fs-5">฿{{ number_format($opSummary->total_inc ?? 0, 2) }}</strong>
              <span class="text-muted ms-1" style="font-size: 0.75rem;">(รวม {{ number_format($opSummary->total ?? 0) }} เคส)</span>
            </div>
          </div>
        </div>

        <div class="row g-3">
          @foreach($rightsConfig as $rKey => $rCfg)
          @php
            $rData = $opRightsRows[$rKey] ?? ['cases' => 0, 'inc' => 0];
            $rPct = ($opCasesTotal > 0) ? round(($rData['cases'] / $opCasesTotal) * 100, 1) : 0;
          @endphp
          <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="p-3 rounded-4 border h-100 bg-white shadow-sm position-relative opoh-right-card"
                 onclick="openRightBreakdownModal('op', '{{ $rKey }}')"
                 role="button"
                 title="คลิกเพื่อดูสถิติแยกรายโรงพยาบาล">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge rounded-pill px-2 py-0.5" style="background-color: {{ $rCfg['bg'] }}; color: {{ $rCfg['color'] }}; font-size: 0.72rem; font-weight: 700;">
                  <i class="fa-solid {{ $rCfg['icon'] }} me-1"></i>{{ $rCfg['badge'] }}
                </span>
                <span class="text-muted fw-bold" style="font-size: 0.75rem;">{{ $rPct }}%</span>
              </div>
              <div class="text-secondary small fw-bold text-truncate" title="{{ $rCfg['name'] }}">
                {{ $rCfg['name'] }}
              </div>
              <div class="d-flex align-items-baseline gap-1 mt-1">
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($rData['cases']) }}</h4>
                <span class="text-muted" style="font-size: 0.72rem;">เคส</span>
              </div>
              <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted" style="font-size: 0.7rem;">มูลค่าค่าบริการ</span>
                <span class="fw-bold text-dark" style="font-size: 0.78rem;">฿{{ number_format($rData['inc'], 2) }}</span>
              </div>
              <div class="text-end mt-1.5">
                <span class="text-primary opoh-view-badge" style="font-size: 0.68rem; font-weight: 600;">
                  <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>แยกราย รพ.
                </span>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

    </div>

    {{-- ==================== 4. TAB INCOME (หมวดค่ารักษาพยาบาล 16 หมวด x 6 สิทธิ) ==================== --}}
    <div class="tab-pane fade {{ $active_tab === 'income' ? 'show active' : '' }}" id="pills-income" role="tabpanel" aria-labelledby="pills-income-tab">
      
      {{-- 1. ส่วนหัวการเงิน และตัวกรองประเภทบริการ/รพ. --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-success bg-opacity-10 text-success rounded-4">
              <i class="fa-solid fa-file-invoice-dollar fs-3 text-success"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">OPOH • หมวดค่ารักษา 16 หมวด</span>
                <span class="text-secondary small fw-semibold">สถิติด้านการเงินและกองทุนรักษาพยาบาล จังหวัดอำนาจเจริญ</span>
              </div>
              <h4 class="fw-bold mb-0 text-dark">
                รายได้ตามหมวดค่ารักษาพยาบาล ปีงบประมาณ {{ $budget_year }}
              </h4>
            </div>
          </div>

          {{-- ตัวเลือกประเภทบริการ (OPD / IPD / รวมทั้งหมด) และ โรงพยาบาล --}}
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="btn-group btn-group-sm shadow-sm" role="group" aria-label="Income Service Type">
              <a href="{{ route('web.opoh', array_merge(request()->query(), ['active_tab' => 'income', 'income_service_type' => 'ALL'])) }}" class="btn {{ $income_service_type === 'ALL' ? 'btn-success fw-bold text-white' : 'btn-outline-secondary' }}">
                <i class="fa-solid fa-layer-group me-1"></i>รวมทั้งหมด
              </a>
              <a href="{{ route('web.opoh', array_merge(request()->query(), ['active_tab' => 'income', 'income_service_type' => 'OPD'])) }}" class="btn {{ $income_service_type === 'OPD' ? 'btn-primary fw-bold text-white' : 'btn-outline-secondary' }}">
                <i class="fa-solid fa-stethoscope me-1"></i>ผู้ป่วยนอก (OPD)
              </a>
              <a href="{{ route('web.opoh', array_merge(request()->query(), ['active_tab' => 'income', 'income_service_type' => 'IPD'])) }}" class="btn {{ $income_service_type === 'IPD' ? 'btn-danger fw-bold text-white' : 'btn-outline-secondary' }}">
                <i class="fa-solid fa-bed-pulse me-1"></i>ผู้ป่วยใน (IPD)
              </a>
            </div>

            <select class="form-select form-select-sm shadow-sm" id="incomeHospSelect" style="width: 170px;" onchange="location.href='{{ route('web.opoh', array_merge(request()->query(), ['active_tab' => 'income'])) }}&income_hospcode=' + this.value">
              <option value="ALL" {{ $income_hospcode === 'ALL' ? 'selected' : '' }}>รพช. {{ count($hospitalsList) }} แห่ง (ทั้งหมด)</option>
              @foreach($hospitalsList as $hcode => $hname)
                <option value="{{ $hcode }}" {{ $income_hospcode == $hcode ? 'selected' : '' }}>{{ $hname }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- 2. แถวการ์ดสรุปมูลค่าแยก 6 สิทธิมาตรฐาน (UC ในเขต, UC นอกเขต, ข้าราชการ, อปท, ประกันสังคม, อื่นๆ) --}}
        <div class="border-top pt-3 mt-2">
          <div class="d-flex justify-content-between align-items-center mb-2.5">
            <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
              <i class="fa-solid fa-wallet text-success"></i>
              <span>มูลค่าการรักษาจำแนกตาม 6 สิทธิมาตรฐาน (ยอดรวมทั้งปีงบประมาณ {{ $budget_year }})</span>
            </span>
            <div class="text-end">
              <span class="text-muted small">มูลค่ารวมทั้งสิ้น:</span>
              <strong class="text-success fs-5 ms-1">฿{{ number_format($incomeSumsYearly['total'], 2) }}</strong>
            </div>
          </div>

          <div class="row g-2">
            @php
              $rightsDisplay = [
                'ucs_incup'   => ['name' => 'UC ในจังหวัด',   'color' => '#0284c7', 'icon' => 'fa-id-card',           'val' => $incomeSumsYearly['ucs_incup']],
                'ucs_outprov' => ['name' => 'UC ต่างจังหวัด', 'color' => '#06b6d4', 'icon' => 'fa-plane-departure',   'val' => $incomeSumsYearly['ucs_outprov']],
                'ofc'         => ['name' => 'ข้าราชการ (OFC)', 'color' => '#16a34a', 'icon' => 'fa-building-columns',   'val' => $incomeSumsYearly['ofc']],
                'lgo'         => ['name' => 'อปท. (LGO)',     'color' => '#d97706', 'icon' => 'fa-landmark',           'val' => $incomeSumsYearly['lgo']],
                'sss'         => ['name' => 'ประกันสังคม (SSS)', 'color' => '#9333ea', 'icon' => 'fa-shield-halved',   'val' => $incomeSumsYearly['sss']],
                'other'       => ['name' => 'ชำระเงิน/อื่นๆ',  'color' => '#64748b', 'icon' => 'fa-money-bill-wave',    'val' => $incomeSumsYearly['other']],
              ];
            @endphp
            @foreach($rightsDisplay as $rKey => $rConf)
            @php
              $pct = $incomeSumsYearly['total'] > 0 ? ($rConf['val'] / $incomeSumsYearly['total']) * 100 : 0;
            @endphp
            <div class="col-6 col-md-4 col-xl-2">
              <div class="opoh-stat-card p-3 h-100 position-relative overflow-hidden shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="fw-bold text-secondary text-truncate small" title="{{ $rConf['name'] }}">{{ $rConf['name'] }}</span>
                  <i class="fa-solid {{ $rConf['icon'] }} fs-4 ms-2" style="color: {{ $rConf['color'] }}; opacity: 0.85;"></i>
                </div>
                <div class="d-flex align-items-baseline gap-1 mt-1">
                  <h4 class="fw-bolder mb-0" style="color: {{ $rConf['color'] }};">฿{{ number_format($rConf['val'], 2) }}</h4>
                </div>
                <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.72rem;">
                  <span>สัดส่วน</span><span class="fw-bold">{{ number_format($pct, 1) }}%</span>
                </div>
                <div class="progress mt-1" style="height: 4px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background-color: {{ $rConf['color'] }};"></div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- 3. Monthly Trend Line Chart Card (เหมือน RIMS) --}}
      <div class="card border rounded-4 shadow-sm mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
          <div class="d-flex align-items-center gap-2">
            <span class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
              <i class="fa-solid fa-chart-line fs-5"></i>
            </span>
            <div>
              <h6 class="mb-0 fw-bold text-dark">กราฟแสดงแนวโน้มรายเดือนของรายได้ค่ารักษา</h6>
              <span class="text-muted small">เส้นแนวโน้ม 12 เดือน (ต.ค. - ก.ย.) ของปีงบประมาณ {{ $budget_year }}</span>
            </div>
          </div>

          {{-- Category Dropdown Selector --}}
          <div class="d-flex align-items-center gap-2">
            <label for="incomeCategorySelector" class="small fw-semibold text-secondary text-nowrap mb-0">เลือกหมวด:</label>
            <select id="incomeCategorySelector" class="form-select form-select-sm shadow-sm" style="min-width: 280px; max-width: 380px;">
              <option value="all">ทั้งหมด (รายได้รวมทุกหมวด)</option>
              @foreach($incomeYearlyData as $catId => $cat)
                <option value="{{ $catId }}">{{ $cat->group_name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="card-body p-3">
          <div id="incomeMonthlyTrendChart" style="min-height: 290px;"></div>
        </div>
      </div>

      {{-- 4. Month Tabs Navigation (เหมือน RIMS: รวมทั้งปี, ต.ค., พ.ย., ..., ก.ย.) --}}
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden mb-4">
        <div class="card-header bg-light py-2.5 px-3 border-bottom">
          <ul class="nav nav-pills gap-1.5 flex-nowrap overflow-auto" id="incomeMonthTabs" role="tablist" style="padding-bottom: 2px;">
            <li class="nav-item" role="presentation">
              <button class="nav-link active px-3 py-1.5 rounded-pill fw-bold text-nowrap" id="tab-inc-yearly" data-bs-toggle="pill" data-bs-target="#pane-inc-yearly" type="button" role="tab" aria-selected="true" style="font-size: 0.85rem;">
                <i class="fa-solid fa-calendar-check me-1"></i> รวมทั้งปี
              </button>
            </li>
            @foreach($incomeMonthsList as $m)
            <li class="nav-item" role="presentation">
              <button class="nav-link px-3 py-1.5 rounded-pill fw-semibold text-nowrap" id="tab-inc-{{ $m }}" data-bs-toggle="pill" data-bs-target="#pane-inc-{{ $m }}" type="button" role="tab" aria-selected="false" style="font-size: 0.85rem;">
                {{ $incomeMonthsNames[$m] }}
              </button>
            </li>
            @endforeach
          </ul>
        </div>

        {{-- 5. Tab Panes: ตาราง Matrix 16 หมวด x 6 สิทธิ สำหรับแต่ละเดือนและรวมทั้งปี --}}
        <div class="tab-content" id="incomeMonthTabsContent">
          
          {{-- PANE 1: รวมทั้งปี --}}
          <div class="tab-pane fade show active" id="pane-inc-yearly" role="tabpanel" aria-labelledby="tab-inc-yearly">
            <div class="d-flex flex-wrap justify-content-between align-items-center py-2.5 px-3 bg-white border-bottom gap-2">
              <span class="fw-bold text-primary small d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-table-cells fs-6"></i>
                <span>รายได้ตามหมวดค่ารักษา ({{ $income_service_type === 'ALL' ? 'OPD+IPD' : $income_service_type }}) ยอดรวมทั้งปีงบประมาณ {{ $budget_year }}</span>
              </span>
              <button type="button" onclick="exportTableToExcel('incomeTable-yearly', 'รายงาน16หมวดค่ารักษา_{{ $income_service_type }}_{{ $income_hospcode === 'ALL' ? 'รพช_6แห่ง' : ($hospitalsList[$income_hospcode] ?? $income_hospcode) }}_ทั้งปีงบ_{{ $budget_year }}', 'รวมทั้งปี_{{ $budget_year }}')" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" style="font-size: 0.78rem;" title="ส่งออกตารางรวมทั้งปีเป็น Excel">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
              </button>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-opoh align-middle mb-0 w-100" id="incomeTable-yearly" style="font-size: 0.88rem;">
                <thead class="table-light">
                  <tr class="text-center align-middle">
                    <th class="text-start" style="min-width: 260px;">กลุ่มหมวดค่ารักษา (drg_chrgitem)</th>
                    <th style="min-width: 120px;" class="text-end">UC ในจังหวัด</th>
                    <th style="min-width: 120px;" class="text-end">UC ต่างจังหวัด</th>
                    <th style="min-width: 120px;" class="text-end">ข้าราชการ</th>
                    <th style="min-width: 110px;" class="text-end">อปท.</th>
                    <th style="min-width: 120px;" class="text-end">ประกันสังคม</th>
                    <th style="min-width: 110px;" class="text-end">อื่นๆ/ชำระเงิน</th>
                    <th style="min-width: 130px;" class="text-end bg-primary bg-opacity-10 text-primary fw-bold">ยอดรวม (บาท)</th>
                    <th style="min-width: 80px;" class="text-end">สัดส่วน (%)</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $grandTotalYearly = $incomeSumsYearly['total'] > 0 ? $incomeSumsYearly['total'] : 1;
                  @endphp
                  @foreach($incomeYearlyData as $catId => $row)
                  @php
                    $rowPct = ($row->total / $grandTotalYearly) * 100;
                  @endphp
                  <tr class="opoh-right-row" role="button" title="คลิกเพื่อดูข้อมูลแยกรายโรงพยาบาล" onclick="openIncomeHospModal('yearly', {{ $catId }}, 'รวมทั้งปีงบประมาณ {{ $budget_year }}')">
                    <td class="fw-bold text-dark text-start">
                      <div class="d-flex align-items-center justify-content-between gap-2">
                        <span>{{ $row->group_name }}</span>
                        <span class="badge bg-light text-primary border rounded-pill px-2 py-1 opoh-view-pill text-nowrap" style="font-size: 0.7rem;">
                          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>แยกราย รพ.
                        </span>
                      </div>
                    </td>
                    <td class="text-end {{ $row->ucs_incup > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->ucs_incup > 0 ? number_format($row->ucs_incup, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->ucs_outprov > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->ucs_outprov > 0 ? number_format($row->ucs_outprov, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->ofc > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->ofc > 0 ? number_format($row->ofc, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->lgo > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->lgo > 0 ? number_format($row->lgo, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->sss > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->sss > 0 ? number_format($row->sss, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->other > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->other > 0 ? number_format($row->other, 2) : '-' }}
                    </td>
                    <td class="text-end fw-bold text-primary bg-primary bg-opacity-10">
                      {{ number_format($row->total, 2) }}
                    </td>
                    <td class="text-end small text-secondary">
                      {{ number_format($rowPct, 1) }}%
                    </td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr class="fw-bold tr-opoh-total text-end align-middle">
                    <td class="text-center">รวมทั้งหมด</td>
                    <td>{{ number_format($incomeSumsYearly['ucs_incup'], 2) }}</td>
                    <td>{{ number_format($incomeSumsYearly['ucs_outprov'], 2) }}</td>
                    <td>{{ number_format($incomeSumsYearly['ofc'], 2) }}</td>
                    <td>{{ number_format($incomeSumsYearly['lgo'], 2) }}</td>
                    <td>{{ number_format($incomeSumsYearly['sss'], 2) }}</td>
                    <td>{{ number_format($incomeSumsYearly['other'], 2) }}</td>
                    <td class="text-primary fs-6">{{ number_format($incomeSumsYearly['total'], 2) }}</td>
                    <td>100.0%</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          {{-- PANE 2..13: ประจำแต่ละเดือน (ต.ค. 68 .. ก.ย. 69) --}}
          @foreach($incomeMonthsList as $m)
          @php
            $mSums = $incomeSumsMonthly[$m] ?? ['ucs_incup'=>0,'ucs_outprov'=>0,'ofc'=>0,'lgo'=>0,'sss'=>0,'other'=>0,'total'=>0];
            $mGrandTotal = $mSums['total'] > 0 ? $mSums['total'] : 1;
          @endphp
          <div class="tab-pane fade" id="pane-inc-{{ $m }}" role="tabpanel" aria-labelledby="tab-inc-{{ $m }}">
            <div class="d-flex flex-wrap justify-content-between align-items-center py-2.5 px-3 bg-white border-bottom gap-2">
              <span class="fw-bold text-success small d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-table-cells fs-6"></i>
                <span>รายได้ตามหมวดค่ารักษา ({{ $income_service_type === 'ALL' ? 'OPD+IPD' : $income_service_type }}) ประจำเดือน {{ $incomeMonthsNames[$m] }}</span>
              </span>
              <button type="button" onclick="exportTableToExcel('incomeTable-{{ $m }}', 'รายงาน16หมวดค่ารักษา_{{ $income_service_type }}_{{ $income_hospcode === 'ALL' ? 'รพช_6แห่ง' : ($hospitalsList[$income_hospcode] ?? $income_hospcode) }}_เดือน_{{ str_replace(' ', '_', $incomeMonthsNames[$m]) }}', '{{ str_replace(' ', '_', $incomeMonthsNames[$m]) }}')" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" style="font-size: 0.78rem;" title="ส่งออกตารางประจำเดือนเป็น Excel">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
              </button>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-opoh align-middle mb-0 w-100" id="incomeTable-{{ $m }}" style="font-size: 0.88rem;">
                <thead class="table-light">
                  <tr class="text-center align-middle">
                    <th class="text-start" style="min-width: 260px;">กลุ่มหมวดค่ารักษา (drg_chrgitem)</th>
                    <th style="min-width: 120px;" class="text-end">UC ในจังหวัด</th>
                    <th style="min-width: 120px;" class="text-end">UC ต่างจังหวัด</th>
                    <th style="min-width: 120px;" class="text-end">ข้าราชการ</th>
                    <th style="min-width: 110px;" class="text-end">อปท.</th>
                    <th style="min-width: 120px;" class="text-end">ประกันสังคม</th>
                    <th style="min-width: 110px;" class="text-end">อื่นๆ/ชำระเงิน</th>
                    <th style="min-width: 130px;" class="text-end bg-success bg-opacity-10 text-success fw-bold">ยอดรวม (บาท)</th>
                    <th style="min-width: 80px;" class="text-end">สัดส่วน (%)</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($incomeMonthlyData[$m] as $catId => $row)
                  @php
                    $rowPct = ($row->total / $mGrandTotal) * 100;
                  @endphp
                  <tr class="opoh-right-row" role="button" title="คลิกเพื่อดูข้อมูลแยกรายโรงพยาบาล" onclick="openIncomeHospModal({{ $m }}, {{ $catId }}, 'ประจำเดือน {{ $incomeMonthsNames[$m] }}')">
                    <td class="fw-bold text-dark text-start">
                      <div class="d-flex align-items-center justify-content-between gap-2">
                        <span>{{ $row->group_name }}</span>
                        <span class="badge bg-light text-primary border rounded-pill px-2 py-1 opoh-view-pill text-nowrap" style="font-size: 0.7rem;">
                          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>แยกราย รพ.
                        </span>
                      </div>
                    </td>
                    <td class="text-end {{ $row->ucs_incup > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->ucs_incup > 0 ? number_format($row->ucs_incup, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->ucs_outprov > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->ucs_outprov > 0 ? number_format($row->ucs_outprov, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->ofc > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->ofc > 0 ? number_format($row->ofc, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->lgo > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->lgo > 0 ? number_format($row->lgo, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->sss > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->sss > 0 ? number_format($row->sss, 2) : '-' }}
                    </td>
                    <td class="text-end {{ $row->other > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                      {{ $row->other > 0 ? number_format($row->other, 2) : '-' }}
                    </td>
                    <td class="text-end fw-bold text-success bg-success bg-opacity-10">
                      {{ number_format($row->total, 2) }}
                    </td>
                    <td class="text-end small text-secondary">
                      {{ number_format($rowPct, 1) }}%
                    </td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr class="fw-bold tr-opoh-total text-end align-middle">
                    <td class="text-center">รวมประจำเดือน</td>
                    <td>{{ number_format($mSums['ucs_incup'], 2) }}</td>
                    <td>{{ number_format($mSums['ucs_outprov'], 2) }}</td>
                    <td>{{ number_format($mSums['ofc'], 2) }}</td>
                    <td>{{ number_format($mSums['lgo'], 2) }}</td>
                    <td>{{ number_format($mSums['sss'], 2) }}</td>
                    <td>{{ number_format($mSums['other'], 2) }}</td>
                    <td class="text-success fs-6">{{ number_format($mSums['total'], 2) }}</td>
                    <td>100.0%</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          @endforeach

        </div>
      </div>

    </div>

  </div>

</div>

{{-- ==================== MODAL: หมวดค่ารักษา แยกรายโรงพยาบาล ==================== --}}
<div class="modal fade" id="incomeHospModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-2 px-4 pt-4 align-items-start">
        <div>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="badge bg-success rounded-pill px-2.5 py-1 fw-bold">หมวดค่ารักษา</span>
            <span id="incHospPeriod" class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-semibold"></span>
            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-semibold">
              {{ $income_service_type === 'ALL' ? 'OPD+IPD' : $income_service_type }}
            </span>
          </div>
          <h5 class="modal-title fw-bold text-dark mb-0" id="incHospTitle"></h5>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 py-3">
        <div class="table-responsive rounded-4 border">
          <table class="table table-hover table-opoh align-middle mb-0" style="font-size: 0.88rem;">
            <thead>
              <tr>
                <th class="text-start" style="min-width: 160px;">โรงพยาบาล</th>
                <th class="text-end" style="min-width: 110px;">UC ในจังหวัด</th>
                <th class="text-end" style="min-width: 110px;">UC ต่างจังหวัด</th>
                <th class="text-end" style="min-width: 110px;">ข้าราชการ</th>
                <th class="text-end" style="min-width: 100px;">อปท.</th>
                <th class="text-end" style="min-width: 110px;">ประกันสังคม</th>
                <th class="text-end" style="min-width: 100px;">อื่นๆ</th>
                <th class="text-end bg-success bg-opacity-10 text-success" style="min-width: 120px;">ยอดรวม (บาท)</th>
                <th class="text-end" style="min-width: 80px;">สัดส่วน</th>
              </tr>
            </thead>
            <tbody id="incHospBody"></tbody>
            <tfoot id="incHospFoot"></tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ==================== MODAL: ข้อมูลจำแนกรายโรงพยาบาลตามสิทธิการรักษา ==================== --}}
<div class="modal fade" id="rightBreakdownModal" tabindex="-1" aria-labelledby="rightBreakdownModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4">
      
      {{-- Modal Header --}}
      <div class="modal-header border-0 pb-2 px-4 pt-4 align-items-start">
        <div class="d-flex align-items-start gap-3">
          <div id="modalRightIconBox" class="p-3 rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; min-width: 58px; background-color: rgba(2, 132, 199, 0.1); color: #0284c7;">
            <i id="modalRightIcon" class="fa-solid fa-id-card fs-3"></i>
          </div>
          <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1.5">
              <span id="modalRightBadge" class="badge rounded-pill px-2.5 py-1 fw-bold shadow-sm" style="background-color: #0284c7; color: #ffffff; font-size: 0.75rem;">
                สิทธิการรักษา
              </span>
              <span id="modalServiceTypeBadge" class="badge rounded-pill bg-light text-dark border px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                บริการผู้ป่วยนอก (OPD)
              </span>
              <span id="modalRateBadge" class="badge rounded-pill bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1 fw-semibold d-none" style="font-size: 0.75rem;">
                อัตราจ่าย DRG: -
              </span>
            </div>
            <h4 class="modal-title fw-bold text-dark mb-1" id="modalRightTitle">ชื่อสิทธิการรักษา</h4>
            <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
              <span><i class="fa-regular fa-calendar text-primary me-1"></i>ช่วงเวลา: <strong id="modalDateRangeText" class="text-dark">{{ $thaiDateRangeText }}</strong></span>
              <span><i class="fa-solid fa-hospital text-info me-1"></i>ครอบคลุม: <strong class="text-dark">รพช. {{ count($hospitalsList) }} แห่ง + {{ $hubHospitalName }} แม่ข่าย ({{ count($allModalHospitals) }} รพ.)</strong></span>
            </div>
          </div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Modal Body --}}
      <div class="modal-body px-4 py-3">
        
        {{-- Top Summary KPI Cards --}}
        <div class="row g-3 mb-4" id="modalSummaryCardsContainer">
          {{-- Dynamic 3 KPI cards inserted via JS --}}
        </div>

        {{-- Hospital Breakdown Table Card --}}
        <div class="card border rounded-4 overflow-hidden shadow-sm">
          <div class="card-header bg-light py-2.5 px-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-bold text-dark small d-flex align-items-center gap-2">
              <i class="fa-solid fa-table-list text-primary"></i>
              <span>ตารางเปรียบเทียบข้อมูลจำแนกรายโรงพยาบาล (รพช. {{ count($hospitalsList) }} แห่ง และ {{ $hubHospitalName }} แม่ข่าย)</span>
            </span>
            <span class="text-muted small" style="font-size: 0.75rem;">
              <i class="fa-solid fa-circle-info text-info me-1"></i>แสดงข้อมูลสถิติและมูลค่าค่าบริการแยกตามโรงพยาบาล
            </span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-opoh align-middle mb-0">
              <thead id="modalTableHead">
                {{-- Dynamic columns --}}
              </thead>
              <tbody id="modalTableBody">
                {{-- Dynamic rows --}}
              </tbody>
              <tfoot id="modalTableFoot">
                {{-- Dynamic footer totals --}}
              </tfoot>
            </table>
          </div>
        </div>

      </div>

      {{-- Modal Footer --}}
      <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex justify-content-between align-items-center">
        <span class="text-muted small">
          <i class="fa-solid fa-shield-halved text-success me-1"></i>ระบบข้อมูลบริการสุขภาพ One Province One Hospital (OPOH) เครือข่าย {{ $hubHospitalName }}
        </span>
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark me-1"></i>ปิดหน้าต่าง
        </button>
      </div>

    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/th.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/flatpickr-th-buddhist.js') }}"></script>
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendor/xlsx.full.min.js') }}"></script>
<script>
  $(document).ready(function() {
    // 1. กำหนด Flatpickr ปฏิทิน พ.ศ. (Thai Buddhist Calendar)
    const startPicker = initThaiDatePicker('#start_date', {
      defaultDate: '{{ $current_start }}'
    });

    const endPicker = initThaiDatePicker('#end_date', {
      defaultDate: '{{ $current_end }}'
    });

    // เมื่อสลับแท็บ ให้จำแท็บที่เลือกไว้ใน hidden input #active_tab
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
      const target = $(e.target).attr('data-bs-target');
      if (target === '#pills-opd') {
        $('#active_tab').val('opd');
        initOpdChart();
      } else if (target === '#pills-ipd') {
        $('#active_tab').val('ipd');
        initIpdChart();
      } else if (target === '#pills-op') {
        $('#active_tab').val('operation');
        initOpChart();
      } else if (target === '#pills-income') {
        $('#active_tab').val('income');
        initIncomeChart();
      }
    });

    // 2. เมื่อเปลี่ยนปีงบประมาณ ให้ส่งฟอร์มเพื่อแสดงปีงบประมาณนั้น
    $('#budget_year_select').on('change', function() {
      $('#filter_type').val('fiscal');
      $('#opohFilterForm').submit();
    });

    // 3. ปุ่มช่วงเวลารวดเร็ว (สัปดาห์นี้, เดือนนี้, ทั้งปีงบ)
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
        $('#opohFilterForm').submit();
      }
    });

    // เมื่อพิมพ์หรือเลือกวันที่เอง ให้ตั้ง filter_type เป็น custom
    $('#start_date, #end_date').on('change', function() {
      $('#filter_type').val('custom');
    });

    // 4. กราฟแท่งแนวโน้มรายเดือน (Monthly Trend Charts 3 แท็บ)
    const opdChartData = @json($opdMonthlyChart);
    const ipdChartData = @json($ipdMonthlyChart);
    const opChartData  = @json($opMonthlyChart);

    let opdChart = null;
    let ipdChart = null;
    let opChart  = null;

    function initOpdChart() {
      const ctx = document.getElementById('opdMonthlyChartCanvas');
      if (!ctx || opdChart) return;
      opdChart = new Chart(ctx, {
        data: opdChartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                boxWidth: 12,
                font: { family: 'Noto Sans Thai, sans-serif', size: 12 }
              }
            },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.9)',
              titleFont: { family: 'Noto Sans Thai, sans-serif' },
              bodyFont: { family: 'Noto Sans Thai, sans-serif' },
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            x: {
              stacked: true,
              grid: { display: false },
              ticks: { font: { family: 'Noto Sans Thai, sans-serif' } }
            },
            y: {
              stacked: true,
              beginAtZero: true,
              grid: { color: 'rgba(0,0,0,0.06)' },
              ticks: { font: { family: 'Noto Sans Thai, sans-serif' } }
            }
          }
        }
      });
    }

    function initIpdChart() {
      const ctx = document.getElementById('ipdMonthlyChartCanvas');
      if (!ctx || ipdChart) return;
      ipdChart = new Chart(ctx, {
        data: ipdChartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                boxWidth: 12,
                font: { family: 'Noto Sans Thai, sans-serif', size: 12 }
              }
            },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.9)',
              titleFont: { family: 'Noto Sans Thai, sans-serif' },
              bodyFont: { family: 'Noto Sans Thai, sans-serif' },
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            x: {
              stacked: true,
              grid: { display: false },
              ticks: { font: { family: 'Noto Sans Thai, sans-serif' } }
            },
            y: {
              stacked: true,
              beginAtZero: true,
              grid: { color: 'rgba(0,0,0,0.06)' },
              ticks: { font: { family: 'Noto Sans Thai, sans-serif' } }
            }
          }
        }
      });
    }

    function initOpChart() {
      const ctx = document.getElementById('opMonthlyChartCanvas');
      if (!ctx || opChart) return;
      opChart = new Chart(ctx, {
        data: opChartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                boxWidth: 12,
                font: { family: 'Noto Sans Thai, sans-serif', size: 12 }
              }
            },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.9)',
              titleFont: { family: 'Noto Sans Thai, sans-serif' },
              bodyFont: { family: 'Noto Sans Thai, sans-serif' },
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            x: {
              stacked: true,
              grid: { display: false },
              ticks: { font: { family: 'Noto Sans Thai, sans-serif' } }
            },
            y: {
              stacked: true,
              beginAtZero: true,
              grid: { color: 'rgba(0,0,0,0.06)' },
              ticks: { font: { family: 'Noto Sans Thai, sans-serif' } }
            }
          }
        }
      });
    }

    // ==================== กราฟแนวโน้มรายได้ 16 หมวด (ApexCharts) ====================
    let incomeChartInstance = null;
    const incomeChartData = @json($incomeChartData);
    const incomeMonthsLabels = @json(array_values($incomeMonthsNames));
    const masterCatsNames = @json($masterCategories);

    function initIncomeChart() {
      const chartEl = document.querySelector("#incomeMonthlyTrendChart");
      if (!chartEl) return;
      if (incomeChartInstance) {
        incomeChartInstance.destroy();
      }

      const initialCatId = 'all';
      const initialCatName = 'ทั้งหมด (รายได้รวมทุกหมวด)';

      const chartOptions = {
        series: [{
          name: 'ยอดรวม (บาท)',
          data: incomeChartData[initialCatId] || []
        }],
        chart: {
          height: 310,
          type: 'area',
          toolbar: { show: false },
          fontFamily: 'Inter, Noto Sans Thai, sans-serif'
        },
        colors: ['#0284c7'],
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0.05,
            stops: [0, 90, 100]
          }
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return Number(val).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          },
          style: {
            fontSize: '11px',
            fontWeight: 600,
            colors: ['#0369a1']
          },
          background: {
            enabled: true,
            foreColor: '#ffffff',
            borderRadius: 4,
            padding: 4,
            opacity: 0.9,
            borderWidth: 1,
            borderColor: '#38bdf8'
          }
        },
        yaxis: {
          labels: {
            formatter: function (val) {
              return Number(val).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
          }
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return '฿' + Number(val).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
          }
        },
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 5, strokeWidth: 2, strokeColors: '#ffffff', hover: { size: 7 } },
        xaxis: {
          categories: incomeMonthsLabels,
          labels: {
            style: { fontWeight: 600 }
          }
        },
        title: {
          text: initialCatName,
          align: 'left',
          style: {
            fontSize: '14px',
            fontWeight: 'bold',
            color: '#334155'
          }
        }
      };

      incomeChartInstance = new ApexCharts(chartEl, chartOptions);
      incomeChartInstance.render();

      $('#incomeCategorySelector').off('change').on('change', function() {
        const selectedCatId = this.value;
        const selectedCatName = selectedCatId === 'all' 
          ? 'ทั้งหมด (รายได้รวมทุกหมวด)' 
          : (masterCatsNames[selectedCatId] || ('หมวด ' + selectedCatId));

        incomeChartInstance.updateOptions({
          title: { text: selectedCatName }
        });

        incomeChartInstance.updateSeries([{
          name: 'ยอดรวม (บาท)',
          data: incomeChartData[selectedCatId] || []
        }]);
      });
    }

    // Export Table to Excel
    window.exportTableToExcel = function(tableId, filename, sheetName) {
      if (typeof XLSX === 'undefined') {
        alert('กรุณารอไลบรารี XLSX โหลดสักครู่');
        return;
      }
      const tableSelect = document.getElementById(tableId);
      if (!tableSelect) return;
      let cleanSheet = (sheetName || 'Sheet1').replace(/[:\\/?*\[\]]/g, '').substring(0, 31);
      const wb = XLSX.utils.table_to_book(tableSelect, { sheet: cleanSheet });
      let cleanFilename = (filename ? filename : 'opoh_income_data').replace(/[\/\\:\*\?"<>\|]+/g, '_');
      const fname = cleanFilename + '.xlsx';
      XLSX.writeFile(wb, fname);
    };

    // เริ่มต้นแสดงกราฟตามแท็บที่ active อยู่
    const currentTab = '{{ $active_tab }}';
    if (currentTab === 'ipd') {
      initIpdChart();
    } else if (currentTab === 'operation') {
      initOpChart();
    } else if (currentTab === 'income') {
      initIncomeChart();
    } else {
      initOpdChart();
    }
  });

  // ==================== 4. Modal จำแนกข้อมูลตามสิทธิการรักษารายโรงพยาบาล ====================
  const rightsBreakdownData = {
    opd: @json($opdRightsByHosp),
    ipd: @json($ipdRightsByHosp),
    op: @json($opRightsByHosp),
    hospitals: @json($allModalHospitals),
    hubCode: @json($hubHospitalCode),
    hubName: @json($hubHospitalName),
    commCount: {{ count($hospitalsList) }},
    allHospCount: {{ count($allModalHospitals) }},
    configs: @json($rightsConfig),
    dateRange: @json($thaiDateRangeText)
  };

  function formatModalNumber(num, decimals = 0) {
    if (num === null || num === undefined || isNaN(num)) return decimals > 0 ? (0).toFixed(decimals) : '0';
    return Number(num).toLocaleString('th-TH', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  // ==================== Modal หมวดค่ารักษา แยกรายโรงพยาบาล ====================
  const incomeHospData = {
    byHosp: @json($incomeByHosp),
    hospitals: @json($allModalHospitals),
    hubCode: @json($hubHospitalCode),
    categories: @json(collect($incomeYearlyData)->map(fn($c) => $c->group_name)),
    selectedHosp: @json($income_hospcode)
  };

  window.openIncomeHospModal = function(period, catId, periodLabel) {
    const byCat = (incomeHospData.byHosp[period] || {})[catId] || {};
    const hospitals = incomeHospData.hospitals;
    const codes = Object.keys(hospitals).filter(function(c) {
      return c in byCat || (c !== incomeHospData.hubCode && (incomeHospData.selectedHosp === 'ALL' || incomeHospData.selectedHosp === c));
    });

    document.getElementById('incHospTitle').textContent = incomeHospData.categories[catId] || ('หมวด ' + catId);
    document.getElementById('incHospPeriod').textContent = periodLabel;

    const sum = [0, 0, 0, 0, 0, 0, 0];
    codes.forEach(function(c) {
      const v = byCat[c] || [0, 0, 0, 0, 0, 0, 0];
      v.forEach(function(x, i) { sum[i] += x; });
    });

    let html = '';
    codes.forEach(function(c) {
      const v = byCat[c] || [0, 0, 0, 0, 0, 0, 0];
      const pct = sum[6] > 0 ? (v[6] / sum[6] * 100).toFixed(1) : '0.0';
      html += '<tr><td class="text-start"><strong class="text-dark">' + hospitals[c].name + '</strong>' +
              '<span class="text-muted small ms-1">(' + c + ')</span></td>';
      for (let i = 0; i < 6; i++) {
        html += '<td class="text-end ' + (v[i] > 0 ? 'text-dark' : 'text-muted opacity-50') + '">' +
                (v[i] > 0 ? formatModalNumber(v[i], 2) : '-') + '</td>';
      }
      html += '<td class="text-end fw-bold text-success bg-success bg-opacity-10">' + formatModalNumber(v[6], 2) + '</td>' +
              '<td class="text-end small text-secondary">' + pct + '%</td></tr>';
    });
    if (!codes.length) {
      html = '<tr><td colspan="9" class="text-center text-muted py-4">ไม่พบข้อมูล</td></tr>';
    }
    document.getElementById('incHospBody').innerHTML = html;

    let foot = '<tr class="tr-opoh-total text-end"><td class="text-center">รวม</td>';
    for (let i = 0; i < 7; i++) {
      foot += '<td>' + formatModalNumber(sum[i], 2) + '</td>';
    }
    foot += '<td>100.0%</td></tr>';
    document.getElementById('incHospFoot').innerHTML = foot;

    const el = document.getElementById('incomeHospModal');
    (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show();
  };

  window.openRightBreakdownModal = function(serviceType, schemeKey) {
    const cfg = rightsBreakdownData.configs[schemeKey];
    if (!cfg) return;

    const dataMap = (rightsBreakdownData[serviceType] && rightsBreakdownData[serviceType][schemeKey]) ? rightsBreakdownData[serviceType][schemeKey] : {};
    const hospitals = rightsBreakdownData.hospitals;
    const hubCode = rightsBreakdownData.hubCode;
    const hubName = rightsBreakdownData.hubName;
    const commCount = rightsBreakdownData.commCount;
    const allHospCount = rightsBreakdownData.allHospCount;

    // 1. ตั้งค่าหัวข้อ Modal
    const iconBox = document.getElementById('modalRightIconBox');
    if (iconBox) {
      iconBox.style.backgroundColor = cfg.bg;
      iconBox.style.color = cfg.color;
    }
    const iconEl = document.getElementById('modalRightIcon');
    if (iconEl) {
      iconEl.className = 'fa-solid ' + cfg.icon + ' fs-3';
    }
    const badgeEl = document.getElementById('modalRightBadge');
    if (badgeEl) {
      badgeEl.style.backgroundColor = cfg.color;
      badgeEl.innerHTML = '<i class="fa-solid ' + cfg.icon + ' me-1"></i>' + cfg.badge;
    }
    const titleEl = document.getElementById('modalRightTitle');
    if (titleEl) {
      titleEl.textContent = cfg.name + ' (' + cfg.sub + ')';
    }

    // Badge ประเภทบริการ
    const sTypeBadge = document.getElementById('modalServiceTypeBadge');
    if (sTypeBadge) {
      if (serviceType === 'opd') {
        sTypeBadge.className = 'badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 fw-semibold';
        sTypeBadge.innerHTML = '<i class="fa-solid fa-hospital-user me-1"></i>บริการผู้ป่วยนอก (OPD)';
      } else if (serviceType === 'ipd') {
        sTypeBadge.className = 'badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 fw-semibold';
        sTypeBadge.innerHTML = '<i class="fa-solid fa-bed-pulse me-1"></i>บริการผู้ป่วยใน (IPD DRG Clearing)';
      } else {
        sTypeBadge.className = 'badge rounded-pill border px-2.5 py-1 fw-semibold';
        sTypeBadge.style.backgroundColor = 'rgba(139, 92, 246, 0.1)';
        sTypeBadge.style.color = '#8b5cf6';
        sTypeBadge.style.borderColor = 'rgba(139, 92, 246, 0.25)';
        sTypeBadge.innerHTML = '<i class="fa-solid fa-hand-holding-medical me-1"></i>บริการผ่าตัดและหัตถการ (Surgery)';
      }
    }

    // Rate Badge (สำหรับ IPD)
    const rateBadge = document.getElementById('modalRateBadge');
    if (rateBadge) {
      if (serviceType === 'ipd') {
        rateBadge.classList.remove('d-none');
        if (cfg.rate > 0) {
          rateBadge.innerHTML = '<i class="fa-solid fa-scale-balanced me-1"></i>Base Rate DRG: ฿' + formatModalNumber(cfg.rate) + '/RW';
        } else {
          rateBadge.innerHTML = '<i class="fa-solid fa-scale-balanced me-1"></i>Base Rate DRG: ชดเชยตามจริง';
        }
      } else {
        rateBadge.classList.add('d-none');
      }
    }

    // 2. คำนวณยอดรวม (รพช. 6 แห่ง, รพ.อำนาจเจริญ, รวมทั้งจังหวัด)
    let commVisits = 0, commOpdInc = 0;
    let amnatVisits = 0, amnatOpdInc = 0;
    let totVisits = 0, totOpdInc = 0;

    let commAns = 0, commLos = 0, commAdjrw = 0, commEstComp = 0, commIpdInc = 0;
    let amnatAns = 0, amnatLos = 0, amnatAdjrw = 0, amnatEstComp = 0, amnatIpdInc = 0;
    let totAns = 0, totLos = 0, totAdjrw = 0, totEstComp = 0, totIpdInc = 0;

    let commCases = 0, commOpInc = 0;
    let amnatCases = 0, amnatOpInc = 0;
    let totCases = 0, totOpInc = 0;

    Object.keys(hospitals).forEach(function(code) {
      const hInfo = hospitals[code];
      const row = dataMap[code] || {};
      const isHub = (code === hubCode);

      if (serviceType === 'opd') {
        const v = Number(row.visits || 0);
        const inc = Number(row.inc || 0);
        if (isHub) {
          amnatVisits += v;
          amnatOpdInc += inc;
        } else {
          commVisits += v;
          commOpdInc += inc;
        }
        totVisits += v;
        totOpdInc += inc;
      } else if (serviceType === 'ipd') {
        const ans = Number(row.ans || 0);
        const los = Number(row.los || 0);
        const rw = Number(row.adjrw || 0);
        const est = Number(row.est_comp || 0);
        const inc = Number(row.inc || 0);
        if (isHub) {
          amnatAns += ans;
          amnatLos += los;
          amnatAdjrw += rw;
          amnatEstComp += est;
          amnatIpdInc += inc;
        } else {
          commAns += ans;
          commLos += los;
          commAdjrw += rw;
          commEstComp += est;
          commIpdInc += inc;
        }
        totAns += ans;
        totLos += los;
        totAdjrw += rw;
        totEstComp += est;
        totIpdInc += inc;
      } else {
        const c = Number(row.cases || 0);
        const inc = Number(row.inc || 0);
        if (isHub) {
          amnatCases += c;
          amnatOpInc += inc;
        } else {
          commCases += c;
          commOpInc += inc;
        }
        totCases += c;
        totOpInc += inc;
      }
    });

    // 3. Render Top 3 Summary Cards
    const summaryContainer = document.getElementById('modalSummaryCardsContainer');
    if (summaryContainer) {
      let kpiHtml = '';
      if (serviceType === 'opd') {
        const commPct = totVisits > 0 ? ((commVisits / totVisits) * 100).toFixed(1) : 0;
        const amnatPct = totVisits > 0 ? ((amnatVisits / totVisits) * 100).toFixed(1) : 0;
        const commAvg = commVisits > 0 ? (commOpdInc / commVisits) : 0;
        const amnatAvg = amnatVisits > 0 ? (amnatOpdInc / amnatVisits) : 0;
        const totAvg = totVisits > 0 ? (totOpdInc / totVisits) : 0;

        kpiHtml = `
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-info bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold">ยอดรวม รพช. ${commCount} แห่ง</span>
                <span class="badge bg-info text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">${commPct}%</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-dark">${formatModalNumber(commVisits)}</h3>
                <span class="text-muted small">ครั้ง</span>
              </div>
              <div class="mt-2 pt-2 border-top border-info border-opacity-25 d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                <span>มูลค่าค่ารักษา: <strong class="text-dark">฿${formatModalNumber(commOpdInc, 2)}</strong></span>
                <span>เฉลี่ย: ฿${formatModalNumber(commAvg, 2)}</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-primary bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold">${hubName} (แม่ข่าย)</span>
                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">${amnatPct}%</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-dark">${formatModalNumber(amnatVisits)}</h3>
                <span class="text-muted small">ครั้ง</span>
              </div>
              <div class="mt-2 pt-2 border-top border-primary border-opacity-25 d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                <span>มูลค่าค่ารักษา: <strong class="text-dark">฿${formatModalNumber(amnatOpdInc, 2)}</strong></span>
                <span>เฉลี่ย: ฿${formatModalNumber(amnatAvg, 2)}</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-success bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold">ยอดรวมทั้งจังหวัด (${allHospCount} รพ.)</span>
                <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">100%</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-success">${formatModalNumber(totVisits)}</h3>
                <span class="text-muted small">ครั้ง</span>
              </div>
              <div class="mt-2 pt-2 border-top border-success border-opacity-25 d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                <span>มูลค่าค่ารักษารวม: <strong class="text-success">฿${formatModalNumber(totOpdInc, 2)}</strong></span>
                <span>เฉลี่ย: ฿${formatModalNumber(totAvg, 2)}</span>
              </div>
            </div>
          </div>
        `;
      } else if (serviceType === 'ipd') {
        const commAlos = commAns > 0 ? (commLos / commAns).toFixed(1) : '0.0';
        const amnatAlos = amnatAns > 0 ? (amnatLos / amnatAns).toFixed(1) : '0.0';
        const totAlos = totAns > 0 ? (totLos / totAns).toFixed(1) : '0.0';
        const commCmi = commAns > 0 ? (commAdjrw / commAns).toFixed(4) : '0.0000';
        const amnatCmi = amnatAns > 0 ? (amnatAdjrw / amnatAns).toFixed(4) : '0.0000';
        const totCmi = totAns > 0 ? (totAdjrw / totAns).toFixed(4) : '0.0000';

        kpiHtml = `
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-info bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-secondary small fw-bold">ยอดรวม รพช. ${commCount} แห่ง</span>
                <span class="badge bg-info text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">ALOS: ${commAlos} วัน</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-dark">${formatModalNumber(commAns)}</h3>
                <span class="text-muted small">AN (${formatModalNumber(commLos)} วันนอน)</span>
              </div>
              <div class="mt-2 pt-2 border-top border-info border-opacity-25" style="font-size: 0.76rem;">
                <div class="d-flex justify-content-between mb-1">
                  <span>AdjRW รวม: <strong class="text-danger">${formatModalNumber(commAdjrw, 4)}</strong></span>
                  <span>CMI เฉลี่ย: <strong class="text-dark">${commCmi}</strong></span>
                </div>
                <div class="d-flex justify-content-between text-muted">
                  <span>ชดเชย DRG: <strong class="text-success">฿${formatModalNumber(commEstComp, 2)}</strong></span>
                  <span>รักษาจริง: ฿${formatModalNumber(commIpdInc, 2)}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-primary bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-secondary small fw-bold">${hubName} (แม่ข่าย)</span>
                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">ALOS: ${amnatAlos} วัน</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-dark">${formatModalNumber(amnatAns)}</h3>
                <span class="text-muted small">AN (${formatModalNumber(amnatLos)} วันนอน)</span>
              </div>
              <div class="mt-2 pt-2 border-top border-primary border-opacity-25" style="font-size: 0.76rem;">
                <div class="d-flex justify-content-between mb-1">
                  <span>AdjRW รวม: <strong class="text-danger">${formatModalNumber(amnatAdjrw, 4)}</strong></span>
                  <span>CMI เฉลี่ย: <strong class="text-dark">${amnatCmi}</strong></span>
                </div>
                <div class="d-flex justify-content-between text-muted">
                  <span>ชดเชย DRG: <strong class="text-success">฿${formatModalNumber(amnatEstComp, 2)}</strong></span>
                  <span>รักษาจริง: ฿${formatModalNumber(amnatIpdInc, 2)}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-success bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-secondary small fw-bold">ยอดรวมทั้งจังหวัด (${allHospCount} รพ.)</span>
                <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">ALOS: ${totAlos} วัน</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-success">${formatModalNumber(totAns)}</h3>
                <span class="text-muted small">AN รวม (${formatModalNumber(totLos)} วันนอน)</span>
              </div>
              <div class="mt-2 pt-2 border-top border-success border-opacity-25" style="font-size: 0.76rem;">
                <div class="d-flex justify-content-between mb-1">
                  <span>AdjRW รวม: <strong class="text-danger">${formatModalNumber(totAdjrw, 4)}</strong></span>
                  <span>CMI รวม: <strong class="text-dark">${totCmi}</strong></span>
                </div>
                <div class="d-flex justify-content-between text-muted">
                  <span>ชดเชย DRG รวม: <strong class="text-success">฿${formatModalNumber(totEstComp, 2)}</strong></span>
                  <span>รักษาจริงรวม: ฿${formatModalNumber(totIpdInc, 2)}</span>
                </div>
              </div>
            </div>
          </div>
        `;
      } else {
        const commPct = totCases > 0 ? ((commCases / totCases) * 100).toFixed(1) : 0;
        const amnatPct = totCases > 0 ? ((amnatCases / totCases) * 100).toFixed(1) : 0;
        const commAvg = commCases > 0 ? (commOpInc / commCases) : 0;
        const amnatAvg = amnatCases > 0 ? (amnatOpInc / amnatCases) : 0;
        const totAvg = totCases > 0 ? (totOpInc / totCases) : 0;

        kpiHtml = `
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-info bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold">ยอดรวม รพช. ${commCount} แห่ง</span>
                <span class="badge bg-info text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">${commPct}%</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-dark">${formatModalNumber(commCases)}</h3>
                <span class="text-muted small">เคส</span>
              </div>
              <div class="mt-2 pt-2 border-top border-info border-opacity-25 d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                <span>มูลค่าค่าบริการ: <strong class="text-dark">฿${formatModalNumber(commOpInc, 2)}</strong></span>
                <span>เฉลี่ย: ฿${formatModalNumber(commAvg, 2)}</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-primary bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold">${hubName} (แม่ข่าย)</span>
                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">${amnatPct}%</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-dark">${formatModalNumber(amnatCases)}</h3>
                <span class="text-muted small">เคส</span>
              </div>
              <div class="mt-2 pt-2 border-top border-primary border-opacity-25 d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                <span>มูลค่าค่าบริการ: <strong class="text-dark">฿${formatModalNumber(amnatOpInc, 2)}</strong></span>
                <span>เฉลี่ย: ฿${formatModalNumber(amnatAvg, 2)}</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-4 border bg-success bg-opacity-10 h-100 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold">ยอดรวมทั้งจังหวัด (${allHospCount} รพ.)</span>
                <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">100%</span>
              </div>
              <div class="d-flex align-items-baseline gap-1">
                <h3 class="fw-bold mb-0 text-success">${formatModalNumber(totCases)}</h3>
                <span class="text-muted small">เคส</span>
              </div>
              <div class="mt-2 pt-2 border-top border-success border-opacity-25 d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                <span>มูลค่าค่าบริการรวม: <strong class="text-success">฿${formatModalNumber(totOpInc, 2)}</strong></span>
                <span>เฉลี่ย: ฿${formatModalNumber(totAvg, 2)}</span>
              </div>
            </div>
          </div>
        `;
      }
      summaryContainer.innerHTML = kpiHtml;
    }

    // 4. Render Table Header
    const tHead = document.getElementById('modalTableHead');
    if (tHead) {
      if (serviceType === 'opd') {
        tHead.innerHTML = `
          <tr>
            <th style="width: 45px;">#</th>
            <th class="text-start" style="min-width: 200px;">รหัส & โรงพยาบาล</th>
            <th style="width: 100px;">ประเภท</th>
            <th class="text-end" style="min-width: 130px;">จำนวนผู้รับบริการ (ครั้ง)</th>
            <th class="text-end" style="min-width: 90px;">สัดส่วน (%)</th>
            <th class="text-end" style="min-width: 140px;">มูลค่าค่ารักษา (บาท)</th>
            <th class="text-end" style="min-width: 130px;">เฉลี่ยต่อครั้ง (บาท)</th>
          </tr>
        `;
      } else if (serviceType === 'ipd') {
        tHead.innerHTML = `
          <tr>
            <th style="width: 40px;">#</th>
            <th class="text-start" style="min-width: 180px;">รหัส & โรงพยาบาล</th>
            <th style="width: 90px;">ประเภท</th>
            <th class="text-end" style="min-width: 100px;">ผู้ป่วยใน (AN)</th>
            <th class="text-end" style="min-width: 85px;">วันนอน (LOS)</th>
            <th class="text-end" style="min-width: 90px;">วันนอนเฉลี่ย</th>
            <th class="text-end bg-danger bg-opacity-10 text-danger" style="min-width: 110px;">AdjRW รวม</th>
            <th class="text-end" style="min-width: 95px;">CMI เฉลี่ย</th>
            <th class="text-end bg-success bg-opacity-10 text-success" style="min-width: 140px;">ประมาณการชดเชย DRG</th>
            <th class="text-end" style="min-width: 130px;">มูลค่าค่ารักษาจริง</th>
          </tr>
        `;
      } else {
        tHead.innerHTML = `
          <tr>
            <th style="width: 45px;">#</th>
            <th class="text-start" style="min-width: 200px;">รหัส & โรงพยาบาล</th>
            <th style="width: 100px;">ประเภท</th>
            <th class="text-end" style="min-width: 130px;">จำนวนเคสผ่าตัด (เคส)</th>
            <th class="text-end" style="min-width: 90px;">สัดส่วน (%)</th>
            <th class="text-end" style="min-width: 140px;">มูลค่าค่าบริการ (บาท)</th>
            <th class="text-end" style="min-width: 130px;">เฉลี่ยต่อเคส (บาท)</th>
          </tr>
        `;
      }
    }

    // 5. Render Table Body (7 Hospitals)
    const tBody = document.getElementById('modalTableBody');
    if (tBody) {
      let bodyHtml = '';
      let idx = 1;
      Object.keys(hospitals).forEach(function(code) {
        const hInfo = hospitals[code];
        const row = dataMap[code] || {};
        const isHub = (code === hubCode);
        const badgeClass = isHub ? 'bg-primary text-primary' : 'bg-info text-info';

        if (serviceType === 'opd') {
          const v = Number(row.visits || 0);
          const inc = Number(row.inc || 0);
          const pct = totVisits > 0 ? ((v / totVisits) * 100).toFixed(1) : 0;
          const avg = v > 0 ? (inc / v) : 0;

          bodyHtml += `
            <tr class="${isHub ? 'table-light' : ''}">
              <td class="text-center text-muted small">${idx++}</td>
              <td class="text-start">
                <strong class="text-dark">${hInfo.name}</strong>
                <span class="text-muted small ms-1">(${code})</span>
              </td>
              <td class="text-center">
                <span class="badge ${badgeClass} bg-opacity-10 px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">${hInfo.type}</span>
              </td>
              <td class="text-end fw-bold ${v > 0 ? 'text-dark' : 'text-muted'}">${formatModalNumber(v)}</td>
              <td class="text-end text-muted small">${pct}%</td>
              <td class="text-end fw-bold text-dark">฿${formatModalNumber(inc, 2)}</td>
              <td class="text-end text-muted small">฿${formatModalNumber(avg, 2)}</td>
            </tr>
          `;
        } else if (serviceType === 'ipd') {
          const ans = Number(row.ans || 0);
          const los = Number(row.los || 0);
          const alos = ans > 0 ? (los / ans).toFixed(1) : '0.0';
          const rw = Number(row.adjrw || 0);
          const cmi = ans > 0 ? (rw / ans).toFixed(4) : '0.0000';
          const est = Number(row.est_comp || 0);
          const inc = Number(row.inc || 0);

          bodyHtml += `
            <tr class="${isHub ? 'table-light' : ''}">
              <td class="text-center text-muted small">${idx++}</td>
              <td class="text-start">
                <strong class="text-dark">${hInfo.name}</strong>
                <span class="text-muted small ms-1">(${code})</span>
              </td>
              <td class="text-center">
                <span class="badge ${badgeClass} bg-opacity-10 px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">${hInfo.type}</span>
              </td>
              <td class="text-end fw-bold ${ans > 0 ? 'text-dark' : 'text-muted'}">${formatModalNumber(ans)}</td>
              <td class="text-end ${los > 0 ? 'fw-bold text-dark' : 'text-muted'}">${formatModalNumber(los)}</td>
              <td class="text-end text-muted small font-monospace">${alos}</td>
              <td class="text-end fw-bolder bg-danger bg-opacity-10 text-danger font-monospace">${formatModalNumber(rw, 4)}</td>
              <td class="text-end font-monospace small ${ans > 0 ? 'fw-bold text-dark' : 'text-muted'}">${cmi}</td>
              <td class="text-end fw-bold bg-success bg-opacity-10 text-success">${est > 0 ? '฿' + formatModalNumber(est, 2) : '-'}</td>
              <td class="text-end fw-bold text-dark">฿${formatModalNumber(inc, 2)}</td>
            </tr>
          `;
        } else {
          const c = Number(row.cases || 0);
          const inc = Number(row.inc || 0);
          const pct = totCases > 0 ? ((c / totCases) * 100).toFixed(1) : 0;
          const avg = c > 0 ? (inc / c) : 0;

          bodyHtml += `
            <tr class="${isHub ? 'table-light' : ''}">
              <td class="text-center text-muted small">${idx++}</td>
              <td class="text-start">
                <strong class="text-dark">${hInfo.name}</strong>
                <span class="text-muted small ms-1">(${code})</span>
              </td>
              <td class="text-center">
                <span class="badge ${badgeClass} bg-opacity-10 px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">${hInfo.type}</span>
              </td>
              <td class="text-end fw-bold ${c > 0 ? 'text-dark' : 'text-muted'}">${formatModalNumber(c)}</td>
              <td class="text-end text-muted small">${pct}%</td>
              <td class="text-end fw-bold text-dark">฿${formatModalNumber(inc, 2)}</td>
              <td class="text-end text-muted small">฿${formatModalNumber(avg, 2)}</td>
            </tr>
          `;
        }
      });
      tBody.innerHTML = bodyHtml;
    }

    // 6. Render Table Footer (Subtotal รพช. 6 แห่ง, รพ.อำนาจเจริญ แม่ข่าย, Grand Total)
    const tFoot = document.getElementById('modalTableFoot');
    if (tFoot) {
      let footHtml = '';
      if (serviceType === 'opd') {
        const commPct = totVisits > 0 ? ((commVisits / totVisits) * 100).toFixed(1) : 0;
        const amnatPct = totVisits > 0 ? ((amnatVisits / totVisits) * 100).toFixed(1) : 0;
        const commAvg = commVisits > 0 ? (commOpdInc / commVisits) : 0;
        const amnatAvg = amnatVisits > 0 ? (amnatOpdInc / amnatVisits) : 0;
        const totAvg = totVisits > 0 ? (totOpdInc / totVisits) : 0;

        footHtml = `
          <tr class="tr-modal-community-total">
            <td colspan="3" class="text-end fw-bold">ยอดรวม รพช. ${commCount} แห่ง</td>
            <td class="text-end fw-bolder fs-6">${formatModalNumber(commVisits)}</td>
            <td class="text-end fw-bold">${commPct}%</td>
            <td class="text-end fw-bolder">฿${formatModalNumber(commOpdInc, 2)}</td>
            <td class="text-end fw-bold">฿${formatModalNumber(commAvg, 2)}</td>
          </tr>
          <tr class="tr-modal-amnat-total">
            <td colspan="3" class="text-end fw-bold">${hubName} (แม่ข่าย)</td>
            <td class="text-end fw-bolder fs-6">${formatModalNumber(amnatVisits)}</td>
            <td class="text-end fw-bold">${amnatPct}%</td>
            <td class="text-end fw-bolder">฿${formatModalNumber(amnatOpdInc, 2)}</td>
            <td class="text-end fw-bold">฿${formatModalNumber(amnatAvg, 2)}</td>
          </tr>
          <tr class="tr-modal-grand-total">
            <td colspan="3" class="text-end fw-bolder fs-6">ยอดรวมทั้งจังหวัด (${allHospCount} รพ.)</td>
            <td class="text-end fw-bolder fs-6 text-success">${formatModalNumber(totVisits)}</td>
            <td class="text-end fw-bolder text-success">100%</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(totOpdInc, 2)}</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(totAvg, 2)}</td>
          </tr>
        `;
      } else if (serviceType === 'ipd') {
        const commAlos = commAns > 0 ? (commLos / commAns).toFixed(1) : '0.0';
        const amnatAlos = amnatAns > 0 ? (amnatLos / amnatAns).toFixed(1) : '0.0';
        const totAlos = totAns > 0 ? (totLos / totAns).toFixed(1) : '0.0';
        const commCmi = commAns > 0 ? (commAdjrw / commAns).toFixed(4) : '0.0000';
        const amnatCmi = amnatAns > 0 ? (amnatAdjrw / amnatAns).toFixed(4) : '0.0000';
        const totCmi = totAns > 0 ? (totAdjrw / totAns).toFixed(4) : '0.0000';

        footHtml = `
          <tr class="tr-modal-community-total">
            <td colspan="3" class="text-end fw-bold">ยอดรวม รพช. ${commCount} แห่ง</td>
            <td class="text-end fw-bolder fs-6">${formatModalNumber(commAns)}</td>
            <td class="text-end fw-bold">${formatModalNumber(commLos)}</td>
            <td class="text-end font-monospace small">${commAlos}</td>
            <td class="text-end fw-bolder text-danger font-monospace fs-6">${formatModalNumber(commAdjrw, 4)}</td>
            <td class="text-end font-monospace small fw-bold">${commCmi}</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(commEstComp, 2)}</td>
            <td class="text-end fw-bolder">฿${formatModalNumber(commIpdInc, 2)}</td>
          </tr>
          <tr class="tr-modal-amnat-total">
            <td colspan="3" class="text-end fw-bold">${hubName} (แม่ข่าย)</td>
            <td class="text-end fw-bolder fs-6">${formatModalNumber(amnatAns)}</td>
            <td class="text-end fw-bold">${formatModalNumber(amnatLos)}</td>
            <td class="text-end font-monospace small">${amnatAlos}</td>
            <td class="text-end fw-bolder text-danger font-monospace fs-6">${formatModalNumber(amnatAdjrw, 4)}</td>
            <td class="text-end font-monospace small fw-bold">${amnatCmi}</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(amnatEstComp, 2)}</td>
            <td class="text-end fw-bolder">฿${formatModalNumber(amnatIpdInc, 2)}</td>
          </tr>
          <tr class="tr-modal-grand-total">
            <td colspan="3" class="text-end fw-bolder fs-6">ยอดรวมทั้งจังหวัด (${allHospCount} รพ.)</td>
            <td class="text-end fw-bolder fs-6 text-success">${formatModalNumber(totAns)}</td>
            <td class="text-end fw-bolder text-success">${formatModalNumber(totLos)}</td>
            <td class="text-end font-monospace small fw-bold">${totAlos}</td>
            <td class="text-end fw-bolder text-danger font-monospace fs-6">${formatModalNumber(totAdjrw, 4)}</td>
            <td class="text-end font-monospace small fw-bolder text-dark">${totCmi}</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(totEstComp, 2)}</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(totIpdInc, 2)}</td>
          </tr>
        `;
      } else {
        const commPct = totCases > 0 ? ((commCases / totCases) * 100).toFixed(1) : 0;
        const amnatPct = totCases > 0 ? ((amnatCases / totCases) * 100).toFixed(1) : 0;
        const commAvg = commCases > 0 ? (commOpInc / commCases) : 0;
        const amnatAvg = amnatCases > 0 ? (amnatOpInc / amnatCases) : 0;
        const totAvg = totCases > 0 ? (totOpInc / totCases) : 0;

        footHtml = `
          <tr class="tr-modal-community-total">
            <td colspan="3" class="text-end fw-bold">ยอดรวม รพช. ${commCount} แห่ง</td>
            <td class="text-end fw-bolder fs-6">${formatModalNumber(commCases)}</td>
            <td class="text-end fw-bold">${commPct}%</td>
            <td class="text-end fw-bolder">฿${formatModalNumber(commOpInc, 2)}</td>
            <td class="text-end fw-bold">฿${formatModalNumber(commAvg, 2)}</td>
          </tr>
          <tr class="tr-modal-amnat-total">
            <td colspan="3" class="text-end fw-bold">${hubName} (แม่ข่าย)</td>
            <td class="text-end fw-bolder fs-6">${formatModalNumber(amnatCases)}</td>
            <td class="text-end fw-bold">${amnatPct}%</td>
            <td class="text-end fw-bolder">฿${formatModalNumber(amnatOpInc, 2)}</td>
            <td class="text-end fw-bold">฿${formatModalNumber(amnatAvg, 2)}</td>
          </tr>
          <tr class="tr-modal-grand-total">
            <td colspan="3" class="text-end fw-bolder fs-6">ยอดรวมทั้งจังหวัด (${allHospCount} รพ.)</td>
            <td class="text-end fw-bolder fs-6 text-success">${formatModalNumber(totCases)}</td>
            <td class="text-end fw-bolder text-success">100%</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(totOpInc, 2)}</td>
            <td class="text-end fw-bolder text-success">฿${formatModalNumber(totAvg, 2)}</td>
          </tr>
        `;
      }
      tFoot.innerHTML = footHtml;
    }

    // 7. เปิด Modal
    const modalEl = document.getElementById('rightBreakdownModal');
    if (modalEl) {
      const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      bsModal.show();
    }
  };
</script>
@endpush
