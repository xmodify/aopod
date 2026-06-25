@extends('layouts.admin')

@section('title', 'ตั้งค่าระบบ - AOPOD')
@section('header_title', 'ตั้งค่าระบบ (System Settings)')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            {{-- Tab Navigation --}}
            <ul class="nav nav-pills mb-4 pb-2 border-bottom" id="settingsTab" role="tablist" style="gap: 10px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4 py-2.5 d-flex align-items-center gap-2" id="git-tab" data-bs-toggle="tab" data-bs-target="#git" type="button" role="tab" aria-controls="git" aria-selected="true" style="border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-code-branch"></i> ปรับปรุงระบบ
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2.5 d-flex align-items-center gap-2" id="beds-tab" data-bs-toggle="tab" data-bs-target="#beds" type="button" role="tab" aria-controls="beds" aria-selected="false" style="border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-bed"></i> ข้อมูลประเภทเตียง
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabContent">
                {{-- Tab 1: ปรับปรุงระบบ --}}
                <div class="tab-pane fade show active" id="git" role="tabpanel" aria-labelledby="git-tab">
                    <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-code-branch text-green me-2"></i> การปรับปรุงระบบผ่าน Git (Deployment)</h5>
                    <p class="text-secondary mb-4">คุณสามารถดึงข้อมูลเวอร์ชันล่าสุดจาก Git Repository (กิ่ง `main`) และทำความสะอาดแคชระบบของ Laravel ได้โดยอัตโนมัติ เพื่อให้ระบบทำงานได้เต็มประสิทธิภาพและอัปเดตเป็นปัจจุบัน</p>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 border d-flex flex-column justify-content-between h-100 gap-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3" style="font-size: 1.8rem; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">ดึงข้อมูลเวอร์ชันล่าสุด (Git Pull & Clear Cache)</h6>
                                        <span class="text-secondary small">ระบบจะทำการรันคำสั่ง <code>git reset --hard</code>, <code>git pull origin main</code> และ <code>php artisan optimize:clear</code></span>
                                    </div>
                                </div>
                                <div class="text-end mt-auto pt-2">
                                    <button type="button" class="btn btn-success w-100 py-2.5 fw-bold text-white shadow-sm" id="btnGitPull" style="border-radius: 12px; background: linear-gradient(135deg, #18a573 0%, #21c08b 100%); border: none;">
                                        <i class="fa-solid fa-rotate me-2"></i> รันระบบ Git Pull
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 border d-flex flex-column justify-content-between h-100 gap-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3" style="font-size: 1.8rem; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa-solid fa-database"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">ปรับปรุงโครงสร้างฐานข้อมูล (Upgrade Database Structure)</h6>
                                        <span class="text-secondary small">รันการทำงานเกี่ยวกับการเพิ่มตาราง เพิ่มฟิลด์ของระบบ (Migrate Database Schema)</span>
                                    </div>
                                </div>
                                <div class="text-end mt-auto pt-2">
                                    <button type="button" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" id="btnUpgradeStructure" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">
                                        <i class="fa-solid fa-gears me-2"></i> Upgrade Structure
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- API Integration Section --}}
                    <div class="mt-4 pt-2">
                        <div class="p-4 bg-light rounded-4 border">
                            <div class="row g-4">
                                {{-- Left Column: API info & URL --}}
                                <div class="col-md-6">
                                    <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-link text-primary me-2"></i> ลิงก์ API สำหรับดึงข้อมูลไปตรวจสอบที่ HOSxP ของแต่ละ รพ.</h5>
                                    <p class="text-secondary mb-4" style="font-size: 0.9rem; line-height: 1.6;">โรงพยาบาลทั้ง 7 สามารถดึงข้อมูลการตายผ่าน API ด้านล่างนี้ โดยส่งคำขอแบบ **POST** และแนบ Token ประจำโรงพยาบาลมาใน **Authorization Header (Bearer token)** หรือแนบใน Body (<code>token</code>) เพื่อความปลอดภัยสูงสุด</p>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.95rem;">API Endpoint URL (POST Method)</label>
                                        <div class="input-group">
                                            <code class="form-control bg-white text-break d-flex align-items-center py-2 px-3 fs-6" id="api_endpoint_url" style="color: #0d6efd;">{{ url('/api/death-data') }}</code>
                                            <button class="btn btn-primary px-4 fw-bold copy-api-btn" data-target="api_endpoint_url" type="button" style="border-radius: 0 12px 12px 0;">
                                                <i class="fa-solid fa-copy me-2"></i> คัดลอกลิงก์ API
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Column: Active Hospitals Table --}}
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-hospital text-slate-500 me-2"></i> รายชื่อโรงพยาบาลที่มีสิทธิ์ใช้งาน</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 bg-white border" style="font-size: 0.9rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 60%;">ชื่อโรงพยาบาล</th>
                                                    <th style="width: 40%;">รหัส รพ. (hospcode)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($hospitals as $hosp)
                                                <tr>
                                                    <td class="fw-bold text-dark">{{ $hosp->name }}</td>
                                                    <td><code>{{ $hosp->hospcode }}</code></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 2: ข้อมูลประเภทเตียง --}}
                <div class="tab-pane fade" id="beds" role="tabpanel" aria-labelledby="beds-tab">
                    <div style="max-width: 900px;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-bed text-blue me-2"></i> จัดการข้อมูลประเภทเตียง (IPD Bed Type)</h5>
                                <span class="text-secondary small">เพิ่ม ลบ หรือแก้ไขรหัสและรายชื่อประเภทเตียงของระบบ IPD</span>
                            </div>
                            <button type="button" class="btn btn-primary px-4 py-2 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#addBedModal" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">
                                <i class="fa-solid fa-plus me-2"></i> เพิ่มประเภทเตียง
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 w-100" id="bedsTable">
                            <thead>
                                <tr class="text-secondary" style="font-size: 0.9rem;">
                                    <th style="width: 20%;">รหัสเตียง (bed_code)</th>
                                    <th style="width: 50%;">ชื่อประเภทเตียง (bed_name)</th>
                                    <th style="width: 15%;">หน่วย (unit)</th>
                                    <th style="width: 15%;" class="text-end">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bedTypes as $bed)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $bed->bed_code }}</td>
                                    <td class="fw-semibold">{{ $bed->bed_name }}</td>
                                    <td>
                                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-3" style="font-size: 0.85rem;">{{ $bed->unit }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-light border edit-bed-btn" 
                                                    data-code="{{ $bed->bed_code }}" 
                                                    data-name="{{ $bed->bed_name }}" 
                                                    data-unit="{{ $bed->unit }}"
                                                    style="border-radius: 8px;"
                                                    aria-label="Edit Bed Type">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border delete-bed-btn" 
                                                    data-code="{{ $bed->bed_code }}" 
                                                    data-name="{{ $bed->bed_name }}"
                                                    style="border-radius: 8px;"
                                                    aria-label="Delete Bed Type">
                                                <i class="fa-solid fa-trash-can text-danger"></i>
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
            </div> {{-- End tab-content --}}

            </div>
        </div>
    </div>
