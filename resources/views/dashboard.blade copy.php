@extends('layouts.app')

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

  <!-- SUMMARY (4 blocks, no foreach) -->
  <section id="summary" class="pb-2">
    <div class="container-fluid">
      @php
        $fmtInt   = fn($n) => number_format((int)($n ?? 0));
        $fmtMoney = fn($n) => number_format((float)($n ?? 0), 2);
      @endphp

      <div class="row g-3">
        
        {{--  กำลังรักษาอยู่ ------------------------------------------------------------------------------------------------------------ --}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#AdmiitDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-danger"><strong>กำลังรักษาอยู่ </strong></h6>
                <span> <i class="bi bi-hospital fs-5 text-danger"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">จำนวนเตียง</div>
                  <div class="fw-bold text-primary" style="font-size:1.75rem;">
                    {{ $fmtInt($total_bed_qty ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">Admit</div>
                  <div class="fw-bold text-danger" style="font-size:1.75rem;">
                    {{ $fmtInt($total_bed_use ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">เตียงว่าง</div>
                  <div class="fw-bold text-success" style="font-size:1.75rem;">
                    {{ $fmtInt($total_bed_empty ?? 0) }}
                  </div>
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
                  <i class="bi bi-hospital me-2"></i> ข้อมูลจำนวนเตียง
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
                      <th>จำนวนเตียง</th>
                      <th>Admit</th>
                      <th>เตียงว่าง</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($hospitals as $h)
                      <tr>
                        <td align="right" class="text-secondary">{{ $h->hospcode }}</td>
                        <td>
                          <span class="fw-semibold text-dark">{{ $h->hospname }}</span><br>
                          <small class="text-muted">
                            {{ \Carbon\Carbon::parse($h->updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') }} น.
                          </small>
                        </td>
                        <td align="right" class="text-primary">{{ $h->bed_qty }}</td>
                        <td align="right" class="text-danger">{{ $h->bed_use }}</td>
                        <td align="right" class="fw-bold text-success">
                          {{ $h->bed_qty - $h->bed_use }}
                        </td>
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

        {{--  ส่งต่อ-------------------------------------------------------------------------------------------------------------- --}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#ReferDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>การส่งต่อ Refer</strong></h6>              
                <span> <i class="bi bi-truck fs-5 text-danger"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">Refer In</div>
                  <div class="fw-bold" style="font-size:1.75rem;">
                    {{ $fmtInt($visit_referin_inprov+$visit_referin_outprov ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">Refer Out</div>
                  <div class="fw-bold text-primary" style="font-size:1.75rem;">
                    {{ $fmtInt($visit_referout_inprov+$visit_referout_outprov ?? 0) }}
                  </div>
                </div>                
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="ReferDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">
              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-arrow-left-right me-2"></i> การส่งต่อ Refer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <!-- Body -->
              <div class="modal-body py-3">
                <table class="table table-hover align-middle shadow-sm rounded-3 overflow-hidden mb-0"
                      style="background-color: #ffffff; border-radius: 0.75rem;">
                  <thead style="background-color:#d9e8fb;">
                    <tr class="text-center text-primary fw-semibold">
                      <th rowspan="2" class="text-center">รหัส</th>
                      <th rowspan="2" class="text-center">ชื่อโรงพยาบาล</th>
                      <th colspan="2" style="border-right: 1px solid #aac6ec;">Refer In</th>
                      <th colspan="2">Refer Out</th>
                    </tr>
                    <tr class="text-center text-primary fw-semibold">
                      <th>ในจังหวัด</th>
                      <th style="border-right: 1px solid #aac6ec;">นอกจังหวัด</th>
                      <th>ในจังหวัด</th>
                      <th>นอกจังหวัด</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_referin_inprov) }}</td>
                        <td align="right" class="fw-bold text-success" style="border-right: 1px solid #aac6ec;">{{ number_format($h->visit_referin_outprov) }}</td>
                        <td align="right" class="text-primary">{{ number_format($h->visit_referout_inprov) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->visit_referout_outprov) }}</td>
                      </tr>
                    @endforeach
                    {{-- แถวผลรวม --}}
                    <tr style="background-color:#eef4fb;" class="fw-bold text-end">
                      <td colspan="2" class="text-center text-dark" >รวมทั้งหมด</td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_referin_inprov')) }}</td>
                      <td class="text-success" style="border-right: 1px solid #aac6ec;">{{ number_format($hospitalSummary->sum('visit_referin_outprov')) }}</td>
                      <td class="text-primary">{{ number_format($hospitalSummary->sum('visit_referout_inprov')) }}</td>
                      <td class="text-success">{{ number_format($hospitalSummary->sum('visit_referout_outprov')) }}</td>
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

        {{--  ผู้ป่วยนอก ----------------------------------------------------------------------------------------------- --}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#VisitDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>ผู้ป่วยนอก OPD</strong></h6>
                <span><i class="bi bi-person-heart fs-5 text-primary"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit op</div>
                  <div class="fw-bold" style="font-size:1.75rem;">
                    {{ $fmtInt($visit_total_op ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">visit pp</div>
                  <div class="fw-bold text-primary" style="font-size:1.75rem;">
                    {{ $fmtInt($visit_total_pp ?? 0) }}
                  </div>
                </div>          
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">ปิดสิทธิ สปสช.</div>
                  <div class="fw-bold text-success" style="font-size:1.75rem;">
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
                            {{ \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') }} น.
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

        {{--  สิทธิประกันสุขภาพ UCS------------------------------------------------------------------------------------------------ --}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#UCSDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>สิทธิประกันสุขภาพ UCS</strong></h6>
                <span><i class="bi bi-person-heart fs-5 text-success"></i></span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ucs ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_ucs ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="UCSDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-shield-check me-2"></i> สิทธิประกันสุขภาพ (UCS)
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_ucs) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_ucs,2) }}</td>
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

  <!-- SUMMARY (4 blocks, no foreach) ----------------------------------------------------------------------------------------->
  <section id="summary" class="pb-2">
    <div class="container-fluid">
      @php
        $fmtInt   = fn($n) => number_format((int)($n ?? 0));
        $fmtMoney = fn($n) => number_format((float)($n ?? 0), 2);
      @endphp

      <div class="row g-3">      

        {{--  สิทธิกรมบัญชีกลาง OFC -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#OFCDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>สิทธิกรมบัญชีกลาง OFC</strong></h6>
                <span><i class="bi bi-people fs-5 text-info"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ofc ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_ofc ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="OFCDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-clipboard2-pulse me-2"></i> สิทธิกรมบัญชีกลาง (OFC)
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_ofc) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_ofc,2) }}</td>
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

        {{--  สิทธิ อปท. LGO -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#LGODetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>สิทธิ อปท. LGO</strong></h6>
                <span><i class="bi bi-people fs-5 text-success"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_lgo ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_lgo ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="LGODetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-building-check me-2"></i> สิทธิ อปท. (LGO)
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_lgo) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_lgo,2) }}</td>
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

        {{--  สิทธิประกันสังคม SSS -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#SSSDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>สิทธิประกันสังคม SSS</strong></h6>
                <span><i class="bi bi-people fs-5 text-warning"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_sss ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_sss ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="SSSDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-person-vcard me-2"></i> สิทธิประกันสังคม (SSS)
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_sss) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_sss,2) }}</td>
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

        {{--  ชำระเงิน/พรบ. -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#PayDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>สิทธิชำระเงิน/พรบ.</strong></h6>
                <span><i class="bi bi-people fs-5 text-primary"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_pay ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_pay ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="PayDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-cash-coin me-2"></i> สิทธิชำระเงิน / พ.ร.บ.
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_pay) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_pay,2) }}</td>
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

  <!-- SUMMARY (4 blocks, no foreach) ------------------------------------------------------------------------------------>
  <section id="summary" class="pb-2">
    <div class="container-fluid">
      @php
        $fmtInt   = fn($n) => number_format((int)($n ?? 0));
        $fmtMoney = fn($n) => number_format((float)($n ?? 0), 2);
      @endphp

      <div class="row g-3">      

        {{--  PP Fee Schedule : ครั้ง -------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#PPFSDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>PP Fee Schedule</strong></h6>
                <span><i class="bi bi-diagram-3 fs-5 text-warning"></i></span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ppfs ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_ppfs ?? 0) }}
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_ppfs,2) }}</td>
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

        {{--  UC-OP Anywhere : ครั้ง | บาท -------------------------------------------------------------------------------------------}}
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#AnywhereDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>UC-OP Anywhere</strong></h6>
                <span><i class="bi bi-people fs-5 text-info"></i> </span>
              </div>
              <div class="d-flex align-items-end gap-4">
                <div class="text-end">
                  <div class="small text-secondary text-center">visit</div>
                  <div class="fw-bold" style="font-size:1.5rem;">
                    {{ $fmtInt($visit_ucs_outprov ?? 0) }}
                  </div>
                </div>
                <div class="vr d-none d-sm-block"></div>
                <div class="text-end">
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_ucs_outprov ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div> 
        {{-- Modal แสดงรายละเอียด รพ. (โทนน้ำเงินพาสเทลเข้ม / modal-lg) --}}
        <div class="modal fade" id="AnywhereDetailModal" tabindex="-1" aria-labelledby="hospitalDetailLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color:#f5f8fc;">

              <!-- Header -->
              <div class="modal-header text-white rounded-top-3"
                  style="background: linear-gradient(135deg, #2f6fb6, #4b8edc);">
                <h5 class="modal-title fw-bold" id="hospitalDetailLabel">
                  <i class="bi bi-globe2 me-2"></i> UC-OP Anywhere
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="text-primary">{{ number_format($h->visit_ucs_outprov) }}</td>
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_ucs_outprov,2) }}</td>
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
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#CrDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>UC-บริการเฉพาะ CR </strong></h6>
                <span><i class="bi bi-hospital fs-5 text-danger"></i> </span>
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
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_uccr ?? 0) }}
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_uccr,2) }}</td>
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
        <div class="col-12 col-sm-6 col-xl-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#HerbDetailModal" class="text-decoration-none text-dark">
            <div class="glass p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 text-primary"><strong>UC-สมุนไพร 32 รายการ</strong></h6>
                <span><i class="bi bi-capsule fs-5 text-danger"></i></span></span>
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
                  <div class="small text-secondary text-center">บาท</div>
                  <div class="fw-bold text-success" style="font-size:1.5rem;">
                    {{ $fmtMoney($inc_herb ?? 0) }}
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
                      <th>ค่ารักษารวม (บาท)</th>
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
                        <td align="right" class="fw-bold text-success">{{ number_format($h->inc_herb,2) }}</td>
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

        <!-- 10985 OPD -->
        <div class="tab-pane fade show active" id="pane-10985" role="tabpanel" aria-labelledby="tab-10985" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10985] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาชานุมาน ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10985}}</span>              
            </div>
            <div class="table-responsive">
              <table id="table10985" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-primary">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" colspan="7">ทั้งหมด</th>
                    <th class="text-center" colspan="4">UCS ใน CUP</th> 
                    <th class="text-center" colspan="4">UCS ในจังหวัด</th>
                    <th class="text-center" colspan="4">UCS นอกจังหวัด</th>       
                    <th class="text-center" colspan="4">OFC ข้าราชการ</th>  
                    <th class="text-center" colspan="4">BKK กทม.</th>
                    <th class="text-center" colspan="4">BMT ขสมก.</th>
                    <th class="text-center" colspan="4">SSS ประกันสังคม</th>
                    <th class="text-center" colspan="4">LGO อปท.</th>
                    <th class="text-center" colspan="4">FSS ต่างด้าว</th>
                    <th class="text-center" colspan="4">STP Stateless</th>
                    <th class="text-center" colspan="4">ชำระเงิน/พรบ.</th>                 
                  </tr>    
                  <tr class="table-primary">            
                    <td class="text-center text-primary">HN Total</td>
                    <td class="text-center text-primary">Visit Total</td>
                    <td class="text-center text-primary">Visit OP</td>
                    <td class="text-center text-primary">Visit PP</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                  </tr>    
                </thead>
                <tbody>
                  <?php $count = 1 ; ?> 
                  <?php $sum_hn_total = 0 ; ?> 
                  <?php $sum_visit_total = 0 ; ?>   
                  <?php $sum_visit_total_op = 0 ; ?>  
                  <?php $sum_visit_total_pp = 0 ; ?> 
                  <?php $sum_inc_total = 0 ; ?>  
                  <?php $sum_inc_lab_total = 0 ; ?>
                  <?php $sum_inc_drug_total = 0 ; ?> 
                  <?php $sum_visit_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_drug_ucs_incup = 0 ; ?>  
                  <?php $sum_visit_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_inprov = 0 ; ?> 
                  <?php $sum_inc_drug_ucs_inprov = 0 ; ?>   
                  <?php $sum_visit_ucs_outprov = 0 ; ?>  
                  <?php $sum_inc_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_lab_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_drug_ucs_outprov = 0 ; ?>  
                  <?php $sum_visit_ofc = 0 ; ?>  
                  <?php $sum_inc_ofc = 0 ; ?>
                  <?php $sum_inc_lab_ofc = 0 ; ?>
                  <?php $sum_inc_drug_ofc = 0 ; ?>
                  <?php $sum_visit_bkk = 0 ; ?>  
                  <?php $sum_inc_bkk = 0 ; ?>
                  <?php $sum_inc_lab_bkk = 0 ; ?>
                  <?php $sum_inc_drug_bkk = 0 ; ?> 
                  <?php $sum_visit_bmt = 0 ; ?>  
                  <?php $sum_inc_bmt = 0 ; ?>
                  <?php $sum_inc_lab_bmt = 0 ; ?>
                  <?php $sum_inc_drug_bmt = 0 ; ?>  
                  <?php $sum_visit_sss = 0 ; ?>  
                  <?php $sum_inc_sss = 0 ; ?>
                  <?php $sum_inc_lab_sss = 0 ; ?>
                  <?php $sum_inc_drug_sss = 0 ; ?> 
                  <?php $sum_visit_lgo = 0 ; ?>  
                  <?php $sum_inc_lgo = 0 ; ?>
                  <?php $sum_inc_lab_lgo = 0 ; ?>
                  <?php $sum_inc_drug_lgo = 0 ; ?> 
                  <?php $sum_visit_fss = 0 ; ?>  
                  <?php $sum_inc_fss = 0 ; ?>
                  <?php $sum_inc_lab_fss = 0 ; ?>
                  <?php $sum_inc_drug_fss = 0 ; ?> 
                  <?php $sum_visit_stp = 0 ; ?>  
                  <?php $sum_inc_stp = 0 ; ?>
                  <?php $sum_inc_lab_stp = 0 ; ?>
                  <?php $sum_inc_drug_stp = 0 ; ?>
                  <?php $sum_visit_pay = 0 ; ?>  
                  <?php $sum_inc_pay = 0 ; ?>
                  <?php $sum_inc_lab_pay = 0 ; ?>
                  <?php $sum_inc_drug_pay = 0 ; ?>  
                  @foreach($total_10985 as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->hn_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total_op) }}</td>
                    <td align="right">{{ number_format($row->visit_total_pp) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_incup) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_inprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_outprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ofc) }}</td>
                    <td align="right">{{ number_format($row->inc_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bkk) }}</td>
                    <td align="right">{{ number_format($row->inc_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bmt) }}</td>
                    <td align="right">{{ number_format($row->inc_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->visit_sss) }}</td>
                    <td align="right">{{ number_format($row->inc_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_sss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_lgo) }}</td>
                    <td align="right">{{ number_format($row->inc_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->visit_fss) }}</td>
                    <td align="right">{{ number_format($row->inc_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_fss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_stp) }}</td>
                    <td align="right">{{ number_format($row->inc_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_stp,2) }}</td>
                    <td align="right">{{ number_format($row->visit_pay) }}</td>
                    <td align="right">{{ number_format($row->inc_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  <?php $count++; ?>
                  <?php $sum_hn_total += $row->hn_total ; ?>
                  <?php $sum_visit_total += $row->visit_total ; ?>
                  <?php $sum_visit_total_op += $row->visit_total_op ; ?>
                  <?php $sum_visit_total_pp += $row->visit_total_pp ; ?>
                  <?php $sum_inc_total += $row->inc_total ; ?>
                  <?php $sum_inc_lab_total += $row->inc_lab_total ; ?>
                  <?php $sum_inc_drug_total += $row->inc_drug_total ; ?>
                  <?php $sum_visit_ucs_incup += $row->visit_ucs_incup ; ?>
                  <?php $sum_inc_ucs_incup += $row->inc_ucs_incup ; ?>
                  <?php $sum_inc_lab_ucs_incup += $row->inc_lab_ucs_incup ; ?>
                  <?php $sum_inc_drug_ucs_incup += $row->inc_drug_ucs_incup ; ?>   
                  <?php $sum_visit_ucs_inprov += $row->visit_ucs_inprov ; ?>
                  <?php $sum_inc_ucs_inprov += $row->inc_ucs_inprov ; ?>
                  <?php $sum_inc_lab_ucs_inprov += $row->inc_lab_ucs_inprov ; ?>
                  <?php $sum_inc_drug_ucs_inprov += $row->inc_drug_ucs_inprov ; ?>
                  <?php $sum_visit_ucs_outprov += $row->visit_ucs_outprov ; ?>
                  <?php $sum_inc_ucs_outprov += $row->inc_ucs_outprov ; ?>
                  <?php $sum_inc_lab_ucs_outprov += $row->inc_lab_ucs_outprov ; ?>
                  <?php $sum_inc_drug_ucs_outprov += $row->inc_drug_ucs_outprov ; ?> 
                  <?php $sum_visit_ofc += $row->visit_ofc ; ?>
                  <?php $sum_inc_ofc += $row->inc_ofc ; ?>
                  <?php $sum_inc_lab_ofc += $row->inc_lab_ofc ; ?>
                  <?php $sum_inc_drug_ofc += $row->inc_drug_ofc ; ?> 
                  <?php $sum_visit_bkk += $row->visit_bkk ; ?>
                  <?php $sum_inc_bkk += $row->inc_bkk ; ?>
                  <?php $sum_inc_lab_bkk += $row->inc_lab_bkk ; ?>
                  <?php $sum_inc_drug_bkk += $row->inc_drug_bkk ; ?>  
                  <?php $sum_visit_bmt += $row->visit_bmt ; ?>
                  <?php $sum_inc_bmt += $row->inc_bmt ; ?>
                  <?php $sum_inc_lab_bmt += $row->inc_lab_bmt ; ?>
                  <?php $sum_inc_drug_bmt += $row->inc_drug_bmt ; ?> 
                  <?php $sum_visit_sss += $row->visit_sss ; ?>
                  <?php $sum_inc_sss += $row->inc_sss ; ?>
                  <?php $sum_inc_lab_sss += $row->inc_lab_sss ; ?>
                  <?php $sum_inc_drug_sss += $row->inc_drug_sss ; ?>   
                  <?php $sum_visit_lgo += $row->visit_lgo ; ?>
                  <?php $sum_inc_lgo += $row->inc_lgo ; ?>
                  <?php $sum_inc_lab_lgo += $row->inc_lab_lgo ; ?>
                  <?php $sum_inc_drug_lgo += $row->inc_drug_lgo ; ?>
                  <?php $sum_visit_fss += $row->visit_fss ; ?>
                  <?php $sum_inc_fss += $row->inc_fss ; ?>
                  <?php $sum_inc_lab_fss += $row->inc_lab_fss ; ?>
                  <?php $sum_inc_drug_fss += $row->inc_drug_fss ; ?>    
                  <?php $sum_visit_stp += $row->visit_stp ; ?>
                  <?php $sum_inc_stp += $row->inc_stp ; ?>
                  <?php $sum_inc_lab_stp += $row->inc_lab_stp ; ?>
                  <?php $sum_inc_drug_stp += $row->inc_drug_stp ; ?>   
                  <?php $sum_visit_pay += $row->visit_pay ; ?>
                  <?php $sum_inc_pay += $row->inc_pay ; ?>
                  <?php $sum_inc_lab_pay += $row->inc_lab_pay ; ?>
                  <?php $sum_inc_drug_pay += $row->inc_drug_pay ; ?> 
                  @endforeach    
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_hn_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total_op)}}</strong></td>     
                    <td align="right"><strong>{{number_format($sum_visit_total_pp)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_inc_lab_total,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_incup)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_incup,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_ucs_inprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_outprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_outprov,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_ofc)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ofc,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bkk)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bkk,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bmt)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bmt,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_sss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_sss,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_lgo)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_lgo,2)}}</strong></td>       
                    <td align="right"><strong>{{number_format($sum_visit_fss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_fss,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_stp)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_stp,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_pay)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_pay,2)}}</strong></td> 
                  </tr>   
                </tbody>
              </table>
            </div>
          </div>
          <br> <!-- 10985 IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10985] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาชานุมาน ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10985}}</span>              
            </div>
            <div class="table-responsive">
              <table id="table10985_ipd" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-danger">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" rowspan="2">จำนวน AN</th>
                    <th class="text-center" rowspan="2">วันนอนรวม</th> 
                    <th class="text-center" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="text-center" rowspan="2">Active Base (เตียง)</th>       
                    <th class="text-center" rowspan="2">AdjRW</th>  
                    <th class="text-center" rowspan="2">CMI</th>
                    <th class="text-center" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr class="table-danger"> 
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>                 
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
                    $bed_report = $total_10985_ipd[0]->bed_report ?? 30; // ค่าเตียงจาก hospital_config
                  ?>  
                  @foreach($total_10985_ipd as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right">{{ number_format($row->bed_occupancy,2) }}</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total;
                    $sum_admdate += $row->admdate;
                    $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total;
                    $sum_inc_lab_total += $row->inc_lab_total;
                    $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                  // ✅ จำนวนเตียง
                    $bed_report = 30;
                  // ✅ อัตราครองเตียงรวม
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                  // ✅ Active Bed = วันนอนรวม ÷ จำนวนวัน
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                  // ✅ CMI รวม
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_an_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_admdate)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_bed_occupancy,2)}}</td>     
                    <td align="right"><strong>{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right"><strong>{{number_format($sum_adjrw,4)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_cmi,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                  </tr>   
                </tbody>
              </table>
              <!-- กราฟ -->
              <div class="row mt-4">
                <!-- กราฟอัตราครองเตียง -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-primary mb-1">
                        📈 อัตราครองเตียง (%)
                      </h6>
                      <div id="bed_occupancy_10985"></div>
                    </div>
                  </div>
                </div>
                <!-- กราฟ CMI -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-danger mb-1">
                        📊 CMI
                      </h6>
                      <div id="cmi_chart_10985"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>          
        <!-- END 10985 -->
        </div>

        <!-- 10986 OPD -->
        <div class="tab-pane fade" id="pane-10986" role="tabpanel" aria-labelledby="tab-10986" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10986] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาปทุมราชวงศา ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10986}}</span>            
            </div>
            <div class="table-responsive">
              <table id="table10986" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-primary">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" colspan="7">ทั้งหมด</th>
                    <th class="text-center" colspan="4">UCS ใน CUP</th> 
                    <th class="text-center" colspan="4">UCS ในจังหวัด</th>
                    <th class="text-center" colspan="4">UCS นอกจังหวัด</th>       
                    <th class="text-center" colspan="4">OFC ข้าราชการ</th>  
                    <th class="text-center" colspan="4">BKK กทม.</th>
                    <th class="text-center" colspan="4">BMT ขสมก.</th>
                    <th class="text-center" colspan="4">SSS ประกันสังคม</th>
                    <th class="text-center" colspan="4">LGO อปท.</th>
                    <th class="text-center" colspan="4">FSS ต่างด้าว</th>
                    <th class="text-center" colspan="4">STP Stateless</th>
                    <th class="text-center" colspan="4">ชำระเงิน/พรบ.</th>                 
                  </tr>    
                  <tr class="table-primary">            
                    <td class="text-center text-primary">HN Total</td>
                    <td class="text-center text-primary">Visit Total</td>
                    <td class="text-center text-primary">Visit OP</td>
                    <td class="text-center text-primary">Visit PP</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                  </tr>    
                </thead>
                <tbody>
                  <?php $count = 1 ; ?> 
                  <?php $sum_hn_total = 0 ; ?> 
                  <?php $sum_visit_total = 0 ; ?>   
                  <?php $sum_visit_total_op = 0 ; ?>  
                  <?php $sum_visit_total_pp = 0 ; ?> 
                  <?php $sum_inc_total = 0 ; ?>  
                  <?php $sum_inc_lab_total = 0 ; ?>
                  <?php $sum_inc_drug_total = 0 ; ?> 
                  <?php $sum_visit_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_drug_ucs_incup = 0 ; ?>  
                  <?php $sum_visit_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_inprov = 0 ; ?> 
                  <?php $sum_inc_drug_ucs_inprov = 0 ; ?>   
                  <?php $sum_visit_ucs_outprov = 0 ; ?>  
                  <?php $sum_inc_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_lab_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_drug_ucs_outprov = 0 ; ?>  
                  <?php $sum_visit_ofc = 0 ; ?>  
                  <?php $sum_inc_ofc = 0 ; ?>
                  <?php $sum_inc_lab_ofc = 0 ; ?>
                  <?php $sum_inc_drug_ofc = 0 ; ?>
                  <?php $sum_visit_bkk = 0 ; ?>  
                  <?php $sum_inc_bkk = 0 ; ?>
                  <?php $sum_inc_lab_bkk = 0 ; ?>
                  <?php $sum_inc_drug_bkk = 0 ; ?> 
                  <?php $sum_visit_bmt = 0 ; ?>  
                  <?php $sum_inc_bmt = 0 ; ?>
                  <?php $sum_inc_lab_bmt = 0 ; ?>
                  <?php $sum_inc_drug_bmt = 0 ; ?>  
                  <?php $sum_visit_sss = 0 ; ?>  
                  <?php $sum_inc_sss = 0 ; ?>
                  <?php $sum_inc_lab_sss = 0 ; ?>
                  <?php $sum_inc_drug_sss = 0 ; ?> 
                  <?php $sum_visit_lgo = 0 ; ?>  
                  <?php $sum_inc_lgo = 0 ; ?>
                  <?php $sum_inc_lab_lgo = 0 ; ?>
                  <?php $sum_inc_drug_lgo = 0 ; ?> 
                  <?php $sum_visit_fss = 0 ; ?>  
                  <?php $sum_inc_fss = 0 ; ?>
                  <?php $sum_inc_lab_fss = 0 ; ?>
                  <?php $sum_inc_drug_fss = 0 ; ?> 
                  <?php $sum_visit_stp = 0 ; ?>  
                  <?php $sum_inc_stp = 0 ; ?>
                  <?php $sum_inc_lab_stp = 0 ; ?>
                  <?php $sum_inc_drug_stp = 0 ; ?>
                  <?php $sum_visit_pay = 0 ; ?>  
                  <?php $sum_inc_pay = 0 ; ?>
                  <?php $sum_inc_lab_pay = 0 ; ?>
                  <?php $sum_inc_drug_pay = 0 ; ?>  
                  @foreach($total_10986 as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->hn_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total_op) }}</td>
                    <td align="right">{{ number_format($row->visit_total_pp) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_incup) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_inprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_outprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ofc) }}</td>
                    <td align="right">{{ number_format($row->inc_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bkk) }}</td>
                    <td align="right">{{ number_format($row->inc_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bmt) }}</td>
                    <td align="right">{{ number_format($row->inc_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->visit_sss) }}</td>
                    <td align="right">{{ number_format($row->inc_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_sss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_lgo) }}</td>
                    <td align="right">{{ number_format($row->inc_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->visit_fss) }}</td>
                    <td align="right">{{ number_format($row->inc_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_fss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_stp) }}</td>
                    <td align="right">{{ number_format($row->inc_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_stp,2) }}</td>
                    <td align="right">{{ number_format($row->visit_pay) }}</td>
                    <td align="right">{{ number_format($row->inc_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  <?php $count++; ?>
                  <?php $sum_hn_total += $row->hn_total ; ?>
                  <?php $sum_visit_total += $row->visit_total ; ?>
                  <?php $sum_visit_total_op += $row->visit_total_op ; ?>
                  <?php $sum_visit_total_pp += $row->visit_total_pp ; ?>
                  <?php $sum_inc_total += $row->inc_total ; ?>
                  <?php $sum_inc_lab_total += $row->inc_lab_total ; ?>
                  <?php $sum_inc_drug_total += $row->inc_drug_total ; ?>
                  <?php $sum_visit_ucs_incup += $row->visit_ucs_incup ; ?>
                  <?php $sum_inc_ucs_incup += $row->inc_ucs_incup ; ?>
                  <?php $sum_inc_lab_ucs_incup += $row->inc_lab_ucs_incup ; ?>
                  <?php $sum_inc_drug_ucs_incup += $row->inc_drug_ucs_incup ; ?>   
                  <?php $sum_visit_ucs_inprov += $row->visit_ucs_inprov ; ?>
                  <?php $sum_inc_ucs_inprov += $row->inc_ucs_inprov ; ?>
                  <?php $sum_inc_lab_ucs_inprov += $row->inc_lab_ucs_inprov ; ?>
                  <?php $sum_inc_drug_ucs_inprov += $row->inc_drug_ucs_inprov ; ?>
                  <?php $sum_visit_ucs_outprov += $row->visit_ucs_outprov ; ?>
                  <?php $sum_inc_ucs_outprov += $row->inc_ucs_outprov ; ?>
                  <?php $sum_inc_lab_ucs_outprov += $row->inc_lab_ucs_outprov ; ?>
                  <?php $sum_inc_drug_ucs_outprov += $row->inc_drug_ucs_outprov ; ?> 
                  <?php $sum_visit_ofc += $row->visit_ofc ; ?>
                  <?php $sum_inc_ofc += $row->inc_ofc ; ?>
                  <?php $sum_inc_lab_ofc += $row->inc_lab_ofc ; ?>
                  <?php $sum_inc_drug_ofc += $row->inc_drug_ofc ; ?> 
                  <?php $sum_visit_bkk += $row->visit_bkk ; ?>
                  <?php $sum_inc_bkk += $row->inc_bkk ; ?>
                  <?php $sum_inc_lab_bkk += $row->inc_lab_bkk ; ?>
                  <?php $sum_inc_drug_bkk += $row->inc_drug_bkk ; ?>  
                  <?php $sum_visit_bmt += $row->visit_bmt ; ?>
                  <?php $sum_inc_bmt += $row->inc_bmt ; ?>
                  <?php $sum_inc_lab_bmt += $row->inc_lab_bmt ; ?>
                  <?php $sum_inc_drug_bmt += $row->inc_drug_bmt ; ?> 
                  <?php $sum_visit_sss += $row->visit_sss ; ?>
                  <?php $sum_inc_sss += $row->inc_sss ; ?>
                  <?php $sum_inc_lab_sss += $row->inc_lab_sss ; ?>
                  <?php $sum_inc_drug_sss += $row->inc_drug_sss ; ?>   
                  <?php $sum_visit_lgo += $row->visit_lgo ; ?>
                  <?php $sum_inc_lgo += $row->inc_lgo ; ?>
                  <?php $sum_inc_lab_lgo += $row->inc_lab_lgo ; ?>
                  <?php $sum_inc_drug_lgo += $row->inc_drug_lgo ; ?>
                  <?php $sum_visit_fss += $row->visit_fss ; ?>
                  <?php $sum_inc_fss += $row->inc_fss ; ?>
                  <?php $sum_inc_lab_fss += $row->inc_lab_fss ; ?>
                  <?php $sum_inc_drug_fss += $row->inc_drug_fss ; ?>    
                  <?php $sum_visit_stp += $row->visit_stp ; ?>
                  <?php $sum_inc_stp += $row->inc_stp ; ?>
                  <?php $sum_inc_lab_stp += $row->inc_lab_stp ; ?>
                  <?php $sum_inc_drug_stp += $row->inc_drug_stp ; ?>   
                  <?php $sum_visit_pay += $row->visit_pay ; ?>
                  <?php $sum_inc_pay += $row->inc_pay ; ?>
                  <?php $sum_inc_lab_pay += $row->inc_lab_pay ; ?>
                  <?php $sum_inc_drug_pay += $row->inc_drug_pay ; ?> 
                  @endforeach    
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_hn_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total_op)}}</strong></td>     
                    <td align="right"><strong>{{number_format($sum_visit_total_pp)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_inc_lab_total,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_incup)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_incup,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_ucs_inprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_outprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_outprov,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_ofc)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ofc,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bkk)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bkk,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bmt)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bmt,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_sss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_sss,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_lgo)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_lgo,2)}}</strong></td>       
                    <td align="right"><strong>{{number_format($sum_visit_fss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_fss,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_stp)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_stp,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_pay)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_pay,2)}}</strong></td> 
                  </tr>                  
                </tbody>
              </table>
            </div>
          </div>
          <br> <!-- 10986 IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10986] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลปทุมราช ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10986}}</span>              
            </div>
            <div class="table-responsive">
              <table id="table10986_ipd" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-danger">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" rowspan="2">จำนวน AN</th>
                    <th class="text-center" rowspan="2">วันนอนรวม</th> 
                    <th class="text-center" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="text-center" rowspan="2">Active Base (เตียง)</th>       
                    <th class="text-center" rowspan="2">AdjRW</th>  
                    <th class="text-center" rowspan="2">CMI</th>
                    <th class="text-center" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr class="table-danger"> 
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>                 
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
                    $bed_report = $total_10986_ipd[0]->bed_report ?? 30; // ค่าเตียงจาก hospital_config
                  ?>  
                  @foreach($total_10986_ipd as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right">{{ number_format($row->bed_occupancy,2) }}</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total;
                    $sum_admdate += $row->admdate;
                    $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total;
                    $sum_inc_lab_total += $row->inc_lab_total;
                    $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                  // ✅ จำนวนเตียง
                    $bed_report = 50;
                  // ✅ อัตราครองเตียงรวม
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                  // ✅ Active Bed = วันนอนรวม ÷ จำนวนวัน
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                  // ✅ CMI รวม
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_an_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_admdate)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_bed_occupancy,2)}}</td>     
                    <td align="right"><strong>{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right"><strong>{{number_format($sum_adjrw,4)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_cmi,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                  </tr>   
                </tbody>
              </table>
              <!-- กราฟ -->
              <div class="row mt-4">
                <!-- กราฟอัตราครองเตียง -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-primary mb-1">
                        📈 อัตราครองเตียง (%)
                      </h6>
                      <div id="bed_occupancy_10986"></div>
                    </div>
                  </div>
                </div>
                <!-- กราฟ CMI -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-danger mb-1">
                        📊 CMI
                      </h6>
                      <div id="cmi_chart_10986"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10987 OPD-->
        <div class="tab-pane fade" id="pane-10987" role="tabpanel" aria-labelledby="tab-10987" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10987] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาพนา ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10987}}</span>   
            </div>
            <div class="table-responsive">
              <table id="table10987" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-primary">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" colspan="7">ทั้งหมด</th>
                    <th class="text-center" colspan="4">UCS ใน CUP</th> 
                    <th class="text-center" colspan="4">UCS ในจังหวัด</th>
                    <th class="text-center" colspan="4">UCS นอกจังหวัด</th>       
                    <th class="text-center" colspan="4">OFC ข้าราชการ</th>  
                    <th class="text-center" colspan="4">BKK กทม.</th>
                    <th class="text-center" colspan="4">BMT ขสมก.</th>
                    <th class="text-center" colspan="4">SSS ประกันสังคม</th>
                    <th class="text-center" colspan="4">LGO อปท.</th>
                    <th class="text-center" colspan="4">FSS ต่างด้าว</th>
                    <th class="text-center" colspan="4">STP Stateless</th>
                    <th class="text-center" colspan="4">ชำระเงิน/พรบ.</th>                 
                  </tr>    
                  <tr class="table-primary">            
                    <td class="text-center text-primary">HN Total</td>
                    <td class="text-center text-primary">Visit Total</td>
                    <td class="text-center text-primary">Visit OP</td>
                    <td class="text-center text-primary">Visit PP</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                  </tr>    
                </thead>
                <tbody>
                  <?php $count = 1 ; ?> 
                  <?php $sum_hn_total = 0 ; ?> 
                  <?php $sum_visit_total = 0 ; ?>   
                  <?php $sum_visit_total_op = 0 ; ?>  
                  <?php $sum_visit_total_pp = 0 ; ?> 
                  <?php $sum_inc_total = 0 ; ?>  
                  <?php $sum_inc_lab_total = 0 ; ?>
                  <?php $sum_inc_drug_total = 0 ; ?> 
                  <?php $sum_visit_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_drug_ucs_incup = 0 ; ?>  
                  <?php $sum_visit_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_inprov = 0 ; ?> 
                  <?php $sum_inc_drug_ucs_inprov = 0 ; ?>   
                  <?php $sum_visit_ucs_outprov = 0 ; ?>  
                  <?php $sum_inc_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_lab_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_drug_ucs_outprov = 0 ; ?>  
                  <?php $sum_visit_ofc = 0 ; ?>  
                  <?php $sum_inc_ofc = 0 ; ?>
                  <?php $sum_inc_lab_ofc = 0 ; ?>
                  <?php $sum_inc_drug_ofc = 0 ; ?>
                  <?php $sum_visit_bkk = 0 ; ?>  
                  <?php $sum_inc_bkk = 0 ; ?>
                  <?php $sum_inc_lab_bkk = 0 ; ?>
                  <?php $sum_inc_drug_bkk = 0 ; ?> 
                  <?php $sum_visit_bmt = 0 ; ?>  
                  <?php $sum_inc_bmt = 0 ; ?>
                  <?php $sum_inc_lab_bmt = 0 ; ?>
                  <?php $sum_inc_drug_bmt = 0 ; ?>  
                  <?php $sum_visit_sss = 0 ; ?>  
                  <?php $sum_inc_sss = 0 ; ?>
                  <?php $sum_inc_lab_sss = 0 ; ?>
                  <?php $sum_inc_drug_sss = 0 ; ?> 
                  <?php $sum_visit_lgo = 0 ; ?>  
                  <?php $sum_inc_lgo = 0 ; ?>
                  <?php $sum_inc_lab_lgo = 0 ; ?>
                  <?php $sum_inc_drug_lgo = 0 ; ?> 
                  <?php $sum_visit_fss = 0 ; ?>  
                  <?php $sum_inc_fss = 0 ; ?>
                  <?php $sum_inc_lab_fss = 0 ; ?>
                  <?php $sum_inc_drug_fss = 0 ; ?> 
                  <?php $sum_visit_stp = 0 ; ?>  
                  <?php $sum_inc_stp = 0 ; ?>
                  <?php $sum_inc_lab_stp = 0 ; ?>
                  <?php $sum_inc_drug_stp = 0 ; ?>
                  <?php $sum_visit_pay = 0 ; ?>  
                  <?php $sum_inc_pay = 0 ; ?>
                  <?php $sum_inc_lab_pay = 0 ; ?>
                  <?php $sum_inc_drug_pay = 0 ; ?>  
                  @foreach($total_10987 as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->hn_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total_op) }}</td>
                    <td align="right">{{ number_format($row->visit_total_pp) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_incup) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_inprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_outprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ofc) }}</td>
                    <td align="right">{{ number_format($row->inc_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bkk) }}</td>
                    <td align="right">{{ number_format($row->inc_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bmt) }}</td>
                    <td align="right">{{ number_format($row->inc_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->visit_sss) }}</td>
                    <td align="right">{{ number_format($row->inc_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_sss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_lgo) }}</td>
                    <td align="right">{{ number_format($row->inc_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->visit_fss) }}</td>
                    <td align="right">{{ number_format($row->inc_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_fss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_stp) }}</td>
                    <td align="right">{{ number_format($row->inc_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_stp,2) }}</td>
                    <td align="right">{{ number_format($row->visit_pay) }}</td>
                    <td align="right">{{ number_format($row->inc_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  <?php $count++; ?>
                  <?php $sum_hn_total += $row->hn_total ; ?>
                  <?php $sum_visit_total += $row->visit_total ; ?>
                  <?php $sum_visit_total_op += $row->visit_total_op ; ?>
                  <?php $sum_visit_total_pp += $row->visit_total_pp ; ?>
                  <?php $sum_inc_total += $row->inc_total ; ?>
                  <?php $sum_inc_lab_total += $row->inc_lab_total ; ?>
                  <?php $sum_inc_drug_total += $row->inc_drug_total ; ?>
                  <?php $sum_visit_ucs_incup += $row->visit_ucs_incup ; ?>
                  <?php $sum_inc_ucs_incup += $row->inc_ucs_incup ; ?>
                  <?php $sum_inc_lab_ucs_incup += $row->inc_lab_ucs_incup ; ?>
                  <?php $sum_inc_drug_ucs_incup += $row->inc_drug_ucs_incup ; ?>   
                  <?php $sum_visit_ucs_inprov += $row->visit_ucs_inprov ; ?>
                  <?php $sum_inc_ucs_inprov += $row->inc_ucs_inprov ; ?>
                  <?php $sum_inc_lab_ucs_inprov += $row->inc_lab_ucs_inprov ; ?>
                  <?php $sum_inc_drug_ucs_inprov += $row->inc_drug_ucs_inprov ; ?>
                  <?php $sum_visit_ucs_outprov += $row->visit_ucs_outprov ; ?>
                  <?php $sum_inc_ucs_outprov += $row->inc_ucs_outprov ; ?>
                  <?php $sum_inc_lab_ucs_outprov += $row->inc_lab_ucs_outprov ; ?>
                  <?php $sum_inc_drug_ucs_outprov += $row->inc_drug_ucs_outprov ; ?> 
                  <?php $sum_visit_ofc += $row->visit_ofc ; ?>
                  <?php $sum_inc_ofc += $row->inc_ofc ; ?>
                  <?php $sum_inc_lab_ofc += $row->inc_lab_ofc ; ?>
                  <?php $sum_inc_drug_ofc += $row->inc_drug_ofc ; ?> 
                  <?php $sum_visit_bkk += $row->visit_bkk ; ?>
                  <?php $sum_inc_bkk += $row->inc_bkk ; ?>
                  <?php $sum_inc_lab_bkk += $row->inc_lab_bkk ; ?>
                  <?php $sum_inc_drug_bkk += $row->inc_drug_bkk ; ?>  
                  <?php $sum_visit_bmt += $row->visit_bmt ; ?>
                  <?php $sum_inc_bmt += $row->inc_bmt ; ?>
                  <?php $sum_inc_lab_bmt += $row->inc_lab_bmt ; ?>
                  <?php $sum_inc_drug_bmt += $row->inc_drug_bmt ; ?> 
                  <?php $sum_visit_sss += $row->visit_sss ; ?>
                  <?php $sum_inc_sss += $row->inc_sss ; ?>
                  <?php $sum_inc_lab_sss += $row->inc_lab_sss ; ?>
                  <?php $sum_inc_drug_sss += $row->inc_drug_sss ; ?>   
                  <?php $sum_visit_lgo += $row->visit_lgo ; ?>
                  <?php $sum_inc_lgo += $row->inc_lgo ; ?>
                  <?php $sum_inc_lab_lgo += $row->inc_lab_lgo ; ?>
                  <?php $sum_inc_drug_lgo += $row->inc_drug_lgo ; ?>
                  <?php $sum_visit_fss += $row->visit_fss ; ?>
                  <?php $sum_inc_fss += $row->inc_fss ; ?>
                  <?php $sum_inc_lab_fss += $row->inc_lab_fss ; ?>
                  <?php $sum_inc_drug_fss += $row->inc_drug_fss ; ?>    
                  <?php $sum_visit_stp += $row->visit_stp ; ?>
                  <?php $sum_inc_stp += $row->inc_stp ; ?>
                  <?php $sum_inc_lab_stp += $row->inc_lab_stp ; ?>
                  <?php $sum_inc_drug_stp += $row->inc_drug_stp ; ?>   
                  <?php $sum_visit_pay += $row->visit_pay ; ?>
                  <?php $sum_inc_pay += $row->inc_pay ; ?>
                  <?php $sum_inc_lab_pay += $row->inc_lab_pay ; ?>
                  <?php $sum_inc_drug_pay += $row->inc_drug_pay ; ?> 
                  @endforeach    
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_hn_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total_op)}}</strong></td>     
                    <td align="right"><strong>{{number_format($sum_visit_total_pp)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_inc_lab_total,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_incup)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_incup,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_ucs_inprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_outprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_outprov,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_ofc)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ofc,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bkk)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bkk,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bmt)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bmt,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_sss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_sss,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_lgo)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_lgo,2)}}</strong></td>       
                    <td align="right"><strong>{{number_format($sum_visit_fss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_fss,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_stp)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_stp,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_pay)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_pay,2)}}</strong></td> 
                  </tr> 
                </tbody>
              </table>
            </div>
          </div>
          <br> <!-- 10987 IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10987] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลพนา ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10987}}</span>              
            </div>
            <div class="table-responsive">
              <table id="table10987_ipd" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-danger">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" rowspan="2">จำนวน AN</th>
                    <th class="text-center" rowspan="2">วันนอนรวม</th> 
                    <th class="text-center" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="text-center" rowspan="2">Active Base (เตียง)</th>        
                    <th class="text-center" rowspan="2">AdjRW</th>  
                    <th class="text-center" rowspan="2">CMI</th>
                    <th class="text-center" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr class="table-danger"> 
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>                 
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
                    $bed_report = $total_10987_ipd[0]->bed_report ?? 30; // ค่าเตียงจาก hospital_config
                  ?>  
                  @foreach($total_10987_ipd as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right">{{ number_format($row->bed_occupancy,2) }}</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total;
                    $sum_admdate += $row->admdate;
                    $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total;
                    $sum_inc_lab_total += $row->inc_lab_total;
                    $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                  // ✅ จำนวนเตียง
                    $bed_report = 30;
                  // ✅ อัตราครองเตียงรวม
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                  // ✅ Active Bed = วันนอนรวม ÷ จำนวนวัน
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                  // ✅ CMI รวม
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_an_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_admdate)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_bed_occupancy,2)}}</td>     
                    <td align="right"><strong>{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right"><strong>{{number_format($sum_adjrw,4)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_cmi,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                  </tr>   
                </tbody>
              </table>
              <!-- กราฟ -->
              <div class="row mt-4">
                <!-- กราฟอัตราครองเตียง -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-primary mb-1">
                        📈 อัตราครองเตียง (%)
                      </h6>
                      <div id="bed_occupancy_10987"></div>
                    </div>
                  </div>
                </div>
                <!-- กราฟ CMI -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-danger mb-1">
                        📊 CMI
                      </h6>
                      <div id="cmi_chart_10987"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10988 OPD -->
        <div class="tab-pane fade" id="pane-10988" role="tabpanel" aria-labelledby="tab-10988" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10988] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลเสนางคนิคม ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10988}}</span>   
            </div>
            <div class="table-responsive">
              <table id="table10988" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-primary">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" colspan="7">ทั้งหมด</th>
                    <th class="text-center" colspan="4">UCS ใน CUP</th> 
                    <th class="text-center" colspan="4">UCS ในจังหวัด</th>
                    <th class="text-center" colspan="4">UCS นอกจังหวัด</th>       
                    <th class="text-center" colspan="4">OFC ข้าราชการ</th>  
                    <th class="text-center" colspan="4">BKK กทม.</th>
                    <th class="text-center" colspan="4">BMT ขสมก.</th>
                    <th class="text-center" colspan="4">SSS ประกันสังคม</th>
                    <th class="text-center" colspan="4">LGO อปท.</th>
                    <th class="text-center" colspan="4">FSS ต่างด้าว</th>
                    <th class="text-center" colspan="4">STP Stateless</th>
                    <th class="text-center" colspan="4">ชำระเงิน/พรบ.</th>                 
                  </tr>    
                  <tr class="table-primary">            
                    <td class="text-center text-primary">HN Total</td>
                    <td class="text-center text-primary">Visit Total</td>
                    <td class="text-center text-primary">Visit OP</td>
                    <td class="text-center text-primary">Visit PP</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                  </tr>    
                </thead>
                <tbody>
                  <?php $count = 1 ; ?> 
                  <?php $sum_hn_total = 0 ; ?> 
                  <?php $sum_visit_total = 0 ; ?>   
                  <?php $sum_visit_total_op = 0 ; ?>  
                  <?php $sum_visit_total_pp = 0 ; ?> 
                  <?php $sum_inc_total = 0 ; ?>  
                  <?php $sum_inc_lab_total = 0 ; ?>
                  <?php $sum_inc_drug_total = 0 ; ?> 
                  <?php $sum_visit_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_drug_ucs_incup = 0 ; ?>  
                  <?php $sum_visit_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_inprov = 0 ; ?> 
                  <?php $sum_inc_drug_ucs_inprov = 0 ; ?>   
                  <?php $sum_visit_ucs_outprov = 0 ; ?>  
                  <?php $sum_inc_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_lab_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_drug_ucs_outprov = 0 ; ?>  
                  <?php $sum_visit_ofc = 0 ; ?>  
                  <?php $sum_inc_ofc = 0 ; ?>
                  <?php $sum_inc_lab_ofc = 0 ; ?>
                  <?php $sum_inc_drug_ofc = 0 ; ?>
                  <?php $sum_visit_bkk = 0 ; ?>  
                  <?php $sum_inc_bkk = 0 ; ?>
                  <?php $sum_inc_lab_bkk = 0 ; ?>
                  <?php $sum_inc_drug_bkk = 0 ; ?> 
                  <?php $sum_visit_bmt = 0 ; ?>  
                  <?php $sum_inc_bmt = 0 ; ?>
                  <?php $sum_inc_lab_bmt = 0 ; ?>
                  <?php $sum_inc_drug_bmt = 0 ; ?>  
                  <?php $sum_visit_sss = 0 ; ?>  
                  <?php $sum_inc_sss = 0 ; ?>
                  <?php $sum_inc_lab_sss = 0 ; ?>
                  <?php $sum_inc_drug_sss = 0 ; ?> 
                  <?php $sum_visit_lgo = 0 ; ?>  
                  <?php $sum_inc_lgo = 0 ; ?>
                  <?php $sum_inc_lab_lgo = 0 ; ?>
                  <?php $sum_inc_drug_lgo = 0 ; ?> 
                  <?php $sum_visit_fss = 0 ; ?>  
                  <?php $sum_inc_fss = 0 ; ?>
                  <?php $sum_inc_lab_fss = 0 ; ?>
                  <?php $sum_inc_drug_fss = 0 ; ?> 
                  <?php $sum_visit_stp = 0 ; ?>  
                  <?php $sum_inc_stp = 0 ; ?>
                  <?php $sum_inc_lab_stp = 0 ; ?>
                  <?php $sum_inc_drug_stp = 0 ; ?>
                  <?php $sum_visit_pay = 0 ; ?>  
                  <?php $sum_inc_pay = 0 ; ?>
                  <?php $sum_inc_lab_pay = 0 ; ?>
                  <?php $sum_inc_drug_pay = 0 ; ?>  
                  @foreach($total_10988 as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->hn_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total_op) }}</td>
                    <td align="right">{{ number_format($row->visit_total_pp) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_incup) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_inprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_outprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ofc) }}</td>
                    <td align="right">{{ number_format($row->inc_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bkk) }}</td>
                    <td align="right">{{ number_format($row->inc_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bmt) }}</td>
                    <td align="right">{{ number_format($row->inc_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->visit_sss) }}</td>
                    <td align="right">{{ number_format($row->inc_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_sss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_lgo) }}</td>
                    <td align="right">{{ number_format($row->inc_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->visit_fss) }}</td>
                    <td align="right">{{ number_format($row->inc_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_fss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_stp) }}</td>
                    <td align="right">{{ number_format($row->inc_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_stp,2) }}</td>
                    <td align="right">{{ number_format($row->visit_pay) }}</td>
                    <td align="right">{{ number_format($row->inc_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  <?php $count++; ?>
                  <?php $sum_hn_total += $row->hn_total ; ?>
                  <?php $sum_visit_total += $row->visit_total ; ?>
                  <?php $sum_visit_total_op += $row->visit_total_op ; ?>
                  <?php $sum_visit_total_pp += $row->visit_total_pp ; ?>
                  <?php $sum_inc_total += $row->inc_total ; ?>
                  <?php $sum_inc_lab_total += $row->inc_lab_total ; ?>
                  <?php $sum_inc_drug_total += $row->inc_drug_total ; ?>
                  <?php $sum_visit_ucs_incup += $row->visit_ucs_incup ; ?>
                  <?php $sum_inc_ucs_incup += $row->inc_ucs_incup ; ?>
                  <?php $sum_inc_lab_ucs_incup += $row->inc_lab_ucs_incup ; ?>
                  <?php $sum_inc_drug_ucs_incup += $row->inc_drug_ucs_incup ; ?>   
                  <?php $sum_visit_ucs_inprov += $row->visit_ucs_inprov ; ?>
                  <?php $sum_inc_ucs_inprov += $row->inc_ucs_inprov ; ?>
                  <?php $sum_inc_lab_ucs_inprov += $row->inc_lab_ucs_inprov ; ?>
                  <?php $sum_inc_drug_ucs_inprov += $row->inc_drug_ucs_inprov ; ?>
                  <?php $sum_visit_ucs_outprov += $row->visit_ucs_outprov ; ?>
                  <?php $sum_inc_ucs_outprov += $row->inc_ucs_outprov ; ?>
                  <?php $sum_inc_lab_ucs_outprov += $row->inc_lab_ucs_outprov ; ?>
                  <?php $sum_inc_drug_ucs_outprov += $row->inc_drug_ucs_outprov ; ?> 
                  <?php $sum_visit_ofc += $row->visit_ofc ; ?>
                  <?php $sum_inc_ofc += $row->inc_ofc ; ?>
                  <?php $sum_inc_lab_ofc += $row->inc_lab_ofc ; ?>
                  <?php $sum_inc_drug_ofc += $row->inc_drug_ofc ; ?> 
                  <?php $sum_visit_bkk += $row->visit_bkk ; ?>
                  <?php $sum_inc_bkk += $row->inc_bkk ; ?>
                  <?php $sum_inc_lab_bkk += $row->inc_lab_bkk ; ?>
                  <?php $sum_inc_drug_bkk += $row->inc_drug_bkk ; ?>  
                  <?php $sum_visit_bmt += $row->visit_bmt ; ?>
                  <?php $sum_inc_bmt += $row->inc_bmt ; ?>
                  <?php $sum_inc_lab_bmt += $row->inc_lab_bmt ; ?>
                  <?php $sum_inc_drug_bmt += $row->inc_drug_bmt ; ?> 
                  <?php $sum_visit_sss += $row->visit_sss ; ?>
                  <?php $sum_inc_sss += $row->inc_sss ; ?>
                  <?php $sum_inc_lab_sss += $row->inc_lab_sss ; ?>
                  <?php $sum_inc_drug_sss += $row->inc_drug_sss ; ?>   
                  <?php $sum_visit_lgo += $row->visit_lgo ; ?>
                  <?php $sum_inc_lgo += $row->inc_lgo ; ?>
                  <?php $sum_inc_lab_lgo += $row->inc_lab_lgo ; ?>
                  <?php $sum_inc_drug_lgo += $row->inc_drug_lgo ; ?>
                  <?php $sum_visit_fss += $row->visit_fss ; ?>
                  <?php $sum_inc_fss += $row->inc_fss ; ?>
                  <?php $sum_inc_lab_fss += $row->inc_lab_fss ; ?>
                  <?php $sum_inc_drug_fss += $row->inc_drug_fss ; ?>    
                  <?php $sum_visit_stp += $row->visit_stp ; ?>
                  <?php $sum_inc_stp += $row->inc_stp ; ?>
                  <?php $sum_inc_lab_stp += $row->inc_lab_stp ; ?>
                  <?php $sum_inc_drug_stp += $row->inc_drug_stp ; ?>   
                  <?php $sum_visit_pay += $row->visit_pay ; ?>
                  <?php $sum_inc_pay += $row->inc_pay ; ?>
                  <?php $sum_inc_lab_pay += $row->inc_lab_pay ; ?>
                  <?php $sum_inc_drug_pay += $row->inc_drug_pay ; ?> 
                  @endforeach    
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_hn_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total_op)}}</strong></td>     
                    <td align="right"><strong>{{number_format($sum_visit_total_pp)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_inc_lab_total,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_incup)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_incup,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_ucs_inprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_outprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_outprov,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_ofc)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ofc,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bkk)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bkk,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bmt)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bmt,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_sss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_sss,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_lgo)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_lgo,2)}}</strong></td>       
                    <td align="right"><strong>{{number_format($sum_visit_fss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_fss,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_stp)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_stp,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_pay)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_pay,2)}}</strong></td> 
                  </tr>   
                </tbody>
              </table>
            </div>
          </div>
          <br> <!-- 10988 IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10988] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลเสนางคนิคม ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10988}}</span>              
            </div>
            <div class="table-responsive">
              <table id="table10988_ipd" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-danger">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" rowspan="2">จำนวน AN</th>
                    <th class="text-center" rowspan="2">วันนอนรวม</th> 
                    <th class="text-center" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="text-center" rowspan="2">Active Base (เตียง)</th>     
                    <th class="text-center" rowspan="2">AdjRW</th>  
                    <th class="text-center" rowspan="2">CMI</th>
                    <th class="text-center" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr class="table-danger"> 
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>                 
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
                    $bed_report = $total_10988_ipd[0]->bed_report ?? 30; // ค่าเตียงจาก hospital_config
                  ?>  
                  @foreach($total_10988_ipd as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right">{{ number_format($row->bed_occupancy,2) }}</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total;
                    $sum_admdate += $row->admdate;
                    $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total;
                    $sum_inc_lab_total += $row->inc_lab_total;
                    $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                  // ✅ จำนวนเตียง
                    $bed_report = 30;
                  // ✅ อัตราครองเตียงรวม
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                  // ✅ Active Bed = วันนอนรวม ÷ จำนวนวัน
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                  // ✅ CMI รวม
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_an_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_admdate)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_bed_occupancy,2)}}</td>     
                    <td align="right"><strong>{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right"><strong>{{number_format($sum_adjrw,4)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_cmi,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                  </tr>   
                </tbody>
              </table>
              <!-- กราฟ -->
              <div class="row mt-4">
                <!-- กราฟอัตราครองเตียง -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-primary mb-1">
                        📈 อัตราครองเตียง (%)
                      </h6>
                      <div id="bed_occupancy_10988"></div>
                    </div>
                  </div>
                </div>
                <!-- กราฟ CMI -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-danger mb-1">
                        📊 CMI
                      </h6>
                      <div id="cmi_chart_10988"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>        
        </div>

        <!-- 10989 OPD -->
        <div class="tab-pane fade" id="pane-10989" role="tabpanel" aria-labelledby="tab-10989" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10989] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลหัวตะพาน ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10989}}</span>   
            </div>
            <div class="table-responsive">
              <table id="table10989" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-primary">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" colspan="7">ทั้งหมด</th>
                    <th class="text-center" colspan="4">UCS ใน CUP</th> 
                    <th class="text-center" colspan="4">UCS ในจังหวัด</th>
                    <th class="text-center" colspan="4">UCS นอกจังหวัด</th>       
                    <th class="text-center" colspan="4">OFC ข้าราชการ</th>  
                    <th class="text-center" colspan="4">BKK กทม.</th>
                    <th class="text-center" colspan="4">BMT ขสมก.</th>
                    <th class="text-center" colspan="4">SSS ประกันสังคม</th>
                    <th class="text-center" colspan="4">LGO อปท.</th>
                    <th class="text-center" colspan="4">FSS ต่างด้าว</th>
                    <th class="text-center" colspan="4">STP Stateless</th>
                    <th class="text-center" colspan="4">ชำระเงิน/พรบ.</th>                 
                  </tr>    
                  <tr class="table-primary">            
                    <td class="text-center text-primary">HN Total</td>
                    <td class="text-center text-primary">Visit Total</td>
                    <td class="text-center text-primary">Visit OP</td>
                    <td class="text-center text-primary">Visit PP</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                  </tr>    
                </thead>
                <tbody>
                  <?php $count = 1 ; ?> 
                  <?php $sum_hn_total = 0 ; ?> 
                  <?php $sum_visit_total = 0 ; ?>   
                  <?php $sum_visit_total_op = 0 ; ?>  
                  <?php $sum_visit_total_pp = 0 ; ?> 
                  <?php $sum_inc_total = 0 ; ?>  
                  <?php $sum_inc_lab_total = 0 ; ?>
                  <?php $sum_inc_drug_total = 0 ; ?> 
                  <?php $sum_visit_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_drug_ucs_incup = 0 ; ?>  
                  <?php $sum_visit_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_inprov = 0 ; ?> 
                  <?php $sum_inc_drug_ucs_inprov = 0 ; ?>   
                  <?php $sum_visit_ucs_outprov = 0 ; ?>  
                  <?php $sum_inc_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_lab_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_drug_ucs_outprov = 0 ; ?>  
                  <?php $sum_visit_ofc = 0 ; ?>  
                  <?php $sum_inc_ofc = 0 ; ?>
                  <?php $sum_inc_lab_ofc = 0 ; ?>
                  <?php $sum_inc_drug_ofc = 0 ; ?>
                  <?php $sum_visit_bkk = 0 ; ?>  
                  <?php $sum_inc_bkk = 0 ; ?>
                  <?php $sum_inc_lab_bkk = 0 ; ?>
                  <?php $sum_inc_drug_bkk = 0 ; ?> 
                  <?php $sum_visit_bmt = 0 ; ?>  
                  <?php $sum_inc_bmt = 0 ; ?>
                  <?php $sum_inc_lab_bmt = 0 ; ?>
                  <?php $sum_inc_drug_bmt = 0 ; ?>  
                  <?php $sum_visit_sss = 0 ; ?>  
                  <?php $sum_inc_sss = 0 ; ?>
                  <?php $sum_inc_lab_sss = 0 ; ?>
                  <?php $sum_inc_drug_sss = 0 ; ?> 
                  <?php $sum_visit_lgo = 0 ; ?>  
                  <?php $sum_inc_lgo = 0 ; ?>
                  <?php $sum_inc_lab_lgo = 0 ; ?>
                  <?php $sum_inc_drug_lgo = 0 ; ?> 
                  <?php $sum_visit_fss = 0 ; ?>  
                  <?php $sum_inc_fss = 0 ; ?>
                  <?php $sum_inc_lab_fss = 0 ; ?>
                  <?php $sum_inc_drug_fss = 0 ; ?> 
                  <?php $sum_visit_stp = 0 ; ?>  
                  <?php $sum_inc_stp = 0 ; ?>
                  <?php $sum_inc_lab_stp = 0 ; ?>
                  <?php $sum_inc_drug_stp = 0 ; ?>
                  <?php $sum_visit_pay = 0 ; ?>  
                  <?php $sum_inc_pay = 0 ; ?>
                  <?php $sum_inc_lab_pay = 0 ; ?>
                  <?php $sum_inc_drug_pay = 0 ; ?>  
                  @foreach($total_10989 as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->hn_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total_op) }}</td>
                    <td align="right">{{ number_format($row->visit_total_pp) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_incup) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_inprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_outprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ofc) }}</td>
                    <td align="right">{{ number_format($row->inc_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bkk) }}</td>
                    <td align="right">{{ number_format($row->inc_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bmt) }}</td>
                    <td align="right">{{ number_format($row->inc_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->visit_sss) }}</td>
                    <td align="right">{{ number_format($row->inc_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_sss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_lgo) }}</td>
                    <td align="right">{{ number_format($row->inc_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->visit_fss) }}</td>
                    <td align="right">{{ number_format($row->inc_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_fss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_stp) }}</td>
                    <td align="right">{{ number_format($row->inc_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_stp,2) }}</td>
                    <td align="right">{{ number_format($row->visit_pay) }}</td>
                    <td align="right">{{ number_format($row->inc_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  <?php $count++; ?>
                  <?php $sum_hn_total += $row->hn_total ; ?>
                  <?php $sum_visit_total += $row->visit_total ; ?>
                  <?php $sum_visit_total_op += $row->visit_total_op ; ?>
                  <?php $sum_visit_total_pp += $row->visit_total_pp ; ?>
                  <?php $sum_inc_total += $row->inc_total ; ?>
                  <?php $sum_inc_lab_total += $row->inc_lab_total ; ?>
                  <?php $sum_inc_drug_total += $row->inc_drug_total ; ?>
                  <?php $sum_visit_ucs_incup += $row->visit_ucs_incup ; ?>
                  <?php $sum_inc_ucs_incup += $row->inc_ucs_incup ; ?>
                  <?php $sum_inc_lab_ucs_incup += $row->inc_lab_ucs_incup ; ?>
                  <?php $sum_inc_drug_ucs_incup += $row->inc_drug_ucs_incup ; ?>   
                  <?php $sum_visit_ucs_inprov += $row->visit_ucs_inprov ; ?>
                  <?php $sum_inc_ucs_inprov += $row->inc_ucs_inprov ; ?>
                  <?php $sum_inc_lab_ucs_inprov += $row->inc_lab_ucs_inprov ; ?>
                  <?php $sum_inc_drug_ucs_inprov += $row->inc_drug_ucs_inprov ; ?>
                  <?php $sum_visit_ucs_outprov += $row->visit_ucs_outprov ; ?>
                  <?php $sum_inc_ucs_outprov += $row->inc_ucs_outprov ; ?>
                  <?php $sum_inc_lab_ucs_outprov += $row->inc_lab_ucs_outprov ; ?>
                  <?php $sum_inc_drug_ucs_outprov += $row->inc_drug_ucs_outprov ; ?> 
                  <?php $sum_visit_ofc += $row->visit_ofc ; ?>
                  <?php $sum_inc_ofc += $row->inc_ofc ; ?>
                  <?php $sum_inc_lab_ofc += $row->inc_lab_ofc ; ?>
                  <?php $sum_inc_drug_ofc += $row->inc_drug_ofc ; ?> 
                  <?php $sum_visit_bkk += $row->visit_bkk ; ?>
                  <?php $sum_inc_bkk += $row->inc_bkk ; ?>
                  <?php $sum_inc_lab_bkk += $row->inc_lab_bkk ; ?>
                  <?php $sum_inc_drug_bkk += $row->inc_drug_bkk ; ?>  
                  <?php $sum_visit_bmt += $row->visit_bmt ; ?>
                  <?php $sum_inc_bmt += $row->inc_bmt ; ?>
                  <?php $sum_inc_lab_bmt += $row->inc_lab_bmt ; ?>
                  <?php $sum_inc_drug_bmt += $row->inc_drug_bmt ; ?> 
                  <?php $sum_visit_sss += $row->visit_sss ; ?>
                  <?php $sum_inc_sss += $row->inc_sss ; ?>
                  <?php $sum_inc_lab_sss += $row->inc_lab_sss ; ?>
                  <?php $sum_inc_drug_sss += $row->inc_drug_sss ; ?>   
                  <?php $sum_visit_lgo += $row->visit_lgo ; ?>
                  <?php $sum_inc_lgo += $row->inc_lgo ; ?>
                  <?php $sum_inc_lab_lgo += $row->inc_lab_lgo ; ?>
                  <?php $sum_inc_drug_lgo += $row->inc_drug_lgo ; ?>
                  <?php $sum_visit_fss += $row->visit_fss ; ?>
                  <?php $sum_inc_fss += $row->inc_fss ; ?>
                  <?php $sum_inc_lab_fss += $row->inc_lab_fss ; ?>
                  <?php $sum_inc_drug_fss += $row->inc_drug_fss ; ?>    
                  <?php $sum_visit_stp += $row->visit_stp ; ?>
                  <?php $sum_inc_stp += $row->inc_stp ; ?>
                  <?php $sum_inc_lab_stp += $row->inc_lab_stp ; ?>
                  <?php $sum_inc_drug_stp += $row->inc_drug_stp ; ?>   
                  <?php $sum_visit_pay += $row->visit_pay ; ?>
                  <?php $sum_inc_pay += $row->inc_pay ; ?>
                  <?php $sum_inc_lab_pay += $row->inc_lab_pay ; ?>
                  <?php $sum_inc_drug_pay += $row->inc_drug_pay ; ?> 
                  @endforeach    
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_hn_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total_op)}}</strong></td>     
                    <td align="right"><strong>{{number_format($sum_visit_total_pp)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_inc_lab_total,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_incup)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_incup,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_ucs_inprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_outprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_outprov,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_ofc)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ofc,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bkk)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bkk,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bmt)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bmt,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_sss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_sss,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_lgo)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_lgo,2)}}</strong></td>       
                    <td align="right"><strong>{{number_format($sum_visit_fss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_fss,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_stp)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_stp,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_pay)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_pay,2)}}</strong></td> 
                  </tr>   
                </tbody>
              </table>
            </div>
          </div>
          <br> <!-- 10989 IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10989] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลหัวตะพาน ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10989}}</span>              
            </div>
            <div class="table-responsive">
              <table id="table10989_ipd" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-danger">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" rowspan="2">จำนวน AN</th>
                    <th class="text-center" rowspan="2">วันนอนรวม</th> 
                    <th class="text-center" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="text-center" rowspan="2">Active Base (เตียง)</th>       
                    <th class="text-center" rowspan="2">AdjRW</th>  
                    <th class="text-center" rowspan="2">CMI</th>
                    <th class="text-center" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr class="table-danger"> 
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>                 
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
                    $bed_report = $total_10989_ipd[0]->bed_report ?? 30; // ค่าเตียงจาก hospital_config
                  ?>  
                  @foreach($total_10989_ipd as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right">{{ number_format($row->bed_occupancy,2) }}</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total;
                    $sum_admdate += $row->admdate;
                    $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total;
                    $sum_inc_lab_total += $row->inc_lab_total;
                    $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                  // ✅ จำนวนเตียง
                    $bed_report = 60;
                  // ✅ อัตราครองเตียงรวม
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                  // ✅ Active Bed = วันนอนรวม ÷ จำนวนวัน
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                  // ✅ CMI รวม
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_an_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_admdate)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_bed_occupancy,2)}}</td>     
                    <td align="right"><strong>{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right"><strong>{{number_format($sum_adjrw,4)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_cmi,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                  </tr>   
                </tbody>
              </table>
              <!-- กราฟ -->
              <div class="row mt-4">
                <!-- กราฟอัตราครองเตียง -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-primary mb-1">
                        📈 อัตราครองเตียง (%)
                      </h6>
                      <div id="bed_occupancy_10989"></div>
                    </div>
                  </div>
                </div>
                <!-- กราฟ CMI -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-danger mb-1">
                        📊 CMI
                      </h6>
                      <div id="cmi_chart_10989"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10990 OPD -->
        <div class="tab-pane fade" id="pane-10990" role="tabpanel" aria-labelledby="tab-10990" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10990] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลลืออำนาจ ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10990}}</span>   
            </div>
            <div class="table-responsive">
              <table id="table10990" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-primary">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" colspan="7">ทั้งหมด</th>
                    <th class="text-center" colspan="4">UCS ใน CUP</th> 
                    <th class="text-center" colspan="4">UCS ในจังหวัด</th>
                    <th class="text-center" colspan="4">UCS นอกจังหวัด</th>       
                    <th class="text-center" colspan="4">OFC ข้าราชการ</th>  
                    <th class="text-center" colspan="4">BKK กทม.</th>
                    <th class="text-center" colspan="4">BMT ขสมก.</th>
                    <th class="text-center" colspan="4">SSS ประกันสังคม</th>
                    <th class="text-center" colspan="4">LGO อปท.</th>
                    <th class="text-center" colspan="4">FSS ต่างด้าว</th>
                    <th class="text-center" colspan="4">STP Stateless</th>
                    <th class="text-center" colspan="4">ชำระเงิน/พรบ.</th>                 
                  </tr>    
                  <tr class="table-primary">            
                    <td class="text-center text-primary">HN Total</td>
                    <td class="text-center text-primary">Visit Total</td>
                    <td class="text-center text-primary">Visit OP</td>
                    <td class="text-center text-primary">Visit PP</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                  </tr>    
                </thead>
                <tbody>
                  <?php $count = 1 ; ?> 
                  <?php $sum_hn_total = 0 ; ?> 
                  <?php $sum_visit_total = 0 ; ?>   
                  <?php $sum_visit_total_op = 0 ; ?>  
                  <?php $sum_visit_total_pp = 0 ; ?> 
                  <?php $sum_inc_total = 0 ; ?>  
                  <?php $sum_inc_lab_total = 0 ; ?>
                  <?php $sum_inc_drug_total = 0 ; ?> 
                  <?php $sum_visit_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_drug_ucs_incup = 0 ; ?>  
                  <?php $sum_visit_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_inprov = 0 ; ?> 
                  <?php $sum_inc_drug_ucs_inprov = 0 ; ?>   
                  <?php $sum_visit_ucs_outprov = 0 ; ?>  
                  <?php $sum_inc_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_lab_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_drug_ucs_outprov = 0 ; ?>  
                  <?php $sum_visit_ofc = 0 ; ?>  
                  <?php $sum_inc_ofc = 0 ; ?>
                  <?php $sum_inc_lab_ofc = 0 ; ?>
                  <?php $sum_inc_drug_ofc = 0 ; ?>
                  <?php $sum_visit_bkk = 0 ; ?>  
                  <?php $sum_inc_bkk = 0 ; ?>
                  <?php $sum_inc_lab_bkk = 0 ; ?>
                  <?php $sum_inc_drug_bkk = 0 ; ?> 
                  <?php $sum_visit_bmt = 0 ; ?>  
                  <?php $sum_inc_bmt = 0 ; ?>
                  <?php $sum_inc_lab_bmt = 0 ; ?>
                  <?php $sum_inc_drug_bmt = 0 ; ?>  
                  <?php $sum_visit_sss = 0 ; ?>  
                  <?php $sum_inc_sss = 0 ; ?>
                  <?php $sum_inc_lab_sss = 0 ; ?>
                  <?php $sum_inc_drug_sss = 0 ; ?> 
                  <?php $sum_visit_lgo = 0 ; ?>  
                  <?php $sum_inc_lgo = 0 ; ?>
                  <?php $sum_inc_lab_lgo = 0 ; ?>
                  <?php $sum_inc_drug_lgo = 0 ; ?> 
                  <?php $sum_visit_fss = 0 ; ?>  
                  <?php $sum_inc_fss = 0 ; ?>
                  <?php $sum_inc_lab_fss = 0 ; ?>
                  <?php $sum_inc_drug_fss = 0 ; ?> 
                  <?php $sum_visit_stp = 0 ; ?>  
                  <?php $sum_inc_stp = 0 ; ?>
                  <?php $sum_inc_lab_stp = 0 ; ?>
                  <?php $sum_inc_drug_stp = 0 ; ?>
                  <?php $sum_visit_pay = 0 ; ?>  
                  <?php $sum_inc_pay = 0 ; ?>
                  <?php $sum_inc_lab_pay = 0 ; ?>
                  <?php $sum_inc_drug_pay = 0 ; ?>  
                  @foreach($total_10990 as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->hn_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total_op) }}</td>
                    <td align="right">{{ number_format($row->visit_total_pp) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_incup) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_inprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_outprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ofc) }}</td>
                    <td align="right">{{ number_format($row->inc_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bkk) }}</td>
                    <td align="right">{{ number_format($row->inc_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bmt) }}</td>
                    <td align="right">{{ number_format($row->inc_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->visit_sss) }}</td>
                    <td align="right">{{ number_format($row->inc_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_sss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_lgo) }}</td>
                    <td align="right">{{ number_format($row->inc_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->visit_fss) }}</td>
                    <td align="right">{{ number_format($row->inc_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_fss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_stp) }}</td>
                    <td align="right">{{ number_format($row->inc_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_stp,2) }}</td>
                    <td align="right">{{ number_format($row->visit_pay) }}</td>
                    <td align="right">{{ number_format($row->inc_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  <?php $count++; ?>
                  <?php $sum_hn_total += $row->hn_total ; ?>
                  <?php $sum_visit_total += $row->visit_total ; ?>
                  <?php $sum_visit_total_op += $row->visit_total_op ; ?>
                  <?php $sum_visit_total_pp += $row->visit_total_pp ; ?>
                  <?php $sum_inc_total += $row->inc_total ; ?>
                  <?php $sum_inc_lab_total += $row->inc_lab_total ; ?>
                  <?php $sum_inc_drug_total += $row->inc_drug_total ; ?>
                  <?php $sum_visit_ucs_incup += $row->visit_ucs_incup ; ?>
                  <?php $sum_inc_ucs_incup += $row->inc_ucs_incup ; ?>
                  <?php $sum_inc_lab_ucs_incup += $row->inc_lab_ucs_incup ; ?>
                  <?php $sum_inc_drug_ucs_incup += $row->inc_drug_ucs_incup ; ?>   
                  <?php $sum_visit_ucs_inprov += $row->visit_ucs_inprov ; ?>
                  <?php $sum_inc_ucs_inprov += $row->inc_ucs_inprov ; ?>
                  <?php $sum_inc_lab_ucs_inprov += $row->inc_lab_ucs_inprov ; ?>
                  <?php $sum_inc_drug_ucs_inprov += $row->inc_drug_ucs_inprov ; ?>
                  <?php $sum_visit_ucs_outprov += $row->visit_ucs_outprov ; ?>
                  <?php $sum_inc_ucs_outprov += $row->inc_ucs_outprov ; ?>
                  <?php $sum_inc_lab_ucs_outprov += $row->inc_lab_ucs_outprov ; ?>
                  <?php $sum_inc_drug_ucs_outprov += $row->inc_drug_ucs_outprov ; ?> 
                  <?php $sum_visit_ofc += $row->visit_ofc ; ?>
                  <?php $sum_inc_ofc += $row->inc_ofc ; ?>
                  <?php $sum_inc_lab_ofc += $row->inc_lab_ofc ; ?>
                  <?php $sum_inc_drug_ofc += $row->inc_drug_ofc ; ?> 
                  <?php $sum_visit_bkk += $row->visit_bkk ; ?>
                  <?php $sum_inc_bkk += $row->inc_bkk ; ?>
                  <?php $sum_inc_lab_bkk += $row->inc_lab_bkk ; ?>
                  <?php $sum_inc_drug_bkk += $row->inc_drug_bkk ; ?>  
                  <?php $sum_visit_bmt += $row->visit_bmt ; ?>
                  <?php $sum_inc_bmt += $row->inc_bmt ; ?>
                  <?php $sum_inc_lab_bmt += $row->inc_lab_bmt ; ?>
                  <?php $sum_inc_drug_bmt += $row->inc_drug_bmt ; ?> 
                  <?php $sum_visit_sss += $row->visit_sss ; ?>
                  <?php $sum_inc_sss += $row->inc_sss ; ?>
                  <?php $sum_inc_lab_sss += $row->inc_lab_sss ; ?>
                  <?php $sum_inc_drug_sss += $row->inc_drug_sss ; ?>   
                  <?php $sum_visit_lgo += $row->visit_lgo ; ?>
                  <?php $sum_inc_lgo += $row->inc_lgo ; ?>
                  <?php $sum_inc_lab_lgo += $row->inc_lab_lgo ; ?>
                  <?php $sum_inc_drug_lgo += $row->inc_drug_lgo ; ?>
                  <?php $sum_visit_fss += $row->visit_fss ; ?>
                  <?php $sum_inc_fss += $row->inc_fss ; ?>
                  <?php $sum_inc_lab_fss += $row->inc_lab_fss ; ?>
                  <?php $sum_inc_drug_fss += $row->inc_drug_fss ; ?>    
                  <?php $sum_visit_stp += $row->visit_stp ; ?>
                  <?php $sum_inc_stp += $row->inc_stp ; ?>
                  <?php $sum_inc_lab_stp += $row->inc_lab_stp ; ?>
                  <?php $sum_inc_drug_stp += $row->inc_drug_stp ; ?>   
                  <?php $sum_visit_pay += $row->visit_pay ; ?>
                  <?php $sum_inc_pay += $row->inc_pay ; ?>
                  <?php $sum_inc_lab_pay += $row->inc_lab_pay ; ?>
                  <?php $sum_inc_drug_pay += $row->inc_drug_pay ; ?> 
                  @endforeach    
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_hn_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total_op)}}</strong></td>     
                    <td align="right"><strong>{{number_format($sum_visit_total_pp)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_inc_lab_total,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_incup)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_incup,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_ucs_inprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_outprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_outprov,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_ofc)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ofc,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bkk)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bkk,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bmt)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bmt,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_sss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_sss,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_lgo)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_lgo,2)}}</strong></td>       
                    <td align="right"><strong>{{number_format($sum_visit_fss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_fss,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_stp)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_stp,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_pay)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_pay,2)}}</strong></td> 
                  </tr>   
                </tbody>
              </table>
            </div>
          </div>
          <br> <!-- 10990 IPD -->
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10990] ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลลืออำนาจ ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10990}}</span>              
            </div>
            <div class="table-responsive">
              <table id="table10990_ipd" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-danger">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" rowspan="2">จำนวน AN</th>
                    <th class="text-center" rowspan="2">วันนอนรวม</th> 
                    <th class="text-center" rowspan="2">อัตราครองเตียง (%)</th>
                    <th class="text-center" rowspan="2">Active Base (เตียง)</th>      
                    <th class="text-center" rowspan="2">AdjRW</th>  
                    <th class="text-center" rowspan="2">CMI</th>
                    <th class="text-center" colspan="3">ค่ารักษาพยาบาล</th>                
                  </tr>    
                  <tr class="table-danger"> 
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>                 
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
                    $bed_report = $total_10990_ipd[0]->bed_report ?? 30; // ค่าเตียงจาก hospital_config
                  ?>  
                  @foreach($total_10990_ipd as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->an_total) }}</td>
                    <td align="right">{{ number_format($row->admdate) }}</td>
                    <td align="right">{{ number_format($row->bed_occupancy,2) }}</td>
                    <td align="right">{{ number_format($row->active_bed,2) }}</td>
                    <td align="right">{{ number_format($row->adjrw,5) }}</td>
                    <td align="right">{{ number_format($row->cmi,2) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                  </tr>
                  <?php 
                    $sum_an_total += $row->an_total;
                    $sum_admdate += $row->admdate;
                    $sum_adjrw += $row->adjrw;
                    $sum_inc_total += $row->inc_total;
                    $sum_inc_lab_total += $row->inc_lab_total;
                    $sum_inc_drug_total += $row->inc_drug_total;
                  ?>
                  @endforeach 
                  <?php                   
                  // ✅ จำนวนเตียง
                    $bed_report = 30;
                  // ✅ อัตราครองเตียงรวม
                    $sum_bed_occupancy = ($sum_admdate > 0 && $bed_report > 0) ? round(($sum_admdate * 100) / ($bed_report * $diff_days), 2) : 0;  
                  // ✅ Active Bed = วันนอนรวม ÷ จำนวนวัน
                    $sum_active_bed = ($sum_admdate > 0 && $diff_days > 0) ? round($sum_admdate / $diff_days, 2) : 0;
                  // ✅ CMI รวม
                    $sum_cmi = ($sum_an_total > 0) ? round($sum_adjrw / $sum_an_total, 2) : 0; 
                  ?>   
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_an_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_admdate)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_bed_occupancy,2)}}</td>     
                    <td align="right"><strong>{{number_format($sum_active_bed,2)}}</td>   
                    <td align="right"><strong>{{number_format($sum_adjrw,4)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_cmi,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                  </tr>   
                </tbody>
              </table>
              <!-- กราฟ -->
              <div class="row mt-4">
                <!-- กราฟอัตราครองเตียง -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-primary mb-1">
                        📈 อัตราครองเตียง (%)
                      </h6>
                      <div id="bed_occupancy_10990"></div>
                    </div>
                  </div>
                </div>
                <!-- กราฟ CMI -->
                <div class="col-md-6 mb-4">
                  <div class="card shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center text-danger mb-1">
                        📊 CMI
                      </h6>
                      <div id="cmi_chart_10990"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10703 OPD -->
        <div class="tab-pane fade" id="pane-10703" role="tabpanel" aria-labelledby="tab-10703" tabindex="0">
          <div class="glass p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6>[10703] ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลอำนาจเจริญ ปีงบประมาณ {{$budget_year}}</h6>
              <span class="text-secondary small">Update {{$update_at10703}}</span>   
            </div>
            <div class="table-responsive">
              <table id="table10703" class="table table-bordered table-striped my-3" width ="100%">
                <thead class="table-light">
                  <tr class="table-primary">
                    <th class="text-center" rowspan="2" width ="4%">เดือน</th>
                    <th class="text-center" colspan="7">ทั้งหมด</th>
                    <th class="text-center" colspan="4">UCS ใน CUP</th> 
                    <th class="text-center" colspan="4">UCS ในจังหวัด</th>
                    <th class="text-center" colspan="4">UCS นอกจังหวัด</th>       
                    <th class="text-center" colspan="4">OFC ข้าราชการ</th>  
                    <th class="text-center" colspan="4">BKK กทม.</th>
                    <th class="text-center" colspan="4">BMT ขสมก.</th>
                    <th class="text-center" colspan="4">SSS ประกันสังคม</th>
                    <th class="text-center" colspan="4">LGO อปท.</th>
                    <th class="text-center" colspan="4">FSS ต่างด้าว</th>
                    <th class="text-center" colspan="4">STP Stateless</th>
                    <th class="text-center" colspan="4">ชำระเงิน/พรบ.</th>                 
                  </tr>    
                  <tr class="table-primary">            
                    <td class="text-center text-primary">HN Total</td>
                    <td class="text-center text-primary">Visit Total</td>
                    <td class="text-center text-primary">Visit OP</td>
                    <td class="text-center text-primary">Visit PP</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                    <td class="text-center text-primary">Visit</td>
                    <td class="text-center text-primary">ค่ารักษารวม</td>
                    <td class="text-center text-primary">ค่า Lab</td>
                    <td class="text-center text-primary">ค่า ยา</td>
                  </tr>    
                </thead>
                <tbody>
                  <?php $count = 1 ; ?> 
                  <?php $sum_hn_total = 0 ; ?> 
                  <?php $sum_visit_total = 0 ; ?>   
                  <?php $sum_visit_total_op = 0 ; ?>  
                  <?php $sum_visit_total_pp = 0 ; ?> 
                  <?php $sum_inc_total = 0 ; ?>  
                  <?php $sum_inc_lab_total = 0 ; ?>
                  <?php $sum_inc_drug_total = 0 ; ?> 
                  <?php $sum_visit_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_incup = 0 ; ?>  
                  <?php $sum_inc_drug_ucs_incup = 0 ; ?>  
                  <?php $sum_visit_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_ucs_inprov = 0 ; ?>  
                  <?php $sum_inc_lab_ucs_inprov = 0 ; ?> 
                  <?php $sum_inc_drug_ucs_inprov = 0 ; ?>   
                  <?php $sum_visit_ucs_outprov = 0 ; ?>  
                  <?php $sum_inc_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_lab_ucs_outprov = 0 ; ?>
                  <?php $sum_inc_drug_ucs_outprov = 0 ; ?>  
                  <?php $sum_visit_ofc = 0 ; ?>  
                  <?php $sum_inc_ofc = 0 ; ?>
                  <?php $sum_inc_lab_ofc = 0 ; ?>
                  <?php $sum_inc_drug_ofc = 0 ; ?>
                  <?php $sum_visit_bkk = 0 ; ?>  
                  <?php $sum_inc_bkk = 0 ; ?>
                  <?php $sum_inc_lab_bkk = 0 ; ?>
                  <?php $sum_inc_drug_bkk = 0 ; ?> 
                  <?php $sum_visit_bmt = 0 ; ?>  
                  <?php $sum_inc_bmt = 0 ; ?>
                  <?php $sum_inc_lab_bmt = 0 ; ?>
                  <?php $sum_inc_drug_bmt = 0 ; ?>  
                  <?php $sum_visit_sss = 0 ; ?>  
                  <?php $sum_inc_sss = 0 ; ?>
                  <?php $sum_inc_lab_sss = 0 ; ?>
                  <?php $sum_inc_drug_sss = 0 ; ?> 
                  <?php $sum_visit_lgo = 0 ; ?>  
                  <?php $sum_inc_lgo = 0 ; ?>
                  <?php $sum_inc_lab_lgo = 0 ; ?>
                  <?php $sum_inc_drug_lgo = 0 ; ?> 
                  <?php $sum_visit_fss = 0 ; ?>  
                  <?php $sum_inc_fss = 0 ; ?>
                  <?php $sum_inc_lab_fss = 0 ; ?>
                  <?php $sum_inc_drug_fss = 0 ; ?> 
                  <?php $sum_visit_stp = 0 ; ?>  
                  <?php $sum_inc_stp = 0 ; ?>
                  <?php $sum_inc_lab_stp = 0 ; ?>
                  <?php $sum_inc_drug_stp = 0 ; ?>
                  <?php $sum_visit_pay = 0 ; ?>  
                  <?php $sum_inc_pay = 0 ; ?>
                  <?php $sum_inc_lab_pay = 0 ; ?>
                  <?php $sum_inc_drug_pay = 0 ; ?>  
                  @foreach($total_10703 as $row) 
                  <tr>
                    <td align="center"width ="4%">{{ $row->month }}</td>
                    <td align="right">{{ number_format($row->hn_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total) }}</td>
                    <td align="right">{{ number_format($row->visit_total_op) }}</td>
                    <td align="right">{{ number_format($row->visit_total_pp) }}</td>
                    <td align="right">{{ number_format($row->inc_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_total,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_total,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_incup) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_incup,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_inprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_inprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ucs_outprov) }}</td>
                    <td align="right">{{ number_format($row->inc_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ucs_outprov,2) }}</td>
                    <td align="right">{{ number_format($row->visit_ofc) }}</td>
                    <td align="right">{{ number_format($row->inc_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_ofc,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bkk) }}</td>
                    <td align="right">{{ number_format($row->inc_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bkk,2) }}</td>
                    <td align="right">{{ number_format($row->visit_bmt) }}</td>
                    <td align="right">{{ number_format($row->inc_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_bmt,2) }}</td>
                    <td align="right">{{ number_format($row->visit_sss) }}</td>
                    <td align="right">{{ number_format($row->inc_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_sss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_sss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_lgo) }}</td>
                    <td align="right">{{ number_format($row->inc_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_lgo,2) }}</td>
                    <td align="right">{{ number_format($row->visit_fss) }}</td>
                    <td align="right">{{ number_format($row->inc_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_fss,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_fss,2) }}</td>
                    <td align="right">{{ number_format($row->visit_stp) }}</td>
                    <td align="right">{{ number_format($row->inc_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_stp,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_stp,2) }}</td>
                    <td align="right">{{ number_format($row->visit_pay) }}</td>
                    <td align="right">{{ number_format($row->inc_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_lab_pay,2) }}</td>
                    <td align="right">{{ number_format($row->inc_drug_pay,2) }}</td>
                  </tr>
                  <?php $count++; ?>
                  <?php $sum_hn_total += $row->hn_total ; ?>
                  <?php $sum_visit_total += $row->visit_total ; ?>
                  <?php $sum_visit_total_op += $row->visit_total_op ; ?>
                  <?php $sum_visit_total_pp += $row->visit_total_pp ; ?>
                  <?php $sum_inc_total += $row->inc_total ; ?>
                  <?php $sum_inc_lab_total += $row->inc_lab_total ; ?>
                  <?php $sum_inc_drug_total += $row->inc_drug_total ; ?>
                  <?php $sum_visit_ucs_incup += $row->visit_ucs_incup ; ?>
                  <?php $sum_inc_ucs_incup += $row->inc_ucs_incup ; ?>
                  <?php $sum_inc_lab_ucs_incup += $row->inc_lab_ucs_incup ; ?>
                  <?php $sum_inc_drug_ucs_incup += $row->inc_drug_ucs_incup ; ?>   
                  <?php $sum_visit_ucs_inprov += $row->visit_ucs_inprov ; ?>
                  <?php $sum_inc_ucs_inprov += $row->inc_ucs_inprov ; ?>
                  <?php $sum_inc_lab_ucs_inprov += $row->inc_lab_ucs_inprov ; ?>
                  <?php $sum_inc_drug_ucs_inprov += $row->inc_drug_ucs_inprov ; ?>
                  <?php $sum_visit_ucs_outprov += $row->visit_ucs_outprov ; ?>
                  <?php $sum_inc_ucs_outprov += $row->inc_ucs_outprov ; ?>
                  <?php $sum_inc_lab_ucs_outprov += $row->inc_lab_ucs_outprov ; ?>
                  <?php $sum_inc_drug_ucs_outprov += $row->inc_drug_ucs_outprov ; ?> 
                  <?php $sum_visit_ofc += $row->visit_ofc ; ?>
                  <?php $sum_inc_ofc += $row->inc_ofc ; ?>
                  <?php $sum_inc_lab_ofc += $row->inc_lab_ofc ; ?>
                  <?php $sum_inc_drug_ofc += $row->inc_drug_ofc ; ?> 
                  <?php $sum_visit_bkk += $row->visit_bkk ; ?>
                  <?php $sum_inc_bkk += $row->inc_bkk ; ?>
                  <?php $sum_inc_lab_bkk += $row->inc_lab_bkk ; ?>
                  <?php $sum_inc_drug_bkk += $row->inc_drug_bkk ; ?>  
                  <?php $sum_visit_bmt += $row->visit_bmt ; ?>
                  <?php $sum_inc_bmt += $row->inc_bmt ; ?>
                  <?php $sum_inc_lab_bmt += $row->inc_lab_bmt ; ?>
                  <?php $sum_inc_drug_bmt += $row->inc_drug_bmt ; ?> 
                  <?php $sum_visit_sss += $row->visit_sss ; ?>
                  <?php $sum_inc_sss += $row->inc_sss ; ?>
                  <?php $sum_inc_lab_sss += $row->inc_lab_sss ; ?>
                  <?php $sum_inc_drug_sss += $row->inc_drug_sss ; ?>   
                  <?php $sum_visit_lgo += $row->visit_lgo ; ?>
                  <?php $sum_inc_lgo += $row->inc_lgo ; ?>
                  <?php $sum_inc_lab_lgo += $row->inc_lab_lgo ; ?>
                  <?php $sum_inc_drug_lgo += $row->inc_drug_lgo ; ?>
                  <?php $sum_visit_fss += $row->visit_fss ; ?>
                  <?php $sum_inc_fss += $row->inc_fss ; ?>
                  <?php $sum_inc_lab_fss += $row->inc_lab_fss ; ?>
                  <?php $sum_inc_drug_fss += $row->inc_drug_fss ; ?>    
                  <?php $sum_visit_stp += $row->visit_stp ; ?>
                  <?php $sum_inc_stp += $row->inc_stp ; ?>
                  <?php $sum_inc_lab_stp += $row->inc_lab_stp ; ?>
                  <?php $sum_inc_drug_stp += $row->inc_drug_stp ; ?>   
                  <?php $sum_visit_pay += $row->visit_pay ; ?>
                  <?php $sum_inc_pay += $row->inc_pay ; ?>
                  <?php $sum_inc_lab_pay += $row->inc_lab_pay ; ?>
                  <?php $sum_inc_drug_pay += $row->inc_drug_pay ; ?> 
                  @endforeach    
                  <tr>
                    <td align="right"><strong>รวม</strong></td>
                    <td align="right"><strong>{{number_format($sum_hn_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_total_op)}}</strong></td>     
                    <td align="right"><strong>{{number_format($sum_visit_total_pp)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_inc_total,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_inc_lab_total,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_inc_drug_total,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_incup)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_incup,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_incup,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_ucs_inprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_inprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_visit_ucs_outprov)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ucs_outprov,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ucs_outprov,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_ofc)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_ofc,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_ofc,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bkk)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bkk,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bkk,2)}}</strong></td>   
                    <td align="right"><strong>{{number_format($sum_visit_bmt)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_bmt,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_bmt,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_sss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_sss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_sss,2)}}</strong></td>  
                    <td align="right"><strong>{{number_format($sum_visit_lgo)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_lgo,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_lgo,2)}}</strong></td>       
                    <td align="right"><strong>{{number_format($sum_visit_fss)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_fss,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_fss,2)}}</strong></td>    
                    <td align="right"><strong>{{number_format($sum_visit_stp)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_stp,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_stp,2)}}</strong></td> 
                    <td align="right"><strong>{{number_format($sum_visit_pay)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_lab_pay,2)}}</strong></td>
                    <td align="right"><strong>{{number_format($sum_inc_drug_pay,2)}}</strong></td> 
                  </tr>   
                </tbody>
              </table>
            </div>
          </div>
          
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
  <script>
    $(function () {
      $('#table10985').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลชานุมาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10986').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลปทุมราชวงศา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10987').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลพนา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10988').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลเสนางคนิคม {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10989').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลหัวตะพาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10990').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลลืออำนาจ {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10703').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยนอก OPD โรงพยาบาลอำนาจเจริญ {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>

  <script>
    $(function () {
      $('#table10985_ipd').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลชานุมาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10986_ipd').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลปทุมราชวงศา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10987_ipd').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลพนา {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10988_ipd').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลเสนางคนิคม {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10989_ipd').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลหัวตะพาน {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>
  <script>
    $(function () {
      $('#table10990_ipd').DataTable({
        dom: '<"d-flex justify-content-end mb-2"B>rt',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> ส่งออก Excel',
            className: 'btn btn-success btn-sm',
            title: 'ข้อมูลบริการผู้ป่วยใน IPD โรงพยาบาลลืออำนาจ {{ $budget_year ?? "" }}'
          }
        ],
        ordering: false,
        paging: false,
        info: false,
        lengthChange: false,
        language: { search: "ค้นหา:" }
      });
    });
  </script>

<!-- script กราฟ  ---------------------------------------------------------------------------------------->
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ✅ ดึงข้อมูลจาก PHP
      const months = {!! json_encode(array_column($total_10985_ipd, 'month')) !!};
      const bed_occupancy = {!! json_encode(array_column($total_10985_ipd, 'bed_occupancy')) !!};
      const cmi = {!! json_encode(array_column($total_10985_ipd, 'cmi')) !!};
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
      const months = {!! json_encode(array_column($total_10986_ipd, 'month')) !!};
      const bed_occupancy = {!! json_encode(array_column($total_10986_ipd, 'bed_occupancy')) !!};
      const cmi = {!! json_encode(array_column($total_10986_ipd, 'cmi')) !!};
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
      const months = {!! json_encode(array_column($total_10987_ipd, 'month')) !!};
      const bed_occupancy = {!! json_encode(array_column($total_10987_ipd, 'bed_occupancy')) !!};
      const cmi = {!! json_encode(array_column($total_10987_ipd, 'cmi')) !!};
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
      const months = {!! json_encode(array_column($total_10988_ipd, 'month')) !!};
      const bed_occupancy = {!! json_encode(array_column($total_10988_ipd, 'bed_occupancy')) !!};
      const cmi = {!! json_encode(array_column($total_10988_ipd, 'cmi')) !!};
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
      const months = {!! json_encode(array_column($total_10989_ipd, 'month')) !!};
      const bed_occupancy = {!! json_encode(array_column($total_10989_ipd, 'bed_occupancy')) !!};
      const cmi = {!! json_encode(array_column($total_10989_ipd, 'cmi')) !!};
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
      const months = {!! json_encode(array_column($total_10990_ipd, 'month')) !!};
      const bed_occupancy = {!! json_encode(array_column($total_10990_ipd, 'bed_occupancy')) !!};
      const cmi = {!! json_encode(array_column($total_10990_ipd, 'cmi')) !!};
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
