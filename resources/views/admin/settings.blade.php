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
                </div>

                {{-- Tab 2: ข้อมูลประเภทเตียง --}}
                <div class="tab-pane fade" id="beds" role="tabpanel" aria-labelledby="beds-tab">
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
                        url: "{{ route('admin.git-pull') }}",
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
                text: "ระบบจะทำการเพิ่มตารางและฟิลด์ที่กำหนดไว้ลงในฐานข้อมูล",
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
                        title: 'กำลังปรับปรุงฐานข้อมูล...',
                        html: 'โปรดรอสักครู่ ระบบกำลังปรับปรุงตารางและข้อมูล...',
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
                        url: "{{ route('admin.settings.upgrade-structure') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire({
                                title: 'ปรับปรุงฐานข้อมูลสำเร็จ!',
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
                                title: 'ปรับปรุงฐานข้อมูลล้มเหลว',
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
                url: "{{ route('admin.settings.bed-types.create') }}",
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
                url: "{{ route('admin.settings.bed-types.update', ':code') }}".replace(':code', code),
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
                        url: "{{ route('admin.settings.bed-types.delete', ':code') }}".replace(':code', code),
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
    });
</script>
@endpush
