<?php

use App\Http\Middleware\RoleAdminMiddleware;
use App\Http\Middleware\RoleDoctorMiddleware;
use App\Http\Middleware\RolePatientAdminMiddleware;
use App\Http\Middleware\RolePatientMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'patientmid' => RolePatientMiddleware::class,
            'adminmid' => RoleAdminMiddleware::class,
            'patientadminmid' => RolePatientAdminMiddleware::class,
            'doctormid' => RoleDoctorMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
