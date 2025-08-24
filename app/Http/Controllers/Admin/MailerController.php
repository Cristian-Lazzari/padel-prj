<?php

namespace App\Http\Controllers\Admin;

use App\Models\Model;
use App\Models\Order;
use App\Models\Setting;
use App\Mail\BuildableMail;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class mailerController extends Controller
{
    private $validations = [
        'name'              => 'required|string|min:1|max:50',
        'object'            => 'required|string|min:1|max:150',
        'heading'           => 'required|string|min:1|max:150',
        'img_1'             => 'nullable|image|max:1012',
        'img_2'             => 'nullable|image|max:1012',
        'body'              => 'required|string|min:1',
        'ending'            => 'required|string|min:1',
        'sender'            => 'required|string|min:1|max:50',
    ];
    private $validations_send = [
        'recipients'    => 'required|min:1',
        'models'        => 'required',
    ];
    
    public function mailer()
    {

        $last_mail_list = [];
        $extra_mail_list = [];

        $models = Model::all();

        // Conta il numero totale di utenti
        $old_mail = Setting::where('name', 'email_marketing')->first();
        if($old_mail !== null){
            $prop = json_decode($old_mail->property);
            $last_mail_list = $prop->last_mail_list;
            $extra_mail_list = $prop->extra_mail_list;
            //dd($prop->last_mail_list);
            return view('admin.Mailer.index', compact('models', 'last_mail_list', 'extra_mail_list'));   
        }else{
            $new_set = [
                'name' => 'email_marketing',  
                'status' => 1,
                'property' => [
                    'last_mail_list' => [],
                    'extra_mail_list'=> [],
                ],
            ];
            $new_set['property'] = json_encode($new_set['property']);
            Setting::create($new_set);
            return view('admin.Mailer.index', compact('models', 'last_mail_list', 'extra_mail_list'));   
        }
    }
    public function create_model(){
        return view('admin.Mailer.createModel');
    }
    public function send_mail(){
        $models = Model::all();

        // Conta il numero totale di utenti
        $old_mail = Setting::where('name', 'email_marketing')->first();
        if($old_mail !== null){
            $prop = json_decode($old_mail->property);
            $last_mail_list = $prop->last_mail_list;
            $extra_mail_list = $prop->extra_mail_list;

            $n_c = [
                count($extra_mail_list),
                count($last_mail_list),
            ];
            
            return view('admin.Mailer.send', compact('models', 'n_c'));
        }else{
            $n_c = [
                count($reservation_users),
                count($order_users),
                0,
                0
            ];
               
            return view('admin.Mailer.send', compact('models', 'n_c'));
        }
    }

    public function create_m(Request $request)
    {
        $request->validate($this->validations);
        $data = $request->all();

        if (isset($data['img_1'])) {
            $img_1_path = Storage::put('public/uploads', $data['img_1']);
        }else{
            $img_1_path = NULL;
        } 

        if (isset($data['img_2'])) {
            $img_2_path = Storage::put('public/uploads', $data['img_2']);
        }else{
            $img_2_path = NULL;
        } 
        
        $model = [
            'name' => $data['name'],
            'object' => $data['object'],
            'heading' => $data['heading'],
            'body' => $data['body'],
            'ending' => $data['ending'],
            'sender' => $data['sender'],
            'img_1' => $img_1_path,
            'img_2' => $img_2_path,
        ];

        Model::create($model);

        $m = 'Il modello "' . $data['name'] . '" è stato creato correttamente';
        return to_route('admin.mailer.index')->with('create_success', $m);   
    }

    public function extra_list(Request $request){
        $data = $request->all();
       

        $old_mail = Setting::where('name', 'email_marketing')->first();
        $prop = json_decode($old_mail->property);
        $new_list = [];
        if(isset($data['recipients'])){
            foreach ($data['recipients'] as $r) {
                array_push($new_list, json_decode($r));
            }
        }
        $prop->extra_mail_list = $new_list;
        $old_mail->property = json_encode($prop);
        $old_mail->update();

        $m = 'Lista aggiornata correttamente';
        return to_route('admin.mailer.index')->with('extra', $m);   

    }

    public function send_m(Request $request)
    {
        $request->validate($this->validations_send);
        $data = $request->all();
        

        $last_mail_list = [];
        $extra_mail_list = [];

        // Conta il numero totale di utenti
        $old_mail = Setting::where('name', 'email_marketing')->first();

        $prop = json_decode($old_mail->property);
        $extra_mail_list = $prop->extra_mail_list;
        $last_mail_list = $prop->last_mail_list;
        

            
        $recipients = [];
        if(in_array(1, $data['recipients'])){
            array_push($recipients, $reservation_users);
        }
        if(in_array(2, $data['recipients'])){
            array_push($recipients, $order_users);
        }
        if(in_array(3, $data['recipients'])){
            array_push($recipients, $extra_mail_list);
        }
        if(in_array(4, $data['recipients'])){
            array_push($recipients, $last_mail_list);
        }
    
        // Trasformazione in un unico array eliminando duplicati basati sull'email
        $recipients_unique = collect($recipients) // Trasformiamo in una collection
            ->flatten(1) // Appiattiamo di un livello
            ->unique('email') // Rimuoviamo duplicati sulla base dell'email
            ->values() // Reindicizziamo l'array
            ->toArray(); // Ritorniamo l'array normale
        $n_contact = count($recipients_unique);

        $model = Model::where('id', $data['models'])->first();

        $contentMail = [
            'name' => '',
            'object' => $model['object'],
            'heading' => $model['heading'],
            'body' => explode("/*/", $model['body']),
            'ending' => $model['ending'],
            'sender' => $model['sender'],
            'img_1' => $model['img_1'],
            'img_2' => $model['img_2'],
        ];

        //necessario per trasformare tutti i contatti in array
        $contatti = collect($recipients_unique)->map(function ($contatto) {
            return (array) $contatto;
        })->toArray();

        foreach ($contatti as $c ) {  
            $contentMail['name'] = $c['name'];
            $mail = new BuildableMail($contentMail);
            Mail::to($c['email'])->send($mail);
        }
        
        
        $old_mail = Setting::where('name', 'email_marketing')->first();
        if($old_mail == null){
            $new_set = [
                'name' => 'email_marketing',  
                'status' => 1,
                'property' => [
                    'last_mail_list' => $recipients_unique,
                    'extra_mail_list'=> [],
                ],
            ];
            $new_set['property'] = json_encode($new_set['property']);
            Setting::create($new_set);
        }else{
            $new_prop = json_decode($old_mail->property);
            $new_prop->last_mail_list = $recipients_unique;
            
            $old_mail->property = json_encode($new_prop);
            $old_mail->update();
        }
        $m = 'Sono state correttamente inviate ' . $n_contact . ' email';
        return to_route('admin.mailer.index')->with('send_success', $m);   
    }

    public function edit_model($id = null){
        // dd($id);
        $model = Model::where('id', $id)->first();
        return view('admin.Mailer.editModel', compact('model'));
    }

    public function update_model(Request $request){
        $request->validate($this->validations);
        $data = $request->all();
        $model = Model::where('id', $data['id'])->first();


        if (isset($data['img_1'])) {
            $img_1_path = Storage::put('public/uploads', $data['img_1']);
            if ($model->img_1) {
                Storage::delete($model->img_1);
            }
            $model->img_1 = $img_1_path;
        }

        if (isset($data['img_2'])) {
            $img_2_path = Storage::put('public/uploads', $data['img_2']);
            if ($model->img_2) {
                Storage::delete($model->img_2);
            }
            $model->img_2 = $img_2_path;
        }


        $model->name = $data['name'];
        $model->object = $data['object'];
        $model->heading = $data['heading'];
        $model->body = $data['body'];
        $model->ending = $data['ending'];
        $model->sender = $data['sender'];

        

        $model->update();

        $m = 'Il modello "' . $data['name'] . '" è stato modificato correttamente';
        return to_route('admin.mailer.index')->with('create_success', $m);   
    }

    public function delete($id)
    {
        $model = Model::find($id);

        if (!$model) {
            return response()->json(['message' => 'Record non trovato'], 404);
        }

        $model->delete();
        $m = 'Modello eliminato con successo';
        return to_route('admin.mailer.index')->with('extra', $m);
    }

}
