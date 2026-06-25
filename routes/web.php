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

// Manage Area Protected Routes (Admin Only)
Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('/manage/settings', [AdminController::class, 'settings'])->name('manage.settings');
    Route::get('/manage/settings/upgrade-stream', [AdminController::class, 'upgradeStructureStream'])->name('manage.settings.upgrade-stream');
    Route::post('/manage/settings/upgrade-structure', [AdminController::class, 'upgradeStructure'])->name('manage.settings.upgrade-structure');
    Route::post('/manage/settings/git-pull', [AdminController::class, 'gitPull'])->name('manage.git-pull');
    Route::post('/manage/settings/bed-types', [AdminController::class, 'createBedType'])->name('manage.settings.bed-types.create');
    Route::put('/manage/settings/bed-types/{bed_code}', [AdminController::class, 'updateBedType'])->name('manage.settings.bed-types.update');
    Route::delete('/manage/settings/bed-types/{bed_code}', [AdminController::class, 'deleteBedType'])->name('manage.settings.bed-types.delete');

    // User Management Routes
    Route::get('/manage/users', [AdminController::class, 'users'])->name('manage.users');
    Route::post('/manage/users', [AdminController::class, 'createUser'])->name('manage.users.create');
    Route::put('/manage/users/{user}', [AdminController::class, 'updateUser'])->name('manage.users.update');
    Route::delete('/manage/users/{user}', [AdminController::class, 'deleteUser'])->name('manage.users.delete');
    Route::post('/manage/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('manage.users.reset-password');
});

// Manage Data Routes (Requires Web Authentication, access checked in Controller)
Route::middleware(['auth:web'])->group(function () {
    Route::get('/manage', [AdminController::class, 'index'])->name('manage.index');
    Route::get('/manage/death-data', [\App\Http\Controllers\Web\DeathDataController::class, 'index'])->name('manage.death-data.index');
    Route::post('/manage/death-data/import', [\App\Http\Controllers\Web\DeathDataController::class, 'import'])->name('manage.death-data.import');
    Route::get('/manage/birth-data', [\App\Http\Controllers\Web\BirthDataController::class, 'index'])->name('manage.birth-data.index');
    Route::post('/manage/birth-data/import', [\App\Http\Controllers\Web\BirthDataController::class, 'import'])->name('manage.birth-data.import');
});