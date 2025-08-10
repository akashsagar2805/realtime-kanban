<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\CardController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/invitations/{token}/accept', [BoardController::class, 'acceptInvitation'])->name('invitations.accept');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('boards', BoardController::class);
    Route::resource('boards.columns', ColumnController::class)->except(['index', 'create', 'show', 'edit']);
    Route::resource('cards', CardController::class)->except(['index', 'create', 'show', 'edit']);

    Route::post('/cards', [CardController::class, 'store'])->name('cards.store');

    Route::post('/boards/{board}/invite', [BoardController::class, 'invite'])->name('boards.invite');
    Route::put('/boards/{board}/members/{user}', [BoardController::class, 'updateMemberRole'])->name('boards.members.update');
    Route::delete('/boards/{board}/members/{user}', [BoardController::class, 'removeMember'])->name('boards.members.destroy');
});

require __DIR__.'/auth.php';
