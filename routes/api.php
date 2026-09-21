<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HospitalTokenController;
use App\Http\Controllers\Api\OpdController;
use App\Http\Controllers\Api\IpdController;
use App\Http\Controllers\Api\IpdBedDepController;
use App\Http\Controllers\Api\HospitalUpdateController;

// Route::get('/hospitals/{hospcode}/tokens', [HospitalTokenController::class, 'index']);
//Route::post('/hospitals/{hospcode}/tokens', [HospitalTokenController::class, 'issue']);
    // http://1.179.128.29:3394/api/hospitals/00025/tokens
    // {
    // "name": "10987-ingest",
    // "abilities": ["ingest"]
    // }
// Route::delete('/hospitals/{hospcode}/tokens/{tokenId}', [HospitalTokenController::class, 'revoke']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/opd', [OpdController::class, 'opd']);
    Route::get('/opd', [OpdController::class, 'get_opd']);    
    Route::post('/ipd', [IpdController::class, 'ipd']);
    Route::get('/ipd', [IpdController::class, 'get_ipd']);
    Route::post('/ipd_bed_dep', [IpdBedDepController::class, 'ingest']);
    Route::get('/ipd_bed_dep', [IpdBedDepController::class, 'get']);
    Route::post('/hospital_config', [HospitalUpdateController::class, 'update']);

    // Agent Management APIs
    Route::post('/agent/verify', [\App\Http\Controllers\Api\AgentApiController::class, 'verifyToken']);
    Route::get('/agent/config', [\App\Http\Controllers\Api\AgentApiController::class, 'getConfig']);
    Route::post('/agent/heartbeat', [\App\Http\Controllers\Api\AgentApiController::class, 'heartbeat']);
    Route::post('/agent/task/complete', [\App\Http\Controllers\Api\AgentApiController::class, 'completeTask']);
});

// Public / Token-less Lookup & Download Route for Agent
Route::get('/agent/lookups/icd10', [\App\Http\Controllers\Api\AgentApiController::class, 'getIcd10Lookup']);
Route::get('/agent/download-latest', [\App\Http\Controllers\Web\AgentWebController::class, 'downloadExe']);

Route::post('/death-data', [\App\Http\Controllers\Api\DeathApiController::class, 'getDeathData']);
 


