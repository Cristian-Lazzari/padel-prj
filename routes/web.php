<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;


use App\Http\Controllers\Admin\MailerController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Admin\FixedSlotController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Guests\PageController as GuestsPageController;


Route::get('/', function () {
    return view('guests/home');
});

Route::get('/delete_succes', function () {
    return view('guests/documentazione');
});


Route::middleware(['auth', 'verified'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {

        Route::get('/',           [AdminPageController::class, 'admin'])->name('dashboard'); //calendar
        // Il calendario carica un mese alla volta: le frecce chiedono qui il successivo
        Route::get('/calendar/month', [AdminPageController::class, 'month'])->name('calendar.month');
        // /reservations
        // /players
        Route::get('/settings',            [SettingController::class, 'index'])->name('settings');
        Route::get('/statistics',          [StatisticController::class, 'index'])->name('statistics');

        Route::get('/mailer/index',         [MailerController::class, 'mailer'])->name('mailer.index');
        Route::get('/mailer/send_mail',     [MailerController::class, 'send_mail'])->name('mailer.send_mail');
        
        Route::post('/mailer/send_m',       [MailerController::class, 'send_m'])->name('mailer.send_m');
        Route::post('/mailer/extra_list',   [MailerController::class, 'extra_list'])->name('mailer.extra_list');
        
        Route::get('/mailer/create_model',  [MailerController::class, 'create_model'])->name('mailer.create_model');
        Route::post('/mailer/create_m',     [MailerController::class, 'create_m'])->name('mailer.create_m');

        Route::get('/mailer/edit_model/{id}', [MailerController::class, 'edit_model'])->name('mailer.edit_model');

        Route::post('/mailer/update_model',   [MailerController::class, 'update_model'])->name('mailer.update_model');
        Route::delete('/models/{id}',         [MailerController::class, 'delete'])->name('models.delete');
        
        Route::post('/settings/updateAll',     [SettingController::class, 'updateAll'])->name('settings.updateAll');

        Route::post('/reservations/cancel',    [ReservationController::class, 'cancel'])->name('reservations.cancel');

        // Gestione degli iscritti alle partite aperte
        Route::post('/reservations/{id}/participants',              [ReservationController::class, 'addParticipant'])->name('reservations.participants.store');
        Route::delete('/reservations/{id}/participants/{playerId}', [ReservationController::class, 'removeParticipant'])->name('reservations.participants.destroy');
        Route::post('/reservations/{id}/close-open',                [ReservationController::class, 'closeOpen'])->name('reservations.close_open');

        Route::post('/reservations/createFromD',    [ReservationController::class, 'createFromD'])->name('reservations.createFromD');
        Route::post('/settings/cancelDates',        [SettingController::class, 'cancelDates'])->name('settings.cancelDates');
        
        Route::get('/players/trainer_register',    [PlayerController::class, 'trainer_register'])->name('players.trainer_register');
        Route::post('/players/create_register',    [PlayerController::class, 'create_register'])->name('players.create_register');
        // Toglie l'accesso a un istruttore: sparisce l'utenza, non il giocatore.
        Route::delete('/trainers/{user}',          [SettingController::class, 'trainerDestroy'])
            ->middleware('role:admin')->name('trainers.destroy');

        


        // Tornei: iscritti, calendario e risultati
        Route::post('/tournaments/{tournament}/registrations',                       [TournamentController::class, 'registrationStore'])->name('tournaments.registrations.store');
        Route::post('/tournaments/{tournament}/registrations/{registration}/status', [TournamentController::class, 'registrationStatus'])->name('tournaments.registrations.status');
        Route::post('/tournaments/{tournament}/registrations/{registration}/paid',   [TournamentController::class, 'registrationPaid'])->name('tournaments.registrations.paid');

        Route::post('/tournaments/{tournament}/matches',                  [TournamentController::class, 'matchStore'])->name('tournaments.matches.store');
        Route::post('/tournaments/{tournament}/matches/{match}',          [TournamentController::class, 'matchUpdate'])->name('tournaments.matches.update');
        Route::delete('/tournaments/{tournament}/matches/{match}',        [TournamentController::class, 'matchDestroy'])->name('tournaments.matches.destroy');

        Route::post('/tournaments/{tournament}/reminder',                 [TournamentController::class, 'reminder'])->name('tournaments.reminder');

        // Campi fissi
        Route::post('/fixed-slots/{fixedSlot}/status',                       [FixedSlotController::class, 'status'])->name('fixed-slots.status');
        Route::post('/fixed-slots/{fixedSlot}/exceptions',                   [FixedSlotController::class, 'exceptionStore'])->name('fixed-slots.exceptions.store');
        Route::delete('/fixed-slots/{fixedSlot}/exceptions/{exception}',     [FixedSlotController::class, 'exceptionDestroy'])->name('fixed-slots.exceptions.destroy');
        Route::resource('fixed-slots', FixedSlotController::class)->parameters(['fixed-slots' => 'fixedSlot']);

        // Bacheca annunci
        Route::post('/listings/settings',              [ListingController::class, 'updateSettings'])->name('listings.settings');
        Route::post('/listings/{listing}/approve',     [ListingController::class, 'approve'])->name('listings.approve');
        Route::post('/listings/{listing}/reject',      [ListingController::class, 'reject'])->name('listings.reject');
        Route::post('/listings/{listing}/status',      [ListingController::class, 'status'])->name('listings.status');
        Route::delete('/listings/{listing}',           [ListingController::class, 'destroy'])->name('listings.destroy');
        Route::get('/listings/{listing}',              [ListingController::class, 'show'])->name('listings.show');
        Route::get('/listings',                        [ListingController::class, 'index'])->name('listings.index');

        Route::resource('tournaments',   TournamentController::class);
        Route::resource('reservations',  ReservationController::class);
        Route::resource('players',  PlayerController::class);

    });

Route::middleware('auth')
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });


require __DIR__ . '/auth.php';



