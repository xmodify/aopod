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
</div>
@endsection
