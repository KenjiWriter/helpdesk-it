<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified', 'role.user'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('tickets/create', 'pages::tickets.create')->name('tickets.create');
    Route::livewire('tickets/{ticket}', 'pages::tickets.show')->name('tickets.show');
});

require __DIR__.'/settings.php';
