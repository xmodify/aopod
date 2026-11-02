@extends('layouts.app')

@section('title', 'ยอดสะสมผู้รับบริการแยกรายสาขา | AOPOD')

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
</style>

<div class="container-fluid px-3 px-xl-5 py-4">

  {{-- Nav Pills สลับดูข้อมูล: ผู้ป่วยนอก (OPD) / ผู้ป่วยใน (IPD) --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <ul class="nav nav-pills gap-2" id="opohServiceTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active px-4 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2" id="pills-opd-tab" data-bs-toggle="pill" data-bs-target="#pills-opd" type="button" role="tab" aria-controls="pills-opd" aria-selected="true">
          <i class="fa-solid fa-stethoscope"></i>
          <span>ผู้ป่วยนอก (OPD)</span>
          <span class="badge bg-primary bg-opacity-25 text-white rounded-pill ms-1" style="font-size: 0.75rem;">{{ number_format($sumCumulative) }} ครั้ง</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2" id="pills-ipd-tab" data-bs-toggle="pill" data-bs-target="#pills-ipd" type="button" role="tab" aria-controls="pills-ipd" aria-selected="false">
          <i class="fa-solid fa-bed-pulse"></i>
          <span>ผู้ป่วยใน (IPD)</span>
          <span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.75rem;">{{ number_format($ipdSumCumulative) }} ราย</span>
        </button>
      </li>
    </ul>
  </div>

  <div class="tab-content" id="opohServiceTabsContent">
    {{-- ==================== 1. TAB OPD (ผู้ป่วยนอก) ==================== --}}
    <div class="tab-pane fade show active" id="pills-opd" role="tabpanel" aria-labelledby="pills-opd-tab">
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
              <p class="text-muted small mb-0 mt-1">
                ยอดสะสมผู้รับบริการแยกรายสาขา - สถิติการหมุนเวียนแพทย์เฉพาะทางจากโรงพยาบาลอำนาจเจริญ ออกให้บริการ ณ โรงพยาบาลชุมชน
              </p>
            </div>
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
                <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $spec['color'] }}15; color: {{ $spec['color'] }}; font-weight: 700; font-size: 0.75rem;">
                  {{ $code }}
                </span>
                <i class="fa-solid {{ $spec['icon'] }} fs-4" style="color: {{ $spec['color'] }}; opacity: 0.85;"></i>
              </div>
              <div class="fw-bold text-secondary text-truncate small" title="{{ $spec['name'] }}">
                {{ $spec['name'] }}
              </div>
              <div class="d-flex align-items-baseline gap-1 mt-1">
                <h3 class="fw-bolder mb-0 text-dark" style="color: {{ $spec['color'] }} !important;">
                  {{ number_format($val) }}
                </h3>
                <span class="text-muted" style="font-size: 0.75rem;">ราย</span>
              </div>
              <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $sumCumulative > 0 ? ($val / $sumCumulative) * 100 : 0 }}%; background-color: {{ $spec['color'] }};"></div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- 2. สถิติการรับบริการแพทย์เฉพาะทางรายโรงพยาบาลชุมชน (OPD) --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="row align-items-start g-3 mb-3">
          {{-- Header Left: Title, Date text, Total Badge --}}
          <div class="col-12 col-xl-5">
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-hospital text-primary"></i>
              <span>สถิติการรับบริการแพทย์เฉพาะทางรายโรงพยาบาลชุมชน (OPD)</span>
            </h5>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
              <span class="text-muted small">
                ช่วงวันที่เลือก: <strong class="text-primary">{{ $thaiDateRangeText }}</strong>
              </span>
              <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                รวมทั้งจังหวัด: <strong class="fs-6">{{ number_format($sumCumulative) }}</strong> ครั้ง
              </span>
            </div>
          </div>

          {{-- Header Right: Filter Controls (ปีงบ วันที่ ค้นหา ช่วงเวลารวดเร็ว) --}}
          <div class="col-12 col-xl-7">
            <form method="GET" action="{{ route('web.opoh') }}" id="opohFilterForm" class="d-flex flex-column align-items-xl-end gap-2">
              <input type="hidden" name="filter_type" id="filter_type" value="{{ $filter_type }}">

              <div class="d-flex flex-wrap align-items-center gap-2 justify-content-xl-end">
                <div style="min-width: 140px;">
                  <select name="budget_year" id="budget_year_select" class="form-select form-select-sm shadow-sm" title="ปีงบประมาณ">
                    @foreach($budget_year_select as $by)
                      <option value="{{ $by->LEAVE_YEAR_ID }}" {{ $budget_year == $by->LEAVE_YEAR_ID ? 'selected' : '' }}>
                        {{ $by->LEAVE_YEAR_NAME }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="input-group input-group-sm shadow-sm" style="width: 135px;">
                  <span class="input-group-text bg-white text-primary px-2"><i class="fa-regular fa-calendar"></i></span>
                  <input type="text" id="start_date" name="start_date" class="form-control form-control-sm bg-white px-2" value="{{ $current_start }}" placeholder="วว/ดด/ปปปป" title="วันที่เริ่มต้น">
                </div>

                <span class="text-secondary small fw-bold">ถึง</span>

                <div class="input-group input-group-sm shadow-sm" style="width: 135px;">
                  <span class="input-group-text bg-white text-primary px-2"><i class="fa-regular fa-calendar"></i></span>
                  <input type="text" id="end_date" name="end_date" class="form-control form-control-sm bg-white px-2" value="{{ $current_end }}" placeholder="วว/ดด/ปปปป" title="วันที่สิ้นสุด">
                </div>

                <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-1.5 py-1.5">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <span>ค้นหา</span>
                </button>

                <a href="{{ route('web.opoh', ['budget_year' => $budget_year, 'filter_type' => 'week']) }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1.5 rounded-3" title="คืนค่าเริ่มต้น">
                  <i class="fa-solid fa-rotate-left"></i>
                </a>
              </div>

              <div class="d-flex flex-wrap align-items-center gap-1.5 justify-content-xl-end">
                <span class="small text-muted me-1"><i class="fa-solid fa-bolt text-warning me-1"></i>ช่วงเวลารวดเร็ว:</span>
                <button type="button" class="btn btn-sm btn-outline-primary btn-quick-range {{ $filter_type === 'week' ? 'active' : '' }}" data-range="week">
                  สัปดาห์นี้
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary btn-quick-range {{ $filter_type === 'month' ? 'active' : '' }}" data-range="month">
                  เดือนนี้
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary btn-quick-range {{ $filter_type === 'fiscal' ? 'active' : '' }}" data-range="fiscal">
                  ทั้งปีงบประมาณ {{ $budget_year }}
                </button>
              </div>
            </form>
          </div>
        </div>

        {{-- ตารางสถิติรายโรงพยาบาลชุมชน (OPD) --}}
        <div class="table-responsive rounded-4 border">
          <table class="table table-hover table-opoh align-middle mb-0">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th class="text-start" style="min-width: 200px;">หน่วยบริการ (รพช. / รพท.)</th>
                <th style="min-width: 120px;" class="text-end">สะสมในอดีต (ราย)</th>
                <th style="min-width: 140px;" class="text-end bg-primary bg-opacity-10 text-primary">ช่วงที่เลือก (ราย)</th>
                <th style="min-width: 130px;" class="text-end">สะสมรวม (ราย)</th>
                @foreach($specialties as $code => $spec)
                <th style="min-width: 90px;" class="text-end" title="{{ $spec['name'] }}">
                  {{ $spec['name'] }}
                </th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @forelse($hospitalRows as $index => $row)
              <tr>
                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                <td>
                  <div class="fw-bold text-dark">{{ $row['name'] }}</div>
                  <span class="text-muted small">รหัส: {{ $row['hospcode'] }}</span>
                </td>
                <td class="text-end fw-semibold text-secondary">{{ number_format($row['past_visits']) }}</td>
                <td class="text-end fw-bold text-primary bg-primary bg-opacity-10">{{ number_format($row['current_visits']) }}</td>
                <td class="text-end fw-bolder text-success fs-6">{{ number_format($row['total_visits']) }}</td>
                @foreach($specialties as $code => $spec)
                  @php
                    $spVisits = $row['specialties'][$code] ?? 0;
                  @endphp
                  <td class="text-end {{ $spVisits > 0 ? 'fw-bold text-dark' : 'text-muted opacity-50' }}">
                    {{ $spVisits > 0 ? number_format($spVisits) : '-' }}
                  </td>
                @endforeach
              </tr>
              @empty
              <tr>
                <td colspan="12" class="text-center py-4 text-muted">
                  <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                  ยังไม่มีข้อมูลการให้บริการในโรงพยาบาลชุมชน ในช่วงเวลานี้
                </td>
              </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="tr-opoh-total">
                <td colspan="2" class="text-end fw-bold">ยอดรวมทั้งหมด</td>
                <td class="text-end fw-bolder">{{ number_format($sumPast) }}</td>
                <td class="text-end fw-bolder bg-primary bg-opacity-10 text-primary fs-6">{{ number_format($sumCurrent) }}</td>
                <td class="text-end fw-bolder text-success fs-5">{{ number_format($sumCumulative) }}</td>
                @foreach($specialties as $code => $spec)
                  @php
                    $colSum = 0;
                    foreach($hospitalRows as $r) {
                      $colSum += $r['specialties'][$code] ?? 0;
                    }
                  @endphp
                  <td class="text-end fw-bold">{{ number_format($colSum) }}</td>
                @endforeach
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- 3. สรุปสิทธิการรักษาและมูลค่าบริการ (OPD) --}}
      <div class="row g-4">
        {{-- กองทุนสิทธิการรักษา 4 กองทุนหลัก --}}
        <div class="col-12 col-lg-6">
          <div class="opoh-glass-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
              <i class="fa-solid fa-id-card text-success"></i>
              <span>สิทธิการรักษา 4 กองทุนหลัก (ช่วงเวลาที่เลือก)</span>
            </h5>

            <div class="row g-3">
              <div class="col-6 col-sm-3">
                <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 text-center">
                  <div class="fw-bold text-success mb-1">บัตรทอง</div>
                  <h4 class="fw-bold text-success mb-0 mt-1">{{ number_format($summary->visit_ucs ?? 0) }}</h4>
                  <span class="text-muted" style="font-size: 0.75rem;">ครั้ง</span>
                </div>
              </div>
              <div class="col-6 col-sm-3">
                <div class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 text-center">
                  <div class="fw-bold text-primary mb-1">ข้าราชการ</div>
                  <h4 class="fw-bold text-primary mb-0 mt-1">{{ number_format($summary->visit_ofc ?? 0) }}</h4>
                  <span class="text-muted" style="font-size: 0.75rem;">ครั้ง</span>
                </div>
              </div>
              <div class="col-6 col-sm-3">
                <div class="p-3 rounded-4 bg-warning bg-opacity-10 border border-warning border-opacity-25 text-center">
                  <div class="fw-bold text-warning text-dark mb-1">อปท.</div>
                  <h4 class="fw-bold text-warning text-dark mb-0 mt-1">{{ number_format($summary->visit_lgo ?? 0) }}</h4>
                  <span class="text-muted" style="font-size: 0.75rem;">ครั้ง</span>
                </div>
              </div>
              <div class="col-6 col-sm-3">
                <div class="p-3 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-25 text-center">
                  <div class="fw-bold text-info mb-1">ประกันสังคม</div>
                  <h4 class="fw-bold text-info mb-0 mt-1">{{ number_format($summary->visit_sss ?? 0) }}</h4>
                  <span class="text-muted" style="font-size: 0.75rem;">ครั้ง</span>
                </div>
              </div>
            </div>

            <div class="mt-3 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
              <span class="small text-secondary">สิทธิอื่นๆ / พรบ. / ชำระเงินเอง</span>
              <span class="fw-bold text-dark">{{ number_format($summary->visit_other ?? 0) }} ครั้ง</span>
            </div>
          </div>
        </div>

        {{-- สรุปมูลค่าบริการและผลลัพธ์การตรวจ --}}
        <div class="col-12 col-lg-6">
          <div class="opoh-glass-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
              <i class="fa-solid fa-coins text-warning"></i>
              <span>สรุปมูลค่าบริการและผลลัพธ์การตรวจ</span>
            </h5>

            <div class="row g-3">
              <div class="col-sm-4">
                <div class="p-3 rounded-3 bg-light border text-center">
                  <span class="text-muted small">ค่าบริการรวมทั้งหมด</span>
                  <h5 class="fw-bold text-dark mb-0 mt-1">฿{{ number_format($summary->inc_total ?? 0, 2) }}</h5>
                  <span class="text-secondary" style="font-size: 0.75rem;">สะสม: ฿{{ number_format($fiscalSummary->inc_total ?? 0, 2) }}</span>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-3 rounded-3 bg-light border text-center">
                  <span class="text-muted small">ค่ายารวม</span>
                  <h5 class="fw-bold text-primary mb-0 mt-1">฿{{ number_format($summary->inc_drug_total ?? 0, 2) }}</h5>
                  <span class="text-secondary" style="font-size: 0.75rem;">สะสม: ฿{{ number_format($fiscalSummary->inc_drug_total ?? 0, 2) }}</span>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-3 rounded-3 bg-light border text-center">
                  <span class="text-muted small">ค่าแล็บรวม</span>
                  <h5 class="fw-bold text-info mb-0 mt-1">฿{{ number_format($summary->inc_lab_total ?? 0, 2) }}</h5>
                  <span class="text-secondary" style="font-size: 0.75rem;">สะสม: ฿{{ number_format($fiscalSummary->inc_lab_total ?? 0, 2) }}</span>
                </div>
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-6">
                <div class="p-3 rounded-3 border border-danger bg-danger bg-opacity-10 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="small fw-bold text-danger">ส่งต่อกลับ รพ.อำนาจเจริญ</span>
                    <div class="text-muted" style="font-size: 0.75rem;">ส่งกลับแม่ข่าย (10703)</div>
                  </div>
                  <h4 class="fw-bold text-danger mb-0">{{ number_format($summary->refer_to_origin ?? 0) }}</h4>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 rounded-3 border border-primary bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="small fw-bold text-primary">รับไว้นอนโรงพยาบาล</span>
                    <div class="text-muted" style="font-size: 0.75rem;">ผู้ป่วยใน รพช.</div>
                  </div>
                  <h4 class="fw-bold text-primary mb-0">{{ number_format($summary->admit_ipd ?? 0) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ==================== 2. TAB IPD (ผู้ป่วยใน) ==================== --}}
    <div class="tab-pane fade" id="pills-ipd" role="tabpanel" aria-labelledby="pills-ipd-tab">
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
              <p class="text-muted small mb-0 mt-1">
                ยอดสะสมผู้ป่วยใน (Admit) แยกรายสาขา - สถิติการดูแลผู้ป่วยในและการทำหัตถการ/ผ่าตัด ณ โรงพยาบาลชุมชน
              </p>
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
                <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $spec['color'] }}15; color: {{ $spec['color'] }}; font-weight: 700; font-size: 0.75rem;">
                  {{ $code }}
                </span>
                <i class="fa-solid {{ $spec['icon'] }} fs-4" style="color: {{ $spec['color'] }}; opacity: 0.85;"></i>
              </div>
              <div class="fw-bold text-secondary text-truncate small" title="{{ $spec['name'] }}">
                {{ $spec['name'] }}
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

      {{-- 2. สถิติการรับบริการผู้ป่วยในแพทย์เฉพาะทางรายโรงพยาบาลชุมชน (IPD) --}}
      <div class="opoh-glass-card p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
              <i class="fa-solid fa-hospital-user text-danger"></i>
              <span>สถิติการรับบริการผู้ป่วยในแพทย์เฉพาะทางรายโรงพยาบาลชุมชน (IPD)</span>
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
        </div>

        {{-- ตารางสถิติผู้ป่วยในรายโรงพยาบาลชุมชน (IPD) --}}
        <div class="table-responsive rounded-4 border">
          <table class="table table-hover table-opoh align-middle mb-0">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th class="text-start" style="min-width: 180px;">หน่วยบริการ (รพช. / รพท.)</th>
                <th style="min-width: 110px;" class="text-end">สะสมในอดีต (ราย)</th>
                <th style="min-width: 120px;" class="text-end bg-danger bg-opacity-10 text-danger">ช่วงที่เลือก (ราย)</th>
                <th style="min-width: 110px;" class="text-end">สะสมรวม (ราย)</th>
                @foreach($specialties as $code => $spec)
                <th style="min-width: 80px;" class="text-end" title="{{ $spec['name'] }}">
                  {{ $spec['name'] }}
                </th>
                @endforeach
                <th style="min-width: 100px;" class="text-end text-info">วันนอนรวม (วัน)</th>
                <th style="min-width: 90px;" class="text-end text-primary">AdjRW รวม</th>
                <th style="min-width: 130px;" class="text-end text-success">ค่ารักษารวม (บาท)</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ipdHospitalRows as $index => $row)
              <tr>
                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                <td>
                  <div class="fw-bold text-dark">{{ $row['name'] }}</div>
                  <span class="text-muted small">รหัส: {{ $row['hospcode'] }}</span>
                </td>
                <td class="text-end fw-semibold text-secondary">{{ number_format($row['past_ans']) }}</td>
                <td class="text-end fw-bold text-danger bg-danger bg-opacity-10">{{ number_format($row['current_ans']) }}</td>
                <td class="text-end fw-bolder text-dark fs-6">{{ number_format($row['total_ans']) }}</td>
                @foreach($specialties as $code => $spec)
                  @php
                    $spAns = $row['specialties'][$code] ?? 0;
                  @endphp
                  <td class="text-end {{ $spAns > 0 ? 'fw-bold text-dark' : 'text-muted opacity-50' }}">
                    {{ $spAns > 0 ? number_format($spAns) : '-' }}
                  </td>
                @endforeach
                <td class="text-end fw-semibold text-info">{{ $row['total_los'] > 0 ? number_format($row['total_los']) : '-' }}</td>
                <td class="text-end fw-semibold text-primary">{{ $row['total_rw'] > 0 ? number_format($row['total_rw'], 4) : '-' }}</td>
                <td class="text-end fw-bold text-success">{{ $row['total_inc'] > 0 ? number_format($row['total_inc'], 2) : '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="15" class="text-center py-4 text-muted">
                  <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                  ยังไม่มีข้อมูลผู้ป่วยใน (IPD) ในโรงพยาบาลชุมชน ในช่วงเวลานี้
                </td>
              </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="tr-opoh-total">
                <td colspan="2" class="text-end fw-bold">ยอดรวมทั้งหมด</td>
                <td class="text-end fw-bolder">{{ number_format($ipdSumPast) }}</td>
                <td class="text-end fw-bolder bg-danger bg-opacity-10 text-danger fs-6">{{ number_format($ipdSumCurrent) }}</td>
                <td class="text-end fw-bolder text-dark fs-5">{{ number_format($ipdSumCumulative) }}</td>
                @foreach($specialties as $code => $spec)
                  @php
                    $colIpdSum = 0;
                    foreach($ipdHospitalRows as $r) {
                      $colIpdSum += $r['specialties'][$code] ?? 0;
                    }
                  @endphp
                  <td class="text-end fw-bold">{{ number_format($colIpdSum) }}</td>
                @endforeach
                <td class="text-end fw-bold text-info">{{ number_format($ipdSumLos) }}</td>
                <td class="text-end fw-bold text-primary">{{ number_format($ipdSumRw, 4) }}</td>
                <td class="text-end fw-bold text-success">฿{{ number_format($ipdSumInc, 2) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- 3. สรุปภาพรวมและตัวชี้วัดสำคัญของ IPD --}}
      <div class="row g-4">
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="opoh-glass-card p-3 text-center h-100">
            <span class="text-muted small fw-bold">ยอดผู้ป่วยในสะสม (ANs)</span>
            <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($ipdSumCumulative) }}</h3>
            <span class="text-muted" style="font-size: 0.75rem;">ราย</span>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="opoh-glass-card p-3 text-center h-100">
            <span class="text-muted small fw-bold">วันนอนสะสมรวม (LOS)</span>
            <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($ipdSumLos) }}</h3>
            <span class="text-muted" style="font-size: 0.75rem;">วัน</span>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="opoh-glass-card p-3 text-center h-100">
            <span class="text-muted small fw-bold">ค่าน้ำหนักสัมพัทธ์ (AdjRW รวม)</span>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($ipdSumRw, 4) }}</h3>
            <span class="text-muted" style="font-size: 0.75rem;">AdjRW</span>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="opoh-glass-card p-3 text-center h-100">
            <span class="text-muted small fw-bold">มูลค่าค่ารักษาพยาบาลรวม</span>
            <h3 class="fw-bold text-success mb-0 mt-1">฿{{ number_format($ipdSumInc, 2) }}</h3>
            <span class="text-muted" style="font-size: 0.75rem;">บาท</span>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/th.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/flatpickr-th-buddhist.js') }}"></script>
<script>
  $(document).ready(function() {
    // 1. กำหนด Flatpickr ปฏิทิน พ.ศ. (Thai Buddhist Calendar)
    const startPicker = initThaiDatePicker('#start_date', {
      defaultDate: '{{ $current_start }}'
    });

    const endPicker = initThaiDatePicker('#end_date', {
      defaultDate: '{{ $current_end }}'
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
  });
</script>
@endpush
