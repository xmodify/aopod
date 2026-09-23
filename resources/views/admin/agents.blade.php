@extends('layouts.admin')

@section('title', 'จัดการ Agent โรงพยาบาล - AOPOD')
@section('header_title', 'จัดการ Agent โรงพยาบาล (AOPOD Agents)')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
<style>
.flatpickr-calendar {
    font-family: 'Inter', 'Noto Sans Thai', sans-serif !important;
    border-radius: 16px !important;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
    border: 1px solid rgba(24, 165, 115, 0.2) !important;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #18a573 !important;
    border-color: #18a573 !important;
}
.flatpickr-day:hover {
    background: rgba(24, 165, 115, 0.15) !important;
}

.custom-agent-tabs {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}
.custom-agent-tabs .nav-link {
    color: #475569;
    border-radius: 12px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}
.custom-agent-tabs .nav-link:hover {
    color: #0f172a;
    background-color: #f1f5f9;
}
.custom-agent-tabs .nav-link.active {
    background: linear-gradient(135deg, #18a573 0%, #128259 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(24, 165, 115, 0.35) !important;
}

.sql-editor-container {
    background-color: #0f172a;
    border-radius: 14px;
    border: 1px solid #334155;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.sql-editor-header {
    background-color: #1e293b;
    border-bottom: 1px solid #334155;
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sql-editor-textarea {
    background-color: #0f172a !important;
    color: #38bdf8 !important;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace !important;
    font-size: 0.88rem !important;
    line-height: 1.6 !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 16px !important;
    resize: vertical;
    width: 100%;
}
.sql-editor-textarea:focus {
    outline: none !important;
    box-shadow: none !important;
    background-color: #0b1120 !important;
}
.tag-badge {
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}
.tag-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush

@php
    $thMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $fmtThaiDate = function($date) use ($thMonths) {
        if (!$date) return '-';
        $c = \Carbon\Carbon::parse($date);
        return $c->format('j') . ' ' . $thMonths[$c->month] . ' ' . ($c->year + 543);
    };
    $fmtThaiDiff = function($date) {
        if (!$date) return '';
        return \Carbon\Carbon::parse($date)->locale('th')->diffForHumans();
    };
@endphp

@section('content')
<div class="row g-4">
    <!-- Top Action Banner -->
    <div class="col-12">
        <div class="glass-card bg-white p-3 p-md-4 rounded-4 border shadow-sm">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('images/aopod-agent-logo.png') }}" alt="AOPOD Agent" style="width: 52px; height: 52px; border-radius: 12px; box-shadow: 0 4px 12px rgba(24, 165, 115, 0.2);">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">ระบบจัดการ AOPOD Agent ประจำโรงพยาบาล</h5>
                        <p class="text-secondary small mb-0">ตรวจสอบสถานะการเชื่อมต่อ ดาวน์โหลดโปรแกรมติดตั้ง ปรับแต่งคำสั่ง SQL ส่วนกลาง และสั่งการดึงข้อมูลย้อนหลัง</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-nowrap flex-shrink-0">
                    <a href="{{ route('manage.agents.download-exe') }}" class="btn btn-primary fw-bold px-3 py-2 text-nowrap" style="border-radius: 10px; background: linear-gradient(135deg, #18a573 0%, #128259 100%); border: none;">
                        <i class="fa-solid fa-download me-1"></i> ดาวน์โหลด Agent.exe
                    </a>
                    <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-2 text-nowrap" onclick="handleRemoteUpdate('all', 'ทุกโรงพยาบาลในจังหวัด')" style="border-radius: 10px; background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border: none;">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> อัปเดต Client ทุก รพ.
                    </button>
                    <button type="button" class="btn btn-outline-primary fw-bold px-3 py-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#remoteSyncModal" data-target-hcode="all" data-target-name="ทุกโรงพยาบาลในจังหวัด" style="border-radius: 10px;">
                        <i class="fa-solid fa-paper-plane me-1"></i> สั่ง Sync ทุก รพ.
                    </button>
                    <button type="button" class="btn btn-light border fw-bold px-3 py-2 text-nowrap" onclick="location.reload();" style="border-radius: 10px;">
                        <i class="fa-solid fa-rotate-right me-1"></i> รีเฟรช
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="col-12">
        <ul class="nav nav-pills custom-agent-tabs p-1.5 rounded-4 border shadow-sm" id="agentTabs" role="tablist">
            <li class="nav-item flex-fill" role="presentation">
                <button class="nav-link active w-100 py-3 fw-bold rounded-3 text-center" id="tab-hospitals-link" data-bs-toggle="pill" data-bs-target="#tab-hospitals" type="button" role="tab" aria-controls="tab-hospitals" aria-selected="true">
                    <i class="fa-solid fa-hospital-user me-2"></i> สถานะ Agent และ รพ. (Hospital Agents)
                    <span class="badge bg-white text-success ms-2 rounded-pill shadow-sm">{{ count(array_filter($agentList, fn($a) => $a['is_online'])) }} / {{ count($agentList) }} ออนไลน์</span>
                </button>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <button class="nav-link w-100 py-3 fw-bold rounded-3 text-center" id="tab-queries-link" data-bs-toggle="pill" data-bs-target="#tab-queries" type="button" role="tab" aria-controls="tab-queries" aria-selected="false">
                    <i class="fa-solid fa-code me-2"></i> คำสั่ง SQL ดึงข้อมูลส่วนกลาง (Remote Query Engine)
                    <span class="badge bg-warning text-dark ms-2 rounded-pill shadow-sm" id="badgeQueriesVersionNav">{{ $queriesVersion }}</span>
                </button>
            </li>
            <li class="nav-item flex-fill" role="presentation">
                <button class="nav-link w-100 py-3 fw-bold rounded-3 text-center" id="tab-schedules-link" data-bs-toggle="pill" data-bs-target="#tab-schedules" type="button" role="tab" aria-controls="tab-schedules" aria-selected="false">
                    <i class="fa-solid fa-clock me-2"></i> ตั้งเวลาส่งข้อมูลอัตโนมัติ (Schedule Policy)
                    <span class="badge bg-primary text-white ms-2 rounded-pill shadow-sm" id="badgeScheduleInterval">
                        ทุก {{ $globalSchedule->interval_hours ?? 1 }} ชม. (นาทีที่ {{ $globalSchedule->start_minute ?? 15 }})
                    </span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="col-12">
        <div class="tab-content" id="agentTabsContent">
            
            <!-- TAB 1: HOSPITAL AGENTS LIST -->
            <div class="tab-pane fade show active" id="tab-hospitals" role="tabpanel" aria-labelledby="tab-hospitals-link">
                <div class="glass-card bg-white p-4 rounded-4 border shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-hospital text-success me-2"></i> รายชื่อโรงพยาบาลและสถานะ Agent ({{ count($agentList) }} แห่ง)
                        </h6>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1.5 rounded-pill">
                            ออนไลน์: {{ count(array_filter($agentList, fn($a) => $a['is_online'])) }} แห่ง
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0 8px;">
                            <thead class="table-light">
                                <tr class="text-secondary small">
                                    <th class="border-0 rounded-start">รหัส / โรงพยาบาล</th>
                                    <th class="border-0 text-center">สถานะ Agent</th>
                                    <th class="border-0">ข้อมูล OPD ล่าสุด</th>
                                    <th class="border-0">ข้อมูล IPD ล่าสุด</th>
                                    <th class="border-0 text-center rounded-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agentList as $item)
                                <tr class="bg-light bg-opacity-50 shadow-sm" style="border-radius: 12px;">
                                    <td class="py-3 px-3">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="p-2 bg-white rounded-3 border text-primary fw-bold" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                                {{ $item['hcode'] }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item['name'] }}</div>
                                                <div class="text-secondary" style="font-size: 0.78rem;">เตียง: {{ $item['hospital']->bed_qty ?? 30 }} เตียง</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Live Status -->
                                    <td class="text-center py-3">
                                        @if($item['is_online'])
                                            <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-pill fw-semibold">
                                                <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> ออนไลน์ (v{{ $item['heartbeat']['version'] ?? '1.0.0' }})
                                            </span>
                                            <div class="text-muted" style="font-size: 0.72rem; margin-top: 2px;">
                                                {{ $item['heartbeat']['hostname'] ?? $item['heartbeat']['ip'] ?? '' }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 rounded-pill fw-semibold">
                                                <i class="fa-solid fa-circle text-muted" style="font-size: 0.5rem;"></i> ยังไม่เชื่อมต่อ
                                            </span>
                                        @endif

                                        @if($item['pending_task'])
                                            <div class="badge bg-warning bg-opacity-10 text-warning px-2 py-0.5 mt-1" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-spinner fa-spin me-1"></i> มีคำสั่งค้าง
                                            </div>
                                        @endif
                                    </td>

                                    <!-- OPD Sync (Thai Date) -->
                                    <td class="py-3">
                                        @if($item['last_opd_date'])
                                            <div class="fw-semibold text-dark">{{ $fmtThaiDate($item['last_opd_date']) }}</div>
                                            <div class="text-secondary" style="font-size: 0.75rem;">ซิงค์: {{ $fmtThaiDiff($item['last_opd_sync']) }}</div>
                                        @else
                                            <span class="text-muted small">- ยังไม่มีข้อมูล -</span>
                                        @endif
                                    </td>

                                    <!-- IPD Sync (Thai Date) -->
                                    <td class="py-3">
                                        @if($item['last_ipd_date'])
                                            <div class="fw-semibold text-dark">{{ $fmtThaiDate($item['last_ipd_date']) }}</div>
                                            <div class="text-secondary" style="font-size: 0.75rem;">ซิงค์: {{ $fmtThaiDiff($item['last_ipd_sync']) }}</div>
                                        @else
                                            <span class="text-muted small">- ยังไม่มีข้อมูล -</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-center py-3 px-3">
                                        <div class="btn-group shadow-sm" style="border-radius: 10px;">
                                            <button type="button" class="btn btn-sm btn-light border text-warning" onclick="handleRemoteUpdate('{{ $item['hcode'] }}', '{{ $item['name'] }}')" title="สั่งให้อัปเดต Client เป็นเวอร์ชั่นล่าสุดจากเซิร์ฟเวอร์">
                                                <i class="fa-solid fa-cloud-arrow-up text-warning"></i> อัปเดต
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border text-success fw-semibold btn-view-token" data-hcode="{{ $item['hcode'] }}" data-name="{{ $item['name'] }}" data-token="{{ $item['token_api'] }}" title="ดูและคัดลอก Token">
                                                <i class="fa-solid fa-key text-success"></i> ดู Token
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CENTRAL REMOTE QUERY ENGINE -->
            <div class="tab-pane fade" id="tab-queries" role="tabpanel" aria-labelledby="tab-queries-link">
                <form id="agentQueriesForm">
                    @csrf
                    <div class="row g-4">
                        
                        <!-- Compact Sub-Toolbar -->
                        <div class="col-12">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-2.5 px-3 bg-white rounded-3 border shadow-xs">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-primary fw-bold py-1.5 px-3 rounded-pill shadow-xs" data-bs-toggle="modal" data-bs-target="#ppIcd10Modal" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); border: none;">
                                        <i class="fa-solid fa-list-check me-1.5"></i> รหัสโรค PP (<code>@{{PP_ICD10_LIST}}</code>)
                                        <span class="badge bg-white text-primary ms-1.5 rounded-pill shadow-xs" id="badgePpCount">{{ count($ppIcd10List) }} รหัส</span>
                                    </button>
                                    <span class="badge bg-light text-secondary border py-1.5 px-2.5 rounded-pill" style="font-size: 0.78rem;" title="รหัส รพ. ประจำเครื่อง สำหรับระบุ In-CUP">
                                        <code>@{{HOSPCODE}}</code> = รหัส รพ. ประจำเครื่อง (In-CUP)
                                    </span>
                                    <span class="badge bg-light text-secondary border py-1.5 px-2.5 rounded-pill" style="font-size: 0.78rem;" title="รหัส รพ. ทั้งหมดในจังหวัด สำหรับแยกในจังหวัด/ต่างอำเภอ และต่างจังหวัด">
                                        <code>@{{PROVINCE_HOSPCODES}}</code> = รหัส รพ. ในจังหวัด
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-bolt me-1"></i> Hot-Reload
                                    </span>
                                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        เวอร์ชัน: <b class="text-dark font-monospace" id="badgeQueriesVersion">{{ $queriesVersion }}</b>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 1. OPD Query Editor -->
                        <div class="col-12">
                            <div class="glass-card bg-white p-4 rounded-4 border shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-stethoscope text-primary me-2"></i> 1. คำสั่ง SQL สรุปผู้ป่วยนอก (OPD Query)
                                        </h6>
                                        <small class="text-secondary">ใช้ดึงข้อมูลสถิติผู้ป่วยนอก รายรับ และการส่งต่อ รายวันจากตาราง <code>ovst</code>, <code>vn_stat</code>, <code>referin</code>, <code>referout</code></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="copyTextarea('query_opd')">
                                        <i class="fa-solid fa-copy me-1"></i> คัดลอก SQL
                                    </button>
                                </div>

                                <div class="sql-editor-container mt-2">
                                    <div class="sql-editor-header text-secondary small">
                                        <span><i class="fa-solid fa-file-code text-primary me-1"></i> query_opd.sql</span>
                                        <span class="text-muted" style="font-size: 0.75rem;">พารามิเตอร์: <code>(startDate, endDate) x 4</code></span>
                                    </div>
                                    <textarea name="query_opd" id="query_opd" class="sql-editor-textarea" rows="14" required>{{ $queries['opd'] }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. IPD Query Editor -->
                        <div class="col-12">
                            <div class="glass-card bg-white p-4 rounded-4 border shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-bed text-success me-2"></i> 2. คำสั่ง SQL สรุปผู้ป่วยใน (IPD Query)
                                        </h6>
                                        <small class="text-secondary">ใช้ดึงข้อมูลสถิติผู้ป่วยใน วันนอน อัตราครองเตียง CMI และรายรับ รายวันจากตาราง <code>ipt</code>, <code>an_stat</code></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="copyTextarea('query_ipd')">
                                        <i class="fa-solid fa-copy me-1"></i> คัดลอก SQL
                                    </button>
                                </div>

                                <div class="sql-editor-container mt-2">
                                    <div class="sql-editor-header text-secondary small">
                                        <span><i class="fa-solid fa-file-code text-success me-1"></i> query_ipd.sql</span>
                                        <span class="text-muted" style="font-size: 0.75rem;">พารามิเตอร์: <code>(bedQty, startDate, endDate)</code></span>
                                    </div>
                                    <textarea name="query_ipd" id="query_ipd" class="sql-editor-textarea" rows="12" required>{{ $queries['ipd'] }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Refer Query Editor -->
                        <div class="col-12 col-lg-6">
                            <div class="glass-card bg-white p-4 rounded-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-truck-medical text-danger me-2"></i> 3. คำสั่ง SQL ส่งต่อผู้ป่วย (Refer Query)
                                            </h6>
                                            <small class="text-secondary">ใช้ดึงยอด Refer In / Refer Out / Refer Back จากตาราง <code>referout</code>, <code>referin</code>, <code>refer_reply</code></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" onclick="copyTextarea('query_refer')">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>

                                    <div class="sql-editor-container mt-2">
                                        <div class="sql-editor-header text-secondary small">
                                            <span><i class="fa-solid fa-file-code text-danger me-1"></i> query_refer.sql</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">พารามิเตอร์: <code>(startDate, endDate) x 2</code></span>
                                        </div>
                                        <textarea name="query_refer" id="query_refer" class="sql-editor-textarea" rows="10" required>{{ $queries['refer'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Operation Query Editor -->
                        <div class="col-12 col-lg-6">
                            <div class="glass-card bg-white p-4 rounded-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-heart-pulse text-purple me-2"></i> 4. คำสั่ง SQL ผ่าตัด (Operation Query)
                                            </h6>
                                            <small class="text-secondary">ใช้ดึงยอดการทำหัตถการผ่าตัดจากตาราง <code>operation_list</code></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" onclick="copyTextarea('query_operation')">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>

                                    <div class="sql-editor-container mt-2">
                                        <div class="sql-editor-header text-secondary small">
                                            <span><i class="fa-solid fa-file-code text-purple me-1"></i> query_operation.sql</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">พารามิเตอร์: <code>(startDate, endDate)</code></span>
                                        </div>
                                        <textarea name="query_operation" id="query_operation" class="sql-editor-textarea" rows="10" required>{{ $queries['operation'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Bed Total Query Editor -->
                        <div class="col-12 col-lg-6">
                            <div class="glass-card bg-white p-4 rounded-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-chart-pie text-info me-2"></i> 5. คำสั่งเตียงรวม รพ. (Bed Total)
                                            </h6>
                                            <small class="text-secondary">ดึงจำนวนเตียงที่เปิดใช้และเตียงครอง Real-time</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" onclick="copyTextarea('query_bed_total')">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>

                                    <div class="sql-editor-container mt-2">
                                        <div class="sql-editor-header text-secondary small">
                                            <span><i class="fa-solid fa-file-code text-info me-1"></i> query_bed_total.sql</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">พารามิเตอร์: <code>(bedQty)</code></span>
                                        </div>
                                        <textarea name="query_bed_total" id="query_bed_total" class="sql-editor-textarea" rows="9" required>{{ $queries['bed_total'] }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Bed Department Query Editor -->
                        <div class="col-12 col-lg-6">
                            <div class="glass-card bg-white p-4 rounded-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-building-user text-warning me-2"></i> 6. คำสั่งเตียงแยกแผนก (Bed Department)
                                            </h6>
                                            <small class="text-secondary">ดึงเตียงและผู้ป่วยครองแยกตาม <code>export_code</code></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" onclick="copyTextarea('query_bed_dep')">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>

                                    <div class="sql-editor-container mt-2">
                                        <div class="sql-editor-header text-secondary small">
                                            <span><i class="fa-solid fa-file-code text-warning me-1"></i> query_bed_dep.sql</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">พารามิเตอร์: <code>none</code></span>
                                        </div>
                                        <textarea name="query_bed_dep" id="query_bed_dep" class="sql-editor-textarea" rows="9" required>{{ $queries['bed_dep'] }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Action Bar -->
                        <div class="col-12">
                            <div class="glass-card bg-white p-4 rounded-4 border shadow-sm">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-danger px-4 py-2.5 fw-bold" id="btnResetQueries" style="border-radius: 12px;">
                                            <i class="fa-solid fa-rotate-left me-1"></i> คืนค่าคำสั่ง SQL มาตรฐาน
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="submit" class="btn btn-primary px-5 py-2.5 fw-bold" id="btnSaveQueries" style="border-radius: 12px; background: linear-gradient(135deg, #18a573 0%, #128259 100%); border: none; box-shadow: 0 4px 15px rgba(24, 165, 115, 0.35);">
                                            <i class="fa-solid fa-floppy-disk me-2"></i> บันทึกและกระจายคำสั่ง SQL ไปยัง Agent ทุก รพ.
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <!-- TAB 3: AGENT SCHEDULE POLICY -->
            <div class="tab-pane fade" id="tab-schedules" role="tabpanel" aria-labelledby="tab-schedules-link">
                <div class="row justify-content-center">
                    <div class="col-12 col-xl-10">
                        <div class="glass-card bg-white p-4 p-md-5 rounded-4 border shadow-sm">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <div class="p-3 rounded-3" style="background: rgba(14, 165, 233, 0.1); color: #0284c7; font-size: 1.35rem;">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">ตั้งเวลาส่งข้อมูลอัตโนมัติ (Schedule Policy)</h5>
                                    <p class="text-secondary small mb-0">กำหนดรอบเวลาที่ AOPOD Agent แต่ละโรงพยาบาลจะส่งข้อมูลเข้าสู่ระบบโดยอัตโนมัติ</p>
                                </div>
                            </div>

                            <form id="formGlobalSchedule">
                                @csrf
                                <input type="hidden" name="hospcode" value="ALL">

                                <!-- หมวดที่ 1: รอบส่งข้อมูล (รายชั่วโมง) -->
                                <div class="p-4 rounded-4 border mb-4" style="background: #f8fafc;">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="badge bg-primary px-3 py-1.5 rounded-pill fs-6 fw-bold">หมวดที่ 1</span>
                                        <h6 class="fw-bold text-dark mb-0 fs-5">รอบส่งข้อมูล (รายชั่วโมง)</h6>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold text-dark small">ความถี่การทำงาน:</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fa-solid fa-hourglass-half text-primary"></i></span>
                                                <select class="form-select fw-bold" name="interval_hours" id="selectIntervalHours">
                                                    @foreach([1, 2, 3, 4, 6, 8, 12] as $h)
                                                    <option value="{{ $h }}" {{ ($globalSchedule->interval_hours ?? 1) == $h ? 'selected' : '' }}>ทำงานทุกๆ {{ $h }} ชั่วโมง</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold text-dark small">เวลาเริ่มทำงาน:</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fa-solid fa-clock text-primary"></i></span>
                                                <span class="input-group-text bg-light text-secondary">เริ่มที่นาทีที่</span>
                                                <input type="number" class="form-control text-center fw-bold" name="start_minute" min="0" max="59" value="{{ $globalSchedule->start_minute ?? 15 }}" style="max-width: 80px;">
                                                <span class="input-group-text bg-light text-secondary">น.</span>
                                            </div>
                                            <div class="form-text text-muted small mt-1">
                                                ตัวอย่าง: เริ่มนาทีที่ 15 รอบถัดไปจะเป็น 01:15, 02:15, 03:15 ...
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub-items under Category 1 -->
                                    <div class="p-3 bg-white rounded-3 border d-flex flex-column gap-3">
                                        <!-- 1. OPD / Refer / Operation -->
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                                <label class="form-label fw-bold text-dark mb-0">
                                                    <i class="fa-solid fa-stethoscope text-primary me-1.5"></i> 1. ข้อมูลผู้ป่วยนอก (OPD / Refer / ผ่าตัด)
                                                </label>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputOpdDaysBack').val(3)">3 วัน</button>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputOpdDaysBack').val(5)">5 วัน</button>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputOpdDaysBack').val(7)">7 วัน</button>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputOpdDaysBack').val(10)">10 วัน</button>
                                                </div>
                                            </div>
                                            <div class="input-group" style="max-width: 240px;">
                                                <span class="input-group-text bg-light">ดึงย้อนหลัง</span>
                                                <input type="number" class="form-control fw-bold text-center" name="opd_days_back" id="inputOpdDaysBack" min="1" max="365" value="{{ $globalSchedule->opd_days_back ?? 5 }}">
                                                <span class="input-group-text bg-light">วัน</span>
                                            </div>
                                        </div>

                                        <hr class="my-1 border-light">

                                        <!-- 2. IPD -->
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                                <label class="form-label fw-bold text-dark mb-0">
                                                    <i class="fa-solid fa-bed-pulse text-info me-1.5"></i> 2. ข้อมูลผู้ป่วยใน (IPD - รอสรุปชาร์จ & CMI)
                                                </label>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputIpdDaysBack').val(15)">15 วัน</button>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputIpdDaysBack').val(30)">30 วัน</button>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputIpdDaysBack').val(45)">45 วัน</button>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 0.75rem;" onclick="$('#inputIpdDaysBack').val(60)">60 วัน</button>
                                                </div>
                                            </div>
                                            <div class="input-group" style="max-width: 240px;">
                                                <span class="input-group-text bg-light">ดึงย้อนหลัง</span>
                                                <input type="number" class="form-control fw-bold text-center" name="ipd_days_back" id="inputIpdDaysBack" min="1" max="365" value="{{ $globalSchedule->ipd_days_back ?? 30 }}">
                                                <span class="input-group-text bg-light">วัน</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- หมวดที่ 2: รอบส่งข้อมูล (รายนาที) -->
                                <div class="p-4 rounded-4 border mb-4" style="background: #f8fafc;">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="badge bg-success px-3 py-1.5 rounded-pill fs-6 fw-bold">หมวดที่ 2</span>
                                        <h6 class="fw-bold text-dark mb-0 fs-5">รอบส่งข้อมูล (รายนาที)</h6>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold text-dark small">ความถี่การทำงาน:</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fa-solid fa-stopwatch text-success"></i></span>
                                                <select class="form-select fw-bold" name="bed_interval_mins" id="selectBedIntervalMins">
                                                    @foreach([5, 10, 15, 20, 30, 60] as $m)
                                                    <option value="{{ $m }}" {{ ($globalSchedule->bed_interval_mins ?? 15) == $m ? 'selected' : '' }}>ทำงานทุกๆ {{ $m }} นาที</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 d-flex align-items-center">
                                            <div class="p-2.5 px-3 bg-white rounded-3 border w-100">
                                                <div class="fw-bold text-dark small">
                                                    <i class="fa-solid fa-bed text-success me-1.5"></i> 1. ข้อมูลสถานะเตียง (Bed Snapshot Real-time)
                                                </div>
                                                <span class="text-muted small">ดึงข้อมูลการครองเตียงปัจจุบันแยกแผนก</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- สวิตช์: เปิด / ปิด (Master Switch) -->
                                <div class="mb-4 p-3.5 bg-light rounded-4 d-flex align-items-center justify-content-between border">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2.5 bg-white rounded-circle shadow-xs text-primary fs-5">
                                            <i class="fa-solid fa-power-off"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">เปิดใช้งานการส่งข้อมูลอัตโนมัติ (Master Active Switch)</div>
                                            <div class="text-secondary small">ควบคุมการเปิดหรือปิดการทำงานของระบบส่งข้อมูลอัตโนมัติทั้งหมดร่วมกัน</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-3 mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ ($globalSchedule->is_active ?? true) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4 py-2.5 fw-bold rounded-3 shadow-sm" id="btnSaveSchedule" style="background: linear-gradient(135deg, #18a573 0%, #128259 100%); border: none;">
                                        <i class="fa-solid fa-floppy-disk me-1.5"></i> บันทึกรอบเวลาส่งข้อมูลอัตโนมัติ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal สั่ง Remote Sync -->
<div class="modal fade" id="remoteSyncModal" tabindex="-1" aria-labelledby="remoteSyncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold text-dark" id="remoteSyncModalLabel">
                    <i class="fa-solid fa-paper-plane text-success me-2"></i> สั่งดึงข้อมูลย้อนหลังจากส่วนกลาง
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="remoteSyncForm">
                @csrf
                <div class="modal-body px-4 py-3">
                    
                    <!-- 1. เลือกโรงพยาบาลเป้าหมาย (Target Hospitals) Dropdown -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1.5" for="modalTargetHospcode">
                            <i class="fa-solid fa-hospital text-success me-1"></i> เลือกโรงพยาบาลเป้าหมาย (Target Hospitals):
                        </label>
                        <select class="form-select bg-white shadow-xs py-2" name="target" id="modalTargetHospcode" style="border-radius: 10px; font-weight: 500;">
                            <option value="all" selected>🏢 ทุกโรงพยาบาลในจังหวัดอำนาจเจริญ (7 แห่ง)</option>
                            @foreach($agentList as $item)
                                @if($item['hcode'] !== '00025' && !str_contains($item['name'], 'สาธารณสุข'))
                                <option value="{{ $item['hcode'] }}">
                                    {{ $item['hcode'] }} - {{ $item['name'] }} {{ $item['is_online'] ? '(ออนไลน์)' : '(ยังไม่เชื่อมต่อ)' }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. เลือกประเภทข้อมูลที่ต้องการดึง (1 คิวรี่ต่อ 1 แถว) -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-bold text-dark small mb-0">
                                <i class="fa-solid fa-layer-group text-primary me-1"></i> เลือกประเภทข้อมูลที่ต้องการดึง (กำหนดช่วงวันที่แยกรายหมวด):
                            </label>
                            <button type="button" class="btn btn-xs btn-link text-decoration-none p-0 fw-bold" id="btnToggleAllModules" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-check-double me-1"></i> เลือกทุกหมวด / ยกเลิก
                            </button>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <!-- 1. สถานะเตียง (Bed Snapshot Real-time - NO DATE RANGE) -->
                            <div class="p-2.5 px-3 rounded-3 border bg-white shadow-xs module-card" id="card_bed" style="transition: all 0.2s;">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-5">
                                        <label class="form-check d-flex align-items-center gap-2 m-0 cursor-pointer user-select-none">
                                            <input class="form-check-input mt-0 module-checkbox" type="checkbox" name="modules[]" value="bed" id="modBed" data-mod="bed" checked>
                                            <span class="fw-bold text-dark small">
                                                <i class="fa-solid fa-bed text-success me-1.5"></i> สถานะเตียง (Bed Real-time)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <div class="p-1 px-2.5 rounded-2 bg-success bg-opacity-10 text-success border border-success-subtle d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-bolt text-success"></i>
                                            <span>ดึงสถานะเตียงปัจจุบันแบบ Real-time (ไม่ต้องมีช่วงวันที่)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. ข้อมูลผู้ป่วยใน (IPD) -->
                            <div class="p-2.5 px-3 rounded-3 border bg-white shadow-xs module-card" id="card_ipd" style="transition: all 0.2s;">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-5">
                                        <label class="form-check d-flex align-items-center gap-2 m-0 cursor-pointer user-select-none">
                                            <input class="form-check-input mt-0 module-checkbox" type="checkbox" name="modules[]" value="ipd" id="modIpd" data-mod="ipd" checked>
                                            <span class="fw-bold text-dark small">
                                                <i class="fa-solid fa-bed-pulse text-info me-1.5"></i> ข้อมูลผู้ป่วยใน (IPD)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <div class="d-flex align-items-center gap-1.5 date-range-group" id="date_group_ipd">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[ipd][start_date]" id="ipd_start_date" class="form-control form-control-sm bg-white border-start-0" placeholder="เริ่มต้น" style="font-size: 0.8rem;">
                                            </div>
                                            <span class="text-secondary small px-1 flex-shrink-0">ถึง</span>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[ipd][end_date]" id="ipd_end_date" class="form-control form-control-sm bg-white border-start-0" placeholder="สิ้นสุด" style="font-size: 0.8rem;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. ข้อมูลผู้ป่วยนอก (OPD) -->
                            <div class="p-2.5 px-3 rounded-3 border bg-white shadow-xs module-card" id="card_opd" style="transition: all 0.2s;">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-5">
                                        <label class="form-check d-flex align-items-center gap-2 m-0 cursor-pointer user-select-none">
                                            <input class="form-check-input mt-0 module-checkbox" type="checkbox" name="modules[]" value="opd" id="modOpd" data-mod="opd" checked>
                                            <span class="fw-bold text-dark small">
                                                <i class="fa-solid fa-stethoscope text-primary me-1.5"></i> ข้อมูลผู้ป่วยนอก (OPD)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <div class="d-flex align-items-center gap-1.5 date-range-group" id="date_group_opd">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[opd][start_date]" id="opd_start_date" class="form-control form-control-sm bg-white border-start-0" placeholder="เริ่มต้น" style="font-size: 0.8rem;">
                                            </div>
                                            <span class="text-secondary small px-1 flex-shrink-0">ถึง</span>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[opd][end_date]" id="opd_end_date" class="form-control form-control-sm bg-white border-start-0" placeholder="สิ้นสุด" style="font-size: 0.8rem;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. ข้อมูลส่งต่อ (Refer) -->
                            <div class="p-2.5 px-3 rounded-3 border bg-white shadow-xs module-card" id="card_refer" style="transition: all 0.2s;">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-5">
                                        <label class="form-check d-flex align-items-center gap-2 m-0 cursor-pointer user-select-none">
                                            <input class="form-check-input mt-0 module-checkbox" type="checkbox" name="modules[]" value="refer" id="modRefer" data-mod="refer" checked>
                                            <span class="fw-bold text-dark small">
                                                <i class="fa-solid fa-truck-medical text-warning me-1.5"></i> ข้อมูลส่งต่อ (Refer)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <div class="d-flex align-items-center gap-1.5 date-range-group" id="date_group_refer">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[refer][start_date]" id="refer_start_date" class="form-control form-control-sm bg-white border-start-0" placeholder="เริ่มต้น" style="font-size: 0.8rem;">
                                            </div>
                                            <span class="text-secondary small px-1 flex-shrink-0">ถึง</span>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[refer][end_date]" id="refer_end_date" class="form-control form-control-sm bg-white border-start-0" placeholder="สิ้นสุด" style="font-size: 0.8rem;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. ข้อมูลผ่าตัด (Operation) -->
                            <div class="p-2.5 px-3 rounded-3 border bg-white shadow-xs module-card" id="card_operation" style="transition: all 0.2s;">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-5">
                                        <label class="form-check d-flex align-items-center gap-2 m-0 cursor-pointer user-select-none">
                                            <input class="form-check-input mt-0 module-checkbox" type="checkbox" name="modules[]" value="operation" id="modOperation" data-mod="operation" checked>
                                            <span class="fw-bold text-dark small">
                                                <i class="fa-solid fa-syringe text-danger me-1.5"></i> ข้อมูลผ่าตัด (Operation)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <div class="d-flex align-items-center gap-1.5 date-range-group" id="date_group_operation">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[operation][start_date]" id="operation_start_date" class="form-control form-control-sm bg-white border-start-0" placeholder="เริ่มต้น" style="font-size: 0.8rem;">
                                            </div>
                                            <span class="text-secondary small px-1 flex-shrink-0">ถึง</span>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-secondary border-end-0 py-1 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-day"></i></span>
                                                <input type="text" name="ranges[operation][end_date]" id="operation_end_date" class="form-control form-control-sm bg-white border-start-0" placeholder="สิ้นสุด" style="font-size: 0.8rem;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Date Range Presets -->
                        <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2 pt-2 border-top">
                            <span class="text-secondary small fw-semibold">
                                <i class="fa-solid fa-wand-magic-sparkles text-primary me-1"></i> ปรับช่วงวันที่ทุกแถวพร้อมกัน:
                            </span>
                            <div class="d-flex gap-1.5 flex-wrap">
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 btn-quick-all" data-days="5" style="font-size: 0.75rem;">ย้อนหลัง 5 วัน</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 btn-quick-all" data-days="7" style="font-size: 0.75rem;">ย้อนหลัง 7 วัน</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 btn-quick-all" data-days="15" style="font-size: 0.75rem;">ย้อนหลัง 15 วัน</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 btn-quick-all" data-days="30" style="font-size: 0.75rem;">ย้อนหลัง 30 วัน</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 btn-quick-all" data-days="month" style="font-size: 0.75rem;">เดือนนี้</button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" id="btnSubmitRemoteSync">
                        <i class="fa-solid fa-paper-plane me-1"></i> ส่งคำสั่งไปยัง Agent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ดูและคัดลอก API Token -->
<div class="modal fade" id="viewTokenModal" tabindex="-1" aria-labelledby="viewTokenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3 fs-5" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="viewTokenModalLabel">
                        API Bearer Token ประจำโรงพยาบาล
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="text-secondary small">โรงพยาบาล:</div>
                    <div class="fw-bold text-dark fs-6" id="tokenModalHospName">-</div>
                    <div class="text-muted small" id="tokenModalHospCode">รหัส: -</div>
                </div>

                <!-- 1. AOPOD Server URL -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">
                        <i class="fa-solid fa-server text-primary me-1"></i> AOPOD Server URL (คัดลอกไปใส่ในช่อง AOPOD Server URL)
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary font-monospace bg-white" id="tokenModalServerUrl" value="{{ url('/') }}" readonly style="border-radius: 10px 0 0 10px; font-size: 0.88rem; font-weight: 600;">
                        <button class="btn btn-outline-primary px-3 fw-bold" type="button" id="btnCopyServerUrl" style="border-radius: 0 10px 10px 0;">
                            <i class="fa-solid fa-copy me-1"></i> คัดลอก
                        </button>
                    </div>
                    <div class="form-text text-success d-none" id="copyUrlSuccessText">
                        <i class="fa-solid fa-circle-check me-1"></i> คัดลอก Server URL ลงคลิปบอร์ดเรียบร้อยแล้ว!
                    </div>
                </div>

                <!-- 2. AOPOD API Token -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">
                        <i class="fa-solid fa-key text-success me-1"></i> AOPOD API Token (คัดลอกไปใส่ในช่อง AOPOD API Bearer Token)
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control text-success font-monospace bg-white" id="tokenModalValue" readonly style="border-radius: 10px 0 0 10px; font-size: 0.88rem; font-weight: 600;">
                        <button class="btn btn-success px-3 fw-bold" type="button" id="btnCopyToken" style="border-radius: 0 10px 10px 0;">
                            <i class="fa-solid fa-copy me-1"></i> คัดลอก
                        </button>
                    </div>
                    <div class="form-text text-success d-none" id="copySuccessText">
                        <i class="fa-solid fa-circle-check me-1"></i> คัดลอก Token ลงคลิปบอร์ดเรียบร้อยแล้ว!
                    </div>
                </div>

                <div class="p-3 bg-light bg-opacity-75 rounded-3 border small text-secondary">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i> <strong>วิธีเชื่อมต่อ AOPOD Agent:</strong>
                    <ol class="mb-0 ps-3 mt-1" style="line-height: 1.6;">
                        <li>คัดลอก <strong>Server URL</strong> และ <strong>API Token</strong> ข้างต้น</li>
                        <li>เปิดโปรแกรม <strong>AOPOD Agent</strong> ที่เครื่อง รพ. นำไปวางในแท็บ <strong>"ตั้งค่าการเชื่อมต่อ"</strong> หมวดที่ 1 แล้วกดบันทึก</li>
                    </ol>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-end">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal จัดการรายการรหัสโรค PP (ICD-10) -->
<div class="modal fade" id="ppIcd10Modal" tabindex="-1" aria-labelledby="ppIcd10ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 fs-5" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="ppIcd10ModalLabel">
                            จัดการรายการรหัสโรคกลุ่มส่งเสริมสุขภาพ (PP ICD-10 List)
                        </h5>
                        <small class="text-secondary">กำหนดรหัสโรคที่ใช้ในตัวแปร <code>@{{PP_ICD10_LIST}}</code> สำหรับคำสั่ง SQL ผู้ป่วยนอก</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ppIcd10Form">
                @csrf
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="text-secondary small">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i> คั่นแต่ละรหัสด้วยเครื่องหมายจุลภาค (<code>,</code>) หรือขึ้นบรรทัดใหม่
                            </span>
                            <span class="badge bg-primary rounded-pill px-2.5 py-1" id="modalPpCountLive">
                                รวม <span id="ppLiveCountNum">{{ count($ppIcd10List) }}</span> รหัส
                            </span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary small">รายการรหัสโรค ICD-10 (ตัวพิมพ์ใหญ่):</label>
                        <textarea name="codes" id="ppIcd10Textarea" class="form-control font-monospace bg-white" rows="10" required style="border-radius: 12px; font-size: 0.88rem; line-height: 1.6;">{{ implode(', ', $ppIcd10List) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="btnResetPpIcd10">
                        <i class="fa-solid fa-rotate-left me-1"></i> คืนค่ามาตรฐานระบบ
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnSavePpIcd10">
                            <i class="fa-solid fa-floppy-disk me-1"></i> บันทึกรายการรหัสโรค
                        </button>
                    </div>
                </div>
            </form>
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
    let currentModalHcode = '';
    let currentModalHospName = '';


    // 2. Initialize Thai Buddhist Date Pickers for Each Module
    const moduleDatePickers = {};

    function initModuleDateRange(mod, defaultDaysAgo) {
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - defaultDaysAgo);

        const sPicker = initThaiDatePicker(`#${mod}_start_date`, { defaultDate: start });
        const ePicker = initThaiDatePicker(`#${mod}_end_date`, { defaultDate: end });

        moduleDatePickers[mod] = { start: sPicker, end: ePicker };
    }

    // OPD (7 วัน), IPD (30 วัน), Refer (7 วัน), Operation (7 วัน)
    initModuleDateRange('opd', 7);
    initModuleDateRange('ipd', 30);
    initModuleDateRange('refer', 7);
    initModuleDateRange('operation', 7);

    // 3. Module Checkbox Toggle & Visual State
    function updateModuleCardState($cb) {
        const mod = $cb.data('mod');
        const isChecked = $cb.is(':checked');
        const $card = $(`#card_${mod}`);

        if (isChecked) {
            $card.css('opacity', '1').removeClass('bg-light').addClass('bg-white');
            $card.find('input[type="text"]').prop('disabled', false);
        } else {
            $card.css('opacity', '0.45').removeClass('bg-white').addClass('bg-light');
            $card.find('input[type="text"]').prop('disabled', true);
        }
    }

    $('.module-checkbox').on('change', function() {
        updateModuleCardState($(this));
    });

    // Toggle All Modules
    $('#btnToggleAllModules').on('click', function(e) {
        e.preventDefault();
        const anyUnchecked = $('.module-checkbox:not(:checked)').length > 0;
        $('.module-checkbox').prop('checked', anyUnchecked).each(function() {
            updateModuleCardState($(this));
        });
    });

    // 4. Quick Date Range Presets for All Modules
    $('.btn-quick-all').on('click', function() {
        const type = $(this).data('days');
        const end = new Date();
        let start = new Date();

        if (type === 'month') {
            start = new Date(end.getFullYear(), end.getMonth(), 1);
        } else {
            const days = parseInt(type, 10);
            start.setDate(end.getDate() - days);
        }

        ['opd', 'ipd', 'refer', 'operation'].forEach(mod => {
            if (moduleDatePickers[mod]) {
                if (moduleDatePickers[mod].start) moduleDatePickers[mod].start.setDate(start, true);
                if (moduleDatePickers[mod].end) moduleDatePickers[mod].end.setDate(end, true);
            }
        });
    });

    // Submit Remote Sync
    $('#remoteSyncForm').on('submit', function(e) {
        e.preventDefault();

        const target = $('#modalTargetHospcode').val();
        if (!target) {
            Swal.fire({
                title: 'แจ้งเตือน',
                text: 'กรุณาเลือกโรงพยาบาลเป้าหมาย',
                icon: 'warning',
                confirmButtonColor: '#18a573'
            });
            return;
        }

        if ($('.module-checkbox:checked').length === 0) {
            Swal.fire({
                title: 'แจ้งเตือน',
                text: 'กรุณาเลือกประเภทข้อมูลที่ต้องการดึงอย่างน้อย 1 รายการ',
                icon: 'warning',
                confirmButtonColor: '#18a573'
            });
            return;
        }

        const btn = $('#btnSubmitRemoteSync');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> กำลังส่งคำสั่ง...');

        $.ajax({
            url: "{{ route('manage.agents.remote-sync') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#remoteSyncModal').modal('hide');
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: res.message,
                    icon: 'success',
                    confirmButtonColor: '#18a573',
                    confirmButtonText: 'ตกลง'
                }).then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: xhr.responseJSON?.message || 'ไม่สามารถส่งคำสั่งได้',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> ส่งคำสั่งไปยัง Agent');
            }
        });
    });

    // View Token Modal Open
    $(document).on('click', '.btn-view-token', function() {
        currentModalHcode = $(this).data('hcode');
        currentModalHospName = $(this).data('name');
        let token = $(this).data('token') || '';

        // Dynamically detect server base URL from browser address bar
        const detectedBase = window.location.origin + (window.location.pathname.split('/manage')[0] || '');
        if (detectedBase) {
            $('#tokenModalServerUrl').val(detectedBase);
        }

        $('#tokenModalHospName').text(currentModalHospName);
        $('#tokenModalHospCode').text(`รหัสสถานพยาบาล: ${currentModalHcode}`);
        $('#tokenModalValue').val(token);

        $('#copyUrlSuccessText').addClass('d-none');
        $('#btnCopyServerUrl').html('<i class="fa-solid fa-copy me-1"></i> คัดลอก').removeClass('btn-primary text-white').addClass('btn-outline-primary');
        $('#copySuccessText').addClass('d-none');
        $('#btnCopyToken').html('<i class="fa-solid fa-copy me-1"></i> คัดลอก');

        if (!token) {
            // Auto generate if empty
            generateToken(currentModalHcode, currentModalHospName);
            return;
        }

        $('#viewTokenModal').modal('show');
    });

    // Copy Server URL Button
    $('#btnCopyServerUrl').on('click', function() {
        const urlInput = document.getElementById('tokenModalServerUrl');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(urlInput.value).then(function() {
            $('#copyUrlSuccessText').removeClass('d-none');
            $('#btnCopyServerUrl').html('<i class="fa-solid fa-check me-1"></i> คัดลอกแล้ว!').removeClass('btn-outline-primary').addClass('btn-primary text-white');
            setTimeout(() => {
                $('#btnCopyServerUrl').html('<i class="fa-solid fa-copy me-1"></i> คัดลอก').removeClass('btn-primary text-white').addClass('btn-outline-primary');
            }, 3000);
        });
    });

    // Copy Token Button
    $('#btnCopyToken').on('click', function() {
        const tokenInput = document.getElementById('tokenModalValue');
        tokenInput.select();
        tokenInput.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(tokenInput.value).then(function() {
            $('#copySuccessText').removeClass('d-none');
            $('#btnCopyToken').html('<i class="fa-solid fa-check me-1"></i> คัดลอกแล้ว!');
            setTimeout(() => {
                $('#btnCopyToken').html('<i class="fa-solid fa-copy me-1"></i> คัดลอก');
            }, 3000);
        });
    });

    function generateToken(hcode, name) {
        $.ajax({
            url: `/manage/agents/${hcode}/token`,
            method: 'POST',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                $('#tokenModalValue').val(res.token);
                $(`.btn-view-token[data-hcode="${hcode}"]`).data('token', res.token);
                $('#viewTokenModal').modal('show');
                Swal.fire({
                    title: 'สร้าง Token สำเร็จ!',
                    text: `Token ใหม่ของ ${name} พร้อมใช้งานแล้ว`,
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false
                });
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถสร้าง Token ได้', 'error');
            }
        });
    }

    // Tag click listener
    $(document).on('click', '.btn-copy-tag', function() {
        const tag = $(this).data('tag');
        if (tag) {
            navigator.clipboard.writeText(tag).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `คัดลอก ${tag} แล้ว`,
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    });

    // Save Dynamic SQL Queries
    $('#agentQueriesForm').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'ยืนยันบันทึกและกระจายคำสั่ง SQL?',
            html: 'คำสั่ง SQL ชุดนี้จะถูกส่งไปยัง Agent ทุกโรงพยาบาลในจังหวัดทันที โดย Agent จะ Hot-Reload ใช้งานอัตโนมัติภายใน 1 นาที',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-floppy-disk me-1"></i> ยืนยันบันทึก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#18a573'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#btnSaveQueries');
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังบันทึก...');

                $.ajax({
                    url: "{{ route('manage.agents.update-queries') }}",
                    method: 'POST',
                    data: $('#agentQueriesForm').serialize(),
                    success: function(res) {
                        $('#badgeQueriesVersion').text(res.version);
                        $('#badgeQueriesVersionNav').text(res.version);
                        Swal.fire({
                            title: 'บันทึกสำเร็จ!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#18a573'
                        });
                    },
                    error: function(xhr) {
                        let errMsg = xhr.responseJSON?.message || 'ไม่สามารถบันทึกคำสั่ง SQL ได้';
                        Swal.fire('เกิดข้อผิดพลาด', errMsg, 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-2"></i> บันทึกและกระจายคำสั่ง SQL ไปยัง Agent ทุก รพ.');
                    }
                });
            }
        });
    });

    // Reset Dynamic SQL Queries to default
    $('#btnResetQueries').on('click', function() {
        Swal.fire({
            title: 'คืนค่าคำสั่ง SQL มาตรฐาน?',
            text: 'คำสั่ง SQL ทั้ง 4 รายการจะถูกรีเซ็ตกลับเป็นค่ามาตรฐานเริ่มต้นที่สร้างมาพร้อมระบบ',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-rotate-left me-1"></i> ยืนยันคืนค่ามาตรฐาน',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('manage.agents.reset-queries') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.queries) {
                            $('#query_opd').val(res.queries.opd);
                            $('#query_ipd').val(res.queries.ipd);
                            $('#query_refer').val(res.queries.refer);
                            $('#query_operation').val(res.queries.operation);
                            $('#query_bed_total').val(res.queries.bed_total);
                            $('#query_bed_dep').val(res.queries.bed_dep);
                        }
                        $('#badgeQueriesVersion').text(res.version);
                        $('#badgeQueriesVersionNav').text(res.version);
                        Swal.fire({
                            title: 'คืนค่าสำเร็จ!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#18a573'
                        });
                    },
                    error: function(xhr) {
                        let errMsg = xhr.responseJSON?.message || 'ไม่สามารถคืนค่าได้';
                        Swal.fire('เกิดข้อผิดพลาด', errMsg, 'error');
                    }
                });
            }
        });
    });

    // Live update count in PP ICD-10 modal textarea
    function updatePpCountDisplay() {
        const raw = $('#ppIcd10Textarea').val() || '';
        const tokens = raw.split(/[\r\n,;]+/).filter(t => t.trim().length > 0);
        const unique = [...new Set(tokens.map(t => t.trim().toUpperCase()))];
        $('#ppLiveCountNum').text(unique.length);
    }
    $('#ppIcd10Textarea').on('input', updatePpCountDisplay);

    // Save PP ICD-10 List
    $('#ppIcd10Form').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnSavePpIcd10');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังบันทึก...');

        $.ajax({
            url: "{{ route('manage.agents.update-icd10') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#badgePpCount').text(res.count + ' รหัส');
                $('#ppLiveCountNum').text(res.count);
                if (res.codes) {
                    $('#ppIcd10Textarea').val(res.codes.join(', '));
                }
                $('#ppIcd10Modal').modal('hide');
                Swal.fire({
                    title: 'บันทึกสำเร็จ!',
                    text: res.message,
                    icon: 'success',
                    confirmButtonColor: '#18a573'
                });
            },
            error: function(xhr) {
                let errMsg = xhr.responseJSON?.message || 'ไม่สามารถบันทึกรหัสโรคได้';
                Swal.fire('เกิดข้อผิดพลาด', errMsg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึกรายการรหัสโรค');
            }
        });
    });

    // Reset PP ICD-10 List
    $('#btnResetPpIcd10').on('click', function() {
        Swal.fire({
            title: 'คืนค่ารหัสโรคมาตรฐาน?',
            text: 'ระบบจะรีเซ็ตรายการรหัสโรคกลุ่ม PP กลับเป็นค่ามาตรฐานเริ่มต้น',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันคืนค่า',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('manage.agents.reset-icd10') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.codes) {
                            $('#ppIcd10Textarea').val(res.codes.join(', '));
                        }
                        $('#badgePpCount').text(res.count + ' รหัส');
                        $('#ppLiveCountNum').text(res.count);
                        Swal.fire({
                            title: 'คืนค่าสำเร็จ!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#18a573'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('เกิดข้อผิดพลาด', xhr.responseJSON?.message || 'ไม่สามารถคืนค่าได้', 'error');
                    }
                });
            }
        });
    });
});

