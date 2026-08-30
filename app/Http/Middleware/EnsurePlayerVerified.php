<?php

namespace App\Http\Middleware;

use App\Models\Player;
use Closure;
use Illuminate\Http\Request;

/**
 * Blocca le funzioni di prenotazione ai giocatori che non hanno ancora
 * verificato la propria email tramite OTP.
 */
class EnsurePlayerVerified
{
    public function handle(Request $request, Closure $next)
    {
        $playerId = $request->input('user_id', $request->input('id'));

        if (! $playerId) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non identificato',
            ]);
        }

        $player = Player::find($playerId);

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
            ]);
        }

        if (! $player->isMailVerified()) {
            return response()->json([
                'success' => false,
                'requires_verification' => true,
                'message' => 'Devi verificare la tua email prima di prenotare o iscriverti a una partita. Controlla la posta o richiedi un nuovo codice.',
            ]);
        }

        return $next($request);
    }
}
