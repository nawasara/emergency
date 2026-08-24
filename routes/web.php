<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Emergency\Livewire\Contact\Index as ContactIndex;
use Spatie\Permission\Middleware\PermissionMiddleware;

Route::middleware(['web', 'auth'])->prefix('nawasara-emergency')->group(function () {
    Route::get('contacts', ContactIndex::class)
        ->middleware(PermissionMiddleware::using('emergency.contact.view'))
        ->name('nawasara-emergency.contacts.index');
});
