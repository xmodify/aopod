@extends('layouts.app')

@section('title', 'Dashboard Operation | AOPOD')

<style>
  .card-hospital {
    border: none;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease-in-out;
  }
  
  /* === Modern Table Styling (High-End & Clean) === */
  .custom-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: none !important;
    border-radius: 16px !important;
    overflow: hidden !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
    background: rgba(255, 255, 255, 0.45) !important;
    backdrop-filter: blur(8px) !important;
  }
  .custom-table thead th {
    border: none !important;
    font-weight: 700 !important;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 14px 16px !important;
    font-size: 0.85rem !important;
  }
  .custom-table tbody td {
    border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important;
    border-top: none !important;
    border-left: none !important;
    border-right: none !important;
    padding: 12px 16px !important;
    font-size: 0.88rem !important;
    color: #334155 !important;
    vertical-align: middle !important;
  }
  .custom-table tbody tr:last-child td {
    border-bottom: none !important;
  }
  .custom-table tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.7) !important;
  }
  
  .th-grey { background: rgba(241, 245, 249, 0.7) !important; color: #475569 !important; }
  
  .card-theme-op {
    background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 100%);
    border-left: 6px solid #475569 !important;
  }

  .card-hover {
    transition: all 0.3s ease-in-out;
  }
  .card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }

  /* === Modern Premium Tab Pills Style === */
  .nav-pills .nav-link {
    background: rgba(255, 255, 255, 0.4) !important;
    backdrop-filter: blur(10px) !important;
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
    border-radius: 14px !important;
    padding: 10px 18px !important;
    color: #475569 !important;
    font-weight: 600 !important;
    transition: all 0.25s ease-in-out !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02) !important;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
  }
  .nav-pills .nav-link:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.8) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
  }

  /* ========================================================
     Hospital Custom Colorful Branding (Tabs, Tables, Cards)
     ======================================================== */
  
  /* 10985 Chanuman (Purple) */
  .card-10985 {
    background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
    border-left: 6px solid #8b5cf6 !important;
  }
  .text-10985 { color: #7c3aed !important; }
  .th-10985 { background: rgba(245, 243, 255, 0.8) !important; color: #7c3aed !important; }
  .tr-total-10985 { background: rgba(245, 243, 255, 0.5) !important; font-weight: 800 !important; border-top: 2px solid #8b5cf6 !important; }
  #tab-10985.active {
    background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%) !important;
    border-left: 5px solid #8b5cf6 !important;
    border-top: 1px solid rgba(139, 92, 246, 0.15) !important;
    border-right: 1px solid rgba(139, 92, 246, 0.15) !important;
    border-bottom: 1px solid rgba(139, 92, 246, 0.15) !important;
    color: #7c3aed !important;
    box-shadow: 0 6px 15px rgba(139, 92, 246, 0.12) !important;
  }

  /* 10986 Pathum (Teal) */
  .card-10986 {
    background: linear-gradient(135deg, #ecfeff 0%, #ffffff 100%);
    border-left: 6px solid #06b6d4 !important;
  }
  .text-10986 { color: #0891b2 !important; }
  .th-10986 { background: rgba(236, 254, 255, 0.8) !important; color: #0891b2 !important; }
  .tr-total-10986 { background: rgba(236, 254, 255, 0.5) !important; font-weight: 800 !important; border-top: 2px solid #06b6d4 !important; }
  #tab-10986.active {
    background: linear-gradient(135deg, #ecfeff 0%, #ffffff 100%) !important;
    border-left: 5px solid #06b6d4 !important;
    border-top: 1px solid rgba(6, 182, 212, 0.15) !important;
    border-right: 1px solid rgba(6, 182, 212, 0.15) !important;
    border-bottom: 1px solid rgba(6, 182, 212, 0.15) !important;
    color: #0891b2 !important;
    box-shadow: 0 6px 15px rgba(6, 182, 212, 0.12) !important;
  }

  /* 10987 Phana (Pink) */
  .card-10987 {
    background: linear-gradient(135deg, #fdf2f8 0%, #ffffff 100%);
    border-left: 6px solid #ec4899 !important;
  }
  .text-10987 { color: #db2777 !important; }
  .th-10987 { background: rgba(253, 242, 248, 0.8) !important; color: #db2777 !important; }
  .tr-total-10987 { background: rgba(253, 242, 248, 0.5) !important; font-weight: 800 !important; border-top: 2px solid #ec4899 !important; }
  #tab-10987.active {
    background: linear-gradient(135deg, #fdf2f8 0%, #ffffff 100%) !important;
    border-left: 5px solid #ec4899 !important;
    border-top: 1px solid rgba(236, 72, 153, 0.15) !important;
    border-right: 1px solid rgba(236, 72, 153, 0.15) !important;
    border-bottom: 1px solid rgba(236, 72, 153, 0.15) !important;
    color: #db2777 !important;
    box-shadow: 0 6px 15px rgba(236, 72, 153, 0.12) !important;
  }

  /* 10988 Senangkhanikhom (Amber) */
  .card-10988 {
    background: linear-gradient(135deg, #fef3c7 0%, #ffffff 100%);
    border-left: 6px solid #f59e0b !important;
  }
  .text-10988 { color: #d97706 !important; }
  .th-10988 { background: rgba(254, 243, 199, 0.8) !important; color: #d97706 !important; }
  .tr-total-10988 { background: rgba(254, 243, 199, 0.5) !important; font-weight: 800 !important; border-top: 2px solid #f59e0b !important; }
  #tab-10988.active {
    background: linear-gradient(135deg, #fef3c7 0%, #ffffff 100%) !important;
    border-left: 5px solid #f59e0b !important;
    border-top: 1px solid rgba(245, 158, 11, 0.15) !important;
    border-right: 1px solid rgba(245, 158, 11, 0.15) !important;
    border-bottom: 1px solid rgba(245, 158, 11, 0.15) !important;
    color: #d97706 !important;
    box-shadow: 0 6px 15px rgba(245, 158, 11, 0.12) !important;
  }

  /* 10989 Hua Taphan (Blue) */
  .card-10989 {
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
    border-left: 6px solid #3b82f6 !important;
  }
  .text-10989 { color: #2563eb !important; }
  .th-10989 { background: rgba(239, 246, 255, 0.8) !important; color: #2563eb !important; }
  .tr-total-10989 { background: rgba(239, 246, 255, 0.5) !important; font-weight: 800 !important; border-top: 2px solid #3b82f6 !important; }
  #tab-10989.active {
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%) !important;
    border-left: 5px solid #3b82f6 !important;
    border-top: 1px solid rgba(59, 130, 246, 0.15) !important;
    border-right: 1px solid rgba(59, 130, 246, 0.15) !important;
    border-bottom: 1px solid rgba(59, 130, 246, 0.15) !important;
    color: #2563eb !important;
    box-shadow: 0 6px 15px rgba(59, 130, 246, 0.12) !important;
  }

  /* 10990 Lue Amnat (Emerald) */
  .card-10990 {
    background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
    border-left: 6px solid #10b981 !important;
  }
  .text-10990 { color: #059669 !important; }
  .th-10990 { background: rgba(236, 253, 245, 0.8) !important; color: #059669 !important; }
  .tr-total-10990 { background: rgba(236, 253, 245, 0.5) !important; font-weight: 800 !important; border-top: 2px solid #10b981 !important; }
  #tab-10990.active {
    background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%) !important;
    border-left: 5px solid #10b981 !important;
    border-top: 1px solid rgba(16, 185, 129, 0.15) !important;
    border-right: 1px solid rgba(16, 185, 129, 0.15) !important;
    border-bottom: 1px solid rgba(16, 185, 129, 0.15) !important;
    color: #059669 !important;
    box-shadow: 0 6px 15px rgba(16, 185, 129, 0.12) !important;
  }

  /* 10703 Amnat Charoen (Rose) */
  .card-10703 {
    background: linear-gradient(135deg, #fff1f2 0%, #ffffff 100%);
    border-left: 6px solid #f43f5e !important;
  }
  .text-10703 { color: #e11d48 !important; }
  .th-10703 { background: rgba(255, 241, 242, 0.8) !important; color: #e11d48 !important; }
  .tr-total-10703 { background: rgba(255, 241, 242, 0.5) !important; font-weight: 800 !important; border-top: 2px solid #f43f5e !important; }
  #tab-10703.active {
    background: linear-gradient(135deg, #fff1f2 0%, #ffffff 100%) !important;
    border-left: 5px solid #f43f5e !important;
    border-top: 1px solid rgba(244, 63, 94, 0.15) !important;
    border-right: 1px solid rgba(244, 63, 94, 0.15) !important;
    border-bottom: 1px solid rgba(244, 63, 94, 0.15) !important;
    color: #e11d48 !important;
    box-shadow: 0 6px 15px rgba(244, 63, 94, 0.12) !important;
  }
</style>

@section('content')

  <!-- HERO -->
  <header class="py-4">
    <div class="container-fluid">      
        <div class="row g-4 align-items-center">
          <div class="col-lg-7">          
            <div class="d-flex align-items-center gap-3">
              <div class="bg-purple bg-opacity-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border: 1px solid rgba(139, 92, 246, 0.2); background-color: rgba(139, 92, 246, 0.08);">
                <i class="fa-solid fa-heart-pulse fs-4 text-purple" style="color: #8b5cf6;"></i>
              </div>
              <div>
                <h4 class="fw-bold mb-1" style="color: #1e293b;">ข้อมูลบริการการผ่าตัด (Operation)</h4>
                <p class="text-secondary small mb-0"><i class="fa-solid fa-square-poll-vertical text-green me-1"></i>Amnatcharoen One Province One Data</p>
              </div>
            </div>          
          </div>
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
    $fmtInt = fn($n) => number_format((int)($n ?? 0));
  @endphp

  <!-- แถวการ์ดจำนวนผ่าตัดวันนี้ราย รพ -->
  <section id="summary" class="pb-2">
    <div class="container-fluid">      
      <div class="row g-3">
        <!-- Card รวมทั้งหมด -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
          <div class="card card-theme-op glass p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="mb-0 fw-bold text-dark">ผ่าตัดรวมทั้งหมดวันนี้</h6>
              <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(71, 85, 105, 0.1);">
                <i class="fa-solid fa-square-poll-vertical fs-4 text-dark"></i>
              </div>
            </div>
            <div class="d-flex align-items-end gap-3">
              <div class="text-start">
                <div class="small text-secondary">เคสผ่าตัดวันนี้</div>
                <div class="fw-bold text-dark" style="font-size:1.5rem;">
                  {{ number_format($hospitalSummary->sum('visit_operation')) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Loop 7 รพ (สีสันตามสีประจำ รพ) -->
        @foreach($hospitalSummary as $h)
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
          <div class="card card-{{ $h->hospcode }} card-hover glass p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="mb-0 fw-bold text-{{ $h->hospcode }}">{{ $h->hospname }}</h6>
              <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: rgba(0,0,0,0.03);">
                <i class="fa-solid fa-kit-medical fs-4 text-{{ $h->hospcode }}"></i>
              </div>
            </div>
            <div class="d-flex align-items-end justify-content-between">
              <div class="text-start">
                <div class="small text-secondary">เคสผ่าตัดวันนี้</div>
                <div class="fw-bold text-{{ $h->hospcode }}" style="font-size:1.5rem;">
                  {{ number_format($h->visit_operation) }}
                </div>
              </div>
              <div class="text-end">
                <small class="text-muted" style="font-size: 0.75rem;">
                  Update:<br>
                  {{ $h->last_updated_at ? \Carbon\Carbon::parse($h->last_updated_at)->locale('th')->isoFormat('D MMM YYYY H:mm') . ' น.' : 'ยังไม่มีข้อมูลส่ง' }}
                </small>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>  
  </section>

  <hr>

  {{-- เลือกปีงบประมาณ --}}
  <section id="filter-year" class="pb-2">
      <div class="container-fluid">
        <form method="POST" action="{{ url('web/operation') }}">
        @csrf
          <div class="row g-4 align-items-center">
            <div class="col-lg-9">          
              <h6 class="text-success mb-2"><strong>ตารางวิเคราะห์ข้อมูลการผ่าตัดรายเดือน</strong></h6>          
            </div>
            <div class="col-lg-3 d-flex justify-content-lg-end">
              <div class="d-flex align-items-center gap-2">
                <select class="form-select border-secondary-subtle" id="budget_year" name="budget_year" style="min-width: 190px; border-radius: 8px;">
                  @foreach($budget_year_select as $y)
                    <option value="{{ $y->LEAVE_YEAR_ID }}" {{ $budget_year == $y->LEAVE_YEAR_ID ? 'selected' : '' }}>
                      {{ $y->LEAVE_YEAR_NAME }}
                    </option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-primary px-3 fw-bold" style="background-color: #0d6efd; border-color: #0d6efd; border-radius: 8px;">ค้นหา</button>
              </div>
            </div>
          </div>
        </form>
      </div>
  </section>

  {{-- แท็บแสดงตารางแยก รพ --}}
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

        <!-- 10985 (Chanuman - Purple) -->
        <div class="tab-pane fade show active" id="pane-10985" role="tabpanel" aria-labelledby="tab-10985" tabindex="0">          
          <div class="glass p-4 rounded-4 shadow-sm border border-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-10985"><i class="fa-solid fa-scissors text-10985 me-2"></i>[10985] ข้อมูลการผ่าตัด โรงพยาบาลชานุมาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-3 py-1 rounded-pill border border-light">Update: {{$update_at10985}}</span>
                <div id="btn-10985-op"></div>
              </div>
            </div>
            
            <div class="row g-4">
              <!-- Left: Table -->
              <div class="col-lg-6">
                <div class="table-responsive">
                  <table id="table10985_op" class="table custom-table my-2" width="100%">
                    <thead>
                      <tr>
                        <th class="th-grey" width="45%">เดือน</th>
                        <th class="th-10985">จำนวนผ่าตัด</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($operation_10985 as $row)
                      <tr>
                        <td class="text-start ps-4"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>{{ $row->month }}</td>
                        <td class="text-end pe-4 fw-bold text-10985">{{ number_format($row->visit_operation) }} <span class="text-muted fw-normal small ms-1">เคส</span></td>
                      </tr>
                    @endforeach
                      <tr class="tr-total-10985">
                        <td class="text-start ps-4 fw-bold">รวมทั้งหมด</td>
                        <td class="text-end pe-4 text-10985">{{ number_format($operation_10985->sum('visit_operation')) }} <span class="fw-normal small ms-1">เคส</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              
              <!-- Right: Chart -->
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-10985 text-center mb-3"><i class="fa-solid fa-chart-line me-2"></i>กราฟสรุปจำนวนการผ่าตัดรายเดือน</h6>
                    <div id="chart_10985" style="min-height: 280px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10986 (Pathum - Teal) -->
        <div class="tab-pane fade" id="pane-10986" role="tabpanel" aria-labelledby="tab-10986" tabindex="0">          
          <div class="glass p-4 rounded-4 shadow-sm border border-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-10986"><i class="fa-solid fa-scissors text-10986 me-2"></i>[10986] ข้อมูลการผ่าตัด โรงพยาบาลปทุมราชวงศา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-3 py-1 rounded-pill border border-light">Update: {{$update_at10986}}</span>
                <div id="btn-10986-op"></div>
              </div>
            </div>
            
            <div class="row g-4">
              <div class="col-lg-6">
                <div class="table-responsive">
                  <table id="table10986_op" class="table custom-table my-2" width="100%">
                    <thead>
                      <tr>
                        <th class="th-grey" width="45%">เดือน</th>
                        <th class="th-10986">จำนวนผ่าตัด</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($operation_10986 as $row)
                      <tr>
                        <td class="text-start ps-4"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>{{ $row->month }}</td>
                        <td class="text-end pe-4 fw-bold text-10986">{{ number_format($row->visit_operation) }} <span class="text-muted fw-normal small ms-1">เคส</span></td>
                      </tr>
                    @endforeach
                      <tr class="tr-total-10986">
                        <td class="text-start ps-4 fw-bold">รวมทั้งหมด</td>
                        <td class="text-end pe-4 text-10986">{{ number_format($operation_10986->sum('visit_operation')) }} <span class="fw-normal small ms-1">เคส</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-10986 text-center mb-3"><i class="fa-solid fa-chart-line me-2"></i>กราฟสรุปจำนวนการผ่าตัดรายเดือน</h6>
                    <div id="chart_10986" style="min-height: 280px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10987 (Phana - Pink) -->
        <div class="tab-pane fade" id="pane-10987" role="tabpanel" aria-labelledby="tab-10987" tabindex="0">          
          <div class="glass p-4 rounded-4 shadow-sm border border-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-10987"><i class="fa-solid fa-scissors text-10987 me-2"></i>[10987] ข้อมูลการผ่าตัด โรงพยาบาลพนา</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-3 py-1 rounded-pill border border-light">Update: {{$update_at10987}}</span>
                <div id="btn-10987-op"></div>
              </div>
            </div>
            
            <div class="row g-4">
              <div class="col-lg-6">
                <div class="table-responsive">
                  <table id="table10987_op" class="table custom-table my-2" width="100%">
                    <thead>
                      <tr>
                        <th class="th-grey" width="45%">เดือน</th>
                        <th class="th-10987">จำนวนผ่าตัด</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($operation_10987 as $row)
                      <tr>
                        <td class="text-start ps-4"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>{{ $row->month }}</td>
                        <td class="text-end pe-4 fw-bold text-10987">{{ number_format($row->visit_operation) }} <span class="text-muted fw-normal small ms-1">เคส</span></td>
                      </tr>
                    @endforeach
                      <tr class="tr-total-10987">
                        <td class="text-start ps-4 fw-bold">รวมทั้งหมด</td>
                        <td class="text-end pe-4 text-10987">{{ number_format($operation_10987->sum('visit_operation')) }} <span class="fw-normal small ms-1">เคส</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-10987 text-center mb-3"><i class="fa-solid fa-chart-line me-2"></i>กราฟสรุปจำนวนการผ่าตัดรายเดือน</h6>
                    <div id="chart_10987" style="min-height: 280px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10988 (Senang - Amber) -->
        <div class="tab-pane fade" id="pane-10988" role="tabpanel" aria-labelledby="tab-10988" tabindex="0">          
          <div class="glass p-4 rounded-4 shadow-sm border border-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-10988"><i class="fa-solid fa-scissors text-10988 me-2"></i>[10988] ข้อมูลการผ่าตัด โรงพยาบาลเสนางคนิคม</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-3 py-1 rounded-pill border border-light">Update: {{$update_at10988}}</span>
                <div id="btn-10988-op"></div>
              </div>
            </div>
            
            <div class="row g-4">
              <div class="col-lg-6">
                <div class="table-responsive">
                  <table id="table10988_op" class="table custom-table my-2" width="100%">
                    <thead>
                      <tr>
                        <th class="th-grey" width="45%">เดือน</th>
                        <th class="th-10988">จำนวนผ่าตัด</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($operation_10988 as $row)
                      <tr>
                        <td class="text-start ps-4"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>{{ $row->month }}</td>
                        <td class="text-end pe-4 fw-bold text-10988">{{ number_format($row->visit_operation) }} <span class="text-muted fw-normal small ms-1">เคส</span></td>
                      </tr>
                    @endforeach
                      <tr class="tr-total-10988">
                        <td class="text-start ps-4 fw-bold">รวมทั้งหมด</td>
                        <td class="text-end pe-4 text-10988">{{ number_format($operation_10988->sum('visit_operation')) }} <span class="fw-normal small ms-1">เคส</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-10988 text-center mb-3"><i class="fa-solid fa-chart-line me-2"></i>กราฟสรุปจำนวนการผ่าตัดรายเดือน</h6>
                    <div id="chart_10988" style="min-height: 280px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10989 (Hua Taphan - Blue) -->
        <div class="tab-pane fade" id="pane-10989" role="tabpanel" aria-labelledby="tab-10989" tabindex="0">          
          <div class="glass p-4 rounded-4 shadow-sm border border-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-10989"><i class="fa-solid fa-scissors text-10989 me-2"></i>[10989] ข้อมูลการผ่าตัด โรงพยาบาลหัวตะพาน</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-3 py-1 rounded-pill border border-light">Update: {{$update_at10989}}</span>
                <div id="btn-10989-op"></div>
              </div>
            </div>
            
            <div class="row g-4">
              <div class="col-lg-6">
                <div class="table-responsive">
                  <table id="table10989_op" class="table custom-table my-2" width="100%">
                    <thead>
                      <tr>
                        <th class="th-grey" width="45%">เดือน</th>
                        <th class="th-10989">จำนวนผ่าตัด</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($operation_10989 as $row)
                      <tr>
                        <td class="text-start ps-4"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>{{ $row->month }}</td>
                        <td class="text-end pe-4 fw-bold text-10989">{{ number_format($row->visit_operation) }} <span class="text-muted fw-normal small ms-1">เคส</span></td>
                      </tr>
                    @endforeach
                      <tr class="tr-total-10989">
                        <td class="text-start ps-4 fw-bold">รวมทั้งหมด</td>
                        <td class="text-end pe-4 text-10989">{{ number_format($operation_10989->sum('visit_operation')) }} <span class="fw-normal small ms-1">เคส</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-10989 text-center mb-3"><i class="fa-solid fa-chart-line me-2"></i>กราฟสรุปจำนวนการผ่าตัดรายเดือน</h6>
                    <div id="chart_10989" style="min-height: 280px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10990 (Lue Amnat - Emerald) -->
        <div class="tab-pane fade" id="pane-10990" role="tabpanel" aria-labelledby="tab-10990" tabindex="0">          
          <div class="glass p-4 rounded-4 shadow-sm border border-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-10990"><i class="fa-solid fa-scissors text-10990 me-2"></i>[10990] ข้อมูลการผ่าตัด โรงพยาบาลลืออำนาจ</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-3 py-1 rounded-pill border border-light">Update: {{$update_at10990}}</span>
                <div id="btn-10990-op"></div>
              </div>
            </div>
            
            <div class="row g-4">
              <div class="col-lg-6">
                <div class="table-responsive">
                  <table id="table10990_op" class="table custom-table my-2" width="100%">
                    <thead>
                      <tr>
                        <th class="th-grey" width="45%">เดือน</th>
                        <th class="th-10990">จำนวนผ่าตัด</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($operation_10990 as $row)
                      <tr>
                        <td class="text-start ps-4"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>{{ $row->month }}</td>
                        <td class="text-end pe-4 fw-bold text-10990">{{ number_format($row->visit_operation) }} <span class="text-muted fw-normal small ms-1">เคส</span></td>
                      </tr>
                    @endforeach
                      <tr class="tr-total-10990">
                        <td class="text-start ps-4 fw-bold">รวมทั้งหมด</td>
                        <td class="text-end pe-4 text-10990">{{ number_format($operation_10990->sum('visit_operation')) }} <span class="fw-normal small ms-1">เคส</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-10990 text-center mb-3"><i class="fa-solid fa-chart-line me-2"></i>กราฟสรุปจำนวนการผ่าตัดรายเดือน</h6>
                    <div id="chart_10990" style="min-height: 280px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 10703 (Amnat Charoen - Rose) -->
        <div class="tab-pane fade" id="pane-10703" role="tabpanel" aria-labelledby="tab-10703" tabindex="0">          
          <div class="glass p-4 rounded-4 shadow-sm border border-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-10703"><i class="fa-solid fa-scissors text-10703 me-2"></i>[10703] ข้อมูลการผ่าตัด โรงพยาบาลอำนาจเจริญ</h6>
              <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small bg-white px-3 py-1 rounded-pill border border-light">Update: {{$update_at10703}}</span>
                <div id="btn-10703-op"></div>
              </div>
            </div>
            
            <div class="row g-4">
              <div class="col-lg-6">
                <div class="table-responsive">
                  <table id="table10703_op" class="table custom-table my-2" width="100%">
                    <thead>
                      <tr>
                        <th class="th-grey" width="45%">เดือน</th>
                        <th class="th-10703">จำนวนผ่าตัด</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($operation_10703 as $row)
                      <tr>
                        <td class="text-start ps-4"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>{{ $row->month }}</td>
                        <td class="text-end pe-4 fw-bold text-10703">{{ number_format($row->visit_operation) }} <span class="text-muted fw-normal small ms-1">เคส</span></td>
                      </tr>
                    @endforeach
                      <tr class="tr-total-10703">
                        <td class="text-start ps-4 fw-bold">รวมทั้งหมด</td>
                        <td class="text-end pe-4 text-10703">{{ number_format($operation_10703->sum('visit_operation')) }} <span class="fw-normal small ms-1">เคส</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
                  <div class="card-body p-3">
                    <h6 class="fw-bold text-10703 text-center mb-3"><i class="fa-solid fa-chart-line me-2"></i>กราฟสรุปจำนวนการผ่าตัดรายเดือน</h6>
                    <div id="chart_10703" style="min-height: 280px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

@endsection

@push('scripts')
  <!-- ApexCharts Library -->
  <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

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

      // 10985 (Chanuman - Purple #8b5cf6)
      var t10985 = $('#table10985_op').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการผ่าตัด โรงพยาบาลชานุมาน'}]});
      t10985.buttons().container().appendTo('#btn-10985-op');

      const data_10985 = @json($operation_10985);
      const months_10985 = data_10985.map(item => item.month);
      const values_10985 = data_10985.map(item => parseInt(item.visit_operation || 0));

      new ApexCharts(document.querySelector("#chart_10985"), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#8b5cf6'],
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        series: [{ name: 'จำนวนผ่าตัด', data: values_10985 }],
        xaxis: { categories: months_10985 },
        yaxis: { labels: { formatter: val => parseInt(val) } }
      }).render();

      // 10986 (Pathum - Teal #06b6d4)
      var t10986 = $('#table10986_op').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการผ่าตัด โรงพยาบาลปทุมราชวงศา'}]});
      t10986.buttons().container().appendTo('#btn-10986-op');

      const data_10986 = @json($operation_10986);
      const months_10986 = data_10986.map(item => item.month);
      const values_10986 = data_10986.map(item => parseInt(item.visit_operation || 0));

      new ApexCharts(document.querySelector("#chart_10986"), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#06b6d4'],
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        series: [{ name: 'จำนวนผ่าตัด', data: values_10986 }],
        xaxis: { categories: months_10986 },
        yaxis: { labels: { formatter: val => parseInt(val) } }
      }).render();

      // 10987 (Phana - Pink #ec4899)
      var t10987 = $('#table10987_op').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการผ่าตัด โรงพยาบาลพนา'}]});
      t10987.buttons().container().appendTo('#btn-10987-op');

      const data_10987 = @json($operation_10987);
      const months_10987 = data_10987.map(item => item.month);
      const values_10987 = data_10987.map(item => parseInt(item.visit_operation || 0));

      new ApexCharts(document.querySelector("#chart_10987"), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#ec4899'],
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        series: [{ name: 'จำนวนผ่าตัด', data: values_10987 }],
        xaxis: { categories: months_10987 },
        yaxis: { labels: { formatter: val => parseInt(val) } }
      }).render();

      // 10988 (Senang - Amber #f59e0b)
      var t10988 = $('#table10988_op').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการผ่าตัด โรงพยาบาลเสนางคนิคม'}]});
      t10988.buttons().container().appendTo('#btn-10988-op');

      const data_10988 = @json($operation_10988);
      const months_10988 = data_10988.map(item => item.month);
      const values_10988 = data_10988.map(item => parseInt(item.visit_operation || 0));

      new ApexCharts(document.querySelector("#chart_10988"), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#f59e0b'],
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        series: [{ name: 'จำนวนผ่าตัด', data: values_10988 }],
        xaxis: { categories: months_10988 },
        yaxis: { labels: { formatter: val => parseInt(val) } }
      }).render();

      // 10989 (Hua Taphan - Blue #3b82f6)
      var t10989 = $('#table10989_op').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการผ่าตัด โรงพยาบาลหัวตะพาน'}]});
      t10989.buttons().container().appendTo('#btn-10989-op');

      const data_10989 = @json($operation_10989);
      const months_10989 = data_10989.map(item => item.month);
      const values_10989 = data_10989.map(item => parseInt(item.visit_operation || 0));

      new ApexCharts(document.querySelector("#chart_10989"), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#3b82f6'],
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        series: [{ name: 'จำนวนผ่าตัด', data: values_10989 }],
        xaxis: { categories: months_10989 },
        yaxis: { labels: { formatter: val => parseInt(val) } }
      }).render();

      // 10990 (Lue Amnat - Emerald #10b981)
      var t10990 = $('#table10990_op').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการผ่าตัด โรงพยาบาลลืออำนาจ'}]});
      t10990.buttons().container().appendTo('#btn-10990-op');

      const data_10990 = @json($operation_10990);
      const months_10990 = data_10990.map(item => item.month);
      const values_10990 = data_10990.map(item => parseInt(item.visit_operation || 0));

      new ApexCharts(document.querySelector("#chart_10990"), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#10b981'],
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        series: [{ name: 'จำนวนผ่าตัด', data: values_10990 }],
        xaxis: { categories: months_10990 },
        yaxis: { labels: { formatter: val => parseInt(val) } }
      }).render();

      // 10703 (Amnat Charoen - Rose #f43f5e)
      var t10703 = $('#table10703_op').DataTable({...config, buttons: [{extend:'excelHtml5', text:'<i class="bi bi-file-earmark-excel"></i> Excel', className:'btn btn-success btn-sm rounded-pill px-3', title:'ข้อมูลการผ่าตัด โรงพยาบาลอำนาจเจริญ'}]});
      t10703.buttons().container().appendTo('#btn-10703-op');

      const data_10703 = @json($operation_10703);
      const months_10703 = data_10703.map(item => item.month);
      const values_10703 = data_10703.map(item => parseInt(item.visit_operation || 0));

      new ApexCharts(document.querySelector("#chart_10703"), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#f43f5e'],
        fill: {
          type: 'gradient',
          gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        series: [{ name: 'จำนวนผ่าตัด', data: values_10703 }],
        xaxis: { categories: months_10703 },
        yaxis: { labels: { formatter: val => parseInt(val) } }
      }).render();
    });
  </script>
@endpush
