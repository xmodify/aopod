@extends('layouts.admin')

@section('title', 'แผงควบคุมผู้ดูแลระบบ - AOPOD')
@section('header_title', 'แผงควบคุม (Admin Dashboard)')

@section('content')
<div class="row g-4">
    <!-- Welcome section -->
    <div class="col-12">
        <div class="glass-card p-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3" style="background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(33, 192, 139, 0.1) 100%);">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--admin-primary);">ยินดีต้อนรับกลับมา, {{ Auth::user()->name }}! 👋</h3>
                <p class="text-secondary mb-0">นี่คือแผงควบคุมระบบสำหรับผู้ดูแลระบบ AOPOD (Amnatcharoen One Province One Data)</p>
            </div>
            <div>
                <a href="{{ url('web') }}" class="btn btn-outline-primary px-4 py-2" style="border-radius: 12px; font-weight: 500;">
                    <i class="fa-solid fa-desktop me-2"></i> ดูหน้าเว็บหลัก
                </a>
            </div>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="col-md-4">
        <div class="glass-card d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4" style="font-size: 2rem; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <h6 class="text-secondary mb-1">ผู้ใช้ทั้งหมดในระบบ</h6>
                <h3 class="fw-bold mb-0">1</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="glass-card d-flex align-items-center gap-3">
            <div class="p-3 bg-success bg-opacity-10 text-success rounded-4" style="font-size: 2rem; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <div>
                <h6 class="text-secondary mb-1">โรงพยาบาลในเครือข่าย</h6>
                <h3 class="fw-bold mb-0">7</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="glass-card d-flex align-items-center gap-3">
            <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4" style="font-size: 2rem; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h6 class="text-secondary mb-1">สิทธิ์ปัจจุบันของคุณ</h6>
                <h3 class="fw-bold mb-0 text-capitalize">{{ Auth::user()->role }}</h3>
            </div>
        </div>
    </div>

    <!-- Administrative functions section -->
    <div class="col-12">
        <div class="glass-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-sliders text-green me-2"></i> ส่วนการจัดการและเพิ่มเมนูในอนาคต</h5>
            
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 bg-light p-3" style="border-radius: 16px;">
                        <h6 class="fw-bold text-dark"><i class="fa-solid fa-user-gear me-2 text-primary"></i> จัดการสมาชิก (User Management)</h6>
                        <p class="text-secondary small">จัดการบัญชีผู้ใช้งาน, กำหนดสิทธิ์บทบาทหน้าที่การเข้าถึงระบบ</p>
                        <a href="#" class="btn btn-sm btn-light border w-100 disabled mt-2" style="border-radius: 8px;">ยังไม่เปิดใช้งาน</a>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 bg-light p-3" style="border-radius: 16px;">
                        <h6 class="fw-bold text-dark"><i class="fa-solid fa-gears me-2 text-success"></i> ตั้งค่าระบบ (System Settings)</h6>
                        <p class="text-secondary small">ตั้งค่าข้อมูลทั่วไปของระบบ แหล่งนำเข้าข้อมูล API และการตั้งค่า Token</p>
                        <a href="#" class="btn btn-sm btn-light border w-100 disabled mt-2" style="border-radius: 8px;">ยังไม่เปิดใช้งาน</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 bg-light p-3" style="border-radius: 16px;">
                        <h6 class="fw-bold text-dark"><i class="fa-solid fa-file-invoice-dollar me-2 text-warning"></i> รายงานสถิติการใช้งาน (Usage Logs)</h6>
                        <p class="text-secondary small">ตรวจสอบบันทึกการทำงานและประวัติการแลกเปลี่ยนข้อมูลผ่าน API</p>
                        <a href="#" class="btn btn-sm btn-light border w-100 disabled mt-2" style="border-radius: 8px;">ยังไม่เปิดใช้งาน</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
