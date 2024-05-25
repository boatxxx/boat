<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;


Route::get('/', [AttendanceController::class, 'welcome'])->name('welcome');

Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance1', [AttendanceController::class, 'index1'])->name('attendance.index1');
Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
Route::post('/attendance/fetch-students', [AttendanceController::class, 'fetchStudents'])->name('attendance.fetchStudents');
Route::post('/submit-attendance', [AttendanceController::class, 'submitAttendance'])->name('attendance.submitAttendance');
Route::post('/store', [AttendanceController::class, 'store'])->name('attendance.store');

Route::post('/store', [AttendanceController::class, 'store'])->name('attendance.store');
Route::get('/record', function () {
    return view('record');

})->name('record');
Route::get('/importexcel2', function () {
    return view('import');

})->name('import');

Route::post('/importExcel555', [AttendanceController::class, 'importExcel2'])->name('import.exce');

Route::get('/report', [AttendanceController::class, 'index3'])->name('report');


Route::post('/port', [AttendanceController::class, 'port'])->name('port');
