<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Player;
use App\Models\Reservation;
use App\Services\AvatarService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\Setting;

class PlayerController extends Controller
{

    private $validations_trainer = [
        'password'   => 'required|confirmed|min:8',
        'nickname'   => 'required|string|min:2|unique:players,nickname',
        'mail'       => 'required|string|min:5|unique:players,mail',
        'phone'      => 'required|min:9',
        'name'       => 'required|string',
        'surname'    => 'required|string',
        'level'      => 'required|numeric|min:1|max:5',
        'sex'        => 'required',
        'certificate'=> 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,svg,bmp,tiff|max:1024',
        'hand'       => 'nullable|in:dx,sx',
        'preferred_position' => 'nullable|in:dritto,rovescio,indifferente',
        'city'       => 'nullable|string|max:60',
        'bio'        => 'nullable|string|max:500',
        'birth_date' => 'nullable|date|before:today',
        'certificate_expires_at' => 'nullable|date',
        'img'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
    ];
    private $validations = [
        'nickname'   => 'required|string|min:2|unique:players,nickname',
        'mail'       => 'required|string|min:5|unique:players,mail',
        'phone'      => 'required|min:9',
        'name'       => 'required|string',
        'surname'    => 'required|string',
        'level'      => 'required|numeric|min:1|max:5',
        'sex'        => 'required',
        'certificate'=> 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,svg,bmp,tiff|max:1024',
        'hand'       => 'nullable|in:dx,sx',
        'preferred_position' => 'nullable|in:dritto,rovescio,indifferente',
        'city'       => 'nullable|string|max:60',
        'bio'        => 'nullable|string|max:500',
        'birth_date' => 'nullable|date|before:today',
        'certificate_expires_at' => 'nullable|date',
        'img'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
    ];
    private $validations_1 = [
        'nickname'   => 'required|string|min:2',
        'mail'       => 'required|string|min:2',
        'phone'      => 'required|min:9',
        'name'       => 'required|string',
        'surname'    => 'required|string',
        'level'      => 'required|numeric|min:1|max:5',
        'sex'        => 'required',
        'certificate'=> 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,svg,bmp,tiff|max:1024',
        'hand'       => 'nullable|in:dx,sx',
        'preferred_position' => 'nullable|in:dritto,rovescio,indifferente',
        'city'       => 'nullable|string|max:60',
        'bio'        => 'nullable|string|max:500',
        'birth_date' => 'nullable|date|before:today',
        'certificate_expires_at' => 'nullable|date',
        'img'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
    ];
    public function index()
    {
        $players = Player::all();
        return view('admin.Players.index', compact('players'));
    }

    
     public function create()
    {

        return view('admin.Players.create');
    }
    
    
    public function create_register(Request $request){ // store del trainer
        $data = $request->all();
        $request->validate($this->validations_trainer);

        $player = new Player();
        $player->nickname = $data['nickname'];
        $player->surname = $data['surname'];
        $player->name = $data['name'];
        $player->phone = $data['phone'];
        $player->mail = $data['mail'];

        $player->level = $data['level'];
        $player->sex = $data['sex'];
        $player->save();
        $u = new User();
        $u->name = $data['name'];
        $u->surname = $data['surname'];
        $u->password = Hash::make($data['password']);
        $u->phone = $data['phone'];
        $u->email = $data['mail'];
        
        $u->playerId = $player->id;
        $u->save();
        return to_route('admin.settings');

    }
    public function trainer_register(){
        return view('admin.Players.trainer_registration');
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $request->validate($this->validations);
        
        
        $player = new Player();
        $player->nickname = $data['nickname'];
        $player->surname = $data['surname'];
        $player->name = $data['name'];
        $player->phone = $data['phone'];
        $player->mail = $data['mail'];
        $player->note = $data['note'];
        $player->level = $data['level'];
        $player->sex = $data['sex'];
        
        $this->fillProfileFields($player, $data);
        $this->storeCertificate($request, $player);
        $this->storeAvatar($request, $player);

        $player->save();

        if (isset($data['add_new'])) {
            $m = 'Il giocatore "' . $data['nickname'] . '" è stato registrato correttamente. Puoi aggiungerne un altro';
            return to_route('admin.players.create')->with('create_success', $m);      
        }
        
        $m = 'Il giocatore "' . $data['nickname'] . '" è stato registrato correttamente';
        return to_route('admin.players.index')->with('message', $m);      
    }
    
    public function show($id)
    {
        $player = Player::with(['reservations' => function ($query) {
            $query->orderBy('date_slot', 'desc'); // più recente → più vecchia
        }])->find($id);
        $dinner_off = Setting::where('name', 'Impostazioni cena')->first()->status;
        $player_reservations = Reservation::where('booking_subject',$id)->with('players')->orderBy('date_slot', 'desc')->get();

        $field_set = json_decode(Setting::where('name', 'advanced')->first()->property, 1)['field_set'];

        return view('admin.Players.show', compact('player', 'player_reservations', 'dinner_off', 'field_set'));
    }
    
    
    public function edit($id)
    {
        $player = Player::findOrFail($id);
        return view('admin.Players.edit', compact('player'));
    }
    
    
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate($this->validations_1);
        
        
        $player = Player::findOrFail($id);
        $player->nickname = $data['nickname'];
        $player->surname = $data['surname'];
        $player->name = $data['name'];
        $player->phone = $data['phone'];
        $player->mail = $data['mail'];
        $player->level = $data['level'];
        $player->note = $data['note'];
        $player->sex = $data['sex'];
        
        $this->fillProfileFields($player, $data);
        $this->storeCertificate($request, $player);

        if ($request->boolean('remove_img')) {
            $player->deleteImgFile();
            $player->img = null;
        } else {
            $this->storeAvatar($request, $player);
        }

        $player->update();

        $message = 'Il giocatore "' . $data['nickname'] . '" è stato modificato correttamente';
        return to_route('admin.players.index')->with('message', $message);      
    }

    
    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        
        // stacca tutte le associazioni con le reservations
        $player->reservations()->detach();
        $player->deleteImgFile();
        $player->delete();

        $m = 'Il giocatore "' . $player->nickname . '" è stato eliminato correttamente';
        return to_route('admin.players.index')->with('message', $m);      
    }

    /**
     * Valorizza i campi profilo facoltativi condivisi fra store e update.
     */
    private function fillProfileFields(Player $player, array $data)
    {
        foreach (['hand', 'preferred_position', 'city', 'bio', 'birth_date', 'certificate_expires_at'] as $field) {
            if (array_key_exists($field, $data)) {
                $player->{$field} = $data[$field] !== '' ? $data[$field] : null;
            }
        }
    }

    /**
     * Salva il certificato medico sul disco pubblico, sostituendo il precedente.
     */
    private function storeCertificate(Request $request, Player $player)
    {
        if (! $request->hasFile('certificate')) {
            return;
        }

        if ($player->certificate) {
            Storage::disk('public')->delete($player->certificate);
        }

        $player->certificate = $request->file('certificate')->store('certificates', 'public');
    }

    /**
     * Salva la foto profilo riusando lo stesso servizio dell'area cliente.
     */
    private function storeAvatar(Request $request, Player $player)
    {
        if (! $request->hasFile('img')) {
            return;
        }

        $player->img = app(AvatarService::class)->store($request->file('img'), $player);
    }
}
