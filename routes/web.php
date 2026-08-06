<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventSettingController;
use App\Http\Controllers\InstitutionalGuestController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('dashboard');
    Route::post('/undangan', [AttendanceController::class, 'storeInvitation'])->name('invitations.store');
    Route::patch('/undangan/{invitation}/paket', [AttendanceController::class, 'addPackage'])->name('invitations.package');
    Route::put('/undangan/{invitation}', [AttendanceController::class, 'updateInvitation'])->name('invitations.update');
    Route::get('/undangan/{invitation}', [AttendanceController::class, 'invitation'])->name('invitations.show');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('checkin');
    Route::get('/qr-code/{code}', [AttendanceController::class, 'lookup'])->name('qrcode.lookup');
    Route::post('/undangan/{invitation}/tamu', [AttendanceController::class, 'storeGuest'])->name('invitations.guests.store');
    Route::put('/agenda-aktif', [EventSettingController::class, 'update'])->name('agenda.update');
    Route::get('/laporan/tamu.pdf', [ReportController::class, 'guests'])->name('reports.guests.pdf');
    Route::get('/laporan/data-undangan.xlsx', [ReportController::class, 'invitations'])->name('reports.invitations.xlsx');
    Route::get('/qr-code-png/{code}', [ReportController::class, 'qrCodePng'])->name('qrcodes.png');
    Route::post('/tamu-institusi', [InstitutionalGuestController::class, 'store'])->name('institutional-guests.store');
    Route::get('/tamu-institusi/{institutionalGuest}', [InstitutionalGuestController::class, 'show'])->name('institutional-guests.show');
    Route::put('/tamu-institusi/{institutionalGuest}', [InstitutionalGuestController::class, 'update'])->name('institutional-guests.update');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
