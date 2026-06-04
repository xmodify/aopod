<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DashboardOPDController;
use App\Http\Controllers\Web\DashboardClaimController;
use App\Http\Controllers\Web\DashboardReferController;
use App\Http\Controllers\Web\DashboardOperationController;
use App\Http\Controllers\Web\AdminController;

// หน้าแรก redirect ไป web
Route::get('/', function () {
    // return view('dashboard');
     return redirect()->to(url('web')); 
});

// หน้า web หลัก
Route::match(['get','post'],'web', [DashboardController::class, 'index'])->name('web.index');
Route::get('web/bed_dep/{hospcode}', [DashboardController::class, 'bed_dep']);
Route::match(['get','post'],'web/opd', [DashboardOPDController::class, 'index']);
Route::match(['get','post'],'web/claim', [DashboardClaimController::class, 'index']);
Route::match(['get','post'],'web/refer', [DashboardReferController::class, 'index']);
Route::match(['get','post'],'web/operation', [DashboardOperationController::class, 'index']);

// Login (สำหรับ Modal login)
Route::get('/login', function () {
    return redirect()->to(url('web'));
})->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ถ้าคุณต้องการ page /dashboard ให้ล็อกอินก่อน
Route::middleware('auth:web')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->to(url('web')); 
    });
    Route::post('/change-password', [LoginController::class, 'changePassword'])->name('change-password');
});

// Admin Area Protected Routes
Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings/upgrade-structure', [AdminController::class, 'upgradeStructure'])->name('admin.settings.upgrade-structure');
    Route::post('/admin/settings/git-pull', [AdminController::class, 'gitPull'])->name('admin.git-pull');
    Route::post('/admin/settings/bed-types', [AdminController::class, 'createBedType'])->name('admin.settings.bed-types.create');
    Route::put('/admin/settings/bed-types/{bed_code}', [AdminController::class, 'updateBedType'])->name('admin.settings.bed-types.update');
    Route::delete('/admin/settings/bed-types/{bed_code}', [AdminController::class, 'deleteBedType'])->name('admin.settings.bed-types.delete');

    // User Management Routes
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::post('/admin/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('admin.users.reset-password');
});

// Death Data Routes (Requires Web Authentication, access checked in Controller)
Route::middleware(['auth:web'])->group(function () {
    Route::get('/admin/death-data', [\App\Http\Controllers\Web\DeathDataController::class, 'index'])->name('admin.death-data.index');
    Route::post('/admin/death-data/import', [\App\Http\Controllers\Web\DeathDataController::class, 'import'])->name('admin.death-data.import');
    Route::get('/admin/birth-data', [\App\Http\Controllers\Web\BirthDataController::class, 'index'])->name('admin.birth-data.index');
    Route::post('/admin/birth-data/import', [\App\Http\Controllers\Web\BirthDataController::class, 'import'])->name('admin.birth-data.import');
});