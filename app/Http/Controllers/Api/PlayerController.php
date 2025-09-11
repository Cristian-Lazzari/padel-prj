<?php

namespace App\Http\Controllers\Api;

use App\Mail\otpUser;
use App\Models\Player;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class PlayerController extends Controller
{
    public function verifyOtp(Request $request){

        $user = Player::where('nickname', $request->nickname)->first();

        if ($user->otp_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP scaduto',
            ]); 
        }
        if (password_verify($request->otp, $user->otp)) {
            // invalida OTP
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json(['success' => true]);
        }
        return response()->json([
            'success' => false,
            'message' => 'OTP errato',
        ]); 
    }

    public function login_client(Request $request)
    {
        $data = $request->all();
        $player = Player::where('nickname', $data['nickname'])->where('mail', $data['mail'])->first();

        if($player){

            $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $player->otp = password_hash($otp, PASSWORD_DEFAULT);
            $player->otp_expires_at = now()->addMinutes(5);
            $player->save();
            $contact = json_decode(Setting::where('name', 'Contatti')->first()->property);
            $bodymail = [
                'otp' => $player->otp,
                'email' => $player->email,
                'admin_phone' => $contact['telefono'],
            ];
           
            $mail = new otpUser($bodymail);
            Mail::to($data['email'])->send($mail);

            return response()->json([
                'success' => true,
                'message' => 'Mail per Login inviata con successo',
                'data' => $player
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Nessun utente trovato con queste credenziali',
                'data' => []
            ]); 
        }
    }
    public function search_nn(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        $players = Player::where('name', 'LIKE', "%{$query}%")
            ->orWhere('surname', 'LIKE', "%{$query}%")
            ->orWhere('nickname', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'surname', 'nickname']);
        foreach ($players as $p) {
            $p['user'] = false;
        }

        return response()->json($players);
    }
}
