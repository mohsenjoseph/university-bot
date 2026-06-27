<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ExpertAuthController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\ReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [ExpertAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [ExpertAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [ExpertAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'expert'])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/requests', [DashboardController::class, 'allRequests'])->name('requests.index');   Route::get('/requests/{id}', [DashboardController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/reply', [DashboardController::class, 'reply'])->name('requests.reply');
    Route::post('/requests/{id}/refer', [DashboardController::class, 'refer'])->name('requests.refer');
    Route::post('/requests/{id}/recall', [DashboardController::class, 'recallReferral'])->name('requests.recall');
    Route::post('/requests/{id}/return', [DashboardController::class, 'returnRequest'])->name('requests.return');
    
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

Route::get('/clear-cache-temp', function () {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return 'Cache cleared!';
});