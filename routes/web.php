<?php

use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\StayCategoryController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
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
});

require __DIR__.'/settings.php';
