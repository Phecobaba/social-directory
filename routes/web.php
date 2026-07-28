<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashbord', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/dashboard', '/dashbord');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/members/backup', [MemberController::class, 'backup'])->name('members.backup');
    Route::get('/members/export', [MemberController::class, 'export'])->name('members.export');
    Route::get('/members/export/excel', [MemberController::class, 'exportExcel'])->name('members.export.excel');
    Route::post('/members/bulk-edit', [MemberController::class, 'bulkEdit'])->name('members.bulk-edit');
    Route::post('/members/bulk-update', [MemberController::class, 'bulkUpdate'])->name('members.bulk-update');
    Route::post('/members/bulk-delete', [MemberController::class, 'bulkDestroy'])->name('members.bulk-delete');
    Route::delete('/members', [MemberController::class, 'destroyFromRequest'])->name('members.destroy-from-request');
    Route::post('/members/import/confirm', [MemberController::class, 'confirmImport'])->name('members.import.confirm');
    Route::post('/members/import/clear', [MemberController::class, 'clearImportPreview'])->name('members.import.clear');
    Route::get('/members/import/template', [MemberController::class, 'downloadTemplate'])->name('members.import.template');
    Route::post('/members/import', [MemberController::class, 'import'])->name('members.import');

    Route::resource('branches', BranchController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('members', MemberController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->whereNumber('member');
});
