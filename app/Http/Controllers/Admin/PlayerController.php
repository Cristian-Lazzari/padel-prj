<?php

namespace App\Http\Controllers\Admin;

use App\Models\Player;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    private $validations = [
        'nickname'   => 'required|string|min:2|unique:players,nickname',
        'mail'       => 'required|string|min:2',
        'phone'      => 'required|min:9',
        'name'       => 'required|string',
        'surname'    => 'required|string',
        'level'      => 'required|numeric|min:1|max:5',
        'certificate'=> 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,svg,bmp,tiff|max:1024',
    ];
    
    private $validations_1 = [
        'nickname'          => 'required|string|min:2',
        'mail'       => 'required|string|min:2',
        'phone'      => 'required|min:9',
        'name'       => 'required|string',
        'surname'    => 'required|string',
        'level'      => 'required|numeric|min:1|max:5',
        'certificate'=> 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,svg,bmp,tiff|max:1024',
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
        
        if (isset($data['cretificate'])) {
            $cretificatePath = Storage::put('public/uploads', $data['cretificate']);
            $player->cretificate = $cretificatePath;
        } 
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
        $player = Player::findOrFail($id);
        $player_reservations = Reservation::where('booking_subject',$id)->with('players')->get();
        return view('admin.Players.show', compact('player', 'player_reservations'));
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
        
        if (isset($data['cretificate'])) {
            if($player->cretificate){
                Storage::delete($player->cretificate);
            }
            $cretificatePath = Storage::put('public/uploads', $data['cretificate']);
            $player->cretificate = $cretificatePath;
        } 
        $player->update();

        $message = 'Il giocatore "' . $data['nickname'] . '" è stato modificato correttamente';
        return to_route('admin.players.index')->with('message', $message);      
    }

    
    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $player->delete();

        // stacca tutte le associazioni con le reservations
        $player->reservations()->detach();

        $m = 'Il giocatore "' . $player->nickname . '" è stato eliminato correttamente';
        return to_route('admin.dashboard')->with('message', $m);      
    }
}
