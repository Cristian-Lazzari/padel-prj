<?php

namespace App\Http\Controllers\Admin;

use App\Models\Player;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlayerController extends Controller
{
    private $validations = [
        'nickname'   => 'required|string|min:2|unique:players,nickname',
        'mail'       => 'required|string|min:2',
        'phone'      => 'required|min:9',
        'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,svg,bmp,tiff|max:1024',
    ];

    private $validations_1 = [
        'nickname'          => 'required|string|min:2',
    ];
    public function index()
    {
        //
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

        if (isset($data['cretificate'])) {
            $cretificatePath = Storage::put('public/uploads', $data['cretificate']);
            $player->cretificate = $cretificatePath;
        } 
        $player->save();
        
        $m = 'Il giocatore "' . $data['name'] . '" è stato registrato correttamente';
        return to_route('admin.dashboard')->with('create_success', $m);      
    }

    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }

    
    public function destroy($id)
    {
        //
    }
}
