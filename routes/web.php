<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Route để hiển thị form đăng nhập của trang Admin
Route::get('/admin/login', function () {
    return view('admin.login'); // Trả về file resources/views/admin/login.blade.php
})->name('admin.login');

// ---- PHẦN ĐƯỢC THÊM VÀO ----
// Route để hiển thị trang dashboard sau khi đăng nhập thành công
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard'); // Trả về file resources/views/admin/dashboard.blade.php
})->name('admin.dashboard');
