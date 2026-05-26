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

  /* Hospital Brand Colors & Themes */
  .text-10985 { color: #8b5cf6 !important; }
  .text-10986 { color: #06b6d4 !important; }
  .text-10987 { color: #ec4899 !important; }
  .text-10988 { color: #f59e0b !important; }
  .text-10989 { color: #3b82f6 !important; }
  .text-10990 { color: #10b981 !important; }
  .text-10703 { color: #f43f5e !important; }

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
    2) Refer Out – Red
  =================================*/
  .card-theme-referout {
    background: linear-gradient(135deg, #ffebee 0%, #ffffff 100%);
    border-left: 6px solid #d32f2f !important;
  }

  /* ================================
    3) Refer In – Blue
  =================================*/
  .card-theme-referin {
    background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
    border-left: 6px solid #0d47a1 !important;
  }

  /* ================================
    4) Refer Back – Green
  =================================*/
  .card-theme-referback {
    background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
    border-left: 6px solid #1b5e20 !important;
  }

  /* Hospital Monthly Chart Card Styles (Red, Blue, Green) */
  .card.card-chart-referout {
    border-top: 4px solid #d32f2f !important;
  }
  .card-chart-referout h6 {
    color: #d32f2f !important;
  }
  .card.card-chart-referin {
    border-top: 4px solid #0d47a1 !important;
  }
  .card-chart-referin h6 {
    color: #0d47a1 !important;
  }
  .card.card-chart-referback {
    border-top: 4px solid #1b5e20 !important;
  }
  .card-chart-referback h6 {
    color: #1b5e20 !important;
  }
</style>

@section('content')

  <!-- HERO -->
  <header class="py-4">
    <div class="container-fluid">      
        <div class="row g-4 align-items-center">
          <div class="col-lg-7">          
            <div class="d-flex align-items-center gap-3">
              <div class="bg-danger bg-opacity-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border: 1px solid rgba(239, 68, 68, 0.2);">
                <i class="fa-solid fa-truck-medical fs-4 text-danger"></i>
              </div>
              <div>
                <h4 class="fw-bold mb-1" style="color: #1e293b;">ข้อมูลส่งต่อผู้ป่วย (Refer)</h4>
                <p class="text-secondary small mb-0"><i class="fa-solid fa-square-poll-vertical text-green me-1"></i>Amnatcharoen One Province One Data</p>
              </div>
            </div>          
          </div>
          {{-- ขวาสุด: select + ปุ่ม ติดกันและชิดขวา --}}
          <div class="col-lg-5 d-flex justify-content-lg-end align-items-center gap-3">
            <span class="text-secondary small fw-medium">
                วันที่ {{ \Carbon\Carbon::now()->locale('th')->isoFormat('D MMM YYYY เวลา H:mm') }} น.
            </span>
            <button type="button" class="btn btn-sm btn-glass-action" onclick="location.reload();">
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
                <h6 class="mb-0 fw-bold" style="color: #d32f2f;">Refer Out วันนี้</h6>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(211, 47, 47, 0.1);">
                  <i class="fa-solid fa-truck-medical fs-4" style="color: #d32f2f;"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-5">
                <div class="text-center">
                  <div class="small text-secondary">OPD</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #d32f2f;">
                    {{ $fmtInt($visit_referout_inprov + $visit_referout_outprov) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-center">
                  <div class="small text-secondary">IPD</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #d32f2f;">
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
                            {{ $h->last_updated_at ? \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') . ' น.' : 'ยังไม่มีข้อมูลส่ง' }}
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
                <h6 class="mb-0 fw-bold" style="color: #0d47a1;">Refer In วันนี้</h6>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(13, 71, 161, 0.1);">
                  <i class="fa-solid fa-truck-medical fs-4" style="color: #0d47a1;"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-5">
                <div class="text-center">
                  <div class="small text-secondary">OPD</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #0d47a1;">
                    {{ $fmtInt($visit_referin_inprov + $visit_referin_outprov) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-center">
                  <div class="small text-secondary">IPD</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #0d47a1;">
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
                            {{ $h->last_updated_at ? \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') . ' น.' : 'ยังไม่มีข้อมูลส่ง' }}
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
                <h6 class="mb-0 fw-bold" style="color: #1b5e20;">Refer Back วันนี้</h6>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(27, 94, 32, 0.1);">
                  <i class="fa-solid fa-truck-medical fs-4" style="color: #1b5e20;"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-5">
                <div class="text-center">
                  <div class="small text-secondary">ในจังหวัด</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #1b5e20;">
                    {{ $fmtInt($visit_referback_inprov) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-center">
                  <div class="small text-secondary">ต่างจังหวัด</div>
                  <div class="fw-bold" style="font-size:1.5rem; color: #1b5e20;">
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
                            {{ $h->last_updated_at ? \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') . ' น.' : 'ยังไม่มีข้อมูลส่ง' }}
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
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold m-0 text-10985"><i class="fa-solid fa-truck-arrow-right text-10985 me-2"></i>[10985] ข้อมูลการส่งต่อ Refer โรงพยาบาลชานุมาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-2 py-1 rounded-pill border">Update {{$update_at10985}}</span>
              </div>
            </div>

            <!-- 3 Charts Side-by-Side -->
            <div class="row g-3 mb-4 mt-2">
              <div class="col-lg-4">
                <div class="card card-chart-referout border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Out</h6>
                  <div id="chart_10985_referout" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referin border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer In</h6>
                  <div id="chart_10985_referin" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referback border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Back</h6>
                  <div id="chart_10985_referback" style="min-height: 220px;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mb-2">
              <div id="btn-10985-refer"></div>
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
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold m-0 text-10986"><i class="fa-solid fa-truck-arrow-right text-10986 me-2"></i>[10986] ข้อมูลการส่งต่อ Refer โรงพยาบาลปทุมราชวงศา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-2 py-1 rounded-pill border">Update {{$update_at10986}}</span>
              </div>
            </div>

            <!-- 3 Charts Side-by-Side -->
            <div class="row g-3 mb-4 mt-2">
              <div class="col-lg-4">
                <div class="card card-chart-referout border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Out</h6>
                  <div id="chart_10986_referout" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referin border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer In</h6>
                  <div id="chart_10986_referin" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referback border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Back</h6>
                  <div id="chart_10986_referback" style="min-height: 220px;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mb-2">
              <div id="btn-10986-refer"></div>
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
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold m-0 text-10987"><i class="fa-solid fa-truck-arrow-right text-10987 me-2"></i>[10987] ข้อมูลการส่งต่อ Refer โรงพยาบาลพนา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-2 py-1 rounded-pill border">Update {{$update_at10987}}</span>
              </div>
            </div>

            <!-- 3 Charts Side-by-Side -->
            <div class="row g-3 mb-4 mt-2">
              <div class="col-lg-4">
                <div class="card card-chart-referout border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Out</h6>
                  <div id="chart_10987_referout" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referin border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer In</h6>
                  <div id="chart_10987_referin" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referback border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Back</h6>
                  <div id="chart_10987_referback" style="min-height: 220px;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mb-2">
              <div id="btn-10987-refer"></div>
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
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold m-0 text-10988"><i class="fa-solid fa-truck-arrow-right text-10988 me-2"></i>[10988] ข้อมูลการส่งต่อ Refer โรงพยาบาลเสนางคนิคม</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-2 py-1 rounded-pill border">Update {{$update_at10988}}</span>
              </div>
            </div>

            <!-- 3 Charts Side-by-Side -->
            <div class="row g-3 mb-4 mt-2">
              <div class="col-lg-4">
                <div class="card card-chart-referout border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Out</h6>
                  <div id="chart_10988_referout" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referin border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer In</h6>
                  <div id="chart_10988_referin" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referback border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Back</h6>
                  <div id="chart_10988_referback" style="min-height: 220px;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mb-2">
              <div id="btn-10988-refer"></div>
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
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold m-0 text-10989"><i class="fa-solid fa-truck-arrow-right text-10989 me-2"></i>[10989] ข้อมูลการส่งต่อ Refer โรงพยาบาลหัวตะพาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-2 py-1 rounded-pill border">Update {{$update_at10989}}</span>
              </div>
            </div>

            <!-- 3 Charts Side-by-Side -->
            <div class="row g-3 mb-4 mt-2">
              <div class="col-lg-4">
                <div class="card card-chart-referout border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Out</h6>
                  <div id="chart_10989_referout" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referin border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer In</h6>
                  <div id="chart_10989_referin" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referback border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Back</h6>
                  <div id="chart_10989_referback" style="min-height: 220px;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mb-2">
              <div id="btn-10989-refer"></div>
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
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold m-0 text-10990"><i class="fa-solid fa-truck-arrow-right text-10990 me-2"></i>[10990] ข้อมูลการส่งต่อ Refer โรงพยาบาลลืออำนาจ</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-2 py-1 rounded-pill border">Update {{$update_at10990}}</span>
              </div>
            </div>

            <!-- 3 Charts Side-by-Side -->
            <div class="row g-3 mb-4 mt-2">
              <div class="col-lg-4">
                <div class="card card-chart-referout border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Out</h6>
                  <div id="chart_10990_referout" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referin border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer In</h6>
                  <div id="chart_10990_referin" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referback border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Back</h6>
                  <div id="chart_10990_referback" style="min-height: 220px;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mb-2">
              <div id="btn-10990-refer"></div>
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
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold m-0 text-10703"><i class="fa-solid fa-truck-arrow-right text-10703 me-2"></i>[10703] ข้อมูลการส่งต่อ Refer โรงพยาบาลอำนาจเจริญ</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-2 py-1 rounded-pill border">Update {{$update_at10703}}</span>
              </div>
            </div>

            <!-- 3 Charts Side-by-Side -->
            <div class="row g-3 mb-4 mt-2">
              <div class="col-lg-4">
                <div class="card card-chart-referout border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Out</h6>
                  <div id="chart_10703_referout" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referin border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer In</h6>
                  <div id="chart_10703_referin" style="min-height: 220px;"></div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-chart-referback border-0 shadow-sm rounded-4 h-100 p-2" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(8px);">
                  <h6 class="fw-bold text-center mb-2 small"><i class="fa-solid fa-chart-line me-1"></i>Refer Back</h6>
                  <div id="chart_10703_referback" style="min-height: 220px;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mb-2">
              <div id="btn-10703-refer"></div>
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
  <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script>
    $(function () {
      // 1. Prepare JSON monthly data for all hospitals
      const referData = {
        '10985': @json($refer_10985),
        '10986': @json($refer_10986),
        '10987': @json($refer_10987),
        '10988': @json($refer_10988),
        '10989': @json($refer_10989),
        '10990': @json($refer_10990),
        '10703': @json($refer_10703)
      };

      const hospColors = {
        '10985': { primary: '#8b5cf6', secondary: '#10b981' }, // Purple vs Emerald
        '10986': { primary: '#06b6d4', secondary: '#f59e0b' }, // Teal vs Amber
        '10987': { primary: '#ec4899', secondary: '#3b82f6' }, // Pink vs Blue
        '10988': { primary: '#f59e0b', secondary: '#8b5cf6' }, // Amber vs Purple
        '10989': { primary: '#3b82f6', secondary: '#ec4899' }, // Blue vs Pink
        '10990': { primary: '#10b981', secondary: '#f43f5e' }, // Emerald vs Rose
        '10703': { primary: '#f43f5e', secondary: '#06b6d4' }  // Rose vs Teal
      };

      function createReferChart(containerId, months, seriesData, themeColors) {
        const options = {
          series: seriesData,
          chart: {
            type: 'area',
            height: 220,
            toolbar: { show: false },
            sparkline: { enabled: false },
            fontFamily: 'Sarabun, sans-serif'
          },
          colors: [themeColors.primary, themeColors.secondary],
          stroke: {
            curve: 'smooth',
            width: 2.5
          },
          markers: {
            size: 4,
            strokeWidth: 2,
            hover: {
              size: 6
            }
          },
          fill: {
            type: 'gradient',
            gradient: {
              shadeIntensity: 1,
              opacityFrom: 0.3,
              opacityTo: 0.02,
              stops: [0, 90, 100]
            }
          },
          dataLabels: { enabled: false },
          xaxis: {
            categories: months,
            labels: {
              style: {
                colors: '#64748b',
                fontSize: '11px'
              }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          yaxis: {
            labels: {
              formatter: function (value) {
                return value.toLocaleString('th-TH', { maximumFractionDigits: 0 });
              },
              style: {
                colors: '#64748b',
                fontSize: '10px'
              }
            }
          },
          grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4,
            padding: {
              left: 5,
              right: 5,
              bottom: 0
            }
          },
          legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'center',
            fontSize: '11px',
            markers: {
              radius: 12
            }
          },
          tooltip: {
            theme: 'light',
            y: {
              formatter: function (val) {
                return val.toLocaleString('th-TH', { maximumFractionDigits: 0 }) + ' ราย';
              }
            }
          }
        };

        const chart = new ApexCharts(document.querySelector(`#${containerId}`), options);
        chart.render();
      }

      // Initialize all charts for each hospital
      const hospitalIds = ['10985', '10986', '10987', '10988', '10989', '10990', '10703'];
      hospitalIds.forEach(hospId => {
        const data = referData[hospId];
        if (data && data.length > 0) {
          const months = data.map(item => item.month);
          const colors = hospColors[hospId] || { primary: '#3b82f6', secondary: '#60a5fa' };

          // 1. Refer Out (OPD vs IPD)
          const referOutOpd = data.map(item => (parseFloat(item.visit_referout_inprov) || 0) + (parseFloat(item.visit_referout_outprov) || 0));
          const referOutIpd = data.map(item => (parseFloat(item.visit_referout_inprov_ipd) || 0) + (parseFloat(item.visit_referout_outprov_ipd) || 0));
          createReferChart(`chart_${hospId}_referout`, months, [
            { name: 'OPD Refer Out', data: referOutOpd },
            { name: 'IPD Refer Out', data: referOutIpd }
          ], colors);

          // 2. Refer In (OPD vs IPD)
          const referInOpd = data.map(item => (parseFloat(item.visit_referin_inprov) || 0) + (parseFloat(item.visit_referin_outprov) || 0));
          const referInIpd = data.map(item => (parseFloat(item.visit_referin_inprov_ipd) || 0) + (parseFloat(item.visit_referin_outprov_ipd) || 0));
          createReferChart(`chart_${hospId}_referin`, months, [
            { name: 'OPD Refer In', data: referInOpd },
            { name: 'IPD Refer In', data: referInIpd }
          ], colors);

          // 3. Refer Back (ในจังหวัด vs ต่างจังหวัด)
          const referBackIn = data.map(item => parseFloat(item.visit_referback_inprov) || 0);
          const referBackOut = data.map(item => parseFloat(item.visit_referback_outprov) || 0);
          createReferChart(`chart_${hospId}_referback`, months, [
            { name: 'ในจังหวัด', data: referBackIn },
            { name: 'ต่างจังหวัด', data: referBackOut }
          ], colors);
        }
      });

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