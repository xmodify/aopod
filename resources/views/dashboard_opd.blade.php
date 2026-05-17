@extends('layouts.app')

<style>
  /* === Table Styling === */
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
  .th-cyan { background: #e0f7fa !important; color: #006064 !important; }
  .th-grey { background: #f5f5f5 !important; color: #424242 !important; }
  
  .tr-total {
    background: #f1f8ff !important;
    font-weight: 800 !important;
    border-top: 2px solid #0d47a1 !important;
  }

  /* Glassmorphism Containers */
  .glass {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
  }

  .card-hover {
    transition: all 0.3s ease-in-out;
  }
  .card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }

  /* Theme Specific Cards */
  .card-theme-opd {
    background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
    border-left: 6px solid #1976d2 !important;
  }
  .card-theme-chart {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-left: 6px solid #6c757d !important;
  }
  .card-theme-dent {
    background: linear-gradient(135deg, #f3e5f5 0%, #ffffff 100%);
    border-left: 6px solid #ab47bc !important;
  }
  .card-theme-phy {
    background: linear-gradient(135deg, #fbe9e7 0%, #ffffff 100%);
    border-left: 6px solid #ff7043 !important;
  }
  .card-theme-anc {
    background: linear-gradient(135deg, #fce4ec 0%, #ffffff 100%);
    border-left: 6px solid #ec407a !important;
  }
  .card-theme-hm {
    background: linear-gradient(135deg, #e0f2f1 0%, #ffffff 100%);
    border-left: 6px solid #26a69a !important;
  }
  .card-theme-tele {
    background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);
    border-left: 6px solid #26c6da !important;
  }
  .card-theme-oapp {
    background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
    border-left: 6px solid #66bb6a !important;
  }
</style>

@section('title', 'Dashboard | AOPOD')

@section('content')

  <!-- HERO -->
  <header class="py-4">
    <div class="container-fluid">      
        <div class="row g-4 align-items-center">
          <div class="col-lg-7">          
            <div class="d-flex align-items-center gap-3">
              <div class="bg-success bg-opacity-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="bi bi-person-vcard fs-4 text-success"></i>
              </div>
              <div>
                <h4 class="fw-bold mb-1" style="color: #1e293b;">ข้อมูลบริการผู้ป่วยนอก (OPD)</h4>
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

  <!-- SUMMARY (4 blocks, no foreach) -->
  <section id="summary" class="pb-2">
    <div class="container-fluid">
      @php
        $fmtInt   = fn($n) => number_format((int)($n ?? 0));
        $fmtMoney = fn($n) => number_format((float)($n ?? 0), 2);
      @endphp

      <div class="row g-3">  
        
        {{--  ผู้ป่วยนอก ----------------------------------------------------------------------------------------------- --}}
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="#" data-bs-toggle="modal" data-bs-target="#VisitDetailModal" class="text-decoration-none text-dark">
            <div class="card-opd card card-theme-opd card-hover glass p-3 h-100">
              <!-- Header -->
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-primary fw-bold">ผู้ป่วยนอก OPD วันนี้</h6>
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                  <i class="bi bi-person-heart fs-4 text-primary"></i>
                </div>
              </div>
              <!-- Body Numbers -->
              <div class="d-flex justify-content-between align-items-center text-center">
                <!-- visit op -->
                <div class="flex-fill">
                  <div class="small text-secondary">visit op</div>
                  <div class="fw-bold" style="font-size:1.85rem;">
                    {{ $fmtInt($visit_total_op ?? 0) }}
                  </div>
                </div>
                <div class="vr mx-2 d-none d-sm-block" style="opacity:0.15;"></div>
                <!-- visit pp -->
                <div class="flex-fill">
                  <div class="small text-secondary">visit pp</div>
                  <div class="fw-bold text-primary" style="font-size:1.85rem;">
                    {{ $fmtInt($visit_total_pp ?? 0) }}
                  </div>
                </div>
                <div class="vr mx-2 d-none d-sm-block" style="opacity:0.15;"></div>
                <!-- endpoint สปสช -->
                <div class="flex-fill">
                  <div class="small text-secondary">ปิดสิทธิ สปสช.</div>
                  <div class="fw-bold text-success" style="font-size:1.85rem;">
                    {{ $fmtInt($visit_endpoint ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="VisitDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-person-lines-fill me-2"></i> ผู้ป่วยนอก (OPD)
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
                      <th>Visit OP</th>
                      <th>Visit PP</th>
                      <th>ปิดสิทธิ สปสช.</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_total_op) }}</td>
                        <td align="right" class="text-info">{{ number_format($h->visit_total_pp) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->visit_endpoint) }}</td>
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

        {{--  กราฟแยกสิทธิ------------------------------------------------------------------------------------------------ --}}
        <div class="col-12 col-sm-6 col-xl-8">
          <div class="card-opd card card-theme-chart glass p-3 h-100">
            <canvas id="visitRightsChart" height="200"></canvas>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
            <script>
              document.addEventListener("DOMContentLoaded", () => {
                new Chart(document.querySelector('#visitRightsChart'), {
                  type: 'bar',
                  data: {
                    labels: ['ประกันสุขภาพในจังหวัด','ประกันสุขภาพต่างจังหวัด', 'กรมบัญชีกลาง', 'อปท.', 'ประกันสังคม', 'พรบ./ชำระเงิน'],
                    datasets: [{
                      label: 'ผู้ป่วยนอกตามสิทธิ วันนี้',   // ไม่เกี่ยวกับ tooltip
                      data: [
                        {{ $visit_ucs_incup ?? 0 }},
                        {{ $visit_ucs_outprov ?? 0 }},
                        {{ $visit_ofc ?? 0 }},
                        {{ $visit_lgo ?? 0 }},
                        {{ $visit_sss ?? 0 }},
                        {{ $visit_pay ?? 0 }},
                      ],
                      backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                      ],
                      borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)'
                      ],
                      borderWidth: 1
                    }]
                  },
                  options: {
                    plugins: {
                      legend: {
                        display: true,
                        labels: {
                          usePointStyle: true,
                          pointStyle: 'line',
                          boxWidth: 0
                        }
                      },
                      tooltip: {
                        callbacks: {
                          label: function(context) {
                            return context.formattedValue.toLocaleString();  // ⭐ ตรงนี้ทำให้ตัดคำว่า Visit
                          }
                        }
                      }
                    },
                    scales: {
                      y: { beginAtZero: true }
                    }
                  }
                });
              });
            </script>
          </div>          
        </div>

        {{-- -------------------------------------------------------------------------------------------------------------- --}}
      </div>
    </div>  
  </section>

  <!-- SUMMARY (6 blocks, no foreach) ----------------------------------------------------------------------------------------->
  <section id="summary" class="pb-2">
    <div class="container-fluid">
      @php
        $fmtInt   = fn($n) => number_format((int)($n ?? 0));
        $fmtMoney = fn($n) => number_format((float)($n ?? 0), 2);
      @endphp

      <div class="row g-3">     

        {{--  ทันตกรรม วันนี้ -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-2">
          <a href="#" data-bs-toggle="modal" data-bs-target="#DentDetailModal" class="text-decoration-none text-dark">
            <div class="card-opd card card-theme-dent card-hover glass p-3 h-100">
              <!-- หัวข้อซ้าย + ไอคอนขวา -->
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold" style="color:#ab47bc;">ทันตกรรม วันนี้</h6>
                <div class="bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(171, 71, 188, 0.1);">
                  <i class="fa-solid fa-tooth fs-4" style="color:#ab47bc;"></i>
                </div>
              </div>
              <!-- เนื้อหาตรงกลาง -->
              <div class="text-center mt-3">                
                <div class="fw-bold " style="font-size:1.7rem; color:#D946EF;">
                  {{ $fmtInt($visit_dent ?? 0) }}
                </div>
                <div class="small text-secondary">visit</div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="DentDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-building-check me-2"></i>ทันตกรรม วันนี้
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_dent) }}</td>
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

        {{--  กายภาพบำบัด วันนี้ -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-2">
          <a href="#" data-bs-toggle="modal" data-bs-target="#PhyDetailModal" class="text-decoration-none text-dark">
            <div class="card-opd card card-theme-phy card-hover glass p-3 h-100">
              <!-- หัวข้อซ้าย + ไอคอนขวา -->
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold" style="color:#ff7043;">กายภาพบำบัด วันนี้</h6>
                <div class="bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(255, 112, 67, 0.1);">
                  <i class="fa-solid fa-person-walking fs-4" style="color:#ff7043;"></i>
                </div>
              </div>
              <!-- เนื้อหาตรงกลาง -->
              <div class="text-center mt-3">                
                <div class="fw-bold" style="font-size:1.7rem; color:#ff8a65;">
                  {{ $fmtInt($visit_physic ?? 0) }}
                </div>
                <div class="small text-secondary">visit</div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="PhyDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-person-vcard me-2"></i> กายภาพบำบัด วันนี้
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_physic) }}</td>
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

        {{-- ฝากครรภ์ วันนี้ -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-2">
          <a href="#" data-bs-toggle="modal" data-bs-target="#AncDetailModal" class="text-decoration-none text-dark">
            <div class="card-opd card card-theme-anc card-hover glass p-3 h-100">
              <!-- หัวข้อซ้าย + ไอคอนขวา -->
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold" style="color:#ec407a;">ฝากครรภ์ วันนี้</h6>
                <div class="bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(236, 64, 122, 0.1);">
                  <i class="fa-solid fa-person-pregnant fs-4" style="color:#ec407a;"></i>
                </div>
              </div>
              <!-- เนื้อหาตรงกลาง -->
              <div class="text-center mt-3">                
                <div class="fw-bold" style="font-size:1.7rem; color:#F06292;">
                  {{ $fmtInt($visit_anc ?? 0) }}
                </div>
                <div class="small text-secondary">visit</div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="AncDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-cash-coin me-2"></i> ฝากครรภ์ วันนี้
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_anc) }}</td>
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

        {{--  บริการแพทย์แผนไทย -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-2">
          <a href="#" data-bs-toggle="modal" data-bs-target="#HMDetailModal" class="text-decoration-none text-dark">
            <div class="card-opd card card-theme-hm card-hover glass p-3 h-100">
              <!-- หัวข้อซ้าย + ไอคอนขวา -->
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold" style="color:#26a69a;">แพทย์แผนไทย วันนี้</h6>
                <div class="bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(38, 166, 154, 0.1);">
                  <i class="bi bi-person-arms-up fs-4" style="color:#26a69a;"></i>
                </div>
              </div>
              <!-- เนื้อหาตรงกลาง -->
              <div class="text-center mt-3">                
                <div class="fw-bold" style="font-size:1.7rem; color:#009688;">
                  {{ $fmtInt($visit_healthmed ?? 0) }}
                </div>
                <div class="small text-secondary">visit</div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="HMDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-clipboard2-pulse me-2"></i>แพทย์แผนไทย วันนี้
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_healthmed) }}</td>
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

        {{-- การแพทย์ทางไกล วันนี้ -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-2">
          <a href="#" data-bs-toggle="modal" data-bs-target="#TeleDetailModal" class="text-decoration-none text-dark">
            <div class="card-opd card card-theme-tele card-hover glass p-3 h-100">
              <!-- หัวข้อซ้าย + ไอคอนขวา -->
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold" style="color:#26c6da;">การแพทย์ทางไกล วันนี้</h6>
                <div class="bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(38, 198, 218, 0.1);">
                  <i class="fa-solid fa-video fs-4" style="color:#26c6da;"></i>
                </div>
              </div>
              <!-- เนื้อหาตรงกลาง -->
              <div class="text-center mt-3">                
                <div class="fw-bold" style="font-size:1.7rem; color:#00bcd4;">
                  {{ $fmtInt($visit_telehealth ?? 0) }}
                </div>
                <div class="small text-secondary">visit</div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="TeleDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-cash-coin me-2"></i> การแพทย์ทางไกล วันนี้
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_telehealth) }}</td>
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

        {{-- นัดหมายออนไลน์ วันนี้ -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-2">
          <a href="#" data-bs-toggle="modal" data-bs-target="#OappDetailModal" class="text-decoration-none text-dark">
            <div class="card-opd card card-theme-oapp card-hover glass p-3 h-100">
              <!-- หัวข้อ -->
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold" style="color:#66bb6a;">นัดหมายออนไลน์ วันนี้</h6>
                <div class="bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(102, 187, 106, 0.1);">
                  <i class="fa-solid fa-calendar-days fs-4" style="color:#66bb6a;"></i>
                </div>
              </div>
              <!-- เนื้อหา -->
              <div class="text-center mt-3">
                <div class="d-flex justify-content-center align-items-center gap-3">
                  <!-- นัดหมาย -->
                  <div class="text-center">
                    <div class="fw-bold" style="font-size:1.7rem; color:#42bd41;">
                      {{ $fmtInt($visit_moph_oapp_booking ?? 0) }}
                    </div>
                    <div class="small text-secondary">จองคิวนัดหมาย</div>
                  </div>
                  <!-- ตัวคั่น -->
                  <div class="vr mx-2 d-none d-sm-block" style="opacity:0.15;"></div>
                  <!-- รับบริการ -->
                  <div class="text-center">
                    <div class="fw-bold" style="font-size:1.7rem; color:#42bd41;">
                      {{ $fmtInt($visit_moph_oapp ?? 0) }}
                    </div>
                    <div class="small text-secondary">เข้ารับบริการ</div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="OappDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-cash-coin me-2"></i> นัดหมายออนไลน์ วันนี้
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
                      <th>จองคิวนัดหมาย</th>
                      <th>เข้ารับบริการ</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_moph_oapp_booking) }}</td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_moph_oapp) }}</td>
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

        {{-- -------------------------------------------------------------------------------------------------------------- --}}
          
      </div>
    </div>  
  </section>
  <hr>

  {{-- เลือกปีงบประมาณ ----------------------------------------------------------------------------------------------------------}}
  <section id="summary" class="pb-2">
      <div class="container-fluid">
        <form method="POST" action="{{ url('web/opd') }}" enctype="multipart/form-data">
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

        <!-- 10985-->
        <div class="tab-pane fade show active" id="pane-10985" role="tabpanel" aria-labelledby="tab-10985" tabindex="0">
          <!-- 10985 OPD-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10985] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลชานุมาน ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10985}}</span>
                <div id="btn-10985"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10985" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" colspan="7">ข้อมูลทั้งหมด</th>  
                    <th class="th-green" rowspan="2">Visit ทันตกรรม</th>   
                    <th class="th-green" rowspan="2">Visit กายภาพบำบัด</th> 
                    <th class="th-green" rowspan="2">Visit ฝากครรภ์</th> 
                    <th class="th-green" rowspan="2">Visit แพทย์แผนไทย</th>  
                    <th class="th-green" rowspan="2">Visit การแพทย์ทางไกล</th>    
                    <th class="th-orange" colspan="2">นัดหมายออนไลน์</th>                    
                  </tr>    
                  <tr>        
                    <th class="th-blue">HN Total</th>
                    <th class="th-blue">Visit Total</th>
                    <th class="th-blue">Visit OP</th>
                    <th class="th-blue">Visit PP</th>
                    <th class="th-blue">ค่ารักษารวม</th>
                    <th class="th-blue">ค่า Lab</th>
                    <th class="th-blue">ค่า ยา</th>  
                    <th class="th-orange">จองคิวนัดหมาย</th>
                    <th class="th-orange">เข้ารับบริการ</th>                 
                  </tr>    
                </thead>
                <tbody>
                  @foreach($total_10985 as $row) 
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->hn_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_op) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_pp) }}</td>
                    <td class="text-end">{{ number_format($row->inc_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->visit_dent) }}</td>
                    <td class="text-end">{{ number_format($row->visit_physic) }}</td>
                    <td class="text-end">{{ number_format($row->visit_anc) }}</td>
                    <td class="text-end">{{ number_format($row->visit_healthmed) }}</td>
                    <td class="text-end">{{ number_format($row->visit_telehealth) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp_booking) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp) }}</td>
                  </tr>       
                  @endforeach    
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10985->sum('hn_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_total_op')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_total_pp')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_dent')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_physic')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_anc')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_healthmed')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_telehealth')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_moph_oapp_booking')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_moph_oapp')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>   
          <!-- 10985 ค่ารักษาพยาบาล-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>[10985] ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลชานุมาน ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10985}}</span>
                <div id="btn-10985-inc"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10985_inc" class="table custom-table my-3" width="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width="4%">เดือน</th>
                    <th class="th-blue" colspan="4">UCS ใน CUP</th>
                    <th class="th-blue" colspan="4">UCS ในจังหวัด</th>
                    <th class="th-blue" colspan="4">UCS นอกจังหวัด</th>
                    <th class="th-orange" colspan="4">OFC ข้าราชการ</th>
                    <th class="th-purple" colspan="4">BKK กทม.</th>
                    <th class="th-purple" colspan="4">BMT ขสมก.</th>
                    <th class="th-green" colspan="4">SSS ประกันสังคม</th>
                    <th class="th-green" colspan="4">LGO อปท.</th>
                    <th class="th-cyan" colspan="4">FSS ต่างด้าว</th>
                    <th class="th-cyan" colspan="4">STP Stateless</th>
                    <th class="th-grey" colspan="4">ชำระเงิน/พรบ.</th>
                  </tr>
                  <tr>
                    @for ($i = 0; $i < 3; $i++)
                        <th class="th-blue">Visit</th><th class="th-blue">ค่ารักษารวม</th><th class="th-blue">ค่า Lab</th><th class="th-blue">ค่า ยา</th>
                    @endfor
                    <th class="th-orange">Visit</th><th class="th-orange">ค่ารักษารวม</th><th class="th-orange">ค่า Lab</th><th class="th-orange">ค่า ยา</th>
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-purple">Visit</th><th class="th-purple">ค่ารักษารวม</th><th class="th-purple">ค่า Lab</th><th class="th-purple">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-green">Visit</th><th class="th-green">ค่ารักษารวม</th><th class="th-green">ค่า Lab</th><th class="th-green">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-cyan">Visit</th><th class="th-cyan">ค่ารักษารวม</th><th class="th-cyan">ค่า Lab</th><th class="th-cyan">ค่า ยา</th>
                    @endfor
                    <th class="th-grey">Visit</th><th class="th-grey">ค่ารักษารวม</th><th class="th-grey">ค่า Lab</th><th class="th-grey">ค่า ยา</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($total_10985 as $row)
                  <tr>
                      <td class="text-center">{{ $row->month }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_incup) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_inprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_outprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ofc) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bkk) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bmt) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_sss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_lgo) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_fss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_stp) }}</td>
                      <td class="text-end">{{ number_format($row->inc_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_pay) }}</td>
                      <td class="text-end">{{ number_format($row->inc_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_ucs_incup')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_ucs_inprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_ucs_outprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_ofc')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_bkk')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_bmt')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_sss')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_lgo')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_fss')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_stp')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('visit_pay')) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_lab_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10985->sum('inc_drug_pay'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>                  
        <!-- END 10985 -->
        </div>

        <!-- 10986-->
        <div class="tab-pane fade" id="pane-10986" role="tabpanel" aria-labelledby="tab-10986" tabindex="0">
          <!-- 10986 OPD-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10986] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลปทุมราชวงศา ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10986}}</span>
                <div id="btn-10986"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10986" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" colspan="7">ข้อมูลทั้งหมด</th>  
                    <th class="th-green" rowspan="2">Visit ทันตกรรม</th>   
                    <th class="th-green" rowspan="2">Visit กายภาพบำบัด</th> 
                    <th class="th-green" rowspan="2">Visit ฝากครรภ์</th> 
                    <th class="th-green" rowspan="2">Visit แพทย์แผนไทย</th>  
                    <th class="th-green" rowspan="2">Visit การแพทย์ทางไกล</th>    
                    <th class="th-orange" colspan="2">นัดหมายออนไลน์</th>                     
                  </tr>    
                  <tr>        
                    <th class="th-blue">HN Total</th>
                    <th class="th-blue">Visit Total</th>
                    <th class="th-blue">Visit OP</th>
                    <th class="th-blue">Visit PP</th>
                    <th class="th-blue">ค่ารักษารวม</th>
                    <th class="th-blue">ค่า Lab</th>
                    <th class="th-blue">ค่า ยา</th> 
                    <th class="th-orange">จองคิวนัดหมาย</th>
                    <th class="th-orange">เข้ารับบริการ</th>                    
                  </tr>    
                </thead>
                <tbody>
                  @foreach($total_10986 as $row) 
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->hn_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_op) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_pp) }}</td>
                    <td class="text-end">{{ number_format($row->inc_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->visit_dent) }}</td>
                    <td class="text-end">{{ number_format($row->visit_physic) }}</td>
                    <td class="text-end">{{ number_format($row->visit_anc) }}</td>
                    <td class="text-end">{{ number_format($row->visit_healthmed) }}</td>
                    <td class="text-end">{{ number_format($row->visit_telehealth) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp_booking) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp) }}</td>
                  </tr>       
                  @endforeach    
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10986->sum('hn_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_total_op')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_total_pp')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_dent')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_physic')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_anc')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_healthmed')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_telehealth')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_moph_oapp_booking')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_moph_oapp')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>   
          <!-- 10986 ค่ารักษาพยาบาล-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>[10986] ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลปทุมราชวงศา ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10986}}</span>
                <div id="btn-10986-inc"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10986_inc" class="table custom-table my-3" width="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width="4%">เดือน</th>
                    <th class="th-blue" colspan="4">UCS ใน CUP</th>
                    <th class="th-blue" colspan="4">UCS ในจังหวัด</th>
                    <th class="th-blue" colspan="4">UCS นอกจังหวัด</th>
                    <th class="th-orange" colspan="4">OFC ข้าราชการ</th>
                    <th class="th-purple" colspan="4">BKK กทม.</th>
                    <th class="th-purple" colspan="4">BMT ขสมก.</th>
                    <th class="th-green" colspan="4">SSS ประกันสังคม</th>
                    <th class="th-green" colspan="4">LGO อปท.</th>
                    <th class="th-cyan" colspan="4">FSS ต่างด้าว</th>
                    <th class="th-cyan" colspan="4">STP Stateless</th>
                    <th class="th-grey" colspan="4">ชำระเงิน/พรบ.</th>
                  </tr>
                  <tr>
                    @for ($i = 0; $i < 3; $i++)
                        <th class="th-blue">Visit</th><th class="th-blue">ค่ารักษารวม</th><th class="th-blue">ค่า Lab</th><th class="th-blue">ค่า ยา</th>
                    @endfor
                    <th class="th-orange">Visit</th><th class="th-orange">ค่ารักษารวม</th><th class="th-orange">ค่า Lab</th><th class="th-orange">ค่า ยา</th>
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-purple">Visit</th><th class="th-purple">ค่ารักษารวม</th><th class="th-purple">ค่า Lab</th><th class="th-purple">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-green">Visit</th><th class="th-green">ค่ารักษารวม</th><th class="th-green">ค่า Lab</th><th class="th-green">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-cyan">Visit</th><th class="th-cyan">ค่ารักษารวม</th><th class="th-cyan">ค่า Lab</th><th class="th-cyan">ค่า ยา</th>
                    @endfor
                    <th class="th-grey">Visit</th><th class="th-grey">ค่ารักษารวม</th><th class="th-grey">ค่า Lab</th><th class="th-grey">ค่า ยา</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($total_10986 as $row)
                  <tr>
                      <td class="text-center">{{ $row->month }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_incup) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_inprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_outprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ofc) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bkk) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bmt) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_sss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_lgo) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_fss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_stp) }}</td>
                      <td class="text-end">{{ number_format($row->inc_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_pay) }}</td>
                      <td class="text-end">{{ number_format($row->inc_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_ucs_incup')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_ucs_inprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_ucs_outprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_ofc')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_bkk')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_bmt')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_sss')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_lgo')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_fss')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_stp')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('visit_pay')) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_lab_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10986->sum('inc_drug_pay'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>                  
        <!-- END 10986 -->
        </div>

        <!-- 10987-->
        <div class="tab-pane fade" id="pane-10987" role="tabpanel" aria-labelledby="tab-10987" tabindex="0">
          <!-- 10987 OPD-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10987] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลพนา ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10987}}</span>
                <div id="btn-10987"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10987" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" colspan="7">ข้อมูลทั้งหมด</th>  
                    <th class="th-green" rowspan="2">Visit ทันตกรรม</th>   
                    <th class="th-green" rowspan="2">Visit กายภาพบำบัด</th> 
                    <th class="th-green" rowspan="2">Visit ฝากครรภ์</th> 
                    <th class="th-green" rowspan="2">Visit แพทย์แผนไทย</th>  
                    <th class="th-green" rowspan="2">Visit การแพทย์ทางไกล</th>    
                    <th class="th-orange" colspan="2">นัดหมายออนไลน์</th>                     
                  </tr>    
                  <tr>        
                    <th class="th-blue">HN Total</th>
                    <th class="th-blue">Visit Total</th>
                    <th class="th-blue">Visit OP</th>
                    <th class="th-blue">Visit PP</th>
                    <th class="th-blue">ค่ารักษารวม</th>
                    <th class="th-blue">ค่า Lab</th>
                    <th class="th-blue">ค่า ยา</th> 
                    <th class="th-orange">จองคิวนัดหมาย</th>
                    <th class="th-orange">เข้ารับบริการ</th>                    
                  </tr>    
                </thead>
                <tbody>
                  @foreach($total_10987 as $row) 
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->hn_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_op) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_pp) }}</td>
                    <td class="text-end">{{ number_format($row->inc_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->visit_dent) }}</td>
                    <td class="text-end">{{ number_format($row->visit_physic) }}</td>
                    <td class="text-end">{{ number_format($row->visit_anc) }}</td>
                    <td class="text-end">{{ number_format($row->visit_healthmed) }}</td>
                    <td class="text-end">{{ number_format($row->visit_telehealth) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp_booking) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp) }}</td>
                  </tr>       
                  @endforeach    
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10987->sum('hn_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_total_op')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_total_pp')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_dent')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_physic')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_anc')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_healthmed')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_telehealth')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_moph_oapp_booking')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_moph_oapp')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>   
          <!-- 10987 ค่ารักษาพยาบาล-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>[10987] ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลพนา ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10987}}</span>
                <div id="btn-10987-inc"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10987_inc" class="table custom-table my-3" width="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width="4%">เดือน</th>
                    <th class="th-blue" colspan="4">UCS ใน CUP</th>
                    <th class="th-blue" colspan="4">UCS ในจังหวัด</th>
                    <th class="th-blue" colspan="4">UCS นอกจังหวัด</th>
                    <th class="th-orange" colspan="4">OFC ข้าราชการ</th>
                    <th class="th-purple" colspan="4">BKK กทม.</th>
                    <th class="th-purple" colspan="4">BMT ขสมก.</th>
                    <th class="th-green" colspan="4">SSS ประกันสังคม</th>
                    <th class="th-green" colspan="4">LGO อปท.</th>
                    <th class="th-cyan" colspan="4">FSS ต่างด้าว</th>
                    <th class="th-cyan" colspan="4">STP Stateless</th>
                    <th class="th-grey" colspan="4">ชำระเงิน/พรบ.</th>
                  </tr>
                  <tr>
                    @for ($i = 0; $i < 3; $i++)
                        <th class="th-blue">Visit</th><th class="th-blue">ค่ารักษารวม</th><th class="th-blue">ค่า Lab</th><th class="th-blue">ค่า ยา</th>
                    @endfor
                    <th class="th-orange">Visit</th><th class="th-orange">ค่ารักษารวม</th><th class="th-orange">ค่า Lab</th><th class="th-orange">ค่า ยา</th>
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-purple">Visit</th><th class="th-purple">ค่ารักษารวม</th><th class="th-purple">ค่า Lab</th><th class="th-purple">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-green">Visit</th><th class="th-green">ค่ารักษารวม</th><th class="th-green">ค่า Lab</th><th class="th-green">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-cyan">Visit</th><th class="th-cyan">ค่ารักษารวม</th><th class="th-cyan">ค่า Lab</th><th class="th-cyan">ค่า ยา</th>
                    @endfor
                    <th class="th-grey">Visit</th><th class="th-grey">ค่ารักษารวม</th><th class="th-grey">ค่า Lab</th><th class="th-grey">ค่า ยา</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($total_10987 as $row)
                  <tr>
                      <td class="text-center">{{ $row->month }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_incup) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_inprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_outprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ofc) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bkk) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bmt) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_sss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_lgo) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_fss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_stp) }}</td>
                      <td class="text-end">{{ number_format($row->inc_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_pay) }}</td>
                      <td class="text-end">{{ number_format($row->inc_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_ucs_incup')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_ucs_inprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_ucs_outprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_ofc')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_bkk')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_bmt')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_sss')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_lgo')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_fss')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_stp')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('visit_pay')) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_lab_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10987->sum('inc_drug_pay'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>                  
        <!-- END 10987 -->
        </div>

        <!-- 10988-->
        <div class="tab-pane fade" id="pane-10988" role="tabpanel" aria-labelledby="tab-10988" tabindex="0">
          <!-- 10988 OPD-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10988] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลเสนางคนิคม ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10988}}</span>
                <div id="btn-10988"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10988" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" colspan="7">ข้อมูลทั้งหมด</th>  
                    <th class="th-green" rowspan="2">Visit ทันตกรรม</th>   
                    <th class="th-green" rowspan="2">Visit กายภาพบำบัด</th> 
                    <th class="th-green" rowspan="2">Visit ฝากครรภ์</th> 
                    <th class="th-green" rowspan="2">Visit แพทย์แผนไทย</th>  
                    <th class="th-green" rowspan="2">Visit การแพทย์ทางไกล</th>    
                    <th class="th-orange" colspan="2">นัดหมายออนไลน์</th>                    
                  </tr>    
                  <tr>        
                    <th class="th-blue">HN Total</th>
                    <th class="th-blue">Visit Total</th>
                    <th class="th-blue">Visit OP</th>
                    <th class="th-blue">Visit PP</th>
                    <th class="th-blue">ค่ารักษารวม</th>
                    <th class="th-blue">ค่า Lab</th>
                    <th class="th-blue">ค่า ยา</th> 
                    <th class="th-orange">จองคิวนัดหมาย</th>
                    <th class="th-orange">เข้ารับบริการ</th>                   
                  </tr>    
                </thead>
                <tbody>
                  @foreach($total_10988 as $row) 
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->hn_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_op) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_pp) }}</td>
                    <td class="text-end">{{ number_format($row->inc_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->visit_dent) }}</td>
                    <td class="text-end">{{ number_format($row->visit_physic) }}</td>
                    <td class="text-end">{{ number_format($row->visit_anc) }}</td>
                    <td class="text-end">{{ number_format($row->visit_healthmed) }}</td>
                    <td class="text-end">{{ number_format($row->visit_telehealth) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp_booking) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp) }}</td>
                  </tr>       
                  @endforeach    
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10988->sum('hn_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_total_op')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_total_pp')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_dent')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_physic')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_anc')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_healthmed')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_telehealth')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_moph_oapp_booking')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_moph_oapp')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>   
          <!-- 10988 ค่ารักษาพยาบาล-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>[10988] ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลเสนางคนิคม ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10988}}</span>
                <div id="btn-10988-inc"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10988_inc" class="table custom-table my-3" width="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width="4%">เดือน</th>
                    <th class="th-blue" colspan="4">UCS ใน CUP</th>
                    <th class="th-blue" colspan="4">UCS ในจังหวัด</th>
                    <th class="th-blue" colspan="4">UCS นอกจังหวัด</th>
                    <th class="th-orange" colspan="4">OFC ข้าราชการ</th>
                    <th class="th-purple" colspan="4">BKK กทม.</th>
                    <th class="th-purple" colspan="4">BMT ขสมก.</th>
                    <th class="th-green" colspan="4">SSS ประกันสังคม</th>
                    <th class="th-green" colspan="4">LGO อปท.</th>
                    <th class="th-cyan" colspan="4">FSS ต่างด้าว</th>
                    <th class="th-cyan" colspan="4">STP Stateless</th>
                    <th class="th-grey" colspan="4">ชำระเงิน/พรบ.</th>
                  </tr>
                  <tr>
                    @for ($i = 0; $i < 3; $i++)
                        <th class="th-blue">Visit</th><th class="th-blue">ค่ารักษารวม</th><th class="th-blue">ค่า Lab</th><th class="th-blue">ค่า ยา</th>
                    @endfor
                    <th class="th-orange">Visit</th><th class="th-orange">ค่ารักษารวม</th><th class="th-orange">ค่า Lab</th><th class="th-orange">ค่า ยา</th>
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-purple">Visit</th><th class="th-purple">ค่ารักษารวม</th><th class="th-purple">ค่า Lab</th><th class="th-purple">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-green">Visit</th><th class="th-green">ค่ารักษารวม</th><th class="th-green">ค่า Lab</th><th class="th-green">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-cyan">Visit</th><th class="th-cyan">ค่ารักษารวม</th><th class="th-cyan">ค่า Lab</th><th class="th-cyan">ค่า ยา</th>
                    @endfor
                    <th class="th-grey">Visit</th><th class="th-grey">ค่ารักษารวม</th><th class="th-grey">ค่า Lab</th><th class="th-grey">ค่า ยา</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($total_10988 as $row)
                  <tr>
                      <td class="text-center">{{ $row->month }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_incup) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_inprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_outprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ofc) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bkk) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bmt) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_sss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_lgo) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_fss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_stp) }}</td>
                      <td class="text-end">{{ number_format($row->inc_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_pay) }}</td>
                      <td class="text-end">{{ number_format($row->inc_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_ucs_incup')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_ucs_inprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_ucs_outprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_ofc')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_bkk')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_bmt')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_sss')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_lgo')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_fss')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_stp')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('visit_pay')) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_lab_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10988->sum('inc_drug_pay'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>                  
        <!-- END 10988 -->
        </div>

        <!-- 10989-->
        <div class="tab-pane fade" id="pane-10989" role="tabpanel" aria-labelledby="tab-10989" tabindex="0">
          <!-- 10989 OPD-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10989] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลหัวตะพาน ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10989}}</span>
                <div id="btn-10989"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10989" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" colspan="7">ข้อมูลทั้งหมด</th>  
                    <th class="th-green" rowspan="2">Visit ทันตกรรม</th>   
                    <th class="th-green" rowspan="2">Visit กายภาพบำบัด</th> 
                    <th class="th-green" rowspan="2">Visit ฝากครรภ์</th> 
                    <th class="th-green" rowspan="2">Visit แพทย์แผนไทย</th>  
                    <th class="th-green" rowspan="2">Visit การแพทย์ทางไกล</th>    
                    <th class="th-orange" colspan="2">นัดหมายออนไลน์</th>                    
                  </tr>    
                  <tr>        
                    <th class="th-blue">HN Total</th>
                    <th class="th-blue">Visit Total</th>
                    <th class="th-blue">Visit OP</th>
                    <th class="th-blue">Visit PP</th>
                    <th class="th-blue">ค่ารักษารวม</th>
                    <th class="th-blue">ค่า Lab</th>
                    <th class="th-blue">ค่า ยา</th>
                    <th class="th-orange">จองคิวนัดหมาย</th>
                    <th class="th-orange">เข้ารับบริการ</th>                  
                  </tr>    
                </thead>
                <tbody>
                  @foreach($total_10989 as $row) 
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->hn_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_op) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_pp) }}</td>
                    <td class="text-end">{{ number_format($row->inc_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->visit_dent) }}</td>
                    <td class="text-end">{{ number_format($row->visit_physic) }}</td>
                    <td class="text-end">{{ number_format($row->visit_anc) }}</td>
                    <td class="text-end">{{ number_format($row->visit_healthmed) }}</td>
                    <td class="text-end">{{ number_format($row->visit_telehealth) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp_booking) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp) }}</td>
                  </tr>       
                  @endforeach    
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10989->sum('hn_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_total_op')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_total_pp')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_dent')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_physic')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_anc')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_healthmed')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_telehealth')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_moph_oapp_booking')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_moph_oapp')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>   
          <!-- 10989 ค่ารักษาพยาบาล-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>[10989] ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลหัวตะพาน ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10989}}</span>
                <div id="btn-10989-inc"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10989_inc" class="table custom-table my-3" width="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width="4%">เดือน</th>
                    <th class="th-blue" colspan="4">UCS ใน CUP</th>
                    <th class="th-blue" colspan="4">UCS ในจังหวัด</th>
                    <th class="th-blue" colspan="4">UCS นอกจังหวัด</th>
                    <th class="th-orange" colspan="4">OFC ข้าราชการ</th>
                    <th class="th-purple" colspan="4">BKK กทม.</th>
                    <th class="th-purple" colspan="4">BMT ขสมก.</th>
                    <th class="th-green" colspan="4">SSS ประกันสังคม</th>
                    <th class="th-green" colspan="4">LGO อปท.</th>
                    <th class="th-cyan" colspan="4">FSS ต่างด้าว</th>
                    <th class="th-cyan" colspan="4">STP Stateless</th>
                    <th class="th-grey" colspan="4">ชำระเงิน/พรบ.</th>
                  </tr>
                  <tr>
                    @for ($i = 0; $i < 3; $i++)
                        <th class="th-blue">Visit</th><th class="th-blue">ค่ารักษารวม</th><th class="th-blue">ค่า Lab</th><th class="th-blue">ค่า ยา</th>
                    @endfor
                    <th class="th-orange">Visit</th><th class="th-orange">ค่ารักษารวม</th><th class="th-orange">ค่า Lab</th><th class="th-orange">ค่า ยา</th>
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-purple">Visit</th><th class="th-purple">ค่ารักษารวม</th><th class="th-purple">ค่า Lab</th><th class="th-purple">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-green">Visit</th><th class="th-green">ค่ารักษารวม</th><th class="th-green">ค่า Lab</th><th class="th-green">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-cyan">Visit</th><th class="th-cyan">ค่ารักษารวม</th><th class="th-cyan">ค่า Lab</th><th class="th-cyan">ค่า ยา</th>
                    @endfor
                    <th class="th-grey">Visit</th><th class="th-grey">ค่ารักษารวม</th><th class="th-grey">ค่า Lab</th><th class="th-grey">ค่า ยา</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($total_10989 as $row)
                  <tr>
                      <td class="text-center">{{ $row->month }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_incup) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_inprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_outprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ofc) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bkk) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bmt) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_sss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_lgo) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_fss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_stp) }}</td>
                      <td class="text-end">{{ number_format($row->inc_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_pay) }}</td>
                      <td class="text-end">{{ number_format($row->inc_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_ucs_incup')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_ucs_inprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_ucs_outprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_ofc')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_bkk')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_bmt')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_sss')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_lgo')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_fss')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_stp')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('visit_pay')) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_lab_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10989->sum('inc_drug_pay'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>                  
        <!-- END 10989 -->
        </div>

        <!-- 10990-->
        <div class="tab-pane fade" id="pane-10990" role="tabpanel" aria-labelledby="tab-10990" tabindex="0">
          <!-- 10990 OPD-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10990] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลลืออำนาจ ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10990}}</span>
                <div id="btn-10990"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10990" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" colspan="7">ข้อมูลทั้งหมด</th>  
                    <th class="th-green" rowspan="2">Visit ทันตกรรม</th>   
                    <th class="th-green" rowspan="2">Visit กายภาพบำบัด</th> 
                    <th class="th-green" rowspan="2">Visit ฝากครรภ์</th> 
                    <th class="th-green" rowspan="2">Visit แพทย์แผนไทย</th>  
                    <th class="th-green" rowspan="2">Visit การแพทย์ทางไกล</th>    
                    <th class="th-orange" colspan="2">นัดหมายออนไลน์</th>                    
                  </tr>    
                  <tr>        
                    <th class="th-blue">HN Total</th>
                    <th class="th-blue">Visit Total</th>
                    <th class="th-blue">Visit OP</th>
                    <th class="th-blue">Visit PP</th>
                    <th class="th-blue">ค่ารักษารวม</th>
                    <th class="th-blue">ค่า Lab</th>
                    <th class="th-blue">ค่า ยา</th>    
                    <th class="th-orange">จองคิวนัดหมาย</th>
                    <th class="th-orange">เข้ารับบริการ</th>                
                  </tr>    
                </thead>
                <tbody>
                  @foreach($total_10990 as $row) 
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->hn_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_op) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_pp) }}</td>
                    <td class="text-end">{{ number_format($row->inc_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->visit_dent) }}</td>
                    <td class="text-end">{{ number_format($row->visit_physic) }}</td>
                    <td class="text-end">{{ number_format($row->visit_anc) }}</td>
                    <td class="text-end">{{ number_format($row->visit_healthmed) }}</td>
                    <td class="text-end">{{ number_format($row->visit_telehealth) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp_booking) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp) }}</td>
                  </tr>       
                  @endforeach    
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10990->sum('hn_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_total_op')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_total_pp')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_dent')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_physic')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_anc')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_healthmed')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_telehealth')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_moph_oapp_booking')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_moph_oapp')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>   
          <!-- 10990 ค่ารักษาพยาบาล-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>[10990] ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลลืออำนาจ ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10990}}</span>
                <div id="btn-10990-inc"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10990_inc" class="table custom-table my-3" width="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width="4%">เดือน</th>
                    <th class="th-blue" colspan="4">UCS ใน CUP</th>
                    <th class="th-blue" colspan="4">UCS ในจังหวัด</th>
                    <th class="th-blue" colspan="4">UCS นอกจังหวัด</th>
                    <th class="th-orange" colspan="4">OFC ข้าราชการ</th>
                    <th class="th-purple" colspan="4">BKK กทม.</th>
                    <th class="th-purple" colspan="4">BMT ขสมก.</th>
                    <th class="th-green" colspan="4">SSS ประกันสังคม</th>
                    <th class="th-green" colspan="4">LGO อปท.</th>
                    <th class="th-cyan" colspan="4">FSS ต่างด้าว</th>
                    <th class="th-cyan" colspan="4">STP Stateless</th>
                    <th class="th-grey" colspan="4">ชำระเงิน/พรบ.</th>
                  </tr>
                  <tr>
                    @for ($i = 0; $i < 3; $i++)
                        <th class="th-blue">Visit</th><th class="th-blue">ค่ารักษารวม</th><th class="th-blue">ค่า Lab</th><th class="th-blue">ค่า ยา</th>
                    @endfor
                    <th class="th-orange">Visit</th><th class="th-orange">ค่ารักษารวม</th><th class="th-orange">ค่า Lab</th><th class="th-orange">ค่า ยา</th>
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-purple">Visit</th><th class="th-purple">ค่ารักษารวม</th><th class="th-purple">ค่า Lab</th><th class="th-purple">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-green">Visit</th><th class="th-green">ค่ารักษารวม</th><th class="th-green">ค่า Lab</th><th class="th-green">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-cyan">Visit</th><th class="th-cyan">ค่ารักษารวม</th><th class="th-cyan">ค่า Lab</th><th class="th-cyan">ค่า ยา</th>
                    @endfor
                    <th class="th-grey">Visit</th><th class="th-grey">ค่ารักษารวม</th><th class="th-grey">ค่า Lab</th><th class="th-grey">ค่า ยา</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($total_10990 as $row)
                  <tr>
                      <td class="text-center">{{ $row->month }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_incup) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_inprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_outprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ofc) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bkk) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bmt) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_sss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_lgo) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_fss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_stp) }}</td>
                      <td class="text-end">{{ number_format($row->inc_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_pay) }}</td>
                      <td class="text-end">{{ number_format($row->inc_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_ucs_incup')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_ucs_inprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_ucs_outprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_ofc')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_bkk')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_bmt')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_sss')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_lgo')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_fss')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_stp')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('visit_pay')) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_lab_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10990->sum('inc_drug_pay'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>                  
        <!-- END 10990 -->
        </div>

        <!-- 10703-->
        <div class="tab-pane fade" id="pane-10703" role="tabpanel" aria-labelledby="tab-10703" tabindex="0">
          <!-- 10703 OPD-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-hospital-user text-primary me-2"></i>[10703] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลอำนาจเจริญ ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10703}}</span>
                <div id="btn-10703"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10703" class="table custom-table my-3" width ="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width ="4%">เดือน</th>
                    <th class="th-blue" colspan="7">ข้อมูลทั้งหมด</th>  
                    <th class="th-green" rowspan="2">Visit ทันตกรรม</th>   
                    <th class="th-green" rowspan="2">Visit กายภาพบำบัด</th> 
                    <th class="th-green" rowspan="2">Visit ฝากครรภ์</th> 
                    <th class="th-green" rowspan="2">Visit แพทย์แผนไทย</th>  
                    <th class="th-green" rowspan="2">Visit การแพทย์ทางไกล</th>    
                    <th class="th-orange" colspan="2">นัดหมายออนไลน์</th>                    
                  </tr>    
                  <tr>        
                    <th class="th-blue">HN Total</th>
                    <th class="th-blue">Visit Total</th>
                    <th class="th-blue">Visit OP</th>
                    <th class="th-blue">Visit PP</th>
                    <th class="th-blue">ค่ารักษารวม</th>
                    <th class="th-blue">ค่า Lab</th>
                    <th class="th-blue">ค่า ยา</th>
                    <th class="th-orange">จองคิวนัดหมาย</th>
                    <th class="th-orange">เข้ารับบริการ</th>                   
                  </tr>    
                </thead>
                <tbody>
                  @foreach($total_10703 as $row) 
                  <tr>
                    <td class="text-center">{{ $row->month }}</td>
                    <td class="text-end">{{ number_format($row->hn_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_op) }}</td>
                    <td class="text-end">{{ number_format($row->visit_total_pp) }}</td>
                    <td class="text-end">{{ number_format($row->inc_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td class="text-end">{{ number_format($row->visit_dent) }}</td>
                    <td class="text-end">{{ number_format($row->visit_physic) }}</td>
                    <td class="text-end">{{ number_format($row->visit_anc) }}</td>
                    <td class="text-end">{{ number_format($row->visit_healthmed) }}</td>
                    <td class="text-end">{{ number_format($row->visit_telehealth) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp_booking) }}</td>
                    <td class="text-end">{{ number_format($row->visit_moph_oapp) }}</td>
                  </tr>       
                  @endforeach    
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10703->sum('hn_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_total')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_total_op')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_total_pp')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_total'), 2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_dent')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_physic')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_anc')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_healthmed')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_telehealth')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_moph_oapp_booking')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_moph_oapp')) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>   
          <!-- 10703 ค่ารักษาพยาบาล-->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>[10703] ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลอำนาจเจริญ ปีงบประมาณ {{$budget_year}}</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small">Update {{$update_at10703}}</span>
                <div id="btn-10703-inc"></div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="table10703_inc" class="table custom-table my-3" width="100%">
                <thead>
                  <tr>
                    <th class="th-grey" rowspan="2" width="4%">เดือน</th>
                    <th class="th-blue" colspan="4">UCS ใน CUP</th>
                    <th class="th-blue" colspan="4">UCS ในจังหวัด</th>
                    <th class="th-blue" colspan="4">UCS นอกจังหวัด</th>
                    <th class="th-orange" colspan="4">OFC ข้าราชการ</th>
                    <th class="th-purple" colspan="4">BKK กทม.</th>
                    <th class="th-purple" colspan="4">BMT ขสมก.</th>
                    <th class="th-green" colspan="4">SSS ประกันสังคม</th>
                    <th class="th-green" colspan="4">LGO อปท.</th>
                    <th class="th-cyan" colspan="4">FSS ต่างด้าว</th>
                    <th class="th-cyan" colspan="4">STP Stateless</th>
                    <th class="th-grey" colspan="4">ชำระเงิน/พรบ.</th>
                  </tr>
                  <tr>
                    @for ($i = 0; $i < 3; $i++)
                        <th class="th-blue">Visit</th><th class="th-blue">ค่ารักษารวม</th><th class="th-blue">ค่า Lab</th><th class="th-blue">ค่า ยา</th>
                    @endfor
                    <th class="th-orange">Visit</th><th class="th-orange">ค่ารักษารวม</th><th class="th-orange">ค่า Lab</th><th class="th-orange">ค่า ยา</th>
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-purple">Visit</th><th class="th-purple">ค่ารักษารวม</th><th class="th-purple">ค่า Lab</th><th class="th-purple">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-green">Visit</th><th class="th-green">ค่ารักษารวม</th><th class="th-green">ค่า Lab</th><th class="th-green">ค่า ยา</th>
                    @endfor
                    @for ($i = 0; $i < 2; $i++)
                        <th class="th-cyan">Visit</th><th class="th-cyan">ค่ารักษารวม</th><th class="th-cyan">ค่า Lab</th><th class="th-cyan">ค่า ยา</th>
                    @endfor
                    <th class="th-grey">Visit</th><th class="th-grey">ค่ารักษารวม</th><th class="th-grey">ค่า Lab</th><th class="th-grey">ค่า ยา</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($total_10703 as $row)
                  <tr>
                      <td class="text-center">{{ $row->month }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_incup) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_inprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ucs_outprov) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_ofc) }}</td>
                      <td class="text-end">{{ number_format($row->inc_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_ofc,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bkk) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bkk,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_bmt) }}</td>
                      <td class="text-end">{{ number_format($row->inc_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_bmt,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_sss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_sss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_lgo) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_lgo,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_fss) }}</td>
                      <td class="text-end">{{ number_format($row->inc_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_fss,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_stp) }}</td>
                      <td class="text-end">{{ number_format($row->inc_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_stp,2) }}</td>
                      <td class="text-end">{{ number_format($row->visit_pay) }}</td>
                      <td class="text-end">{{ number_format($row->inc_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_lab_pay,2) }}</td>
                      <td class="text-end">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  @endforeach
                  <tr class="tr-total">
                    <td class="text-center">รวม</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_ucs_incup')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_ucs_incup'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_ucs_inprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_ucs_inprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_ucs_outprov')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_ucs_outprov'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_ofc')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_ofc'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_bkk')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_bkk'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_bmt')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_bmt'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_sss')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_sss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_lgo')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_lgo'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_fss')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_fss'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_stp')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_stp'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('visit_pay')) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_lab_pay'),2) }}</td>
                    <td class="text-end">{{ number_format($total_10703->sum('inc_drug_pay'),2) }}</td>
                  </tr>
                </tbody>
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
      var table10985 = $('#table10985').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลชานุมาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10985.buttons().container().appendTo('#btn-10985');
    });
  </script>
  <script>
    $(function () {
      var table10985_inc = $('#table10985_inc').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลชานุมาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10985_inc.buttons().container().appendTo('#btn-10985-inc');
    });
  </script>
  <script>
    $(function () {
      var table10986 = $('#table10986').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลปทุมราชวงศา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10986.buttons().container().appendTo('#btn-10986');
    });
  </script>
  <script>
    $(function () {
      var table10986_inc = $('#table10986_inc').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลปทุมราชวงศา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10986_inc.buttons().container().appendTo('#btn-10986-inc');
    });
  </script>
  <script>
    $(function () {
      var table10987 = $('#table10987').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลพนา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10987.buttons().container().appendTo('#btn-10987');
    });
  </script>
  <script>
    $(function () {
      var table10987_inc = $('#table10987_inc').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลพนา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10987_inc.buttons().container().appendTo('#btn-10987-inc');
    });
  </script>
  <script>
    $(function () {
      var table10988 = $('#table10988').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลเสนางคนิคม {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10988.buttons().container().appendTo('#btn-10988');
    });
  </script>
    <script>
    $(function () {
      var table10988_inc = $('#table10988_inc').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลเสนางคนิคม {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10988_inc.buttons().container().appendTo('#btn-10988-inc');
    });
  </script>
  <script>
    $(function () {
      var table10989 = $('#table10989').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลหัวตะพาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10989.buttons().container().appendTo('#btn-10989');
    });
  </script>
    <script>
    $(function () {
      var table10989_inc = $('#table10989_inc').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลหัวตะพาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10989_inc.buttons().container().appendTo('#btn-10989-inc');
    });
  </script>
  <script>
    $(function () {
      var table10990 = $('#table10990').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลลืออำนาจ {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10990.buttons().container().appendTo('#btn-10990');
    });
  </script>
    <script>
    $(function () {
      var table10990_inc = $('#table10990_inc').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลลืออำนาจ {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10990_inc.buttons().container().appendTo('#btn-10990-inc');
    });
  </script>
  <script>
    $(function () {
      var table10703 = $('#table10703').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลอำนาจเจริญ {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10703.buttons().container().appendTo('#btn-10703');
    });
  </script>
    <script>
    $(function () {
      var table10703_inc = $('#table10703_inc').DataTable({
        dom: 'rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm rounded-pill px-3',
            title: 'ข้อมูลค่ารักษาพยาบาลผู้ป่วยนอก OPD แยกกลุ่มสิทธิ โรงพยาบาลอำนาจเจริญ {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
      table10703_inc.buttons().container().appendTo('#btn-10703-inc');
    });
  </script>
@endpush

