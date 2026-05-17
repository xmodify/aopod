@extends('layouts.app')

<style>
  /* === Table Styling === */
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
  .th-cyan { background: #e0f7fa !important; color: #006064 !important; }
  .th-grey { background: #f5f5f5 !important; color: #424242 !important; }
  
  .tr-total {
    background: #f8f9fa !important;
    font-weight: 700 !important;
    border-top: 2px solid #dee2e6 !important;
  }
  .tr-total td {
    color: #000 !important;
    font-size: 0.9rem !important;
    padding: 10px 8px !important;
    text-align: right !important;
    border-top: 2px solid #dee2e6 !important;
  }

  /* Glassmorphism Containers */
  .glass {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
  }

  .excel-container {
    display: inline-block !important;
  }
  .dt-buttons {
    display: inline-flex !important;
    vertical-align: middle !important;
    margin: 0 !important;
    float: none !important;
  }
  .btn-container-contents {
    display: inline-flex !important;
    align-items: center !important;
  }
  
  .btn-excel {
    background-color: #18a573 !important;
    border-color: #18a573 !important;
    color: white !important;
    transition: all 0.3s ease;
  }
  .btn-excel:hover {
    background-color: #148c61 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(24, 165, 115, 0.2);
  }

  .card-claim {
    border: none;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease-in-out;
    border-radius: 1rem;
  }

  .card-claim:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }
  /* Theme Specific Cards */
  .card-theme-ppfs {
    background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
    border-left: 6px solid #1976d2 !important;
  }
  .card-theme-cr {
    background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
    border-left: 6px solid #388e3c !important;
  }
  .card-theme-herb {
    background: linear-gradient(135deg, #fff3e0 0%, #ffffff 100%);
    border-left: 6px solid #f57c00 !important;
  }
</style>

@section('title', 'Dashboard | AOPOD')

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
  
  <!-- SUMMARY (3 blocks, no foreach) ------------------------------------------------------------------------------------>
  <section id="summary" class="pb-2">
    <div class="container-fluid">
      @php
        $fmtInt   = fn($n) => number_format((int)($n ?? 0));
        $fmtMoney = fn($n) => number_format((float)($n ?? 0), 2);
      @endphp

      <div class="row g-3">      

        {{--  PP Fee Schedule : ครั้ง -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="#" data-bs-toggle="modal" data-bs-target="#PPFSDetailModal" class="text-decoration-none text-dark">
            <div class="card-claim card-theme-ppfs card glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-primary fw-bold">PP Fee Schedule</h6>
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                  <i class="bi bi-file-earmark-medical fs-4 text-primary"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">Visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ppfs ?? 0) }}
                  </div>
                </div> 
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">Claim</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ppfs_claim ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">ค่ารักษา</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_ppfs ?? 0) }}
                  </div>
                </div>                
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">เรียกเก็บ</div>
                  <div class="fw-bold text-warning" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_ppfs_claim ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="PPFSDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-clipboard-data me-2"></i> PP Fee Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th>รหัส</th>
                      <th>ชื่อโรงพยาบาล</th>
                      <th>Visit</th>
                      <th>Claim</th>
                      <th>ค่ารักษา</th>
                      <th>เรียกเก็บ</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_ppfs) }}</td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_ppfs_claim) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_ppfs,2) }}</td>
                        <td align="right" class="fw-bold text-warning">{{ number_format($h->inc_ppfs_claim,2) }}</td>
                      </tr>
                    @endforeach
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

        {{-- UC-บริการเฉพาะ CR : ครั้ง | บาท ---------------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="#" data-bs-toggle="modal" data-bs-target="#CrDetailModal" class="text-decoration-none text-dark">
            <div class="card-claim card-theme-cr card glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-success fw-bold">UC - บริการเฉพาะ CR</h6>
                <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                  <i class="bi bi-clipboard2-pulse fs-4 text-success"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold " style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ucs_cr ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">Claim</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ucs_cr_claim ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">ค่ารักษา</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_uccr ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">เรียกเก็บ</div>
                  <div class="fw-bold text-warning" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_uccr_claim ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
            </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="CrDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-activity me-2"></i> UC - บริการเฉพาะ (CR)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th>รหัส</th>
                      <th>ชื่อโรงพยาบาล</th>
                      <th>Visit</th>
                      <th>Claim</th>
                      <th>ค่ารักษา</th>
                      <th>เรียกเก็บ</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_ucs_cr) }}</td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_ucs_cr_claim) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_uccr,2) }}</td>
                        <td align="right" class="fw-bold text-warning">{{ number_format($h->inc_uccr_claim,2) }}</td>
                      </tr>
                    @endforeach
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

        {{-- UC-สมุนไพร 32 รายการ : ครั้ง | บาท -----------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="#" data-bs-toggle="modal" data-bs-target="#HerbDetailModal" class="text-decoration-none text-dark">
            <div class="card-claim card-theme-herb card glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-warning fw-bold">UC - สมุนไพร 32 รายการ</h6>
                <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                  <i class="fa-solid fa-leaf fs-4 text-warning"></i>
                </div>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ucs_herb ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">Claim</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ucs_herb_claim ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">ค่ารักษา</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_herb ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">เรียกเก็บ</div>
                  <div class="fw-bold text-warning" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_herb_claim ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>   
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="HerbDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-capsule me-2"></i> UC - สมุนไพร 32 รายการ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th>รหัส</th>
                      <th>ชื่อโรงพยาบาล</th>
                      <th>Visit</th>
                      <th>Claim</th>
                      <th>ค่ารักษา</th>
                      <th>เรียกเก็บ</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_ucs_herb) }}</td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_ucs_herb_claim) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_herb,2) }}</td>
                        <td align="right" class="fw-bold text-warning">{{ number_format($h->inc_herb_claim,2) }}</td>
                      </tr>
                    @endforeach
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

      {{------------------------------------------------------------------------------------------------------------}}

      </div>
    </div>  
  </section>

