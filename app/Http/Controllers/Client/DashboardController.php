<?php

namespace App\Http\Controllers\Client;

use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Customer;
use App\Models\Consumer;
use Stripe\Subscription;
use Stripe\PaymentMethod;
use App\Mail\ContractEmail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function home(Request $request) {
        $consumers = Consumer::where('user_id', Auth::user()->id)->get();
        $new_c = Consumer::where('user_id', Auth::user()->id)->where('r_property', 'like', '%' . '"activation_date":""' . '%')->first();
        $final = $request->query('final', 1);
        if($final == 5){
            $step = [ 'step' => 5, 'm' => 'Complimenti hai completato la registrazione a future+'];
            return view('client.dashboard', compact('step', 'consumers'));
        }

        //dd($new_c);
        if($new_c){
            $r_p_f = json_decode($new_c->r_property, 1);
            if($new_c->vat == null || $new_c->status == 0 || $r_p_f['stripe_id'] == ''){
                
                if($new_c->vat == null)             { $s = 1; } 
                elseif($new_c->status == 0)         { $s = 2; } 
                elseif($r_p_f['stripe_id'] == '')   { $s = 3; } 
                else                                { $s = 6; }
                
                $step = [
                    'step' => $s,
                    'm' => null
                ];
                $consumer = $new_c;
                return view('client.dashboard', compact('step', 'consumers', 'consumer'));
            }
        }
        $step = [
            'step' => 7,
            'm' => null
        ];
        return view('client.dashboard', compact('step','consumers'));
    }
    public function delete_sub(Request $request) {

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $consumer = Consumer::where('id', $request['id'])->firstOrFail();
            
            $r_p = json_decode($consumer->r_property, 1) ;
            if (!$r_p['stripe_id']) {
                return redirect()->back()->with('error', 'Utente non associato a Stripe.');
            }
            $subscription = Subscription::retrieve($r_p['subscription_id']);
            
            if ($subscription->status !== 'canceled') {
                $subscription->cancel();
                $r_p['activation_date'] = '';
                $r_p['renewal_date']    = '';
                $r_p['stripe_id']       = '';
                $r_p['subscription_id'] = '';
                
                $consumer->status = 0;
                $consumer->r_property = json_encode($r_p);
                $consumer->update();

                return redirect()->back()->with('info', 'Abbonamento annullato con successo.');
            }
            return redirect()->back()->with('info', 'L\'abbonamento è già stato annullato.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Errore: ' . $e->getMessage());
        }
    }

    public function create_new(Request $request) {
        $user_id = Auth::user()->id;
        $consumers = Consumer::where('user_id', $user_id)->get();
        
        $r_property = [
            'day_service' => [],
            'r_type' => [],
            'activation_date' => '',
            'renewal_date' => '',
            'stripe_id' => '',
            'subscription_id' => ''
        ];

        $consumer = Consumer::create([
            'user_id' => $user_id,
            'activity_name' => $request->activity_name,
            'r_property' => json_encode($r_property)
        ]);
        $step = [
            'step' => 1,
            'm' => null
        ];
        return redirect()->back()->with('new', $consumers, $consumer, $step);
        //return view('client.dashboard', compact());

    }
    public function complete_registration(Request $request) {
        $consumer = Consumer::where('user_id', Auth::user()->id)->where('id', $request->id)->first();
   
        $data = $request;
        $step = $data['step'];
        $edit = $data['edit'];
        
        // dump($step);
        // dd($data);
        if($step == 1){ $step = $this->step_1($data, $consumer);
        } elseif($step == 2){ $step = $this->step_2($data, $consumer); 
        } elseif($step == 3){ $step = $this->step_3($data, $consumer);
            if($step['error'] == 'none'){
                return response()->json([
                    'success' => true, 
                ]);
            }else{
                return response()->json([
                    'success' => false, 
                    'error' => $step['error']
                ]);
            }
        } 
        if($edit){
            $step['m'] = 'I tuoi dati sono stai aggiornati correttamente';
            $step['step'] = 6;
        }
        return back()->with('success', $step);
        
        
    }

    protected function step_1($data, $consumer){
        $data->validate([
            'type_agency' => ['required'],
            'vat' => ['required', 'string', 'max:25'],
            'address' => ['required', 'string', 'max:255'],
            'pec' => ['required', 'string', 'email', 'max:205'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_phone' => ['required', 'string', 'max:255'],
            'owner_surname' => ['required', 'string', 'max:255'],
            'owner_cf' => ['required', 'string', 'max:20'],

            'menu' => ['nullable', 'array'], // Deve essere un array di file
            'menu.*' => ['file', 'mimes:jpeg,jpg,docx,xlsx', 'max:5120'], // Ogni file deve essere un'immagine max 2MB
            'domain' => ['required'],
        ]);
        
        $consumer->owner_name = $data->owner_name;
        $consumer->owner_phone = $data->owner_phone;
        $consumer->owner_surname = $data->owner_surname;
        $consumer->owner_cf = $data->owner_cf;
        $consumer->type_agency = $data->type_agency;
        $consumer->address = $data->address;
        $consumer->vat = $data->vat;
        $consumer->pec = $data->pec;

        $info = $this->parseCodiceFiscale($data->owner_cf);

        $consumer->owner_bd = $info['data_nascita'] ?? null;
        $consumer->owner_sex = $info['genere'] ?? null;
        $consumer->owner_cm = '??'; //da implementare

        $paths = [];

        if ($data->hasFile('menu')) {
            $files = is_array($data->file('menu')) ? $data->file('menu') : [$data->file('menu')];
            foreach ($files as $image) {
                if ($image && $image->isValid()) {
                    $path = $image->store('menus', 'public'); 
                    $paths[] = $path;
                }
            }
        }
        
        $day_service = [];
        
        $consumer->menu = json_encode(array_values($paths), JSON_UNESCAPED_SLASHES);
        $consumer->services_type = $data['services_type'];
        $domain = [
            'domain'=>$data['domain'],
            'type_domain'=>$data['type_domain']
        ];
        $consumer->domain = json_encode($domain);
        $consumer->update();
        //dd($consumer);
        $step = [
            'step'=> 2,
            'm'=> 'Scegli il pacchetto che più si adatta alle esigenze, indipendentemente dalla scelta che prenderai ora nei 30 giorni di prova gratuita potrai usufruire di tutte le funzioni del piano Boost Up'
        ];
        return $step; 
    }

    protected function step_2($data, $consumer){
        // impostare pacchetto e tipo fatturazione (mensile / annuale)
        // $status = [ 'niente ancora', 'Essntials | Y', 'Work on | Y', 'Boost up | Y' 'Essntials | M', 'Work on | M', 'Boost up | M'];
        $ty = $data['type_pay'];
        $consumer->status = $data['pack'] + ($ty == 1 ? 0 : 3);
        $consumer->update();
        $step = [
            'step'=> 3,
        ];
        return $step;
    }

    protected function step_3($data, $consumer){
        try {
            Stripe::setApiKey(config('c.STRIPE_SECRET'));
            $customer = Customer::create([ // Creazione del cliente su Stripe
                'email' => auth()->user()->email,
                'payment_method' => $data->payment_method, // Associa metodo di pagamento
                'invoice_settings' => ['default_payment_method' => $data->payment_method] // Assicura che venga usato per i pagamenti automatici
            ]);
            $price_list= [
                '',
                'price_1OlsotCusoKCSgsdwj59SwgF',
                'price_1QMbNVCusoKCSgsdpIav4RY4',
                'price_1QMdb1CusoKCSgsdYXeDCz9r',
                
                'price_1QMbKICusoKCSgsdgIZXpYq1',
                'price_1QMdY0CusoKCSgsdbeXqOjtW',
                'price_1QMde1CusoKCSgsdpY5oJfEm',
            ];
            // Creazione dell'abbonamento con pagamento automatico
            $subscription = Subscription::create([
                'customer' => $customer->id,
                //'items' => [['price' => 'price_1OlsrICusoKCSgsdH4AUgm4Q']], // id di test
                'items' => [['price' => $price_list[$consumer->status]]], // id corretti
                'off_session' => true, // Nessuna conferma manuale
                'automatic_tax' => ['enabled' => false],
                'trial_period_days' => 30, // Imposta il numero di giorni di prova gratuita
            ]);
            //dd($subscription);
            
            // Salvataggio dei dati nel DB dell'utente
            $r_p = json_decode($consumer->r_property, 1) ;
            $r_p['activation_date'] = Carbon::now()->format('Y-m-d');
            $r_p['renewal_date']    = Carbon::now()->addDays(30)->format('Y-m-d');
            $r_p['stripe_id']       = $customer->id;
            $r_p['subscription_id'] = $subscription->id;
            $consumer->r_property = json_encode($r_p);
            $consumer->update();

            $this->send_contract($consumer);
            
            $step = [
                'error'=> 'none',
                'step'=> 5,
                'm'=> 'Complimenti hai completato con successo la registrazione'
            ];
            return $step;

        } catch (\Exception $e) {
            $step = [
                'error'=> $e->getMessage(),
                'step'=> 5,
                'm'=> 'Complimenti hai completato con successo la registrazione'
            ];
            return $step;
        }
        // impostare pacchetto e tipo fatturazione (mensile / annuale)


        
    }

    protected function parseCodiceFiscale($cf){
        if (strlen($cf) !== 16) {
            return null; // Codice fiscale non valido
        }
        // Estrarre anno, mese, giorno e codice comune
        $anno = substr($cf, 6, 2);
        $mese = substr($cf, 8, 1);
        $giorno = intval(substr($cf, 9, 2));
        $codice_comune = substr($cf, 11, 4);
        // Tabella conversione per il mese
        $mesi = [
            'A' => '01', 'B' => '02', 'C' => '03', 'D' => '04', 'E' => '05', 'H' => '06',
            'L' => '07', 'M' => '08', 'P' => '09', 'R' => '10', 'S' => '11', 'T' => '12'
        ];
        // Controllare il mese valido
        if (!isset($mesi[$mese])) {
            return null; // Mese non valido
        }
        // Determinare il genere
        $genere = ($giorno > 31) ? 'F' : 'M';
        if ($giorno > 31) {
            $giorno -= 40;
        }
        // Determinare l'anno completo (assumiamo che il CF sia valido dal 1900 in poi)
        $anno = ($anno > date('y')) ? "19$anno" : "20$anno";
        $info = [
            'data_nascita' => "$anno-$mesi[$mese]-" . str_pad($giorno, 2, '0', STR_PAD_LEFT),
            'genere' => $genere,
            'comune' => $codice_comune
        ];

        return $info;
    }

    protected function send_contract($consumer)
    {
        // Percorso della cartella dove salvare il PDF
        $directory = storage_path('app/contratti');
    
        // Controlla se la cartella esiste, altrimenti la crea
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    
        // Percorso completo del file PDF
        $filePath = $directory . '/contratto_' . $consumer->id . '.pdf';
    
        // Genera il PDF
        $pdf = Pdf::loadView('pdf.contract', compact('consumer'));
        
        // Salva il PDF nel percorso specificato
        $pdf->save($filePath);
    
        // Invia l'email con il PDF allegato
        Mail::to(auth()->user()->email)->send(new ContractEmail($filePath, $consumer));

        
    
        return response()->json(['message' => 'Contratto inviato con successo']);
    }
    
}
