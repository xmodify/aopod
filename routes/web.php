<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DashboardOPDController;
use App\Http\Controllers\Web\DashboardClaimController;
use App\Http\Controllers\Web\DashboardReferController;
use App\Http\Controllers\Web\DashboardOperationController;

// หน้าแรก redirect ไป web
Route::get('/', function () {
    // return view('dashboard');
     return redirect()->to('web/'); 
});

// หน้า web หลัก
Route::match(['get','post'],'web', [DashboardController::class, 'index'])->name('web.index');
Route::get('web/bed_dep/{hospcode}', [DashboardController::class, 'bed_dep']);
Route::match(['get','post'],'web/opd', [DashboardOPDController::class, 'index']);
Route::match(['get','post'],'web/claim', [DashboardClaimController::class, 'index']);
Route::match(['get','post'],'web/refer', [DashboardReferController::class, 'index']);
Route::match(['get','post'],'web/operation', [DashboardOperationController::class, 'index']);

// Login (สำหรับ Modal login)
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ถ้าคุณต้องการ page /dashboard ให้ล็อกอินก่อน
Route::middleware('auth:web')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->to('web/'); 
    });
});