<br>
<hr>

  {{-- เลือกปีงบประมาณ ----------------------------------------------------------------------------------------------------------}}
  <section id="summary" class="pb-2">
      <div class="container-fluid">
        <form method="POST" action="{{ url('web/claim') }}" enctype="multipart/form-data">
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

  {{-- ข้อมูลบริการ----------------------------------------------------------------------------------------------------------}}
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
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-nowrap">
              <h6 class="fw-bold m-0 text-truncate"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10985] ข้อมูลจัดเก็บรายได้ โรงพยาบาลชานุมาน ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                <span class="text-secondary small">Update {{$update_at10985}}</span>
                <div id="btn-10985" class="btn-container-contents"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10985" class="table custom-table table-hover align-middle my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>                   
                    <th class="th-blue" colspan="5">PP Fee Schedule</th> 
                    <th class="th-green" colspan="5">UCS - บริการเฉพาะ (CR)</th>
                    <th class="th-orange" colspan="5">UCS - สมุนไพร 32 รายการ</th>  
                  </tr>    
                  <tr> 
                    <th class="th-blue">Visit</th>
                    <th class="th-blue">Claim</th>
                    <th class="th-blue">ค่าบริการ</th>                    
                    <th class="th-blue">เรียกเก็บ</th>
                    <th class="th-blue">ชดเชย</th>   
                    <th class="th-green">Visit</th>
                    <th class="th-green">Claim</th>
                    <th class="th-green">ค่าบริการ</th>                    
                    <th class="th-green">เรียกเก็บ</th>
                    <th class="th-green">ชดเชย</th>   
                    <th class="th-orange">Visit</th>
                    <th class="th-orange">Claim</th>
                    <th class="th-orange">ค่าบริการ</th>                    
                    <th class="th-orange">เรียกเก็บ</th>
                    <th class="th-orange">ชดเชย</th>                    
                  </tr>    
                </thead>           
                <tbody>
                  @foreach($total_10985 as $row) 
                  <tr>
                    <td align="center" width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs) }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_ppfs_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs_receive,2) }}</td>  
                    <td align="right">{{ number_format($row->visit_ucs_cr) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_cr_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_uccr_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr_receive,2) }}</td> 
                    <td align="right">{{ number_format($row->visit_ucs_herb) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_herb_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_herb,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_herb_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_herb_receive,2) }}</td>                     
                  </tr>             
                  @endforeach    
                </tbody>
                <tfoot>
                  <tr class="tr-total">
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right">{{number_format($total_10985->sum('visit_ppfs'))}}</td>
                    <td align="right">{{number_format($total_10985->sum('visit_ppfs_claim'))}}</td>
                    <td align="right">{{number_format($total_10985->sum('inc_ppfs'),2)}}</td>                    
                    <td align="right">{{number_format($total_10985->sum('inc_ppfs_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10985->sum('inc_ppfs_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10985->sum('visit_ucs_cr'))}}</td>
                    <td align="right">{{number_format($total_10985->sum('visit_ucs_cr_claim'))}}</td>
                    <td align="right">{{number_format($total_10985->sum('inc_uccr'),2)}}</td>                    
                    <td align="right">{{number_format($total_10985->sum('inc_uccr_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10985->sum('inc_uccr_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10985->sum('visit_ucs_herb'))}}</td>
                    <td align="right">{{number_format($total_10985->sum('visit_ucs_herb_claim'))}}</td>
                    <td align="right">{{number_format($total_10985->sum('inc_herb'),2)}}</td>                    
                    <td align="right">{{number_format($total_10985->sum('inc_herb_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10985->sum('inc_herb_receive'),2)}}</td>
                  </tr>   
                </tfoot>
              </table>
            </div>
          </div>                   
        <!-- END 10985 -->
        </div>

        <!-- 10986 -->
        <div class="tab-pane fade" id="pane-10986" role="tabpanel" aria-labelledby="tab-10986" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-nowrap">
              <h6 class="fw-bold m-0 text-truncate"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10986] ข้อมูลจัดเก็บรายได้ โรงพยาบาลปทุมราชวงศา ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                <span class="text-secondary small">Update {{$update_at10986}}</span>
                <div id="btn-10986" class="btn-container-contents"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10986" class="table custom-table table-hover align-middle my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>                   
                    <th class="th-blue" colspan="5">PP Fee Schedule</th> 
                    <th class="th-green" colspan="5">UCS - บริการเฉพาะ (CR)</th>
                    <th class="th-orange" colspan="5">UCS - สมุนไพร 32 รายการ</th>  
                  </tr>    
                  <tr> 
                    <th class="th-blue">Visit</th>
                    <th class="th-blue">Claim</th>
                    <th class="th-blue">ค่าบริการ</th>                    
                    <th class="th-blue">เรียกเก็บ</th>
                    <th class="th-blue">ชดเชย</th>   
                    <th class="th-green">Visit</th>
                    <th class="th-green">Claim</th>
                    <th class="th-green">ค่าบริการ</th>                    
                    <th class="th-green">เรียกเก็บ</th>
                    <th class="th-green">ชดเชย</th>   
                    <th class="th-orange">Visit</th>
                    <th class="th-orange">Claim</th>
                    <th class="th-orange">ค่าบริการ</th>                    
                    <th class="th-orange">เรียกเก็บ</th>
                    <th class="th-orange">ชดเชย</th>                    
                  </tr>    
                </thead>           
                <tbody>
                  @foreach($total_10986 as $row) 
                  <tr>
                    <td align="center" width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs) }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_ppfs_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs_receive,2) }}</td>  
                    <td align="right">{{ number_format($row->visit_ucs_cr) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_cr_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_uccr_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr_receive,2) }}</td> 
                    <td align="right">{{ number_format($row->visit_ucs_herb) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_herb_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_herb,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_herb_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_herb_receive,2) }}</td>                     
                  </tr>             
                  @endforeach    
                </tbody>
                <tfoot>
                  <tr class="tr-total">
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right">{{number_format($total_10986->sum('visit_ppfs'))}}</td>
                    <td align="right">{{number_format($total_10986->sum('visit_ppfs_claim'))}}</td>
                    <td align="right">{{number_format($total_10986->sum('inc_ppfs'),2)}}</td>                    
                    <td align="right">{{number_format($total_10986->sum('inc_ppfs_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10986->sum('inc_ppfs_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10986->sum('visit_ucs_cr'))}}</td>
                    <td align="right">{{number_format($total_10986->sum('visit_ucs_cr_claim'))}}</td>
                    <td align="right">{{number_format($total_10986->sum('inc_uccr'),2)}}</td>                    
                    <td align="right">{{number_format($total_10986->sum('inc_uccr_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10986->sum('inc_uccr_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10986->sum('visit_ucs_herb'))}}</td>
                    <td align="right">{{number_format($total_10986->sum('visit_ucs_herb_claim'))}}</td>
                    <td align="right">{{number_format($total_10986->sum('inc_herb'),2)}}</td>                    
                    <td align="right">{{number_format($total_10986->sum('inc_herb_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10986->sum('inc_herb_receive'),2)}}</td>
                  </tr>   
                </tfoot>
              </table>
            </div>
          </div> 
        <!-- END 10986 -->         
        </div>

        <!-- 10987 -->
        <div class="tab-pane fade" id="pane-10987" role="tabpanel" aria-labelledby="tab-10987" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-nowrap">
              <h6 class="fw-bold m-0 text-truncate"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10987] ข้อมูลจัดเก็บรายได้ โรงพยาบาลพนา ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                <span class="text-secondary small">Update {{$update_at10987}}</span>
                <div id="btn-10987" class="btn-container-contents"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10987" class="table custom-table table-hover align-middle my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>                   
                    <th class="th-blue" colspan="5">PP Fee Schedule</th> 
                    <th class="th-green" colspan="5">UCS - บริการเฉพาะ (CR)</th>
                    <th class="th-orange" colspan="5">UCS - สมุนไพร 32 รายการ</th>  
                  </tr>    
                  <tr> 
                    <th class="th-blue">Visit</th>
                    <th class="th-blue">Claim</th>
                    <th class="th-blue">ค่าบริการ</th>                    
                    <th class="th-blue">เรียกเก็บ</th>
                    <th class="th-blue">ชดเชย</th>   
                    <th class="th-green">Visit</th>
                    <th class="th-green">Claim</th>
                    <th class="th-green">ค่าบริการ</th>                    
                    <th class="th-green">เรียกเก็บ</th>
                    <th class="th-green">ชดเชย</th>   
                    <th class="th-orange">Visit</th>
                    <th class="th-orange">Claim</th>
                    <th class="th-orange">ค่าบริการ</th>                    
                    <th class="th-orange">เรียกเก็บ</th>
                    <th class="th-orange">ชดเชย</th>                    
                  </tr>    
                </thead>           
                <tbody>
                  @foreach($total_10987 as $row) 
                  <tr>
                    <td align="center" width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs) }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_ppfs_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs_receive,2) }}</td>  
                    <td align="right">{{ number_format($row->visit_ucs_cr) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_cr_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_uccr_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr_receive,2) }}</td> 
                    <td align="right">{{ number_format($row->visit_ucs_herb) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_herb_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_herb,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_herb_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_herb_receive,2) }}</td>                     
                  </tr>             
                  @endforeach    
                </tbody>
                <tfoot>
                  <tr class="tr-total">
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right">{{number_format($total_10987->sum('visit_ppfs'))}}</td>
                    <td align="right">{{number_format($total_10987->sum('visit_ppfs_claim'))}}</td>
                    <td align="right">{{number_format($total_10987->sum('inc_ppfs'),2)}}</td>                    
                    <td align="right">{{number_format($total_10987->sum('inc_ppfs_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10987->sum('inc_ppfs_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10987->sum('visit_ucs_cr'))}}</td>
                    <td align="right">{{number_format($total_10987->sum('visit_ucs_cr_claim'))}}</td>
                    <td align="right">{{number_format($total_10987->sum('inc_uccr'),2)}}</td>                    
                    <td align="right">{{number_format($total_10987->sum('inc_uccr_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10987->sum('inc_uccr_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10987->sum('visit_ucs_herb'))}}</td>
                    <td align="right">{{number_format($total_10987->sum('visit_ucs_herb_claim'))}}</td>
                    <td align="right">{{number_format($total_10987->sum('inc_herb'),2)}}</td>                    
                    <td align="right">{{number_format($total_10987->sum('inc_herb_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10987->sum('inc_herb_receive'),2)}}</td>
                  </tr>   
                </tfoot>
              </table>
            </div>
          </div> 
        </div>
        <!-- 10988 -->
        <div class="tab-pane fade" id="pane-10988" role="tabpanel" aria-labelledby="tab-10988" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-nowrap">
              <h6 class="fw-bold m-0 text-truncate"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10988] ข้อมูลจัดเก็บรายได้ โรงพยาบาลเสนางคนิคม ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                <span class="text-secondary small">Update {{$update_at10988}}</span>
                <div id="btn-10988" class="btn-container-contents"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10988" class="table custom-table table-hover align-middle my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>                   
                    <th class="th-blue" colspan="5">PP Fee Schedule</th> 
                    <th class="th-green" colspan="5">UCS - บริการเฉพาะ (CR)</th>
                    <th class="th-orange" colspan="5">UCS - สมุนไพร 32 รายการ</th>  
                  </tr>    
                  <tr> 
                    <th class="th-blue">Visit</th>
                    <th class="th-blue">Claim</th>
                    <th class="th-blue">ค่าบริการ</th>                    
                    <th class="th-blue">เรียกเก็บ</th>
                    <th class="th-blue">ชดเชย</th>   
                    <th class="th-green">Visit</th>
                    <th class="th-green">Claim</th>
                    <th class="th-green">ค่าบริการ</th>                    
                    <th class="th-green">เรียกเก็บ</th>
                    <th class="th-green">ชดเชย</th>   
                    <th class="th-orange">Visit</th>
                    <th class="th-orange">Claim</th>
                    <th class="th-orange">ค่าบริการ</th>                    
                    <th class="th-orange">เรียกเก็บ</th>
                    <th class="th-orange">ชดเชย</th>                    
                  </tr>    
                </thead>           
                <tbody>
                  @foreach($total_10988 as $row) 
                  <tr>
                    <td align="center" width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs) }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_ppfs_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs_receive,2) }}</td>  
                    <td align="right">{{ number_format($row->visit_ucs_cr) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_cr_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_uccr_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr_receive,2) }}</td> 
                    <td align="right">{{ number_format($row->visit_ucs_herb) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_herb_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_herb,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_herb_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_herb_receive,2) }}</td>                     
                  </tr>             
                  @endforeach    
                </tbody>
                <tfoot>
                  <tr class="tr-total">
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right">{{number_format($total_10988->sum('visit_ppfs'))}}</td>
                    <td align="right">{{number_format($total_10988->sum('visit_ppfs_claim'))}}</td>
                    <td align="right">{{number_format($total_10988->sum('inc_ppfs'),2)}}</td>                    
                    <td align="right">{{number_format($total_10988->sum('inc_ppfs_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10988->sum('inc_ppfs_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10988->sum('visit_ucs_cr'))}}</td>
                    <td align="right">{{number_format($total_10988->sum('visit_ucs_cr_claim'))}}</td>
                    <td align="right">{{number_format($total_10988->sum('inc_uccr'),2)}}</td>                    
                    <td align="right">{{number_format($total_10988->sum('inc_uccr_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10988->sum('inc_uccr_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10988->sum('visit_ucs_herb'))}}</td>
                    <td align="right">{{number_format($total_10988->sum('visit_ucs_herb_claim'))}}</td>
                    <td align="right">{{number_format($total_10988->sum('inc_herb'),2)}}</td>                    
                    <td align="right">{{number_format($total_10988->sum('inc_herb_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10988->sum('inc_herb_receive'),2)}}</td>
                  </tr>   
                </tfoot>
              </table>
            </div>
          </div>
        <!-- END 10988 -->                
        </div>

        <!-- 10989 -->
        <div class="tab-pane fade" id="pane-10989" role="tabpanel" aria-labelledby="tab-10989" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-nowrap">
              <h6 class="fw-bold m-0 text-truncate"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10989] ข้อมูลจัดเก็บรายได้ โรงพยาบาลหัวตะพาน ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                <span class="text-secondary small">Update {{$update_at10989}}</span>
                <div id="btn-10989" class="btn-container-contents"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10989" class="table custom-table table-hover align-middle my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>                   
                    <th class="th-blue" colspan="5">PP Fee Schedule</th> 
                    <th class="th-green" colspan="5">UCS - บริการเฉพาะ (CR)</th>
                    <th class="th-orange" colspan="5">UCS - สมุนไพร 32 รายการ</th>  
                  </tr>    
                  <tr> 
                    <th class="th-blue">Visit</th>
                    <th class="th-blue">Claim</th>
                    <th class="th-blue">ค่าบริการ</th>                    
                    <th class="th-blue">เรียกเก็บ</th>
                    <th class="th-blue">ชดเชย</th>   
                    <th class="th-green">Visit</th>
                    <th class="th-green">Claim</th>
                    <th class="th-green">ค่าบริการ</th>                    
                    <th class="th-green">เรียกเก็บ</th>
                    <th class="th-green">ชดเชย</th>   
                    <th class="th-orange">Visit</th>
                    <th class="th-orange">Claim</th>
                    <th class="th-orange">ค่าบริการ</th>                    
                    <th class="th-orange">เรียกเก็บ</th>
                    <th class="th-orange">ชดเชย</th>                    
                  </tr>    
                </thead>           
                <tbody>
                  @foreach($total_10989 as $row) 
                  <tr>
                    <td align="center" width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs) }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_ppfs_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs_receive,2) }}</td>  
                    <td align="right">{{ number_format($row->visit_ucs_cr) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_cr_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_uccr_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr_receive,2) }}</td> 
                    <td align="right">{{ number_format($row->visit_ucs_herb) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_herb_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_herb,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_herb_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_herb_receive,2) }}</td>                     
                  </tr>             
                  @endforeach    
                </tbody>
                <tfoot>
                  <tr class="tr-total">
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right">{{number_format($total_10989->sum('visit_ppfs'))}}</td>
                    <td align="right">{{number_format($total_10989->sum('visit_ppfs_claim'))}}</td>
                    <td align="right">{{number_format($total_10989->sum('inc_ppfs'),2)}}</td>                    
                    <td align="right">{{number_format($total_10989->sum('inc_ppfs_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10989->sum('inc_ppfs_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10989->sum('visit_ucs_cr'))}}</td>
                    <td align="right">{{number_format($total_10989->sum('visit_ucs_cr_claim'))}}</td>
                    <td align="right">{{number_format($total_10989->sum('inc_uccr'),2)}}</td>                    
                    <td align="right">{{number_format($total_10989->sum('inc_uccr_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10989->sum('inc_uccr_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10989->sum('visit_ucs_herb'))}}</td>
                    <td align="right">{{number_format($total_10989->sum('visit_ucs_herb_claim'))}}</td>
                    <td align="right">{{number_format($total_10989->sum('inc_herb'),2)}}</td>                    
                    <td align="right">{{number_format($total_10989->sum('inc_herb_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10989->sum('inc_herb_receive'),2)}}</td>
                  </tr>   
                </tfoot>
              </table>
            </div>
          </div> 
        <!-- END 10989 -->          
        </div>

        <!-- 10990 -->
        <div class="tab-pane fade" id="pane-10990" role="tabpanel" aria-labelledby="tab-10990" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-nowrap">
              <h6 class="fw-bold m-0 text-truncate"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10990] ข้อมูลจัดเก็บรายได้ โรงพยาบาลลืออำนาจ ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                <span class="text-secondary small">Update {{$update_at10990}}</span>
                <div id="btn-10990" class="btn-container-contents"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10990" class="table custom-table table-hover align-middle my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>                   
                    <th class="th-blue" colspan="5">PP Fee Schedule</th> 
                    <th class="th-green" colspan="5">UCS - บริการเฉพาะ (CR)</th>
                    <th class="th-orange" colspan="5">UCS - สมุนไพร 32 รายการ</th>  
                  </tr>    
                  <tr> 
                    <th class="th-blue">Visit</th>
                    <th class="th-blue">Claim</th>
                    <th class="th-blue">ค่าบริการ</th>                    
                    <th class="th-blue">เรียกเก็บ</th>
                    <th class="th-blue">ชดเชย</th>   
                    <th class="th-green">Visit</th>
                    <th class="th-green">Claim</th>
                    <th class="th-green">ค่าบริการ</th>                    
                    <th class="th-green">เรียกเก็บ</th>
                    <th class="th-green">ชดเชย</th>   
                    <th class="th-orange">Visit</th>
                    <th class="th-orange">Claim</th>
                    <th class="th-orange">ค่าบริการ</th>                    
                    <th class="th-orange">เรียกเก็บ</th>
                    <th class="th-orange">ชดเชย</th>                    
                  </tr>    
                </thead>           
                <tbody>
                  @foreach($total_10990 as $row) 
                  <tr>
                    <td align="center" width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs) }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_ppfs_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs_receive,2) }}</td>  
                    <td align="right">{{ number_format($row->visit_ucs_cr) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_cr_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_uccr_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr_receive,2) }}</td> 
                    <td align="right">{{ number_format($row->visit_ucs_herb) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_herb_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_herb,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_herb_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_herb_receive,2) }}</td>                     
                  </tr>             
                  @endforeach    
                </tbody>
                <tfoot>
                  <tr class="tr-total">
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right">{{number_format($total_10990->sum('visit_ppfs'))}}</td>
                    <td align="right">{{number_format($total_10990->sum('visit_ppfs_claim'))}}</td>
                    <td align="right">{{number_format($total_10990->sum('inc_ppfs'),2)}}</td>                    
                    <td align="right">{{number_format($total_10990->sum('inc_ppfs_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10990->sum('inc_ppfs_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10990->sum('visit_ucs_cr'))}}</td>
                    <td align="right">{{number_format($total_10990->sum('visit_ucs_cr_claim'))}}</td>
                    <td align="right">{{number_format($total_10990->sum('inc_uccr'),2)}}</td>                    
                    <td align="right">{{number_format($total_10990->sum('inc_uccr_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10990->sum('inc_uccr_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10990->sum('visit_ucs_herb'))}}</td>
                    <td align="right">{{number_format($total_10990->sum('visit_ucs_herb_claim'))}}</td>
                    <td align="right">{{number_format($total_10990->sum('inc_herb'),2)}}</td>                    
                    <td align="right">{{number_format($total_10990->sum('inc_herb_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10990->sum('inc_herb_receive'),2)}}</td>
                  </tr>   
                </tfoot>
              </table>
            </div>
          </div>
        <!-- END 10990 --> 
        </div>

        <!-- 10703  -->
        <div class="tab-pane fade" id="pane-10703" role="tabpanel" aria-labelledby="tab-10703" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-nowrap">
              <h6 class="fw-bold m-0 text-truncate"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10703] ข้อมูลจัดเก็บรายได้ โรงพยาบาลอำนาจเจริญ ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                <span class="text-secondary small">Update {{$update_at10703}}</span>
                <div id="btn-10703" class="btn-container-contents"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10703" class="table custom-table table-hover align-middle my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>                   
                    <th class="th-blue" colspan="5">PP Fee Schedule</th> 
                    <th class="th-green" colspan="5">UCS - บริการเฉพาะ (CR)</th>
                    <th class="th-orange" colspan="5">UCS - สมุนไพร 32 รายการ</th>  
                  </tr>    
                  <tr> 
                    <th class="th-blue">Visit</th>
                    <th class="th-blue">Claim</th>
                    <th class="th-blue">ค่าบริการ</th>                    
                    <th class="th-blue">เรียกเก็บ</th>
                    <th class="th-blue">ชดเชย</th>   
                    <th class="th-green">Visit</th>
                    <th class="th-green">Claim</th>
                    <th class="th-green">ค่าบริการ</th>                    
                    <th class="th-green">เรียกเก็บ</th>
                    <th class="th-green">ชดเชย</th>   
                    <th class="th-orange">Visit</th>
                    <th class="th-orange">Claim</th>
                    <th class="th-orange">ค่าบริการ</th>                    
                    <th class="th-orange">เรียกเก็บ</th>
                    <th class="th-orange">ชดเชย</th>                    
                  </tr>    
                </thead>           
                <tbody>
                  @foreach($total_10703 as $row) 
                  <tr>
                    <td align="center" width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs) }}</td>
                    <td align="right">{{ number_format($row->visit_ppfs_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_ppfs_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_ppfs_receive,2) }}</td>  
                    <td align="right">{{ number_format($row->visit_ucs_cr) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_cr_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_uccr_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_uccr_receive,2) }}</td> 
                    <td align="right">{{ number_format($row->visit_ucs_herb) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_herb_claim) }}</td>
                    <td align="right">{{ number_format($row->inc_herb,2) }}</td>                    
                    <td align="right">{{ number_format($row->inc_herb_claim,2) }}</td>
                    <td align="right">{{ number_format($row->inc_herb_receive,2) }}</td>                     
                  </tr>             
                  @endforeach    
                </tbody>
                <tfoot>
                  <tr class="tr-total">
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right">{{number_format($total_10703->sum('visit_ppfs'))}}</td>
                    <td align="right">{{number_format($total_10703->sum('visit_ppfs_claim'))}}</td>
                    <td align="right">{{number_format($total_10703->sum('inc_ppfs'),2)}}</td>                    
                    <td align="right">{{number_format($total_10703->sum('inc_ppfs_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10703->sum('inc_ppfs_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10703->sum('visit_ucs_cr'))}}</td>
                    <td align="right">{{number_format($total_10703->sum('visit_ucs_cr_claim'))}}</td>
                    <td align="right">{{number_format($total_10703->sum('inc_uccr'),2)}}</td>                    
                    <td align="right">{{number_format($total_10703->sum('inc_uccr_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10703->sum('inc_uccr_receive'),2)}}</td>
                    <td align="right">{{number_format($total_10703->sum('visit_ucs_herb'))}}</td>
                    <td align="right">{{number_format($total_10703->sum('visit_ucs_herb_claim'))}}</td>
                    <td align="right">{{number_format($total_10703->sum('inc_herb'),2)}}</td>                    
                    <td align="right">{{number_format($total_10703->sum('inc_herb_claim'),2)}}</td>
                    <td align="right">{{number_format($total_10703->sum('inc_herb_receive'),2)}}</td>
                  </tr>   
                </tfoot>
              </table>
            </div>
          </div>
        <!-- END 10703 --> 
        </div>

      </div>
    </div>
  </section>

<!-- แจังเตือน login--------------------------------------------------------------------------------------------------- -->
   @if($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: '{{ $errors->first() }}', // แสดง error แรก
            confirmButtonText: 'ตกลง'
        });
    </script>
    @endif

@endsection

<!-- script datatable  ---------------------------------------------------------------------------------------->
@push('scripts')
  <script>
    $(function () {
      const hospitals = [
        { id: '10985', name: 'โรงพยาบาลชานุมาน' },
        { id: '10986', name: 'โรงพยาบาลปทุมราชวงศา' },
        { id: '10987', name: 'โรงพยาบาลพนา' },
        { id: '10988', name: 'โรงพยาบาลเสนางคนิคม' },
        { id: '10989', name: 'โรงพยาบาลหัวตะพาน' },
        { id: '10990', name: 'โรงพยาบาลลืออำนาจ' },
        { id: '10703', name: 'โรงพยาบาลอำนาจเจริญ' }
      ];

      hospitals.forEach(hosp => {
        const table = $(`#table${hosp.id}`).DataTable({
          "dom": "Brt",
          "destroy": true,
          "responsive": false,
          "autoWidth": false,
          "pageLength": 13,
          "lengthChange": false,
          "searching": false,
          "ordering": false,
          "info": false,
          "paging": false,
          "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Thai.json"
          },
          "buttons": [
            {
              extend: 'excelHtml5',
              text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
              className: 'btn btn-excel btn-sm rounded-pill px-3 shadow-sm',
              title: `ข้อมูลจัดเก็บรายได้_${hosp.name}_${hosp.id}_{{$budget_year}}`
            }
          ]
        });
        setTimeout(() => {
          table.buttons().container().detach().appendTo($(`#btn-${hosp.id}`));
        }, 10);
      });
    });
  </script>
@endpush

