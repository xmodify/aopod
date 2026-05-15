<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HospitalTokenController;
use App\Http\Controllers\Api\OpInsuranceController;
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
    Route::post('/op_insurance', [OpInsuranceController::class, 'ingest']);
    Route::post('/opd', [OpdController::class, 'opd']);
    Route::get('/opd', [OpdController::class, 'get_opd']);    
    Route::post('/ipd', [IpdController::class, 'ipd']);
    Route::get('/ipd', [IpdController::class, 'get_ipd']);
    Route::post('/ipd_bed_dep', [IpdBedDepController::class, 'ingest']);
    Route::get('/ipd_bed_dep', [IpdBedDepController::class, 'get']);
    Route::post('/hospital_config', [HospitalUpdateController::class, 'update']);
});
 


