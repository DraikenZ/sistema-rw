<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\RoomBoard;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Dashboard;

Route::view('/', 'welcome');

Route::get('/dashboard', Dashboard::class)->middleware('auth')->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/semaforo', RoomBoard::class)->middleware('auth')->name('rooms.board');

Route::post('/salir', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('hotel.logout');

require __DIR__.'/auth.php';
