@extends('layouts.app')

@section('title', 'Dashboard Refer | AOPOD')

<style>
  .card-hospital {
  border: none;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease-in-out;
  }
  /* เพิ่มสีม่วงสำหรับข้อความ */
  .text-purple {
    color: #8b5cf6 !important;
  }
  
  /* === Table Styling สวยๆ === */
  .custom-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: 1px solid #e0e0e0 !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
  }
  .custom-table thead th {
    font-weight: 700 !important;
    text-align: center !important;
    vertical-align: middle !important;
    border: 1px solid #eee !important;
    padding: 10px 8px !important;
    font-size: 0.85rem !important;
  }
  .custom-table tbody td {
    border: 1px solid #f5f5f5 !important;
    padding: 8px !important;
    font-size: 0.85rem !important;
    color: #444 !important;
  }
  .custom-table tbody tr:hover {
    background-color: #f8fbff !important;
  }
  
  /* Header Color Groups */
  .th-blue { background: #e3f2fd !important; color: #0d47a1 !important; }
  .th-green { background: #e8f5e9 !important; color: #1b5e20 !important; }
  .th-orange { background: #fff3e0 !important; color: #e65100 !important; }
  .th-purple { background: #f3e5f5 !important; color: #4a148c !important; }
  .th-red { background: #ffebee !important; color: #b71c1c !important; }
  .th-grey { background: #f5f5f5 !important; color: #424242 !important; }
  
  .tr-total {
    background: #f1f8ff !important;
    font-weight: 800 !important;
    border-top: 2px solid #0d47a1 !important;
  }
</style>
<style>
  /* === BASE GLASS CARD (ใช้ร่วมกันทุก block) === */
  .glass-card {
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow: 0 8px 20px rgba(0,0,0,0.18);
    color: #fff;
    min-height: 110px;
    padding: 12px 14px !important;
    border-radius: 16px;
    position: relative;
  }
  /* === ICON มุมขวาบน (ใช้ร่วมทุก block) === */
  .glass-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,0.20);
    border: 1px solid rgba(255,255,255,0.26);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 10px;
    right: 10px;
  }
  .glass-icon i {
    color: #ffffff;
    font-size: 1.05rem;
  }
  /* หัวข้อ */
  .glass-title {
    font-weight: bold;
    font-size: .9rem;
    margin-bottom: 2px;
  }
  /* ตัวเลข */
  .glass-number {
    font-size: 1.75rem;
    font-weight: bold;
    margin-top: 6px;
    text-align: center;
  }

  .card-hover {
    transition: all 0.3s ease-in-out;
  }
  .card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }

  .card-theme-admit {
    background: linear-gradient(135deg, #ffebee 0%, #ffffff 100%);
    border: 1px solid #e53935 !important;
    border-left: 6px solid #e53935 !important;
  }
  .card-theme-bed {
    background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
    border: 1px solid #1976d2 !important;
    border-left: 6px solid #1976d2 !important;
  }
</style>
<style>
  /* ================================
    1) Operation – Green
  =================================*/
  .card-theme-op {
    background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
    border: 1px solid #2e7d32 !important;
    border-left: 6px solid #2e7d32 !important;
  }

  /* ================================
    2) Refer Out – Magenta
  =================================*/
  .card-theme-referout {
    background: linear-gradient(135deg, #fce4ec 0%, #ffffff 100%);
    border-left: 6px solid #c2185b !important;
  }

  /* ================================
    3) Refer In – Pink/Red
  =================================*/
  .card-theme-referin {
    background: linear-gradient(135deg, #ffebee 0%, #ffffff 100%);
    border-left: 6px solid #d32f2f !important;
  }

  /* ================================
    4) Refer Back – Orange/Yellow
  =================================*/
  .card-theme-referback {
    background: linear-gradient(135deg, #fff8e1 0%, #ffffff 100%);
    border-left: 6px solid #f57f17 !important;
  }
</style>

@section('content')

  <!-- HERO -->
  <header class="py-4">
    <div class="container-fluid">      
        <div class="row g-4 align-items-center">
          <div class="col-lg-9">          
            <h4 class="text-success mb-2"><strong>Amnatcharoen One Province One Data : AOPOD</strong></h4>          
          </div>
          {{-- ขวาสุด: select + ปุ่ม ติดกันและชิดขวา --}}
          <div class="col-lg-3 d-flex justify-content-lg-end">
            <span class="text-secondary my-1">
                วันที่ {{ \Carbon\Carbon::now()->locale('th')->isoFormat('D MMM YYYY เวลา H:mm') }} น.&nbsp;&nbsp;
            </span>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="location.reload();">
              <i class="bi bi-arrow-clockwise"></i> โหลดใหม่
            </button>
          </div>
        </div>
    </div>
  </header>

  @php
    $fmtInt   = fn($n) => number_format((int)($n ?? 0));
    $fmtMoney = fn($n) => number_format((float)($n ?? 0), 2);
  @endphp

  <!-- แถว 2 จำนวน 3 Block (Refer Out, In, Back) -->
    <section id="summary" class="pb-2">
    <div class="container-fluid">      
      <div class="row justify-content-center g-3">
        <!-- Refer Out ------------------------------------------------------------------------------------------>
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="#" data-bs-toggle="modal" data-bs-target="#ReferOutDetailModal" class="text-decoration-none text-dark d-block">
            <div class="card card-theme-referout card-hover glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-bold" style="color: #c2185b;">Refer Out วันนี้</h6>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(194, 24, 91, 0.1);">
                  <i class="fa-solid fa-truck-medical fs-4" style="color: #c2185b;"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-5">
                <div class="text-center">
                  <div class="small text-secondary">OPD</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #c2185b;">
                    {{ $fmtInt($visit_referout_inprov + $visit_referout_outprov) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-center">
                  <div class="small text-secondary">IPD</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #c2185b;">
                    {{ $fmtInt($visit_referout_inprov_ipd + $visit_referout_outprov_ipd) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="ReferOutDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-arrow-left-right me-2"></i> Refer Out วันนี้
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th rowspan="2" class="text-center align-middle">รหัส</th>
                      <th rowspan="2" class="text-center align-middle">ชื่อโรงพยาบาล</th>
                      <th colspan="2" style="border-right:1px solid #aac6ec;">OPD</th>
                      <th colspan="2">IPD</th>
                    </tr>
                    <tr class="text-center text-primary fw-semibold">
                      <th>ในจังหวัด</th>
                      <th style="border-right:1px solid #aac6ec;">ต่างจังหวัด</th>
                      <th>ในจังหวัด</th>
                      <th>ต่างจังหวัด</th>
                    </tr>
                  </thead>

                  <tbody>
                    @foreach($hospitalSummary as $h)
                      <tr>
                        <td align="right" class="text-secondary">{{ $h->hospcode }}</td>
                        <td>
                          <span class="fw-semibold text-dark">{{ $h->hospname }}</span><br>
                          <small class="text-muted">
                            {{ \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') }} น.
                          </small>
                        </td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_referout_inprov) }}</td>
                        <td align="right" class="text-success">
                          {{ number_format($h->visit_referout_outprov) }}
                        </td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_referout_inprov_ipd) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->visit_referout_outprov_ipd) }}</td>
                      </tr>
                    @endforeach
                    {{-- แถวผลรวม --}}
                    <tr style="background-color:#eef4fb;" class="fw-bold text-end">
                      <td colspan="2" class="text-center text-dark">รวมทั้งหมด</td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_referout_inprov')) }}</td>
                      <td class="text-success">
                        {{ number_format($hospitalSummary->sum('visit_referout_outprov')) }}
                      </td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_referout_inprov_ipd')) }}</td>
                      <td class="text-success">{{ number_format($hospitalSummary->sum('visit_referout_outprov_ipd')) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Footer -->
              <div class="modal-footer" style="background-color:#eef4fb;">
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm"
                        style="background-color:#3e7cc1; border-color:#3e7cc1;"
                        data-bs-dismiss="modal">
                  ปิด
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Refer In  --------------------------------------------------------------------------------------------->
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="#" data-bs-toggle="modal" data-bs-target="#ReferInDetailModal" class="text-decoration-none text-dark d-block">
            <div class="card card-theme-referin card-hover glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-danger fw-bold">Refer In วันนี้</h6>
                <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                  <i class="fa-solid fa-truck-medical text-danger fs-4"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-5">
                <div class="text-center">
                  <div class="small text-secondary">OPD</div>
                  <div class="fw-bold text-danger" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_referin_inprov + $visit_referin_outprov) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-center">
                  <div class="small text-secondary">IPD</div>
                  <div class="fw-bold text-danger" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_referin_inprov_ipd + $visit_referin_outprov_ipd) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="ReferInDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-arrow-left-right me-2"></i>Refer IN วันนี้
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th rowspan="2" class="align-middle">รหัส</th>
                      <th rowspan="2" class="align-middle">ชื่อโรงพยาบาล</th>
                      <th colspan="2" style="border-right:1px solid #aac6ec;">OPD</th>
                      <th colspan="2">IPD</th>
                    </tr>
                    <tr class="text-center text-primary fw-semibold">
                      <th>ในจังหวัด</th>
                      <th style="border-right:1px solid #aac6ec;">ต่างจังหวัด</th>
                      <th>ในจังหวัด</th>
                      <th>ต่างจังหวัด</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($hospitalSummary as $h)
                      <tr>
                        <td align="right" class="text-secondary">{{ $h->hospcode }}</td>
                        <td>
                          <span class="fw-semibold text-dark">{{ $h->hospname }}</span><br>
                          <small class="text-muted">
                            {{ \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') }} น.
                          </small>
                        </td>
                        <!-- OPD -->
                        <td align="right" class="text-primary">{{ number_format($h->visit_referin_inprov) }}</td>
                        <td align="right" class="text-success">
                          {{ number_format($h->visit_referin_outprov) }}
                        </td>
                        <!-- IPD -->
                        <td align="right" class="text-primary">{{ number_format($h->visit_referin_inprov_ipd) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->visit_referin_outprov_ipd) }}</td>
                      </tr>
                    @endforeach
                    {{-- แถวผลรวม --}}
                    <tr style="background-color:#eef4fb;" class="fw-bold text-end">
                      <td colspan="2" class="text-center text-dark">รวมทั้งหมด</td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_referin_inprov')) }}</td>
                      <td class="text-success">{{ number_format($hospitalSummary->sum('visit_referin_outprov')) }}</td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_referin_inprov_ipd')) }}</td>
                      <td class="text-success">{{ number_format($hospitalSummary->sum('visit_referin_outprov_ipd')) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Footer -->
              <div class="modal-footer" style="background-color:#eef4fb;">
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm"
                        style="background-color:#3e7cc1; border-color:#3e7cc1;"
                        data-bs-dismiss="modal">
                  ปิด
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Refer Back  --------------------------------------------------------------------------------------------->
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="#" data-bs-toggle="modal" data-bs-target="#ReferBackDetailModal" class="text-decoration-none text-dark d-block">
            <div class="card card-theme-referback card-hover glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-bold" style="color: #f57f17;">Refer Back วันนี้</h6>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(245, 127, 23, 0.1);">
                  <i class="fa-solid fa-truck-medical fs-4" style="color: #f57f17;"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-5">
                <div class="text-center">
                  <div class="small text-secondary">ในจังหวัด</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #f57f17;">
                    {{ $fmtInt($visit_referback_inprov) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-center">
                  <div class="small text-secondary">ต่างจังหวัด</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #f57f17;">
                    {{ $fmtInt($visit_referback_outprov) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="ReferBackDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-arrow-left-right me-2"></i>Refer Back วันนี้
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th class="align-middle">รหัส</th>
                      <th class="align-middle">ชื่อโรงพยาบาล</th>
                      <th style="border-right:1px solid #aac6ec;">ในจังหวัด</th>
                      <th>ต่างจังหวัด</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($hospitalSummary as $h)
                      <tr>
                        <td align="right" class="text-secondary">{{ $h->hospcode }}</td>
                        <td>
                          <span class="fw-semibold text-dark">{{ $h->hospname }}</span><br>
                          <small class="text-muted">
                            {{ \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') }} น.
                          </small>
                        </td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_referback_inprov) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->visit_referback_outprov) }}</td>
                      </tr>
                    @endforeach
                    {{-- แถวผลรวม --}}
                    <tr style="background-color:#eef4fb;" class="fw-bold text-end">
                      <td colspan="2" class="text-center text-dark">รวมทั้งหมด</td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_referback_inprov')) }}</td>
                      <td class="text-success">{{ number_format($hospitalSummary->sum('visit_referback_outprov')) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Footer -->
              <div class="modal-footer" style="background-color:#eef4fb;">
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm"
                        style="background-color:#3e7cc1; border-color:#3e7cc1;"
                        data-bs-dismiss="modal">
                  ปิด
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <br>
  <hr>
  
  {{-- เลือกปีงบประมาณ ----------------------------------------------------------------------------------------------------------}}
  <section id="budget-year-select" class="pb-2">
      <div class="container-fluid">
        <form method="POST" action="{{ url('web/refer') }}" enctype="multipart/form-data">
        @csrf
          <div class="row g-4 align-items-center">
            <div class="col-lg-9">          
              <h6 class="text-success mb-2"><strong></strong></h6>          
            </div>
            {{-- ขวาสุด: select + ปุ่ม ติดกันและชิดขวา --}}
            <div class="col-lg-3 d-flex justify-content-lg-end">
              <div class="d-flex align-items-center gap-2">
                <select class="form-select" name="budget_year">
                  @foreach ($budget_year_select as $row)
                    <option value="{{ $row->LEAVE_YEAR_ID }}"
                      {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                      {{ $row->LEAVE_YEAR_NAME }}
                    </option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-primary">{{ __('ค้นหา') }}</button>
              </div>
            </div>
          </div>
        </form>
      </div>
  </section>
  <!-- ข้อมูลบริการ -->
  <section id="hospital" class="pb-2">
    <div class="container-fluid">
      <!-- NAV PILLS -->
      <ul class="nav nav-pills overflow-auto flex-nowrap pb-1" id="hospPills" role="tablist">
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link active" id="tab-10985" data-bs-toggle="pill" data-bs-target="#pane-10985" type="button" role="tab" aria-controls="pane-10985" aria-selected="true">
            <i class="fa-solid fa-hospital me-1"></i> รพ.ชานุมาน
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10986" data-bs-toggle="pill" data-bs-target="#pane-10986" type="button" role="tab" aria-controls="pane-10986" aria-selected="false">
            <i class="fa-solid fa-hospital me-1"></i> รพ.ปทุมราชวงศา
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10987" data-bs-toggle="pill" data-bs-target="#pane-10987" type="button" role="tab" aria-controls="pane-10987" aria-selected="false">
            <i class="fa-solid fa-hospital me-1"></i> รพ.พนา
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10988" data-bs-toggle="pill" data-bs-target="#pane-10988" type="button" role="tab" aria-controls="pane-10988" aria-selected="false">
            <i class="fa-solid fa-hospital me-1"></i> รพ.เสนางคนิคม
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10989" data-bs-toggle="pill" data-bs-target="#pane-10989" type="button" role="tab" aria-controls="pane-10989" aria-selected="false">
            <i class="fa-solid fa-hospital me-1"></i> รพ.หัวตะพาน
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10990" data-bs-toggle="pill" data-bs-target="#pane-10990" type="button" role="tab" aria-controls="pane-10990" aria-selected="false">
            <i class="fa-solid fa-hospital me-1"></i> รพ.ลืออำนาจ
          </button>
        </li>    
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10703" data-bs-toggle="pill" data-bs-target="#pane-10703" type="button" role="tab" aria-controls="pane-10703" aria-selected="false">
            <i class="fa-solid fa-hospital-user me-1"></i> รพ.อำนาจเจริญ
          </button>
        </li>      
      </ul>

      <!-- TAB PANES -->
      <div class="tab-content mt-3" id="hospPillsContent">

        <!-- 10985 -->
        <div class="tab-pane fade show active" id="pane-10985" role="tabpanel" aria-labelledby="tab-10985" tabindex="0">          
          <!-- Refer -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-truck-arrow-right text-success me-2"></i>[10985] ข้อมูลการส่งต่อ Refer โรงพยาบาลชานุมาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10985}}</span>
                <div id="btn-10985-refer"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10985_refer" class="table custom-table my-3" width="100%">
                <thead>
                  <tr><th class="th-grey" rowspan="2" width="8%">เดือน</th><th class="th-blue" colspan="4">Refer Out</th><th class="th-green" colspan="4">Refer In</th><th class="th-orange" colspan="2">Refer Back</th></tr>
                  <tr><th class="th-blue">OPD ในจังหวัด</th><th class="th-blue">OPD ต่างจังหวัด</th><th class="th-blue">IPD ในจังหวัด</th><th class="th-blue">IPD ต่างจังหวัด</th><th class="th-green">OPD ในจังหวัด</th><th class="th-green">OPD ต่างจังหวัด</th><th class="th-green">IPD ในจังหวัด</th><th class="th-green">IPD ต่างจังหวัด</th><th class="th-orange">ในจังหวัด</th><th class="th-orange">ต่างจังหวัด</th></tr>
                </thead>
                <tbody>
                @foreach($refer_10985 as $row)
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referout_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referout_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referin_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referin_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_outprov) }}</td>
                  </tr>
                @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($refer_10985->sum('visit_referout_inprov')) }}</td><td class="text-end">{{ number_format($refer_10985->sum('visit_referout_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10985->sum('visit_referout_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10985->sum('visit_referout_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10985->sum('visit_referin_inprov')) }}</td><td class="text-end">{{ number_format($refer_10985->sum('visit_referin_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10985->sum('visit_referin_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10985->sum('visit_referin_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10985->sum('visit_referback_inprov')) }}</td><td class="text-end">{{ number_format($refer_10985->sum('visit_referback_outprov')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 10986 -->
        <div class="tab-pane fade " id="pane-10986" role="tabpanel" aria-labelledby="tab-10986" tabindex="0">          
          <!-- Refer -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-truck-arrow-right text-success me-2"></i>[10986] ข้อมูลการส่งต่อ Refer โรงพยาบาลปทุมราชวงศา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10986}}</span>
                <div id="btn-10986-refer"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10986_refer" class="table custom-table my-3" width="100%">
                <thead>
                  <tr><th class="th-grey" rowspan="2" width="8%">เดือน</th><th class="th-blue" colspan="4">Refer Out</th><th class="th-green" colspan="4">Refer In</th><th class="th-orange" colspan="2">Refer Back</th></tr>
                  <tr><th class="th-blue">OPD ในจังหวัด</th><th class="th-blue">OPD ต่างจังหวัด</th><th class="th-blue">IPD ในจังหวัด</th><th class="th-blue">IPD ต่างจังหวัด</th><th class="th-green">OPD ในจังหวัด</th><th class="th-green">OPD ต่างจังหวัด</th><th class="th-green">IPD ในจังหวัด</th><th class="th-green">IPD ต่างจังหวัด</th><th class="th-orange">ในจังหวัด</th><th class="th-orange">ต่างจังหวัด</th></tr>
                </thead>
                <tbody>
                @foreach($refer_10986 as $row)
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referout_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referout_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referin_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referin_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_outprov) }}</td>
                  </tr>
                @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($refer_10986->sum('visit_referout_inprov')) }}</td><td class="text-end">{{ number_format($refer_10986->sum('visit_referout_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10986->sum('visit_referout_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10986->sum('visit_referout_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10986->sum('visit_referin_inprov')) }}</td><td class="text-end">{{ number_format($refer_10986->sum('visit_referin_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10986->sum('visit_referin_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10986->sum('visit_referin_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10986->sum('visit_referback_inprov')) }}</td><td class="text-end">{{ number_format($refer_10986->sum('visit_referback_outprov')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 10987 -->
        <div class="tab-pane fade " id="pane-10987" role="tabpanel" aria-labelledby="tab-10987" tabindex="0">          
          <!-- Refer -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-truck-arrow-right text-success me-2"></i>[10987] ข้อมูลการส่งต่อ Refer โรงพยาบาลพนา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10987}}</span>
                <div id="btn-10987-refer"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10987_refer" class="table custom-table my-3" width="100%">
                <thead>
                  <tr><th class="th-grey" rowspan="2" width="8%">เดือน</th><th class="th-blue" colspan="4">Refer Out</th><th class="th-green" colspan="4">Refer In</th><th class="th-orange" colspan="2">Refer Back</th></tr>
                  <tr><th class="th-blue">OPD ในจังหวัด</th><th class="th-blue">OPD ต่างจังหวัด</th><th class="th-blue">IPD ในจังหวัด</th><th class="th-blue">IPD ต่างจังหวัด</th><th class="th-green">OPD ในจังหวัด</th><th class="th-green">OPD ต่างจังหวัด</th><th class="th-green">IPD ในจังหวัด</th><th class="th-green">IPD ต่างจังหวัด</th><th class="th-orange">ในจังหวัด</th><th class="th-orange">ต่างจังหวัด</th></tr>
                </thead>
                <tbody>
                @foreach($refer_10987 as $row)
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referout_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referout_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referin_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referin_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_outprov) }}</td>
                  </tr>
                @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($refer_10987->sum('visit_referout_inprov')) }}</td><td class="text-end">{{ number_format($refer_10987->sum('visit_referout_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10987->sum('visit_referout_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10987->sum('visit_referout_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10987->sum('visit_referin_inprov')) }}</td><td class="text-end">{{ number_format($refer_10987->sum('visit_referin_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10987->sum('visit_referin_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10987->sum('visit_referin_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10987->sum('visit_referback_inprov')) }}</td><td class="text-end">{{ number_format($refer_10987->sum('visit_referback_outprov')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 10988 -->
        <div class="tab-pane fade " id="pane-10988" role="tabpanel" aria-labelledby="tab-10988" tabindex="0">          
          <!-- Refer -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-truck-arrow-right text-success me-2"></i>[10988] ข้อมูลการส่งต่อ Refer โรงพยาบาลเสนางคนิคม</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10988}}</span>
                <div id="btn-10988-refer"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10988_refer" class="table custom-table my-3" width="100%">
                <thead>
                  <tr><th class="th-grey" rowspan="2" width="8%">เดือน</th><th class="th-blue" colspan="4">Refer Out</th><th class="th-green" colspan="4">Refer In</th><th class="th-orange" colspan="2">Refer Back</th></tr>
                  <tr><th class="th-blue">OPD ในจังหวัด</th><th class="th-blue">OPD ต่างจังหวัด</th><th class="th-blue">IPD ในจังหวัด</th><th class="th-blue">IPD ต่างจังหวัด</th><th class="th-green">OPD ในจังหวัด</th><th class="th-green">OPD ต่างจังหวัด</th><th class="th-green">IPD ในจังหวัด</th><th class="th-green">IPD ต่างจังหวัด</th><th class="th-orange">ในจังหวัด</th><th class="th-orange">ต่างจังหวัด</th></tr>
                </thead>
                <tbody>
                @foreach($refer_10988 as $row)
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referout_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referout_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referin_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referin_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_outprov) }}</td>
                  </tr>
                @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($refer_10988->sum('visit_referout_inprov')) }}</td><td class="text-end">{{ number_format($refer_10988->sum('visit_referout_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10988->sum('visit_referout_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10988->sum('visit_referout_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10988->sum('visit_referin_inprov')) }}</td><td class="text-end">{{ number_format($refer_10988->sum('visit_referin_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10988->sum('visit_referin_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10988->sum('visit_referin_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10988->sum('visit_referback_inprov')) }}</td><td class="text-end">{{ number_format($refer_10988->sum('visit_referback_outprov')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 10989 -->
        <div class="tab-pane fade " id="pane-10989" role="tabpanel" aria-labelledby="tab-10989" tabindex="0">          
          <!-- Refer -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-truck-arrow-right text-success me-2"></i>[10989] ข้อมูลการส่งต่อ Refer โรงพยาบาลหัวตะพาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10989}}</span>
                <div id="btn-10989-refer"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10989_refer" class="table custom-table my-3" width="100%">
                <thead>
                  <tr><th class="th-grey" rowspan="2" width="8%">เดือน</th><th class="th-blue" colspan="4">Refer Out</th><th class="th-green" colspan="4">Refer In</th><th class="th-orange" colspan="2">Refer Back</th></tr>
                  <tr><th class="th-blue">OPD ในจังหวัด</th><th class="th-blue">OPD ต่างจังหวัด</th><th class="th-blue">IPD ในจังหวัด</th><th class="th-blue">IPD ต่างจังหวัด</th><th class="th-green">OPD ในจังหวัด</th><th class="th-green">OPD ต่างจังหวัด</th><th class="th-green">IPD ในจังหวัด</th><th class="th-green">IPD ต่างจังหวัด</th><th class="th-orange">ในจังหวัด</th><th class="th-orange">ต่างจังหวัด</th></tr>
                </thead>
                <tbody>
                @foreach($refer_10989 as $row)
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referout_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referout_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referin_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referin_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_outprov) }}</td>
                  </tr>
                @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($refer_10989->sum('visit_referout_inprov')) }}</td><td class="text-end">{{ number_format($refer_10989->sum('visit_referout_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10989->sum('visit_referout_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10989->sum('visit_referout_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10989->sum('visit_referin_inprov')) }}</td><td class="text-end">{{ number_format($refer_10989->sum('visit_referin_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10989->sum('visit_referin_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10989->sum('visit_referin_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10989->sum('visit_referback_inprov')) }}</td><td class="text-end">{{ number_format($refer_10989->sum('visit_referback_outprov')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 10990 -->
        <div class="tab-pane fade " id="pane-10990" role="tabpanel" aria-labelledby="tab-10990" tabindex="0">          
          <!-- Refer -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-truck-arrow-right text-success me-2"></i>[10990] ข้อมูลการส่งต่อ Refer โรงพยาบาลลืออำนาจ</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10990}}</span>
                <div id="btn-10990-refer"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10990_refer" class="table custom-table my-3" width="100%">
                <thead>
                  <tr><th class="th-grey" rowspan="2" width="8%">เดือน</th><th class="th-blue" colspan="4">Refer Out</th><th class="th-green" colspan="4">Refer In</th><th class="th-orange" colspan="2">Refer Back</th></tr>
                  <tr><th class="th-blue">OPD ในจังหวัด</th><th class="th-blue">OPD ต่างจังหวัด</th><th class="th-blue">IPD ในจังหวัด</th><th class="th-blue">IPD ต่างจังหวัด</th><th class="th-green">OPD ในจังหวัด</th><th class="th-green">OPD ต่างจังหวัด</th><th class="th-green">IPD ในจังหวัด</th><th class="th-green">IPD ต่างจังหวัด</th><th class="th-orange">ในจังหวัด</th><th class="th-orange">ต่างจังหวัด</th></tr>
                </thead>
                <tbody>
                @foreach($refer_10990 as $row)
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referout_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referout_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referin_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referin_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_outprov) }}</td>
                  </tr>
                @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($refer_10990->sum('visit_referout_inprov')) }}</td><td class="text-end">{{ number_format($refer_10990->sum('visit_referout_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10990->sum('visit_referout_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10990->sum('visit_referout_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10990->sum('visit_referin_inprov')) }}</td><td class="text-end">{{ number_format($refer_10990->sum('visit_referin_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10990->sum('visit_referin_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10990->sum('visit_referin_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10990->sum('visit_referback_inprov')) }}</td><td class="text-end">{{ number_format($refer_10990->sum('visit_referback_outprov')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 10703 -->
        <div class="tab-pane fade " id="pane-10703" role="tabpanel" aria-labelledby="tab-10703" tabindex="0">          
          <!-- Refer -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-truck-arrow-right text-success me-2"></i>[10703] ข้อมูลการส่งต่อ Refer โรงพยาบาลอำนาจเจริญ</h6>
              <span class="text-secondary small">Update {{$update_at10703}}</span>
            </div>
            <div class="table-responsive">
              <table id="table10703_refer" class="table custom-table my-3" width="100%">
                <thead>
                  <tr><th class="th-grey" rowspan="2" width="8%">เดือน</th><th class="th-blue" colspan="4">Refer Out</th><th class="th-green" colspan="4">Refer In</th><th class="th-orange" colspan="2">Refer Back</th></tr>
                  <tr><th class="th-blue">OPD ในจังหวัด</th><th class="th-blue">OPD ต่างจังหวัด</th><th class="th-blue">IPD ในจังหวัด</th><th class="th-blue">IPD ต่างจังหวัด</th><th class="th-green">OPD ในจังหวัด</th><th class="th-green">OPD ต่างจังหวัด</th><th class="th-green">IPD ในจังหวัด</th><th class="th-green">IPD ต่างจังหวัด</th><th class="th-orange">ในจังหวัด</th><th class="th-orange">ต่างจังหวัด</th></tr>
                </thead>
                <tbody>
                @foreach($refer_10703 as $row)
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referout_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referout_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referout_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referin_outprov) }}</td>
                    <td class="text-end text-primary fw-bold">{{ number_format($row->visit_referin_inprov_ipd) }}</td>
                    <td class="text-end text-danger fw-bold">{{ number_format($row->visit_referin_outprov_ipd) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_inprov) }}</td>
                    <td class="text-end">{{ number_format($row->visit_referback_outprov) }}</td>
                  </tr>
                @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($refer_10703->sum('visit_referout_inprov')) }}</td><td class="text-end">{{ number_format($refer_10703->sum('visit_referout_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10703->sum('visit_referout_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10703->sum('visit_referout_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10703->sum('visit_referin_inprov')) }}</td><td class="text-end">{{ number_format($refer_10703->sum('visit_referin_outprov')) }}</td><td class="text-end text-primary">{{ number_format($refer_10703->sum('visit_referin_inprov_ipd')) }}</td><td class="text-end text-danger">{{ number_format($refer_10703->sum('visit_referin_outprov_ipd')) }}</td>
                    <td class="text-end">{{ number_format($refer_10703->sum('visit_referback_inprov')) }}</td><td class="text-end">{{ number_format($refer_10703->sum('visit_referback_outprov')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

   @if($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: '{{ $errors->first() }}',
            confirmButtonText: 'ตกลง'
        });
    </script>
    @endif

@endsection

@push('scripts')
  <script>
    $(function () {
      const config = {
        dom: 'rt',
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      };

      // 10985
      var t10985_ref = $('#table10985_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลชานุมาน'}]});
      t10985_ref.buttons().container().appendTo('#btn-10985-refer');

      // 10986
      var t10986_ref = $('#table10986_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลปทุมราชวงศา'}]});
      t10986_ref.buttons().container().appendTo('#btn-10986-refer');

      // 10987
      var t10987_ref = $('#table10987_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลพนา'}]});
      t10987_ref.buttons().container().appendTo('#btn-10987-refer');

      // 10988
      var t10988_ref = $('#table10988_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลเสนางคนิคม'}]});
      t10988_ref.buttons().container().appendTo('#btn-10988-refer');

      // 10989
      var t10989_ref = $('#table10989_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลหัวตะพาน'}]});
      t10989_ref.buttons().container().appendTo('#btn-10989-refer');

      // 10990
      var t10990_ref = $('#table10990_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลลืออำนาจ'}]});
      t10990_ref.buttons().container().appendTo('#btn-10990-refer');

      // 10703
      var t10703_ref = $('#table10703_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลอำนาจเจริญ'}]});
      t10703_ref.buttons().container().appendTo('#btn-10703-refer');
    });
  </script>
@endpush