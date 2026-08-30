<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Mail\otpUser;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use App\Services\AvatarService;
use App\Models\FixedSlot;
use App\Services\FixedSlotService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    /** Durata di validità dell'OTP, in minuti. */
    private const OTP_TTL = 5;

    /** Secondi da attendere fra due invii OTP per lo stesso giocatore. */
    private const OTP_COOLDOWN = 60;

    /** Numero massimo di OTP inviabili in un'ora allo stesso giocatore. */
    private const OTP_HOURLY_LIMIT = 5;

    // ==========================================================
    // OTP — logica condivisa fra login e registrazione
    // ==========================================================

    /**
     * Genera un OTP a 4 cifre, lo salva hashato sul giocatore e lo invia
     * via mail. Usato sia dal login sia dalla registrazione.
     */
    private function sendOtp(Player $player): void
    {
        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $player->otp = password_hash($otp, PASSWORD_DEFAULT);
        $player->otp_expires_at = now()->addMinutes(self::OTP_TTL)->format('Y-m-d H:i:s');
        $player->otp_sent_at = now();
        $player->save();

        $contactSetting = Setting::where('name', 'Contatti')->first();
        $contact = $contactSetting ? json_decode($contactSetting->property, 1) : [];

        $bodymail = [
            'otp' => $otp,
            'email' => $player->mail,
            'nickname' => $player->nickname,
            'admin_phone' => $contact['phone'] ?? null,
        ];

        Mail::to($bodymail['email'])->send(new otpUser($bodymail));
    }

    /**
     * Verifica l'OTP inviato dal giocatore. In caso di successo lo invalida.
     * Restituisce null se valido, altrimenti il messaggio di errore.
     */
    private function checkOtp(Player $player, ?string $otp): ?string
    {
        if (! $otp) {
            return 'Inserisci il codice ricevuto via email';
        }

        if (! $player->otp || ! $player->otp_expires_at) {
            return 'Nessun codice attivo, richiedine uno nuovo';
        }

        if (Carbon::parse($player->otp_expires_at)->isPast()) {
            return 'OTP scaduto';
        }

        if (! password_verify($otp, $player->otp)) {
            return 'OTP errato';
        }

        $player->otp = null;
        $player->otp_expires_at = null;
        $player->save();

        return null;
    }

    /**
     * Applica il rate limit sull'invio OTP: massimo 1 ogni 60 secondi e
     * 5 all'ora per giocatore. Restituisce null se l'invio è consentito,
     * altrimenti il messaggio da mostrare all'utente.
     */
    private function otpRateLimit(Player $player): ?string
    {
        if ($player->otp_sent_at) {
            $elapsed = Carbon::parse($player->otp_sent_at)->diffInSeconds(now(), false);

            if ($elapsed >= 0 && $elapsed < self::OTP_COOLDOWN) {
                $wait = self::OTP_COOLDOWN - $elapsed;

                return 'Attendi '.$wait.' secondi prima di richiedere un nuovo codice';
            }
        }

        $key = 'otp-hourly:'.$player->id;
        $sent = (int) cache()->get($key, 0);

        if ($sent >= self::OTP_HOURLY_LIMIT) {
            return 'Hai richiesto troppi codici, riprova fra un\'ora';
        }

        cache()->put($key, $sent + 1, now()->addHour());

        return null;
    }

    /**
     * Payload standard restituito al frontend dopo l'invio di un OTP.
     */
    private function otpPayload(Player $player): array
    {
        return [
            'nickname' => $player->nickname,
            'mail' => $player->mail,
            'expires_in' => self::OTP_TTL * 60,
            'resend_in' => self::OTP_COOLDOWN,
        ];
    }

    // ==========================================================
    // LOGIN
    // ==========================================================

    public function verifyOtp(Request $request)
    {
        $user = Player::where('nickname', $request->nickname)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Nessun utente trovato con queste credenziali',
            ]);
        }

        $error = $this->checkOtp($user, $request->otp);

        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ]);
        }

        // Chi accede via OTP ha dimostrato di controllare la casella:
        // i profili storici vengono marcati come verificati al primo login.
        if (! $user->isMailVerified()) {
            $user->mail_verified_at = now();
            $user->save();
        }

        return response()->json([
            'success' => true,
            'user' => $user->fresh(),
        ]);
    }

    public function login_client(Request $request)
    {
        $data = $request->all();
        $player = Player::where('nickname', $data['nickname'] ?? '')
            ->where('mail', $data['mail'] ?? '')
            ->first();

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Nessun utente trovato con queste credenziali',
                'data' => [],
            ]);
        }

        if ($limit = $this->otpRateLimit($player)) {
            return response()->json([
                'success' => false,
                'message' => $limit,
                'data' => [],
            ]);
        }

        $this->sendOtp($player);

        return response()->json([
            'success' => true,
            'message' => 'Mail per Login inviata con successo',
            'data' => $player,
            'otp' => $this->otpPayload($player),
        ]);
    }

    // ==========================================================
    // REGISTRAZIONE CON VERIFICA MAIL
    // ==========================================================

    public function register(Request $request)
    {
        $data = $request->all();

        $existingPlayer = Player::where('nickname', $data['nickname'] ?? '')
            ->orWhere('mail', $data['mail'] ?? '')
            ->first();

        if ($existingPlayer) {
            // Se l'account esiste ma non ha mai completato la verifica,
            // non è un vero duplicato: si riparte da lì.
            $sameAccount = $existingPlayer->nickname === ($data['nickname'] ?? null)
                && $existingPlayer->mail === ($data['mail'] ?? null);

            if ($sameAccount && ! $existingPlayer->isMailVerified()) {
                if ($limit = $this->otpRateLimit($existingPlayer)) {
                    return response()->json([
                        'success' => false,
                        'message' => $limit,
                    ]);
                }

                $this->sendOtp($existingPlayer);

                return response()->json([
                    'success' => true,
                    'message' => 'Ti abbiamo inviato un nuovo codice di verifica',
                    'requires_verification' => true,
                    'data' => $existingPlayer,
                    'otp' => $this->otpPayload($existingPlayer),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nickname o email già in uso',
            ]);
        }

        $validator = validator($data, [
            'nickname' => 'required|string|min:2|max:15|unique:players,nickname',
            'mail'     => 'required|email|max:50|unique:players,mail',
            'phone'    => 'required|string|min:8|max:25',
            'name'     => 'required|string|min:2|max:50',
            'surname'  => 'required|string|min:2|max:50',
            'sex'      => ['required', Rule::in(['m', 'f'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ]);
        }

        $newPlayer = new Player();
        $newPlayer->name = $data['name'];
        $newPlayer->surname = $data['surname'];
        $newPlayer->nickname = $data['nickname'];
        $newPlayer->mail = $data['mail'];
        $newPlayer->sex = $data['sex'];
        $newPlayer->phone = $data['phone'] ?? null;
        $newPlayer->mail_verified_at = null;
        $newPlayer->save();

        try {
            $this->sendOtp($newPlayer);
        } catch (\Throwable $e) {
            Log::error('Invio OTP registrazione fallito: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Account creato ma non siamo riusciti a inviare il codice. Riprova fra qualche minuto.',
                'requires_verification' => true,
                'data' => $newPlayer,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ti abbiamo inviato un codice di verifica via email',
            'requires_verification' => true,
            'data' => $newPlayer,
            'otp' => $this->otpPayload($newPlayer),
        ]);
    }

    /**
     * Completa la registrazione verificando l'OTP inviato per mail.
     */
    public function registerVerify(Request $request)
    {
        $player = Player::where('nickname', $request->nickname)
            ->where('mail', $request->mail)
            ->first();

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Nessun utente trovato con queste credenziali',
            ]);
        }

        if ($player->isMailVerified()) {
            return response()->json([
                'success' => true,
                'message' => 'Account già verificato',
                'user' => $player,
            ]);
        }

        $error = $this->checkOtp($player, $request->otp);

        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ]);
        }

        $player->mail_verified_at = now();
        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Account verificato con successo',
            'user' => $player->fresh(),
        ]);
    }

    /**
     * Reinvio del codice OTP (login o registrazione), con rate limit.
     */
    public function resendOtp(Request $request)
    {
        $player = Player::where('nickname', $request->nickname)
            ->where('mail', $request->mail)
            ->first();

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Nessun utente trovato con queste credenziali',
            ]);
        }

        if ($limit = $this->otpRateLimit($player)) {
            return response()->json([
                'success' => false,
                'message' => $limit,
            ]);
        }

        try {
            $this->sendOtp($player);
        } catch (\Throwable $e) {
            Log::error('Reinvio OTP fallito: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Non siamo riusciti a inviare il codice. Riprova fra qualche minuto.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nuovo codice inviato',
            'otp' => $this->otpPayload($player),
        ]);
    }

    // ==========================================================
    // AREA CLIENTE
    // ==========================================================

    public function account(Request $request)
    {
        $data = $request->all();
        $player = Player::where('id', $data['id'] ?? null)->with('reservations')->first();

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
                'data' => [],
            ]);
        }

        $reservations = Reservation::where('booking_subject', $player->id)->with('players')->get();
        $player->reservations_play = $reservations;
        $player->logged = true;

        return response()->json([
            'success' => true,
            'data' => $player,
        ]);
    }

    /**
     * Aggiornamento dei dati profilo dall'area cliente.
     */
    public function updateAccount(Request $request)
    {
        $player = Player::find($request->id);

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
            ]);
        }

        $validator = validator($request->all(), [
            'name'    => 'sometimes|required|string|min:2|max:50',
            'surname' => 'sometimes|required|string|min:2|max:50',
            'phone'   => 'sometimes|required|string|min:8|max:25',
            'sex'     => ['sometimes', 'required', Rule::in(['m', 'f'])],
            'birth_date' => 'sometimes|nullable|date|before:today',
            'hand'    => ['sometimes', 'nullable', Rule::in(['dx', 'sx'])],
            'preferred_position' => ['sometimes', 'nullable', Rule::in(['dritto', 'rovescio', 'indifferente'])],
            'city'    => 'sometimes|nullable|string|max:60',
            'bio'     => 'sometimes|nullable|string|max:500',
            'certificate_expires_at' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ]);
        }

        // Nickname e mail restano gestiti dal back office: sono le credenziali di accesso.
        foreach ($validator->validated() as $field => $value) {
            $player->{$field} = $value;
        }

        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Profilo aggiornato',
            'data' => $player->fresh(),
        ]);
    }

    /**
     * Upload della foto profilo (max 4MB, jpg/png/webp, ridimensionata a 512px).
     */
    public function uploadPhoto(Request $request, AvatarService $avatars)
    {
        $player = Player::find($request->id);

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
            ]);
        }

        $validator = validator($request->all(), [
            'photo' => 'required|file|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            $path = $avatars->store($request->file('photo'), $player);
        } catch (\Throwable $e) {
            Log::error('Upload foto profilo fallito: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Non siamo riusciti a elaborare l\'immagine. Riprova con un altro file.',
            ]);
        }

        $player->img = $path;
        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profilo aggiornata',
            'img_url' => $player->fresh()->img_url,
            'data' => $player->fresh(),
        ]);
    }

    /**
     * Rimozione della foto profilo.
     */
    public function deletePhoto(Request $request)
    {
        $player = Player::find($request->id);

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
            ]);
        }

        $player->deleteImgFile();
        $player->img = null;
        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profilo rimossa',
            'data' => $player->fresh(),
        ]);
    }

    /**
     * POST api/account/fixed-slot
     * Campo fisso del giocatore con le prossime date.
     */
    public function fixedSlot(Request $request, FixedSlotService $slots)
    {
        $playerId = (int) $request->input('user_id', $request->input('id'));

        if (! $playerId) {
            return response()->json(['success' => false, 'message' => 'Utente non identificato']);
        }

        $slot = FixedSlot::where('player_id', $playerId)
            ->whereIn('status', ['active', 'suspended'])
            ->with('exceptions')
            ->orderByRaw("FIELD(status, 'active', 'suspended')")
            ->first();

        if (! $slot) {
            return response()->json(['success' => true, 'data' => null]);
        }

        $setting = Setting::where('name', 'advanced')->first();
        $fieldSet = $setting ? (json_decode($setting->property, true)['field_set'] ?? []) : [];
        $minutes = $fieldSet[$slot->field]['m_during'] ?? 30;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $slot->id,
                'field' => $slot->field,
                'weekday' => $slot->weekday,
                'weekday_label' => $slot->weekdayLabel(),
                'start_time' => $slot->start_time,
                'end_time' => $slot->endTime($minutes),
                'status' => $slot->status,
                'status_label' => $slot->statusLabel(),
                'valid_from' => optional($slot->valid_from)->format('Y-m-d'),
                'valid_to' => optional($slot->valid_to)->format('Y-m-d'),
                'price' => $slot->price !== null ? (float) $slot->price : null,
                'note' => $slot->note,
                'next_dates' => collect($slots->nextDates($slot, 6))
                    ->map(fn ($d) => $d->format('Y-m-d'))
                    ->values(),
                'exceptions' => $slot->exceptions
                    ->filter(fn ($e) => $e->date->gte(now()->startOfDay()))
                    ->map(fn ($e) => [
                        'date' => $e->date->format('Y-m-d'),
                        'reason' => $e->reason,
                        'reason_label' => $e->reasonLabel(),
                    ])->values(),
            ],
        ]);
    }

    // ==========================================================
    // RICERCA GIOCATORI
    // ==========================================================

    public function search_nn(Request $request)
    {
        $data = $request->all();
        $query = $data['q'];

        if (!$query) {
            return response()->json([]);
        }

        $columns = ['id', 'name', 'surname', 'nickname', 'img'];

        if(isset($data['players'])){
            $q_p = $data['players'];
            if (is_string($q_p)) {
                $q_p = json_decode($q_p, true);
            }
            $q_p = array_values($q_p ?? []); // normalizza

            $players = Player::where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('surname', 'LIKE', "%{$query}%")
                    ->orWhere('nickname', 'LIKE', "%{$query}%");
                })
                ->when(!empty($q_p), fn($q) => $q->whereNotIn('id', $q_p))
                ->limit(5)
                ->get($columns);
        }else{
            $players = Player::where('name', 'LIKE', "%{$query}%")
                ->orWhere('surname', 'LIKE', "%{$query}%")
                ->orWhere('nickname', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get($columns);
        }

        foreach ($players as $p) {
            $p['user'] = false;
        }
        return response()->json($players);
    }
}
