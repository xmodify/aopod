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
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
  }
  .custom-table thead th {
    font-weight: 700 !important;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 10px 8px !important;
    font-size: 0.85rem !important;
  }
  .custom-table tbody td {
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
    background: #ffffff !important;
    border: 1px solid var(--glass-bd) !important;
    border-left: 6px solid #475569 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
  }
  .card-theme-bed {
    background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
    border-left: 6px solid #1976d2 !important;
  }
</style>
<style>
  /* ================================
    1) Operation – Green
  =================================*/
  .card-theme-op {
    background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
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
          <div class="col-lg-7">          
            <div class="d-flex align-items-center gap-3">
              <div class="bg-danger bg-opacity-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border: 1px solid rgba(244, 63, 94, 0.2);">
                <i class="fa-solid fa-bed-pulse fs-4 text-danger"></i>
              </div>
              <div>
                <h4 class="fw-bold mb-1" style="color: #1e293b;">ข้อมูลบริการผู้ป่วยใน (IPD)</h4>
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
                <h6 class="mb-0 fw-bold" style="color: #475569;">กำลังรักษาอยู่</h6>

                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(71, 85, 105, 0.1);">
                  <i class="fa-solid fa-hospital-user fs-4" style="color: #475569;"></i>
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
        @php
          $hospColors = [
            '10985' => [
              'border' => '#8b5cf6',
              'bg_light' => '#f5f3ff',
              'text' => '#7c3aed',
              'icon_bg' => 'rgba(139, 92, 246, 0.12)'
            ],
            '10986' => [
              'border' => '#06b6d4',
              'bg_light' => '#ecfeff',
              'text' => '#0891b2',
              'icon_bg' => 'rgba(6, 182, 212, 0.12)'
            ],
            '10987' => [
              'border' => '#ec4899',
              'bg_light' => '#fdf2f8',
              'text' => '#db2777',
              'icon_bg' => 'rgba(236, 72, 153, 0.12)'
            ],
            '10988' => [
              'border' => '#f59e0b',
              'bg_light' => '#fef3c7',
              'text' => '#d97706',
              'icon_bg' => 'rgba(245, 158, 11, 0.12)'
            ],
            '10989' => [
              'border' => '#3b82f6',
              'bg_light' => '#eff6ff',
              'text' => '#2563eb',
              'icon_bg' => 'rgba(59, 130, 246, 0.12)'
            ],
            '10990' => [
              'border' => '#10b981',
              'bg_light' => '#ecfdf5',
              'text' => '#059669',
              'icon_bg' => 'rgba(16, 185, 129, 0.12)'
            ],
            '10703' => [
              'border' => '#f43f5e',
              'bg_light' => '#fff1f2',
              'text' => '#e11d48',
              'icon_bg' => 'rgba(244, 63, 94, 0.12)'
            ],
          ];
          $c = $hospColors[$hospcode] ?? [
            'border' => '#1976d2',
            'bg_light' => '#e3f2fd',
            'text' => '#0d47a1',
            'icon_bg' => 'rgba(25, 118, 210, 0.12)'
          ];
        @endphp
         <div class="col-12 col-sm-6 col-xl-3" >
            <div class="card card-hover glass p-3 h-100" style="background: #ffffff !important; border: 1px solid var(--glass-bd) !important; border-left: 6px solid {{ $c['border'] }} !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;">

                <!-- หัวข้อ Card -->
                <h6 class="mb-3 fw-bold d-flex justify-content-between align-items-center" style="color: {{ $c['text'] }};">
                    <span>ข้อมูลเตียง{{ $data['hospname'] }}</span>
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background: {{ $c['icon_bg'] }};">
                      <i class="fa-solid fa-bed-pulse fs-5" style="color: {{ $c['border'] }} !important;"></i>
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
                <hr class="my-2" style="border-top: 1px solid rgba(0,0,0,0.15); opacity: 1; margin: 6px 0;">
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
                                style="color:#f43f5e;"      {{-- แดง (สูง) --}}
                            @elseif($b->bed_rate >= 60)
                                style="color:#f59e0b;"      {{-- ส้ม (ปานกลาง) --}}
                            @else
                                style="color:#10b981;"      {{-- เขียว (ปกติ) --}}
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


      // 10986
      var t10986_ipd = $('#table10986_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลปทุมราชวงศา'}]});
      t10986_ipd.buttons().container().appendTo('#btn-10986-ipd');


      // 10987
      var t10987_ipd = $('#table10987_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลพนา'}]});
      t10987_ipd.buttons().container().appendTo('#btn-10987-ipd');


      // 10988
      var t10988_ipd = $('#table10988_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลเสนางคนิคม'}]});
      t10988_ipd.buttons().container().appendTo('#btn-10988-ipd');


      // 10989
      var t10989_ipd = $('#table10989_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลหัวตะพาน'}]});
      t10989_ipd.buttons().container().appendTo('#btn-10989-ipd');


      // 10990
      var t10990_ipd = $('#table10990_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลลืออำนาจ'}]});
      t10990_ipd.buttons().container().appendTo('#btn-10990-ipd');


      // 10703
      var t10703_ipd = $('#table10703_ipd').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลอำนาจเจริญ'}]});
      t10703_ipd.buttons().container().appendTo('#btn-10703-ipd');

    });
  </script>
@endpush
