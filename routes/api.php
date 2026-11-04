<?php
use App\Http\Controllers\{ApplicantController, AttendanceController, AuthController, DashboardController, DepartmentController,
    EmployeeController, JobOpeningController, LeaveRequestController, LeaveTypeController, PayrollController, PerformanceReviewController};
use App\Http\Middleware\HrOnly;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('dashboard', DashboardController::class);

    // Employee self-service
    Route::post('attendance/clock', [AttendanceController::class, 'clock']);
    Route::post('leave-requests', [LeaveRequestController::class, 'store']);

    $resources = [
        'departments' => DepartmentController::class, 'employees' => EmployeeController::class,
        'attendances' => AttendanceController::class, 'leave-types' => LeaveTypeController::class,
        'leave-requests' => LeaveRequestController::class, 'payrolls' => PayrollController::class,
        'job-openings' => JobOpeningController::class, 'applicants' => ApplicantController::class,
        'performance-reviews' => PerformanceReviewController::class,
    ];
    foreach ($resources as $name => $ctrl) Route::apiResource($name, $ctrl)->only(['index', 'show']);

    // HR / admin only
    Route::middleware(HrOnly::class)->group(function () use ($resources) {
        Route::post('payrolls/generate', [PayrollController::class, 'generate']); // before {id} routes
        foreach ($resources as $name => $ctrl) {
            $only = $name === 'leave-requests' ? ['update', 'destroy'] : ['store', 'update', 'destroy'];
            Route::apiResource($name, $ctrl)->only($only);
        }
        Route::post('leave-requests/{id}/decide', [LeaveRequestController::class, 'decide']);
        Route::post('payrolls/{id}/pay', [PayrollController::class, 'pay']);
    });
});
