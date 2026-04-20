<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::view('inventory', 'inventory.index')->name('inventory.index');
    Route::view('inventory/create', 'inventory.create')->name('inventory.create');
    Route::get('inventory/{id}/edit', function ($id) {
        return view('inventory.edit', ['id' => $id]);
    })->name('inventory.edit');
    Route::view('management', 'management.index')->name('management.index');
});

require __DIR__.'/settings.php';
