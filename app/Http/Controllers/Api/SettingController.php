<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(){
        $setting = Setting::all()->keyBy('name');
        $field_arr=[];
        $field_set = Setting::fieldSet();
        foreach ($field_set as $k => $f) {
            $field_arr[] = $f['type'];
        }
        $trainers = User::where('role', 'trainer')->get();
        return response()->json([
            'success' => true,
            'type_of_field' => array_unique($field_arr),
            'service' => $setting['Servizio di Prenotazione Online']->status,
            'social' => $setting['Contatti']->property,
            'trainers' => $trainers,
        ]);
    }

    /**
     * Link "Annulla" della mail di conferma: code = "idPrenotazione.idGiocatore".
     * Stesse regole del bottone nell'app; risponde sempre con una pagina
     * leggibile, perché lo apre il giocatore dal telefono.
     */
    public function client_default(Request $request) {
        $code = (string) $request->query('code', '');
        [$res_id, $player_id] = array_pad(explode('.', $code, 2), 2, null);

        $reservation = ($res_id && $player_id)
            ? Reservation::where('id', $res_id)->where('booking_subject', $player_id)->first()
            : null;

        if (! $reservation) {
            return response()->view('guests.delete_success', [
                'ok' => false,
                'text' => 'Non abbiamo trovato la prenotazione. Controlla il link oppure contatta il circolo.',
            ], 404);
        }

        $hours = Reservation::cancelHours();

        if ($motivo = $reservation->playerCancelBlock($hours)) {
            return view('guests.delete_success', ['ok' => false, 'text' => $motivo]);
        }

        $reservation->status = 0;
        $reservation->is_open = false;
        $reservation->save();

        ReservationController::notifyCancel($reservation, $hours);

        return view('guests.delete_success', [
            'ok' => true,
            'text' => $reservation->field.' del '.Carbon::createFromFormat('Y-m-d H:i', $reservation->date_slot)->format('d/m/Y \a\l\l\e H:i').': lo slot è di nuovo libero.',
        ]);
    }
}
