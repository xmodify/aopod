@extends('layouts.app')

@section('title', 'Dashboard | AOPOD')

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
    1) Operation – Deep Green Glass
  =================================*/
  .glass-op {
    background: linear-gradient(135deg,
                rgba(0,120,50,0.55),
                rgba(0,180,90,0.28));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.35);
    border-left: 6px solid #007832 !important;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    color: #ffffff;
  }

  .glass-op .glass-title,
  .glass-op .glass-number {
    color: #ffffff !important;
  }

  /* ================================
    2) Refer Out – Magenta Glass
  =================================*/
  .glass-referout {
    background: linear-gradient(135deg,
                rgba(180,40,160,0.55),
                rgba(255,115,195,0.25));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.35);
    border-left: 6px solid #b428a0 !important;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    color: #fff0ff;
  }
  .glass-referout .glass-title,
  .glass-referout .glass-number {
    color: #fff0ff !important;
  }

  /* ================================
    3) Refer In – Pink Red Glass
  =================================*/
  .glass-referin {
    background: linear-gradient(135deg,
                rgba(220,40,90,0.55),
                rgba(255,130,160,0.28));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.35);
    border-left: 6px solid #dc285a !important;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    color: #ffe6ea;
  }
  .glass-referin .glass-title,
  .glass-referin .glass-number {
    color: #ffffff !important;
  }


  /* ================================
    4) Refer Back – Warm Yellow Glass
  =================================*/
  .glass-referback {
    background: linear-gradient(135deg,
                rgba(255,180,60,0.55),
                rgba(255,225,120,0.28));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.35);
    border-left: 6px solid #ffb43c !important;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    color: #5e4800;
  }
  .glass-referback .glass-title,
  .glass-referback .glass-number {
    color: #5e4800 !important;
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
  <!-- ข้อมูลเตียง ---------------------------------------------------------------------------------------- -->
  <section id="bed" class="pb-2">
    <div class="container-fluid">
      <div class="row g-3">

        <!-- กำลังรักษาอยู่ (แดงพาสเทล) --------------------------------------------------------------------------------------------->
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#AdmiitDetailModal"
            class="text-decoration-none text-dark">

            <div class="card card-theme-admit card-hover glass p-3 h-100">

              <!-- ส่วนหัว -->
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-danger fw-bold">กำลังรักษาอยู่</h6>

                <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                  <i class="fa-solid fa-hospital-user text-danger fs-4"></i>
                </div>
              </div>

              <!-- ตัวเลข -->
              <div class="d-flex justify-content-between text-center">

                <div class="flex-fill px-1">
                  <div class="small text-secondary">จำนวนเตียง</div>
                  <div class="fw-bold text-primary" style="font-size:1.9rem;">
                    {{ $fmtInt($total_bed_qty ?? 0) }}
                  </div>
                  <i class="fa-solid fa-bed text-primary"></i>
                </div>

                <div class="vr mx-2 d-none d-sm-block"></div>

                <div class="flex-fill px-1">
                  <div class="small text-secondary">Admit</div>
                  <div class="fw-bold text-danger" style="font-size:1.9rem;">
                    {{ $fmtInt($total_bed_use ?? 0) }}
                  </div>
                  <i class="fa-solid fa-bed-pulse text-danger"></i>
                </div>

                <div class="vr mx-2 d-none d-sm-block"></div>

                <div class="flex-fill px-1">
                  <div class="small text-secondary">เตียงว่าง</div>
                  <div class="fw-bold text-success" style="font-size:1.9rem;">
                    {{ $fmtInt($total_bed_empty ?? 0) }}
                  </div>
                  <i class="fa-solid fa-bed text-success"></i>
                </div>

              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / กรอบเล็ก) --}}
        <div class="modal fade" id="AdmiitDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3" 
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="fa-solid fa-bed-pulse fs-5"></i> ข้อมูลเตียง
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <!-- Body -->
              <div class="modal-body py-3">

                {{-- ✅ หน้ารวมโรงพยาบาล --}}
                <div id="hospital-list">
                  <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0" 
                        style="background-color: #ffffff; border-radius: 0.75rem;">
                    <thead style="background-color:#d9e8fb;">
                      <tr class="text-center text-primary fw-semibold">
                        <th>รหัส</th>
                        <th>ชื่อโรงพยาบาล</th>
                        <th>จำนวนเตียง</th>
                        <th>Admit</th>
                        <th>เตียงว่าง</th>
                        <th>อัตราใช้เตียง (%)</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($ipd_bed_dep as $h)
                        @php
                          $bed_occupancy = $h->bed_qty > 0 ? ($h->bed_use / $h->bed_qty) * 100 : 0;
                          if ($bed_occupancy < 60) {
                            $rate_class = 'text-primary fw-semibold';
                          } elseif ($bed_occupancy < 80) {
                            $rate_class = 'text-warning fw-semibold';
                          } else {
                            $rate_class = 'text-danger fw-semibold';
                          }
                        @endphp

                        <tr>
                          <td align="right" class="text-secondary">{{ $h->hospcode }}</td>
                          <td>
                           <a href="#" 
                              class="fw-semibold text-dark text-decoration-none hosp-detail-link" 
                              data-hospcode="{{ $h->hospcode }}"
                              data-hospname="{{ $h->hospname }}">
                              {{ $h->hospname }}
                            </a><br>
                            <small class="text-muted">
                              {{ \Carbon\Carbon::parse($h->updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') }} น.
                            </small>
                          </td>
                          <td align="right" class="text-primary">{{ number_format($h->bed_qty) }}</td>
                          <td align="right" class="text-danger">{{ number_format($h->bed_use) }}</td>
                          <td align="right" class="fw-bold text-success">
                            {{ number_format($h->bed_qty - $h->bed_use) }}
                          </td>
                          <td align="right" class="{{ $rate_class }}">
                            {{ number_format($bed_occupancy, 2) }}%
                          </td>
                        </tr>
                      @endforeach

                      {{-- รวม --}}
                      @php
                        $sum_bed_qty = $ipd_bed_dep->sum('bed_qty');
                        $sum_bed_use = $ipd_bed_dep->sum('bed_use');
                        $total_occupancy = $sum_bed_qty > 0 ? ($sum_bed_use / $sum_bed_qty) * 100 : 0;
                      @endphp
                      <tr style="background-color:#eef4fb;" class="fw-bold text-end">
                        <td colspan="2" class="text-center text-dark">รวมทั้งหมด</td>
                        <td class="text-primary">{{ number_format($sum_bed_qty) }}</td>
                        <td class="text-danger">{{ number_format($sum_bed_use) }}</td>
                        <td class="text-success">{{ number_format($sum_bed_qty - $sum_bed_use) }}</td>
                        <td class="text-dark">{{ number_format($total_occupancy, 2) }}%</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                {{-- ✅ หน้ารายละเอียดเตียง (ซ่อนเริ่มต้น) --}}
                <div id="bed-detail" style="display: none;">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 id="modal-hospname" class="fw-bold text-success mb-0"></h6>
                    <button id="btn-back" class="btn btn-outline-primary btn-sm rounded-pill">
                      <i class="bi bi-arrow-left"></i> กลับ
                    </button>
                  </div>
                  <table class="table table-sm table-bordered align-middle" id="bedDetailTable">
                    <thead class="table-primary text-center">
                      <tr>
                        <th>รหัสเตียง</th>
                        <th>ชื่อแผนก</th>
                        <th>จำนวนเตียง</th>
                        <th>Admit</th>
                        <th>เตียงว่าง</th>
                        <th>อัตราใช้เตียง (%)</th>
                      </tr>
                    </thead>

                    <tbody class="text-center">
                      <tr><td colspan="6" class="text-muted">เลือกโรงพยาบาลเพื่อดูรายละเอียด...</td></tr>
                    </tbody>

                    <tfoot class="table-light text-end fw-bold">
                      <tr>
                        <td colspan="2" class="text-center">รวม</td>
                        <td id="sum-bed"></td>
                        <td id="sum-use"></td>
                        <td id="sum-empty"></td>
                        <td id="sum-rate" class="text-primary"></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>

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

        <script>
          document.addEventListener("DOMContentLoaded", () => {

            const hospitalList = document.getElementById('hospital-list');
            const bedDetail = document.getElementById('bed-detail');
            const btnBack = document.getElementById('btn-back');
            const tbody = document.querySelector('#bedDetailTable tbody');
            const sumBed = document.getElementById('sum-bed');
            const sumUse = document.getElementById('sum-use');
            const sumEmpty = document.getElementById('sum-empty');
            const sumRate = document.getElementById('sum-rate');
            const hospNameEl = document.getElementById('modal-hospname');

            console.log("✅ BedDetail JS Loaded");

            // ✅ คลิกชื่อโรงพยาบาลเพื่อดูรายละเอียดเตียง
            document.addEventListener('click', function (e) {
              const link = e.target.closest('.hosp-detail-link');
              if (!link) return;

              e.preventDefault();
              const hospcode = link.dataset.hospcode;
              const hospname = link.dataset.hospname;

              console.log("🏥 Clicked:", hospcode, hospname);
              hospNameEl.innerText = hospname;
              tbody.innerHTML = `<tr><td colspan="6" class="text-muted">กำลังโหลดข้อมูล...</td></tr>`;

              // ✅ ดึงข้อมูลจาก Controller
              fetch(`{{ url('web/bed_dep') }}/${hospcode}`)
                .then(res => {
                  if (!res.ok) throw new Error(`HTTP ${res.status}`);
                  return res.json();
                })
                .then(data => {
                  tbody.innerHTML = '';

                  if (data.beds && data.beds.length > 0) {
                    data.beds.forEach(b => {
                      const empty = b.bed_qty - b.bed_use;

                      // ✅ ตั้งสีตามอัตราครองเตียง
                      let rateColor = 'text-success';
                      if (b.bed_rate >= 80) rateColor = 'text-danger fw-bold';
                      else if (b.bed_rate >= 60) rateColor = 'text-warning fw-bold';

                      tbody.innerHTML += `
                        <tr>
                          <td class="text-center">${b.bed_code}</td>
                          <td class="text-start">${b.bed_name ?? '-'}</td>
                          <td class="text-end">${b.bed_qty}</td>
                          <td class="text-end text-danger">${b.bed_use}</td>
                          <td class="text-end text-success">${empty}</td>
                          <td class="text-end ${rateColor}">${b.bed_rate}%</td>
                        </tr>`;
                    });
                  } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-muted">ไม่พบข้อมูลเตียง</td></tr>`;
                  }

                  // ✅ อัปเดตผลรวม
                  sumBed.innerText = (data.summary?.total || 0).toLocaleString();
                  sumUse.innerText = (data.summary?.used || 0).toLocaleString();
                  sumEmpty.innerText = (data.summary?.empty || 0).toLocaleString();
                  sumRate.innerText = `${data.summary?.rate || 0}%`;

                  // ✅ สลับหน้า
                  hospitalList.style.display = 'none';
                  bedDetail.style.display = 'block';
                })
                .catch(err => {
                  console.error("❌ Fetch error:", err);
                  tbody.innerHTML = `<tr><td colspan="6" class="text-danger">โหลดข้อมูลไม่สำเร็จ</td></tr>`;
                });
            });

            // ✅ ปุ่ม "กลับ" เพื่อกลับมาหน้ารวม
            btnBack.addEventListener('click', () => {
              bedDetail.style.display = 'none';
              hospitalList.style.display = 'block';
            });
          });
        </script>
      <!-- Block แยกแต่ละ รพ --------------------------------------------------------------------------------------------->
        @foreach($bedData as $hospcode => $data)
        <div class="col-12 col-sm-6 col-xl-3" >
            <div  class="card card-theme-bed card-hover glass p-3 h-100">

                <!-- หัวข้อ Card -->
                <h6 class="mb-3 fw-bold text-primary d-flex justify-content-between align-items-center">
                    <span>ข้อมูลเตียง{{ $data['hospname'] }}</span>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                      <i class="fa-solid fa-bed-pulse text-danger fs-5"></i>
                    </div>
                </h6>
                <!-- Header -->
                <div class="row mb-1">
                    <div class="col-4 small text-secondary">แผนก</div>
                    <div class="col-2 small text-secondary text-center">จำนวนเตียง</div>
                    <div class="col-2 small text-secondary text-center">Admit</div>
                    <div class="col-2 small text-secondary text-center">เตียงว่าง</div>
                    <div class="col-2 small text-secondary text-center">อัตราใช้เตียง</div>
                </div>
                <hr class="my-1">
                <!-- รายการเตียงแต่ละแผนก -->
                @foreach($data['beds'] as $b)
                    @php 
                        $empty = $b->bed_qty - $b->bed_use;
                    @endphp
                    <div class="row mb-1 small align-items-center">
                        <div class="col-4 fw-bold" >
                            {{ $b->bed_name }}
                        </div>
                        <div class="col-2 text-center fw-bold">
                            {{ $b->bed_qty }}
                        </div>
                        <div class="col-2 text-center fw-bold text-danger">
                            {{ $b->bed_use }}
                        </div>
                        <div class="col-2 text-center fw-bold text-success">
                            {{ $empty }}
                        </div>
                        <!-- อัตราครองเตียงพร้อมสี (เขียว / ส้ม / แดง) -->
                        <div class="col-2 text-center fw-bold"
                            @if($b->bed_rate >= 80)
                                style="color:#d32f2f;"      {{-- แดงเข้ม --}}
                            @elseif($b->bed_rate >= 60)
                                style="color:#ffc107;"      {{-- เหลือง --}}
                            @else
                                style="color:#1976d2;"      {{-- น้ำเงิน --}}
                            @endif
                        >
                            {{ $b->bed_rate }}%
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endforeach        
      </div>
    </div>
  </section>
  <br>
  <!-- แถว 2 จำนวน 4 Block -->
  <section id="summary" class="pb-2">
    <div class="container-fluid">      
      <div class="row g-3">
        
        <!-- ผ่าตัด Operation  --------------------------------------------------------------------------------------------->
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#OperationDetailModal" class="text-decoration-none text-dark">
            <div class="glass-card glass-op card-hover border-0">
              <div class="glass-icon"><i class="fa-solid fa-kit-medical"></i></div>
              <div class="glass-title"><h6>ผ่าตัด วันนี้</h6></div>
              <div class="glass-number fs-2">
                {{ $fmtInt($visit_operation) }}
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="OperationDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-arrow-left-right me-2"></i> ผ่าตัด วันนี้
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th class="text-center">รหัส</th>
                      <th class="text-center">ชื่อโรงพยาบาล</th>
                      <th>จำนวนผ่าตัด</th>                      
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_operation) }}</td>                        
                      </tr>
                    @endforeach
                    {{-- แถวผลรวม --}}
                    <tr style="background-color:#eef4fb;" class="fw-bold text-end">
                      <td colspan="2" class="text-center text-dark">รวมทั้งหมด</td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_operation')) }}</td>                    
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

        <!-- Refer Out ------------------------------------------------------------------------------------------>
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#ReferOutDetailModal" class="text-decoration-none text-dark">
            <div class="glass-card glass-referout card-hover border-0">
              <div class="glass-icon"><i class="fa-solid fa-truck-medical"></i></div>
              <div class="glass-title"><h6>Refer Out วันนี้</h6></div>
              <div class="d-flex justify-content-between text-center mt-1">
                <div class="flex-fill">
                  <div class="small">OPD</div>
                  <div class="glass-number fs-4">
                    {{ $fmtInt($visit_referout_inprov + $visit_referout_outprov) }}
                  </div>
                </div>
                <div class="vr mx-2 d-none d-sm-block" style="opacity:0.4;"></div>
                <div class="flex-fill">
                  <div class="small">IPD</div>
                  <div class="glass-number fs-4">
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

        <!-- Refer In  --------------------------------------------------------------------------------------->
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#ReferInDetailModal" class="text-decoration-none text-dark">
            <div class="glass-card glass-referin card-hover border-0">
              <div class="glass-icon"><i class="fa-solid fa-truck-medical"></i></div>
              <div class="glass-title"><h6>Refer In วันนี้</h6></div>
              <div class="d-flex justify-content-between text-center mt-1">
                <div class="flex-fill">
                  <div class="small">OPD</div>
                  <div class="glass-number fs-4">
                    {{ $fmtInt($visit_referin_inprov + $visit_referin_outprov) }}
                  </div>
                </div>
                <div class="vr mx-2 d-none d-sm-block" style="opacity:0.4;"></div>
                <div class="flex-fill">
                  <div class="small">IPD</div>
                  <div class="glass-number fs-4">
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
                      <td class="text-success">
                        {{ number_format($hospitalSummary->sum('visit_referin_outprov')) }}
                      </td>
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
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#ReferBackDetailModal" class="text-decoration-none text-dark">
            <div class="glass-card glass-referback card-hover border-0">
              <div class="glass-icon"><i class="fa-solid fa-truck-medical"></i></div>
              <div class="glass-title"><h6>Refer Back วันนี้</h6></div>
              <div class="d-flex justify-content-between text-center mt-1">
                <div class="flex-fill">
                  <div class="small">ในจังหวัด</div>
                  <div class="glass-number fs-4">
                    {{ $fmtInt($visit_referback_inprov) }}
                  </div>
                </div>
                <div class="vr mx-2 d-none d-sm-block" style="opacity:0.4;"></div>
                <div class="flex-fill">
                  <div class="small">ต่างจังหวัด</div>
                  <div class="glass-number fs-4">
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
                      <th class="text-center">รหัส</th>
                      <th class="text-center">ชื่อโรงพยาบาล</th>
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

        {{-- -------------------------------------------------------------------------------------------------------------- --}}
      </div>
    </div>  
  </section>

