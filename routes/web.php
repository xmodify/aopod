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

use App\Http\Controllers\Auth\ProviderIdAuthController;
use App\Http\Controllers\Auth\MophAlert2FAController;

// Login (สำหรับ Modal login)
Route::get('/login', function () {
    return redirect()->to(url('web'));
})->name('login');
Route::post('/login', [LoginController::class, 'login']);

// 2FA MOPH Alert Routes
Route::get('/login/verify-2fa', [MophAlert2FAController::class, 'index'])->name('auth.2fa.index');
Route::post('/login/verify-2fa', [MophAlert2FAController::class, 'verifyOTP'])->name('auth.2fa.verify');
Route::post('/login/resend-2fa', [MophAlert2FAController::class, 'resendOTP'])->name('auth.2fa.resend');

// Health ID & Provider ID Routes
Route::prefix('auth/health-id')->name('auth.health-id.')->group(function () {
    Route::get('/redirect', [ProviderIdAuthController::class, 'redirectToProvider'])->name('redirect');
    Route::get('/callback', [ProviderIdAuthController::class, 'handleProviderCallback'])->name('callback');
});

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
    Route::post('/manage/settings/moph', [AdminController::class, 'updateMophSettings'])->name('manage.settings.moph');
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

    // Hospital Agent Management Routes
    Route::get('/manage/agents', [\App\Http\Controllers\Web\AgentWebController::class, 'index'])->name('manage.agents');
    Route::post('/manage/agents/remote-sync', [\App\Http\Controllers\Web\AgentWebController::class, 'dispatchRemoteSync'])->name('manage.agents.remote-sync');
    Route::post('/manage/agents/remote-update', [\App\Http\Controllers\Web\AgentWebController::class, 'dispatchRemoteUpdate'])->name('manage.agents.remote-update');
    Route::post('/manage/agents/queries', [\App\Http\Controllers\Web\AgentWebController::class, 'updateQueries'])->name('manage.agents.update-queries');
    Route::post('/manage/agents/queries/reset', [\App\Http\Controllers\Web\AgentWebController::class, 'resetQueries'])->name('manage.agents.reset-queries');
    Route::post('/manage/agents/lookup-icd10', [\App\Http\Controllers\Web\AgentWebController::class, 'updatePpIcd10'])->name('manage.agents.update-icd10');
    Route::post('/manage/agents/lookup-icd10/reset', [\App\Http\Controllers\Web\AgentWebController::class, 'resetPpIcd10'])->name('manage.agents.reset-icd10');
    Route::post('/manage/agents/{hcode}/token', [\App\Http\Controllers\Web\AgentWebController::class, 'issueToken'])->name('manage.agents.issue-token');
    Route::get('/manage/agents/{hcode}/config', [\App\Http\Controllers\Web\AgentWebController::class, 'downloadConfig'])->name('manage.agents.download-config');
    Route::get('/manage/agents/download-exe', [\App\Http\Controllers\Web\AgentWebController::class, 'downloadExe'])->name('manage.agents.download-exe');
});

// Manage Data Routes (Requires Web Authentication, access checked in Controller)
Route::middleware(['auth:web'])->group(function () {
    Route::get('/manage', [AdminController::class, 'index'])->name('manage.index');
    Route::get('/manage/death-data', [\App\Http\Controllers\Web\DeathDataController::class, 'index'])->name('manage.death-data.index');
    Route::post('/manage/death-data/import', [\App\Http\Controllers\Web\DeathDataController::class, 'import'])->name('manage.death-data.import');
    Route::get('/manage/birth-data', [\App\Http\Controllers\Web\BirthDataController::class, 'index'])->name('manage.birth-data.index');
    Route::post('/manage/birth-data/import', [\App\Http\Controllers\Web\BirthDataController::class, 'import'])->name('manage.birth-data.import');
});