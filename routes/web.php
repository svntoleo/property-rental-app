<?php

use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\StayCategoryController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Resource routes for property rental management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('properties', PropertyController::class);
    Route::resource('accommodations', AccommodationController::class);
    Route::resource('stays', StayController::class);
    Route::resource('tenants', TenantController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('stay-categories', StayCategoryController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class);

    // Trash management routes
    Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('trash/{type}/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('trash/{type}/{id}', [TrashController::class, 'forceDelete'])->name('trash.forceDelete');
});

require __DIR__.'/settings.php';
