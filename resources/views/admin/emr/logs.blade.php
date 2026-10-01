@extends('layouts.admin')

@section('title', 'A-EMR Audit Logs - AOPOD')
@section('header_title', 'A-EMR Audit Logs')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
<style>
  .log-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .log-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
  }
  .log-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    overflow: hidden;
  }
  .log-filter-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(241, 245, 249, 0.9) 100%);
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
  }
  .table-logs thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.9rem 0.8rem;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
  }
  .table-logs tbody td {
    padding: 0.85rem 0.8rem;
    vertical-align: middle;
    font-size: 0.88rem;
  }
  .table-logs tbody tr:hover {
    background-color: #f8fafc;
  }
  .badge-action {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    letter-spacing: 0.3px;
  }
  .badge-action-search {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
  }
  .badge-action-visit {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
  }
  .badge-status-success {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
  }
  .badge-status-notfound {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
  }
  .badge-status-error {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
  }
  .cid-code {
    font-family: 'Courier New', Courier, monospace;
    font-weight: 700;
    color: #0f172a;
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
  }
  .flatpickr-input[readonly] {
    background-color: #ffffff !important;
    cursor: pointer;
  }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- Top Action & Navigation Bar -->
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('manage.emr.index') }}" class="btn btn-sm btn-light border shadow-sm rounded-3 px-2.5 py-1.5 text-secondary" title="กลับหน้า A-EMR">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0 text-slate-800">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i> ประวัติการเข้าถึงข้อมูล A-EMR (Audit Trail Logs)
                </h4>
            </div>
            <p class="text-muted small mb-0 ms-md-5">
                บันทึกประวัติการสืบค้นเวชระเบียนและข้อมูลผู้ป่วยข้ามโรงพยาบาล ตามมาตรฐาน พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล (PDPA) และ พ.ร.บ. คอมพิวเตอร์ฯ
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('manage.emr.index') }}" class="btn btn-outline-secondary rounded-3 shadow-sm px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="fa-solid fa-notes-medical text-info"></i>
                <span>หน้าสืบค้น A-EMR</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-success rounded-3 shadow-sm px-3 py-2 fw-semibold d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-csv"></i>
                <span>ส่งออก CSV (Excel)</span>
            </a>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="col-6 col-lg-3">
        <div class="log-stat-card d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem;">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">สืบค้นวันนี้</div>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($todayCount) }} <span class="fs-6 fw-normal text-muted">ครั้ง</span></h4>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="log-stat-card d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">สืบค้นเดือนนี้</div>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($monthCount) }} <span class="fs-6 fw-normal text-muted">ครั้ง</span></h4>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="log-stat-card d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem;">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">บุคลากรที่ใช้งาน</div>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($uniqueUsersCount) }} <span class="fs-6 fw-normal text-muted">คน</span></h4>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="log-stat-card d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem;">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">ความเร็วเฉลี่ย (ms)</div>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($avgLatency, 0) }} <span class="fs-6 fw-normal text-muted">ms</span></h4>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="col-12">
        <div class="log-filter-card">
            <form method="GET" action="{{ route('manage.emr.logs') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">คำค้นหา (ผู้ใช้, CID, เหตุผล, IP)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="ระบุชื่อ, เลขบัตร 13 หลัก..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">ประเภท Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="SEARCH_CID" {{ request('action') == 'SEARCH_CID' ? 'selected' : '' }}>ค้นหาด้วย CID</option>
                        <option value="VIEW_VISIT_DETAIL" {{ request('action') == 'VIEW_VISIT_DETAIL' ? 'selected' : '' }}>ดูใบตรวจยา/แล็บ</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">หน่วยบริการ (รพ.)</label>
                    <select name="hospcode" class="form-select form-select-sm">
                        <option value="">-- ทุก รพ. --</option>
                        @foreach($hospitals as $hosp)
                            <option value="{{ $hosp->hospcode }}" {{ request('hospcode') == $hosp->hospcode ? 'selected' : '' }}>{{ $hosp->name }} ({{ $hosp->hospcode }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">สถานะผลลัพธ์</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>SUCCESS (พบข้อมูล)</option>
                        <option value="NOT_FOUND" {{ request('status') == 'NOT_FOUND' ? 'selected' : '' }}>NOT_FOUND (ไม่พบ)</option>
                        <option value="ERROR" {{ request('status') == 'ERROR' ? 'selected' : '' }}>ERROR (ล้มเหลว)</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">ตั้งแต่วันที่ (พ.ศ.)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-regular fa-calendar text-muted"></i></span>
                        <input type="text" id="date_start" name="date_start" class="form-control form-control-sm thai-datepicker" placeholder="วว/ดด/ปปปป" value="{{ request('date_start') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">ถึงวันที่ (พ.ศ.)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-regular fa-calendar text-muted"></i></span>
                        <input type="text" id="date_end" name="date_end" class="form-control form-control-sm thai-datepicker" placeholder="วว/ดด/ปปปป" value="{{ request('date_end') }}">
                    </div>
                </div>

                <div class="col-12 col-md-auto d-flex gap-1.5 align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" style="border-radius: 8px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>ค้นหา</span>
                    </button>
                    @if(request()->hasAny(['search', 'action', 'status', 'date_start', 'date_end', 'hospcode']))
                    <a href="{{ route('manage.emr.logs') }}" class="btn btn-sm btn-light border text-danger px-2.5 py-1.5 d-inline-flex align-items-center gap-1 shadow-sm" title="ล้างตัวกรอง" style="border-radius: 8px;">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>ล้าง</span>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table of Audit Logs -->
    <div class="col-12">
        <div class="log-table-card">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light bg-opacity-50">
                <span class="small fw-bold text-secondary">
                    พบประวัติการเข้าถึงทั้งหมด <span class="text-primary fw-bold">{{ number_format($logs->total()) }}</span> รายการ
                </span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                    แสดงหน้า {{ $logs->currentPage() }} จาก {{ $logs->lastPage() }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-logs mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th>วัน-เวลา (NTP Sync)</th>
                            <th>ผู้ใช้งาน (Who)</th>
                            <th>การกระทำ (Action)</th>
                            <th>ข้อมูลเป้าหมาย (Target)</th>
                            <th>วัตถุประสงค์ (PDPA Reason)</th>
                            <th>เครือข่าย & อุปกรณ์ (Where)</th>
                            <th class="text-center">สถานะ & ความเร็ว</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="text-center text-muted small fw-bold">{{ $log->id }}</td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                                    {{ $log->created_at ? $log->created_at->addYears(543)->format('d/m/Y H:i:s') : '-' }}
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-slate-800">{{ $log->user_name }}</div>
                                <div class="d-flex align-items-center gap-1.5 text-muted small" style="font-size: 0.78rem;">
                                    <span class="badge bg-light text-secondary border">{{ $log->user_role }}</span>
                                    @if($log->user_hospcode)
                                    <span>รพ. {{ $log->user_hospcode }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($log->action === 'SEARCH_CID')
                                    <span class="badge-action badge-action-search">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> SEARCH_CID
                                    </span>
                                @elseif($log->action === 'VIEW_VISIT_DETAIL')
                                    <span class="badge-action badge-action-visit">
                                        <i class="fa-solid fa-file-medical me-1"></i> VIEW_VISIT
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->action }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->target_cid)
                                <div class="mb-1">
                                    <span class="text-muted small">CID:</span> 
                                    <span class="cid-code">{{ $log->masked_cid }}</span>
                                </div>
                                @endif
                                @if($log->target_hn)
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    HN: <span class="fw-bold text-dark">{{ $log->target_hn }}</span>
                                </div>
                                @endif
                                @if($log->target_vn)
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    VN/AN: <span class="fw-bold text-info">{{ $log->target_vn }}</span>
                                    @if($log->target_hospcode) (รพ. {{ $log->target_hospcode }})@endif
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-slate-700" style="max-width: 250px; line-height: 1.35;">
                                    <i class="fa-solid fa-circle-info text-info me-1" style="font-size: 0.75rem;"></i>
                                    {{ $log->reason }}
                                </div>
                                @if($log->record_count > 0)
                                <div class="text-muted" style="font-size: 0.75rem; margin-top: 3px;">
                                    พบข้อมูล: <span class="fw-semibold text-success">{{ $log->record_count }} รายการ</span>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="font-monospace small text-dark">{{ $log->ip_address }}</div>
                                <div class="text-muted text-truncate" style="max-width: 180px; font-size: 0.72rem;" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($log->status === 'SUCCESS')
                                    <span class="badge badge-status-success mb-1">
                                        <i class="fa-solid fa-check me-1"></i> SUCCESS
                                    </span>
                                @elseif($log->status === 'NOT_FOUND')
                                    <span class="badge badge-status-notfound mb-1">
                                        <i class="fa-solid fa-minus me-1"></i> NOT FOUND
                                    </span>
                                @else
                                    <span class="badge badge-status-error mb-1">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $log->status }}
                                    </span>
                                @endif
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="fa-regular fa-clock me-1"></i>{{ $log->response_time_ms }} ms
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clipboard-check mb-2" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                <div class="fw-semibold">ยังไม่มีประวัติการเข้าถึงข้อมูลตามเงื่อนไขที่ค้นหา</div>
                                <div class="small text-muted mt-1">ประวัติจะถูกบันทึกอัตโนมัติเมื่อมีบุคลากรใช้งานระบบ A-EMR</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if($logs->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    แสดง {{ $logs->firstItem() ?? 0 }} ถึง {{ $logs->lastItem() ?? 0 }} จาก {{ $logs->total() }} รายการ
                </div>
                <div>
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/th.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/flatpickr-th-buddhist.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof initThaiDatePicker === 'function') {
            initThaiDatePicker('.thai-datepicker', {
                altFormat: 'd/m/Y',
                allowInput: true
            });
        }
    });
</script>
@endpush
