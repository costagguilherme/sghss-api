<?php

use App\Http\Controllers\AppointmentController;
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

    Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
});


Route::post('/patients', [PatientController::class, 'store']);
Route::middleware(['auth:sanctum', 'patientadminmid'])->group(function () {
    Route::put('/patients/{id}', [PatientController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'patientadminmid'])->group(function () {
    Route::prefix('appointments')->group(function () {
        Route::post('/', [AppointmentController::class, 'schedule']);
        Route::get('/{id}', [AppointmentController::class, 'getById']);
        Route::delete('/{id}', [AppointmentController::class, 'cancel']);
    });
});


Route::put('/appointments/{id}/medical-info', [AppointmentController::class, 'medicalInfo']);