// Helper: Copy Textarea
function copyTextarea(id) {
    const el = document.getElementById(id);
    if (!el) return;
    navigator.clipboard.writeText(el.value).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'คัดลอก SQL แล้ว',
            showConfirmButton: false,
            timer: 1500
        });
    });
}

// Remote Auto-Update Trigger
function handleRemoteUpdate(targetHcode, targetName) {
    Swal.fire({
        title: 'ยืนยันการส่งคำสั่งอัปเดต?',
        html: `คุณต้องการสั่งให้ <b>${targetName}</b> ทำการอัปเดตตัวโปรแกรม <code>AOPOD-Agent.exe</code> เป็นเวอร์ชั่นล่าสุดจากเซิร์ฟเวอร์หรือไม่?<br><br><small class="text-muted"><i class="fa-solid fa-circle-info"></i> Agent ที่ออนไลน์อยู่จะดาวน์โหลดไฟล์และรีสตาร์ทตัวเองอัตโนมัติภายใน 1 นาที โดยค่าคอนฟิกเดิมไม่หาย</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-cloud-arrow-up me-1"></i> ยืนยันส่งคำสั่งอัปเดต',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#ffc107',
        customClass: {
            confirmButton: 'text-dark fw-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลังส่งคำสั่ง...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('manage.agents.remote-update') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    target: targetHcode
                },
                success: function(res) {
                    Swal.fire({
                        title: 'ส่งคำสั่งสำเร็จ!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonColor: '#18a573'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errMsg = xhr.responseJSON?.message || 'ไม่สามารถส่งคำสั่งอัปเดตได้';
                    Swal.fire('เกิดข้อผิดพลาด', errMsg, 'error');
                }
            });
        }
    });
}


// Submit Global Schedule
$('#formGlobalSchedule').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSaveSchedule');
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1.5"></i> กำลังบันทึก...');

    $.ajax({
        url: "{{ route('manage.agents.update-schedule') }}",
        method: "POST",
        data: $(this).serialize(),
        success: function(res) {
            Swal.fire({
                title: 'บันทึกสำเร็จ!',
                text: res.message,
                icon: 'success',
                confirmButtonColor: '#18a573'
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1.5"></i> บันทึกรอบเวลาส่งข้อมูลอัตโนมัติ');
            let errMsg = xhr.responseJSON?.message || 'ไม่สามารถบันทึกรอบเวลาได้';
            Swal.fire('เกิดข้อผิดพลาด', errMsg, 'error');
        }
    });
});
</script>
@endpush
