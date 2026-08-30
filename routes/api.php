<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\OpenMatchController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\ListingController;



Route::get('setting',                       [SettingController::class,      'index'])->name('api.setting.index');
Route::get('search_nn',                     [PlayerController::class,       'search_nn'])->name('api.player.search_nn');
Route::get('reservation/get_date',          [ReservationController::class,  'get_date'])->name('api.reservation.get_date');

// Prenotazione: solo giocatori con email verificata
Route::post('get_reservation',              [ReservationController::class,  'get_reservation'])->middleware('player.verified')->name('api.player.get_reservation');

// Login via OTP
Route::post('login_client',                 [PlayerController::class,       'login_client'])->name('api.player.login_client');
Route::post('verifyOtp',                    [PlayerController::class,       'verifyOtp'])->name('api.player.verifyOtp');

// Registrazione con verifica email
Route::post('register',                     [PlayerController::class,       'register'])->name('api.player.register');
Route::post('register/verify',              [PlayerController::class,       'registerVerify'])->name('api.player.register.verify');
Route::post('otp/resend',                   [PlayerController::class,       'resendOtp'])->name('api.player.otp.resend');

// Area cliente
Route::post('account',                      [PlayerController::class,       'account'])->name('api.player.account');
Route::post('account/update',               [PlayerController::class,       'updateAccount'])->name('api.player.account.update');
Route::post('account/photo',                [PlayerController::class,       'uploadPhoto'])->name('api.player.account.photo.store');
Route::delete('account/photo',              [PlayerController::class,       'deletePhoto'])->name('api.player.account.photo.destroy');

// ==========================================================
// Partite aperte
// ==========================================================

// Lettura pubblica: anche chi non è loggato vede l'elenco.
Route::get('open-matches',                  [OpenMatchController::class,    'index'])->name('api.open_matches.index');
Route::get('open-matches/{reservation}',    [OpenMatchController::class,    'show'])->name('api.open_matches.show');

// Azioni: solo giocatori con email verificata.
Route::middleware('player.verified')->group(function () {
    Route::post('open-matches/{reservation}/join',   [OpenMatchController::class, 'join'])->name('api.open_matches.join');
    Route::delete('open-matches/{reservation}/join', [OpenMatchController::class, 'leave'])->name('api.open_matches.leave');

    Route::post('reservations/{reservation}/open',   [OpenMatchController::class, 'open'])->name('api.reservations.open');
    Route::delete('reservations/{reservation}/open', [OpenMatchController::class, 'close'])->name('api.reservations.close');

    Route::post('account/open-matches',              [OpenMatchController::class, 'mine'])->name('api.account.open_matches');
});

// ==========================================================
// Tornei
// ==========================================================

// Vetrina pubblica: visibile anche a chi non è loggato.
Route::get('tournaments',          [TournamentController::class, 'index'])->name('api.tournaments.index');
Route::get('tournaments/{slug}',   [TournamentController::class, 'show'])->name('api.tournaments.show');

// Iscrizioni: solo giocatori con email verificata.
Route::middleware('player.verified')->group(function () {
    Route::post('tournaments/{slug}/register',   [TournamentController::class, 'register'])->name('api.tournaments.register');
    Route::delete('tournaments/{slug}/register', [TournamentController::class, 'withdraw'])->name('api.tournaments.withdraw');

    Route::post('account/tournaments',           [TournamentController::class, 'mine'])->name('api.account.tournaments');
});

// ==========================================================
// Bacheca annunci
// ==========================================================

// Consultazione pubblica: visibile anche a chi non è loggato.
Route::get('listings',            [ListingController::class, 'index'])->name('api.listings.index');
Route::get('listings/{listing}',  [ListingController::class, 'show'])->name('api.listings.show');

// Pubblicazione e gestione: solo giocatori con email verificata.
Route::middleware('player.verified')->group(function () {
    Route::post('listings',                  [ListingController::class, 'store'])->name('api.listings.store');
    // PUT per correttezza REST, POST perché PHP non elabora il multipart su PUT.
    Route::match(['put', 'post'], 'listings/{listing}', [ListingController::class, 'update'])->name('api.listings.update');
    Route::delete('listings/{listing}',      [ListingController::class, 'destroy'])->name('api.listings.destroy');
    Route::post('listings/{listing}/sold',   [ListingController::class, 'sold'])->name('api.listings.sold');

    Route::post('account/listings',          [ListingController::class, 'mine'])->name('api.account.listings');
    Route::post('account/fixed-slot',        [PlayerController::class, 'fixedSlot'])->name('api.account.fixed_slot');
});

Route::get('client_default',                [SettingController::class, 'client_default'])->name('api.client_default'); // annullamento tramite mail