</div>

{{-- Add Bed Type Modal --}}
<div class="modal fade" id="addBedModal" tabindex="-1" aria-labelledby="addBedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(33, 192, 139, 0.25);">
            <form id="addBedForm">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="addBedModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">เพิ่มประเภทเตียงใหม่</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสเตียง (bed_code)</label>
                        <input type="text" name="bed_code" class="form-control" required placeholder="เช่น B01" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                        <div class="form-text text-muted">ความยาวไม่เกิน 6 ตัวอักษร และต้องไม่ซ้ำกับที่มีอยู่แล้ว</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ชื่อประเภทเตียง (bed_name)</label>
                        <input type="text" name="bed_name" class="form-control" required placeholder="กรอกชื่อประเภทเตียง" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">หน่วย (unit)</label>
                        <input type="text" name="unit" class="form-control" required placeholder="เช่น เตียง" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Bed Type Modal --}}
<div class="modal fade" id="editBedModal" tabindex="-1" aria-labelledby="editBedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(13, 110, 253, 0.25);">
            <form id="editBedForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="bed_code" id="editBedCode">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="editBedModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #0d6efd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">แก้ไขข้อมูลประเภทเตียง</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสเตียง (bed_code)</label>
                        <input type="text" id="editBedCodeDisplay" class="form-control bg-light" readonly disabled style="border-radius: 12px; padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ชื่อประเภทเตียง (bed_name)</label>
                        <input type="text" name="bed_name" id="editBedName" class="form-control" required placeholder="กรอกชื่อประเภทเตียง" style="border-radius: 12px; border-color: rgba(13, 110, 253, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">หน่วย (unit)</label>
                        <input type="text" name="unit" id="editBedUnit" class="form-control" required placeholder="เช่น เตียง" style="border-radius: 12px; border-color: rgba(13, 110, 253, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: #0d6efd; border: none;">อัปเดตข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable for Beds Table
        $('#bedsTable').DataTable({
            language: {
                url: "{{ asset('assets/vendor/datatables/th.json') }}"
            },
            ordering: true,
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: 3 } // Disable ordering on Action column
            ]
        });

        // Git Pull Process
        document.getElementById('btnGitPull').addEventListener('click', function() {
            Swal.fire({
                title: 'ต้องการปรับปรุงระบบใช่หรือไม่?',
                text: "ระบบจะทำการล้างโค้ดที่แก้ไขล่าสุดและดึงข้อมูลใหม่จาก Git (git reset & git pull)",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#18a573',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ดำเนินการเลย',
                cancelButtonText: 'ยกเลิก',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(15, 23, 42, 0.3)',
                customClass: {
                    popup: 'rounded-4 border shadow-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังอัปเดตระบบ...',
                        html: 'โปรดรอสักครู่ ระบบกำลังดึงโค้ดล่าสุดจาก Git และทำความสะอาดหน่วยความจำแคช...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        background: 'rgba(255, 255, 255, 0.95)',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('manage.git-pull') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire({
                                title: 'อัปเดตระบบสำเร็จ!',
                                html: '<div class="text-start mt-2"><pre class="bg-dark text-light p-3 rounded-3 small" style="max-height: 250px; overflow-y: auto; white-space: pre-wrap;">' + response.message + '</pre></div>',
                                icon: 'success',
                                confirmButtonColor: '#18a573',
                                confirmButtonText: 'ตกลง',
                                width: '600px',
                                background: 'rgba(255, 255, 255, 0.95)'
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'อัปเดตระบบล้มเหลว',
                                html: '<div class="text-start mt-2"><pre class="bg-dark text-danger p-3 rounded-3 small" style="max-height: 250px; overflow-y: auto; white-space: pre-wrap;">' + errorMessage + '</pre></div>',
                                icon: 'error',
                                confirmButtonColor: '#dc3545',
                                confirmButtonText: 'ตกลง',
                                width: '600px',
                                background: 'rgba(255, 255, 255, 0.95)'
                            });
                        }
                    });
                }
            });
        });

        // Upgrade Structure Process
        document.getElementById('btnUpgradeStructure').addEventListener('click', function() {
            Swal.fire({
                title: 'ต้องการปรับปรุงโครงสร้างฐานข้อมูลใช่หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ดำเนินการเลย',
                cancelButtonText: 'ยกเลิก',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(15, 23, 42, 0.3)',
                customClass: {
                    popup: 'rounded-4 border shadow-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังอัปเกรดโครงสร้างฐานข้อมูล...',
                        html: `
                            <div class="mb-3 text-start">
                                <span id="upgrade-status-text" class="fw-semibold text-secondary" style="font-size: 0.95rem;">ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...</span>
                            </div>
                            <div class="progress mb-3" style="height: 20px; border-radius: 8px;">
                                <div id="upgrade-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 8px; font-weight: bold;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            <div class="p-3 bg-light rounded-3 border text-start" style="font-size: 0.85rem; line-height: 1.6; max-height: 200px; overflow-y: auto;">
                                <div id="upgrade-step-list" class="d-flex flex-column gap-2">
                                    <div id="step-1-status" class="text-secondary"><i class="fa-solid fa-hourglass-start me-2"></i> รอดำเนินการ ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...</div>
                                    <div id="step-2-status" class="text-secondary"><i class="fa-solid fa-hourglass-start me-2"></i> รอดำเนินการ ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        background: 'rgba(255, 255, 255, 0.95)',
                        width: '550px',
                    });

                    const eventSource = new EventSource("{{ route('manage.settings.upgrade-stream') }}");
                    const progressBar = document.getElementById('upgrade-progress-bar');
                    const statusText = document.getElementById('upgrade-status-text');

                    let step1Started = false;
                    let step2Started = false;
                    let lastChanges = [];
                    let lastSeedsSummary = [];

                    eventSource.onmessage = function(event) {
                        const data = JSON.parse(event.data);
                        
                        // Update progress bar
                        progressBar.style.width = data.percent + '%';
                        progressBar.setAttribute('aria-valuenow', data.percent);
                        progressBar.innerHTML = data.percent + '%';
                        
                        // Update current status text
                        statusText.innerHTML = data.status;

                        // Save variables for completion screen
                        if (data.changes) lastChanges = data.changes;
                        if (data.seeds_summary) lastSeedsSummary = data.seeds_summary;

                        const step1Div = document.getElementById('step-1-status');
                        const step2Div = document.getElementById('step-2-status');

                        if (data.step === 1) {
                            if (!step1Started) {
                                step1Started = true;
                                step1Div.className = 'text-primary fw-semibold';
                                step1Div.innerHTML = '<i class="fa-solid fa-rocket me-2"></i> เริ่มต้น ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...';
                            }
                            if (data.percent >= 70) {
                                step1Div.className = 'text-success fw-semibold';
                                let changesText = '';
                                if (lastChanges.length > 0) {
                                    changesText = ' (ปรับปรุง: ' + lastChanges.join(', ') + ')';
                                }
                                step1Div.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> ตรวจสอบโครงสร้างทุกตารางสำเร็จ' + changesText;
                            }
                        } else if (data.step === 2) {
                            step1Div.className = 'text-success fw-semibold';
                            let changesText = '';
                            if (lastChanges.length > 0) {
                                changesText = ' (ปรับปรุง: ' + lastChanges.join(', ') + ')';
                            }
                            step1Div.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> ตรวจสอบโครงสร้างทุกตารางสำเร็จ' + changesText;

                            if (!step2Started) {
                                step2Started = true;
                                step2Div.className = 'text-primary fw-semibold';
                                step2Div.innerHTML = '<i class="fa-solid fa-rocket me-2"></i> เริ่มต้น ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...';
                            }

                            if (data.percent >= 100) {
                                step2Div.className = 'text-success fw-semibold';
                                step2Div.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> นำเข้าข้อมูลพื้นฐานสำเร็จ';
                            }
                        }
                        
                        // Check if finished
                        if (data.percent >= 100) {
                            eventSource.close();

                            let changesText = 'ตรวจสอบโครงสร้างทุกตารางสำเร็จ';
                            if (lastChanges.length > 0) {
                                changesText += ' (ปรับปรุง: ' + lastChanges.join(', ') + ')';
                            }

                            let seedsText = 'นำเข้าข้อมูลพื้นฐานสำเร็จ';
                            if (lastSeedsSummary.length > 0) {
                                seedsText += ': ' + lastSeedsSummary.join(', ');
                            }

                            Swal.fire({
                                title: 'อัปเกรดโครงสร้างเสร็จสิ้น!',
                                html: `
                                    <div class="text-start mb-3">
                                        <p class="text-success fw-bold mb-2" style="font-size: 1.05rem;">การดำเนินการทุกขั้นตอนสำเร็จเรียบร้อย:</p>
                                        <ul class="ps-3 mb-0 text-secondary" style="font-size: 0.95rem; line-height: 1.8; list-style-type: disc;">
                                            <li class="mb-1">${changesText}</li>
                                            <li>${seedsText}</li>
                                        </ul>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonColor: '#198754',
                                confirmButtonText: 'ตกลง',
                                width: '500px',
                                background: 'rgba(255, 255, 255, 0.95)',
                                customClass: {
                                    popup: 'rounded-4 border shadow-lg',
                                    confirmButton: 'px-4 py-2 fw-bold text-white rounded-3'
                                }
                            }).then(() => {
                                location.reload();
                            });
                        }
                    };

                    eventSource.onerror = function(err) {
                        eventSource.close();
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: 'การเชื่อมต่อเซิร์ฟเวอร์ล้มเหลว หรือเกิดข้อผิดพลาดระหว่างดำเนินการปรับปรุงโครงสร้างฐานข้อมูล',
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'ตกลง'
                        });
                    };
                }
            });
        });

        // Add Bed Type Form Submission
        $('#addBedForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('manage.settings.bed-types.create') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#18a573'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let err = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        err = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: err,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });

        // Open Edit Bed Type Modal
        $('.edit-bed-btn').on('click', function() {
            const code = $(this).data('code');
            const name = $(this).data('name');
            const unit = $(this).data('unit');

            $('#editBedCode').val(code);
            $('#editBedCodeDisplay').val(code);
            $('#editBedName').val(name);
            $('#editBedUnit').val(unit);

            $('#editBedModal').modal('show');
        });

        // Edit Bed Type Form Submission
        $('#editBedForm').on('submit', function(e) {
            e.preventDefault();
            const code = $('#editBedCode').val();
            Swal.fire({
                title: 'กำลังอัปเดตข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('manage.settings.bed-types.update', ':code') }}".replace(':code', code),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#18a573'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let err = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        err = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: err,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });

        // Delete Bed Type
        $('.delete-bed-btn').on('click', function() {
            const code = $(this).data('code');
            const name = $(this).data('name');

            Swal.fire({
                title: 'ต้องการลบใช่หรือไม่?',
                text: `คุณกำลังจะลบประเภทเตียง "${name}" (รหัส: ${code})`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังลบข้อมูล...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('manage.settings.bed-types.delete', ':code') }}".replace(':code', code),
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'ลบสำเร็จ!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#18a573'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let err = 'เกิดข้อผิดพลาดในการลบข้อมูล';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                err = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด',
                                text: err,
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });

        // Copy API URL function
        $('.copy-api-btn').on('click', function() {
            let targetId = $(this).data('target');
            let textToCopy = $('#' + targetId).text();
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    Swal.fire({
                        title: 'คัดลอกสำเร็จ!',
                        text: 'คัดลอกลิงก์ API เรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }).catch(function() {
                    fallbackCopy(textToCopy);
                });
            } else {
                fallbackCopy(textToCopy);
            }
        });

        function fallbackCopy(textToCopy) {
            let tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(textToCopy).select();
            document.execCommand('copy');
            tempInput.remove();
            Swal.fire({
                title: 'คัดลอกสำเร็จ!',
                text: 'คัดลอกลิงก์ API เรียบร้อยแล้ว',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
</script>
@endpush
