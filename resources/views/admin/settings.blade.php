@extends('layouts.admin')

@section('title', 'ตั้งค่าระบบ - AOPOD')
@section('header_title', 'ตั้งค่าระบบ (System Settings)')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-code-branch text-green me-2"></i> การปรับปรุงระบบผ่าน Git (Deployment)</h5>
            <p class="text-secondary">คุณสามารถดึงข้อมูลเวอร์ชันล่าสุดจาก Git Repository (กิ่ง `main`) และทำความสะอาดแคชระบบของ Laravel ได้โดยอัตโนมัติ เพื่อให้ระบบทำงานได้เต็มประสิทธิภาพและอัปเดตเป็นปัจจุบัน</p>
            
            <div class="p-4 bg-light rounded-4 border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3" style="font-size: 1.8rem; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">ดึงข้อมูลเวอร์ชันล่าสุด (Git Pull & Clear Cache)</h6>
                        <span class="text-secondary small">ระบบจะทำการรันคำสั่ง <code>git reset --hard</code>, <code>git pull origin main</code> และ <code>php artisan optimize:clear</code></span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-success px-4 py-2.5 fw-bold text-white shadow-sm" id="btnGitPull" style="border-radius: 12px; background: linear-gradient(135deg, #18a573 0%, #21c08b 100%); border: none;">
                        <i class="fa-solid fa-rotate me-2"></i> รันระบบ Git Pull
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
                // Show loading status
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

                // AJAX Request to run git pull
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
</script>
@endpush
