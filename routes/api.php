<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum', 'adminmid'])->group(function () {
    Route::prefix('hospitals')->group(function () {
        Route::get('/', [HospitalController::class, 'index']);
        Route::post('/', [HospitalController::class, 'store']);
        Route::put('/{id}', [HospitalController::class, 'update']);
        Route::delete('/{id}', [HospitalController::class, 'destroy']);
    });

    Route::prefix('doctors')->group(function () {
        Route::get('/', [DoctorController::class, 'index']);
        Route::get('/{id}', [DoctorController::class, 'show']);
        Route::post('/', [DoctorController::class, 'store']);
        Route::put('/{id}', [DoctorController::class, 'update']);
        Route::delete('/{id}', [DoctorController::class, 'destroy']);
    });
});

Route::middleware(['auth:sanctum', 'patientmid'])->group(function () {
    
});


Route::prefix('patients')->group(function () {
    Route::get('/', [PatientController::class, 'index']);
    Route::get('/{id}', [PatientController::class, 'show']);
    Route::post('/', [PatientController::class, 'store']);
    Route::put('/{id}', [PatientController::class, 'update']);
    Route::delete('/{id}', [PatientController::class, 'destroy']);
});
