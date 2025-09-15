<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Mail\otpUser;
use App\Models\Player;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class PlayerController extends Controller
{
    public function verifyOtp(Request $request){

        $user = Player::where('nickname', $request->nickname)->first();
        $expire = Carbon::parse($user->otp_expires_at); // Convert to Carbon instance
        if ($expire < now()) {
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

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
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
            $player->otp_expires_at = now()->addMinutes(5)->format('Y-m-d H:i:s');
            $player->update();
            $contact = json_decode(Setting::where('name', 'Contatti')->first()->property, 1);
            $bodymail = [
                'otp' => $otp,
                'email' => $player->mail,
                'nickname' => $player->nickname,
                'admin_phone' => $contact['telefono'],
            ];
           
            $mail = new otpUser($bodymail);
            Mail::to($bodymail['email'])->send($mail);

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
        $data = $request->all();
        $query = $data['q'];
        $players = $data['players'];

        if (!$query) {
            return response()->json([]);
        }

        $players = Player::where('name', 'LIKE', "%{$query}%")
            ->orWhere('surname', 'LIKE', "%{$query}%")
            ->orWhere('nickname', 'LIKE', "%{$query}%")
            ->whereNotIn('id', $players)
            ->limit(5)
            ->get(['id', 'name', 'surname', 'nickname']);
        foreach ($players as $p) {
            $p['user'] = false;
        }

        return response()->json($players);
    }

    public function register(Request $request){
        $data = $request->all();
        $existingPlayer = Player::where('nickname', $data['nickname'])->orWhere('mail', $data['mail'])->first();
        if($existingPlayer){
            return response()->json([
                'success' => false,
                'message' => 'Nickname o email già in uso',
            ]); 
        }
        $newPlayer = new Player();
        $newPlayer->name = $data['name'];
        $newPlayer->surname = $data['surname'];
        $newPlayer->nickname = $data['nickname'];
        $newPlayer->mail = $data['mail'];
        $newPlayer->phone = $data['phone'] ?? null;
        $newPlayer->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Registrazione avvenuta con successo',
            'data' => $newPlayer
        ]);
    }
}