<hr>

  {{-- เลือกปีงบประมาณ ----------------------------------------------------------------------------------------------------------}}
  <section id="summary" class="pb-2">
      <div class="container-fluid">
        <form method="POST" action="{{ route('web.index') }}" enctype="multipart/form-data">
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
      <ul class="nav nav-pills overflow-auto flex-nowrap" id="hospPills" role="tablist">
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link active" id="tab-10985" data-bs-toggle="pill" data-bs-target="#pane-10985" type="button" role="tab" aria-controls="pane-10985" aria-selected="true">
            รพ.ชานุมาน
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10986" data-bs-toggle="pill" data-bs-target="#pane-10986" type="button" role="tab" aria-controls="pane-10986" aria-selected="false">
            รพ.ปทุมราชวงศา
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10987" data-bs-toggle="pill" data-bs-target="#pane-10987" type="button" role="tab" aria-controls="pane-10987" aria-selected="false">
            รพ.พนา
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10988" data-bs-toggle="pill" data-bs-target="#pane-10988" type="button" role="tab" aria-controls="pane-10988" aria-selected="false">
            รพ.เสนางคนิคม
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10989" data-bs-toggle="pill" data-bs-target="#pane-10989" type="button" role="tab" aria-controls="pane-10989" aria-selected="false">
            รพ.หัวตะพาน
          </button>
        </li>
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10990" data-bs-toggle="pill" data-bs-target="#pane-10990" type="button" role="tab" aria-controls="pane-10990" aria-selected="false">
            รพ.ลืออำนาจ
          </button>
        </li>    
        <li class="nav-item me-2" role="presentation">
          <button class="nav-link" id="tab-10703" data-bs-toggle="pill" data-bs-target="#pane-10703" type="button" role="tab" aria-controls="pane-10703" aria-selected="false">
            รพ.อำนาจเจริญ
          </button>
        </li>      
      </ul>

      <!-- TAB PANES -->
      <div class="tab-content mt-3" id="hospPillsContent">

        <!-- 10985 -->
        <div class="tab-pane fade show active" id="pane-10985" role="tabpanel" aria-labelledby="tab-10985" tabindex="0">          
          <!-- IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10985] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลชานุมาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10985}}</span>
                <div id="btn-10985-ipd"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10985_ipd" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" rowspan="2">จำนวน AN</th>
                    <th class="th-blue" rowspan="2">วันนอนรวม</th> 
                    <th class="th-green" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="th-green" rowspan="2">Active Base (เตียง)</th>       
                    <th class="th-orange" rowspan="2">AdjRW</th>  
                    <th class="th-orange" rowspan="2">CMI</th>
                    <th class="th-purple" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr> 
                    <th class="th-purple">ค่ารักษารวม</th>
                    <th class="th-purple">ค่า Lab</th>
                    <th class="th-purple">ค่า ยา</th>                 
                  </tr>    
                </thead>
                <tbody>
                  <?php 
                    $sum_an_total = 0; 
                    $sum_admdate = 0;   
                    $sum_adjrw = 0; 
                    $sum_inc_total = 0;  
                    $sum_inc_lab_total = 0;
                    $sum_inc_drug_total = 0;
                    $bed_report = $ipd_10985[0]->bed_report ?? 30; // ค่าเตียงจาก hospital_config
                  ?>  
                  @foreach($ipd_10985 as $row) 
                  <tr>
                    <td align="center">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right" class="fw-bold text-success">{{ number_format($row->bed_occupancy,2) }}%</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right" class="text-primary">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total; $sum_admdate += $row->admdate; $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total; $sum_inc_lab_total += $row->inc_lab_total; $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr class="tr-total">
                    <td align="center">รวม</td>
                    <td align="right">{{number_format($sum_an_total)}}</td>
                    <td align="right">{{number_format($sum_admdate)}}</td>
                    <td align="right">{{number_format($sum_bed_occupancy,2)}}%</td>     
                    <td align="right">{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right">{{number_format($sum_adjrw,4)}}</td>  
                    <td align="right" class="text-primary">{{number_format($sum_cmi,2)}}</td> 
                    <td align="right">{{number_format($sum_inc_total,2)}}</td>
                    <td align="right">{{number_format($sum_inc_lab_total)}}</td>
                    <td align="right">{{number_format($sum_inc_drug_total,2)}}</td>
                  </tr>   
                </tbody>
              </table>
              <div class="row mt-4">
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-primary fw-bold mb-3">📈 อัตราครองเตียง (%)</h6><div id="bed_occupancy_10985"></div></div></div></div>
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-danger fw-bold mb-3">📊 CMI</h6><div id="cmi_chart_10985"></div></div></div></div>
              </div>
            </div>
          </div>
          <br>
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
        <div class="tab-pane fade" id="pane-10986" role="tabpanel" aria-labelledby="tab-10986" tabindex="0">          
          <!-- IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10986] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลปทุมราชวงศา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10986}}</span>
                <div id="btn-10986-ipd"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10986_ipd" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" rowspan="2">จำนวน AN</th>
                    <th class="th-blue" rowspan="2">วันนอนรวม</th> 
                    <th class="th-green" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="th-green" rowspan="2">Active Base (เตียง)</th>       
                    <th class="th-orange" rowspan="2">AdjRW</th>  
                    <th class="th-orange" rowspan="2">CMI</th>
                    <th class="th-purple" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr> 
                    <th class="th-purple">ค่ารักษารวม</th>
                    <th class="th-purple">ค่า Lab</th>
                    <th class="th-purple">ค่า ยา</th>                 
                  </tr>    
                </thead>
                <tbody>
                  <?php 
                    $sum_an_total = 0; $sum_admdate = 0; $sum_adjrw = 0; $sum_inc_total = 0; $sum_inc_lab_total = 0; $sum_inc_drug_total = 0;
                    $bed_report = $ipd_10986[0]->bed_report ?? 30;
                  ?>  
                  @foreach($ipd_10986 as $row) 
                  <tr>
                    <td align="center">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right" class="fw-bold text-success">{{ number_format($row->bed_occupancy,2) }}%</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right" class="text-primary">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total; $sum_admdate += $row->admdate; $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total; $sum_inc_lab_total += $row->inc_lab_total; $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr class="tr-total">
                    <td align="center">รวม</td>
                    <td align="right">{{number_format($sum_an_total)}}</td>
                    <td align="right">{{number_format($sum_admdate)}}</td>
                    <td align="right">{{number_format($sum_bed_occupancy,2)}}%</td>     
                    <td align="right">{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right">{{number_format($sum_adjrw,4)}}</td>  
                    <td align="right" class="text-primary">{{number_format($sum_cmi,2)}}</td> 
                    <td align="right">{{number_format($sum_inc_total,2)}}</td>
                    <td align="right">{{number_format($sum_inc_lab_total)}}</td>
                    <td align="right">{{number_format($sum_inc_drug_total,2)}}</td>
                  </tr>   
                </tbody>
              </table>
              <div class="row mt-4">
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-primary fw-bold mb-3">📈 อัตราครองเตียง (%)</h6><div id="bed_occupancy_10986"></div></div></div></div>
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-danger fw-bold mb-3">📊 CMI</h6><div id="cmi_chart_10986"></div></div></div></div>
              </div>
            </div>
          </div>
          <br>
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
        <div class="tab-pane fade" id="pane-10987" role="tabpanel" aria-labelledby="tab-10987" tabindex="0">          
          <!-- IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10987] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลพนา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10987}}</span>
                <div id="btn-10987-ipd"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10987_ipd" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" rowspan="2">จำนวน AN</th>
                    <th class="th-blue" rowspan="2">วันนอนรวม</th> 
                    <th class="th-green" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="th-green" rowspan="2">Active Base (เตียง)</th>       
                    <th class="th-orange" rowspan="2">AdjRW</th>  
                    <th class="th-orange" rowspan="2">CMI</th>
                    <th class="th-purple" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr> 
                    <th class="th-purple">ค่ารักษารวม</th>
                    <th class="th-purple">ค่า Lab</th>
                    <th class="th-purple">ค่า ยา</th>                 
                  </tr>    
                </thead>
                <tbody>
                  <?php 
                    $sum_an_total = 0; $sum_admdate = 0; $sum_adjrw = 0; $sum_inc_total = 0; $sum_inc_lab_total = 0; $sum_inc_drug_total = 0;
                    $bed_report = $ipd_10987[0]->bed_report ?? 30;
                  ?>  
                  @foreach($ipd_10987 as $row) 
                  <tr>
                    <td align="center">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right" class="fw-bold text-success">{{ number_format($row->bed_occupancy,2) }}%</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right" class="text-primary">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total; $sum_admdate += $row->admdate; $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total; $sum_inc_lab_total += $row->inc_lab_total; $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr class="tr-total">
                    <td align="center">รวม</td>
                    <td align="right">{{number_format($sum_an_total)}}</td>
                    <td align="right">{{number_format($sum_admdate)}}</td>
                    <td align="right">{{number_format($sum_bed_occupancy,2)}}%</td>     
                    <td align="right">{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right">{{number_format($sum_adjrw,4)}}</td>  
                    <td align="right" class="text-primary">{{number_format($sum_cmi,2)}}</td> 
                    <td align="right">{{number_format($sum_inc_total,2)}}</td>
                    <td align="right">{{number_format($sum_inc_lab_total)}}</td>
                    <td align="right">{{number_format($sum_inc_drug_total,2)}}</td>
                  </tr>   
                </tbody>
              </table>
              <div class="row mt-4">
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-primary fw-bold mb-3">📈 อัตราครองเตียง (%)</h6><div id="bed_occupancy_10987"></div></div></div></div>
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-danger fw-bold mb-3">📊 CMI</h6><div id="cmi_chart_10987"></div></div></div></div>
              </div>
            </div>
          </div>
          <br>
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
        <div class="tab-pane fade" id="pane-10988" role="tabpanel" aria-labelledby="tab-10988" tabindex="0">          
          <!-- IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10988] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลเสนางคนิคม</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10988}}</span>
                <div id="btn-10988-ipd"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10988_ipd" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" rowspan="2">จำนวน AN</th>
                    <th class="th-blue" rowspan="2">วันนอนรวม</th> 
                    <th class="th-green" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="th-green" rowspan="2">Active Base (เตียง)</th>       
                    <th class="th-orange" rowspan="2">AdjRW</th>  
                    <th class="th-orange" rowspan="2">CMI</th>
                    <th class="th-purple" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr> 
                    <th class="th-purple">ค่ารักษารวม</th>
                    <th class="th-purple">ค่า Lab</th>
                    <th class="th-purple">ค่า ยา</th>                 
                  </tr>    
                </thead>
                <tbody>
                  <?php 
                    $sum_an_total = 0; $sum_admdate = 0; $sum_adjrw = 0; $sum_inc_total = 0; $sum_inc_lab_total = 0; $sum_inc_drug_total = 0;
                    $bed_report = $ipd_10988[0]->bed_report ?? 30;
                  ?>  
                  @foreach($ipd_10988 as $row) 
                  <tr>
                    <td align="center">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right" class="fw-bold text-success">{{ number_format($row->bed_occupancy,2) }}%</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right" class="text-primary">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total; $sum_admdate += $row->admdate; $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total; $sum_inc_lab_total += $row->inc_lab_total; $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr class="tr-total">
                    <td align="center">รวม</td>
                    <td align="right">{{number_format($sum_an_total)}}</td>
                    <td align="right">{{number_format($sum_admdate)}}</td>
                    <td align="right">{{number_format($sum_bed_occupancy,2)}}%</td>     
                    <td align="right">{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right">{{number_format($sum_adjrw,4)}}</td>  
                    <td align="right" class="text-primary">{{number_format($sum_cmi,2)}}</td> 
                    <td align="right">{{number_format($sum_inc_total,2)}}</td>
                    <td align="right">{{number_format($sum_inc_lab_total)}}</td>
                    <td align="right">{{number_format($sum_inc_drug_total,2)}}</td>
                  </tr>   
                </tbody>
              </table>
              <div class="row mt-4">
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-primary fw-bold mb-3">📈 อัตราครองเตียง (%)</h6><div id="bed_occupancy_10988"></div></div></div></div>
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-danger fw-bold mb-3">📊 CMI</h6><div id="cmi_chart_10988"></div></div></div></div>
              </div>
            </div>
          </div>
          <br>
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
        <div class="tab-pane fade" id="pane-10989" role="tabpanel" aria-labelledby="tab-10989" tabindex="0">          
          <!-- IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10989] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลหัวตะพาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10989}}</span>
                <div id="btn-10989-ipd"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10989_ipd" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" rowspan="2">จำนวน AN</th>
                    <th class="th-blue" rowspan="2">วันนอนรวม</th> 
                    <th class="th-green" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="th-green" rowspan="2">Active Base (เตียง)</th>       
                    <th class="th-orange" rowspan="2">AdjRW</th>  
                    <th class="th-orange" rowspan="2">CMI</th>
                    <th class="th-purple" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr> 
                    <th class="th-purple">ค่ารักษารวม</th>
                    <th class="th-purple">ค่า Lab</th>
                    <th class="th-purple">ค่า ยา</th>                 
                  </tr>    
                </thead>
                <tbody>
                  <?php 
                    $sum_an_total = 0; $sum_admdate = 0; $sum_adjrw = 0; $sum_inc_total = 0; $sum_inc_lab_total = 0; $sum_inc_drug_total = 0;
                    $bed_report = $ipd_10989[0]->bed_report ?? 30;
                  ?>  
                  @foreach($ipd_10989 as $row) 
                  <tr>
                    <td align="center">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right" class="fw-bold text-success">{{ number_format($row->bed_occupancy,2) }}%</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right" class="text-primary">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total; $sum_admdate += $row->admdate; $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total; $sum_inc_lab_total += $row->inc_lab_total; $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr class="tr-total">
                    <td align="center">รวม</td>
                    <td align="right">{{number_format($sum_an_total)}}</td>
                    <td align="right">{{number_format($sum_admdate)}}</td>
                    <td align="right">{{number_format($sum_bed_occupancy,2)}}%</td>     
                    <td align="right">{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right">{{number_format($sum_adjrw,4)}}</td>  
                    <td align="right" class="text-primary">{{number_format($sum_cmi,2)}}</td> 
                    <td align="right">{{number_format($sum_inc_total,2)}}</td>
                    <td align="right">{{number_format($sum_inc_lab_total)}}</td>
                    <td align="right">{{number_format($sum_inc_drug_total,2)}}</td>
                  </tr>   
                </tbody>
              </table>
              <div class="row mt-4">
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-primary fw-bold mb-3">📈 อัตราครองเตียง (%)</h6><div id="bed_occupancy_10989"></div></div></div></div>
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-danger fw-bold mb-3">📊 CMI</h6><div id="cmi_chart_10989"></div></div></div></div>
              </div>
            </div>
          </div>
          <br>
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
        <div class="tab-pane fade" id="pane-10990" role="tabpanel" aria-labelledby="tab-10990" tabindex="0">          
          <!-- IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10990] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลลืออำนาจ</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10990}}</span>
                <div id="btn-10990-ipd"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10990_ipd" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" rowspan="2">จำนวน AN</th>
                    <th class="th-blue" rowspan="2">วันนอนรวม</th> 
                    <th class="th-green" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="th-green" rowspan="2">Active Base (เตียง)</th>       
                    <th class="th-orange" rowspan="2">AdjRW</th>  
                    <th class="th-orange" rowspan="2">CMI</th>
                    <th class="th-purple" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr> 
                    <th class="th-purple">ค่ารักษารวม</th>
                    <th class="th-purple">ค่า Lab</th>
                    <th class="th-purple">ค่า ยา</th>                 
                  </tr>    
                </thead>
                <tbody>
                  <?php 
                    $sum_an_total = 0; $sum_admdate = 0; $sum_adjrw = 0; $sum_inc_total = 0; $sum_inc_lab_total = 0; $sum_inc_drug_total = 0;
                    $bed_report = $ipd_10990[0]->bed_report ?? 30;
                  ?>  
                  @foreach($ipd_10990 as $row) 
                  <tr>
                    <td align="center">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right" class="fw-bold text-success">{{ number_format($row->bed_occupancy,2) }}%</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right" class="text-primary">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total; $sum_admdate += $row->admdate; $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total; $sum_inc_lab_total += $row->inc_lab_total; $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr class="tr-total">
                    <td align="center">รวม</td>
                    <td align="right">{{number_format($sum_an_total)}}</td>
                    <td align="right">{{number_format($sum_admdate)}}</td>
                    <td align="right">{{number_format($sum_bed_occupancy,2)}}%</td>     
                    <td align="right">{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right">{{number_format($sum_adjrw,4)}}</td>  
                    <td align="right" class="text-primary">{{number_format($sum_cmi,2)}}</td> 
                    <td align="right">{{number_format($sum_inc_total,2)}}</td>
                    <td align="right">{{number_format($sum_inc_lab_total)}}</td>
                    <td align="right">{{number_format($sum_inc_drug_total,2)}}</td>
                  </tr>   
                </tbody>
              </table>
              <div class="row mt-4">
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-primary fw-bold mb-3">📈 อัตราครองเตียง (%)</h6><div id="bed_occupancy_10990"></div></div></div></div>
                <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="text-center text-danger fw-bold mb-3">📊 CMI</h6><div id="cmi_chart_10990"></div></div></div></div>
              </div>
            </div>
          </div>
          <br>
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
        <div class="tab-pane fade" id="pane-10703" role="tabpanel" aria-labelledby="tab-10703" tabindex="0">          
          <!-- IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10703] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลอำนาจเจริญ</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10703}}</span>
                <div id="btn-10703-ipd"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10703_ipd" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" rowspan="2">จำนวน AN</th>
                    <th class="th-blue" rowspan="2">วันนอนรวม</th> 
                    <th class="th-green" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="th-green" rowspan="2">Active Base (เตียง)</th>       
                    <th class="th-orange" rowspan="2">AdjRW</th>  
                    <th class="th-orange" rowspan="2">CMI</th>
                    <th class="th-purple" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr> 
                    <th class="th-purple">ค่ารักษารวม</th>
                    <th class="th-purple">ค่า Lab</th>
                    <th class="th-purple">ค่า ยา</th>                 
                  </tr>    
                </thead>
                <tbody>
                  <?php 
                    $sum_an_total = 0; $sum_admdate = 0; $sum_adjrw = 0; $sum_inc_total = 0; $sum_inc_lab_total = 0; $sum_inc_drug_total = 0;
                    $bed_report = $ipd_10703[0]->bed_report ?? 432;
                  ?>  
                  @foreach($ipd_10703 as $row) 
                  <tr>
                    <td align="center">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right" class="fw-bold text-success">{{ number_format($row->bed_occupancy,2) }}%</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right" class="text-primary">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total; $sum_admdate += $row->admdate; $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total; $sum_inc_lab_total += $row->inc_lab_total; $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr class="tr-total">
                    <td align="center">รวม</td>
                    <td align="right">{{number_format($sum_an_total)}}</td>
                    <td align="right">{{number_format($sum_admdate)}}</td>
                    <td align="right">{{number_format($sum_bed_occupancy,2)}}%</td>     
                    <td align="right">{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right">{{number_format($sum_adjrw,4)}}</td>  
                    <td align="right" class="text-primary">{{number_format($sum_cmi,2)}}</td> 
                    <td align="right">{{number_format($sum_inc_total,2)}}</td>
                    <td align="right">{{number_format($sum_inc_lab_total)}}</td>
                    <td align="right">{{number_format($sum_inc_drug_total,2)}}</td>
                  </tr>   
                </tbody>
              </table>
              <div class="row mt-4">
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                      <h6 class="text-center text-primary fw-bold mb-3">📈 อัตราครองเตียง (%)</h6>
                      <div id="bed_occupancy_10703"></div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                      <h6 class="text-center text-danger fw-bold mb-3">📊 CMI</h6>
                      <div id="cmi_chart_10703"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <br>
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
        <!-- END 10703 --> 
        </div>

      <!-- TAB PANES -->
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



<!-- script กราฟ  ---------------------------------------------------------------------------------------->
@push('scripts')
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode($ipd_10985->pluck('month')) !!};
      const bed_occupancy = {!! json_encode($ipd_10985->pluck('bed_occupancy')) !!};
      const cmi = {!! json_encode($ipd_10985->pluck('cmi')) !!};
      // 🩵 กราฟอัตราครองเตียง
      const bedChart = new ApexCharts(document.querySelector("#bed_occupancy_10985"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: bed_occupancy
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#4154f1'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#4154f1'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      bedChart.render();


      // ❤️ กราฟ CMI
      const cmiChart = new ApexCharts(document.querySelector("#cmi_chart_10985"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: cmi
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#ff6384'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#ff6384'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      cmiChart.render();

    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode($ipd_10986->pluck('month')) !!};
      const bed_occupancy = {!! json_encode($ipd_10986->pluck('bed_occupancy')) !!};
      const cmi = {!! json_encode($ipd_10986->pluck('cmi')) !!};
      // 🩵 กราฟอัตราครองเตียง
      const bedChart = new ApexCharts(document.querySelector("#bed_occupancy_10986"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: bed_occupancy
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#4154f1'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#4154f1'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      bedChart.render();


      // ❤️ กราฟ CMI
      const cmiChart = new ApexCharts(document.querySelector("#cmi_chart_10986"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: cmi
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#ff6384'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#ff6384'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      cmiChart.render();

    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode($ipd_10987->pluck('month')) !!};
      const bed_occupancy = {!! json_encode($ipd_10987->pluck('bed_occupancy')) !!};
      const cmi = {!! json_encode($ipd_10987->pluck('cmi')) !!};
      // 🩵 กราฟอัตราครองเตียง
      const bedChart = new ApexCharts(document.querySelector("#bed_occupancy_10987"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: bed_occupancy
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#4154f1'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#4154f1'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      bedChart.render();


      // ❤️ กราฟ CMI
      const cmiChart = new ApexCharts(document.querySelector("#cmi_chart_10987"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: cmi
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#ff6384'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#ff6384'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      cmiChart.render();

    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode($ipd_10988->pluck('month')) !!};
      const bed_occupancy = {!! json_encode($ipd_10988->pluck('bed_occupancy')) !!};
      const cmi = {!! json_encode($ipd_10988->pluck('cmi')) !!};
      // 🩵 กราฟอัตราครองเตียง
      const bedChart = new ApexCharts(document.querySelector("#bed_occupancy_10988"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: bed_occupancy
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#4154f1'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#4154f1'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      bedChart.render();


      // ❤️ กราฟ CMI
      const cmiChart = new ApexCharts(document.querySelector("#cmi_chart_10988"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: cmi
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#ff6384'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#ff6384'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      cmiChart.render();

    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode($ipd_10989->pluck('month')) !!};
      const bed_occupancy = {!! json_encode($ipd_10989->pluck('bed_occupancy')) !!};
      const cmi = {!! json_encode($ipd_10989->pluck('cmi')) !!};
      // 🩵 กราฟอัตราครองเตียง
      const bedChart = new ApexCharts(document.querySelector("#bed_occupancy_10989"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: bed_occupancy
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#4154f1'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#4154f1'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      bedChart.render();


      // ❤️ กราฟ CMI
      const cmiChart = new ApexCharts(document.querySelector("#cmi_chart_10989"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: cmi
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#ff6384'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#ff6384'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      cmiChart.render();

    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode($ipd_10990->pluck('month')) !!};
      const bed_occupancy = {!! json_encode($ipd_10990->pluck('bed_occupancy')) !!};
      const cmi = {!! json_encode($ipd_10990->pluck('cmi')) !!};
      // 🩵 กราฟอัตราครองเตียง
      const bedChart = new ApexCharts(document.querySelector("#bed_occupancy_10990"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: bed_occupancy
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#4154f1'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#4154f1'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      bedChart.render();


      // ❤️ กราฟ CMI
      const cmiChart = new ApexCharts(document.querySelector("#cmi_chart_10990"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: cmi
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#ff6384'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#ff6384'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      cmiChart.render();

    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode($ipd_10703->pluck('month')) !!};
      const bed_occupancy = {!! json_encode($ipd_10703->pluck('bed_occupancy')) !!};
      const cmi = {!! json_encode($ipd_10703->pluck('cmi')) !!};
      // 🩵 กราฟอัตราครองเตียง
      const bedChart = new ApexCharts(document.querySelector("#bed_occupancy_10703"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: bed_occupancy
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#4154f1'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#4154f1'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      bedChart.render();


      // ❤️ กราฟ CMI
      const cmiChart = new ApexCharts(document.querySelector("#cmi_chart_10703"), {
        series: [{
          name: 'อัตราครองเตียง (%)',
          data: cmi
        }],
        chart: {
          height: 250,
          type: 'area',
          toolbar: { show: false },
          animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        colors: ['#ff6384'],
        markers: { size: 4 },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.4,
            stops: [0, 90, 100]
          }
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: {
          enabled: true,
          style: { fontSize: '12px', colors: ['#ff6384'] },
          background: { enabled: true, foreColor: '#fff', borderRadius: 2 }
        },
        xaxis: {
          categories: months,
          labels: { style: { fontSize: '13px' } }
        },
        yaxis: {
          title: { text: 'ร้อยละ (%)' },
          labels: { formatter: val => val.toFixed(1) }
        }
      });
      cmiChart.render();

    });
  </script>
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
      var t10985_ipd = $('#table10985_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลชานุมาน'}]});
      t10985_ipd.buttons().container().appendTo('#btn-10985-ipd');
      var t10985_ref = $('#table10985_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลชานุมาน'}]});
      t10985_ref.buttons().container().appendTo('#btn-10985-refer');

      // 10986
      var t10986_ipd = $('#table10986_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลปทุมราชวงศา'}]});
      t10986_ipd.buttons().container().appendTo('#btn-10986-ipd');
      var t10986_ref = $('#table10986_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลปทุมราชวงศา'}]});
      t10986_ref.buttons().container().appendTo('#btn-10986-refer');

      // 10987
      var t10987_ipd = $('#table10987_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลพนา'}]});
      t10987_ipd.buttons().container().appendTo('#btn-10987-ipd');
      var t10987_ref = $('#table10987_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลพนา'}]});
      t10987_ref.buttons().container().appendTo('#btn-10987-refer');

      // 10988
      var t10988_ipd = $('#table10988_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลเสนางคนิคม'}]});
      t10988_ipd.buttons().container().appendTo('#btn-10988-ipd');
      var t10988_ref = $('#table10988_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลเสนางคนิคม'}]});
      t10988_ref.buttons().container().appendTo('#btn-10988-refer');

      // 10989
      var t10989_ipd = $('#table10989_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลหัวตะพาน'}]});
      t10989_ipd.buttons().container().appendTo('#btn-10989-ipd');
      var t10989_ref = $('#table10989_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลหัวตะพาน'}]});
      t10989_ref.buttons().container().appendTo('#btn-10989-refer');

      // 10990
      var t10990_ipd = $('#table10990_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลลืออำนาจ'}]});
      t10990_ipd.buttons().container().appendTo('#btn-10990-ipd');
      var t10990_ref = $('#table10990_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลลืออำนาจ'}]});
      t10990_ref.buttons().container().appendTo('#btn-10990-refer');

      // 10703
      var t10703_ipd = $('#table10703_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลอำนาจเจริญ'}]});
      t10703_ipd.buttons().container().appendTo('#btn-10703-ipd');
      var t10703_ref = $('#table10703_refer').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการส่งต่อ Refer โรงพยาบาลอำนาจเจริญ'}]});
      t10703_ref.buttons().container().appendTo('#btn-10703-refer');
    });
  </script>
@endpush
