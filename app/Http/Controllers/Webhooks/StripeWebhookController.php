<?php

namespace App\Http\Controllers\Webhooks;

use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Date;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class StripeWebhookController extends Controller
{   
    public function handleStripeWebhook(Request $request)
    {
        try {
        // La tua chiave segreta di Stripe
        $stripeSecretKey = config('configurazione.STRIPE_SECRET'); 
        
        Log::warning(" SESSIONE CONTROLLER");
        // Imposta la chiave segreta di Stripe
        Stripe::setApiKey($stripeSecretKey);

        // Ottieni il corpo della richiesta
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Il tuo endpoint segreto (ottenuto quando configuri il webhook)
        $endpointSecret = config('configurazione.STRIPE_WEBHOOK_SECRET'); // Inserisci il tuo endpoint segreto

        try {
            // Verifica il webhook
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            // Il payload non è valido
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // La firma non è valida
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Gestisci gli eventi pertinenti
        if($event->type == 'checkout.session.completed'){
            $session = $event->data->object; // contiene i dettagli della sessione
            return $this->handleCheckoutSessionCompleted($session);
               
        }
        elseif($event->type == 'payment_intent.payment_failed'){
            $paymentIntent = $event->data->object; // contiene i dettagli del pagamento
            return $this->handlePaymentIntentFailed($paymentIntent);
        }

        

        //return response()->json(['status' => 'success'], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore del database: ' . $e->getMessage(),
            ]);
        } catch (Exception $e) {
            $trace = $e->getTrace();
            $errorInfo = [
                'success' => false,
                'error' => 'Si è verificato un errore durante l\'elaborazione della richiesta: ' . $e->getMessage(),

            ];

            return response()->json($errorInfo, 500);
        }
    }



    protected function handleCheckoutSessionCompleted($session)
    {
        // Aggiorna il tuo database per segnare l'ordine come completato
        $orderId = $session->metadata->order_id; // Assicurati di aver aggiunto l'ID dell'ordine nei metadata
        $order = Order::where('id', $orderId)->with('products')->firstOrFail();
        $date = Date::where('date_slot', $order->date_slot)->first();
        $vis = json_decode($date->visible, true);
        $av = json_decode($date->availability, true);
        $res = json_decode($date->reserving, true);

        // aggiorno la disponibilità in date
        if(config('configurazione.typeOfOrdering')){
            $res_c1 = $res['cucina_1'];
            $res_c2 = $res['cucina_2'];
            $av_c1  = $av['cucina_1'];
            $av_c2  = $av['cucina_2'];
            // Inizializza i contatori
            $np_c1  = 0;
            $np_c2  = 0;
            // Cicla sui prodotti associati all'ordine
            foreach ($order->products as $product) {
                // return $product;
                // Controlla il tipo di cucina del prodotto
                if ($product->type_plate == 1) {
                    $np_c1 += $product->pivot->quantity * $product->slot_plate;
                } elseif ($product->type_plate == 2) {
                    $np_c2 += $product->pivot->quantity * $product->slot_plate;
                }
            }
            
            if(isset($order->comune)){
                if( ($res['domicilio'] + 1) < $av['domicilio']){
                    $res['domicilio'] = $res['domicilio'] + 1;
                } else{
                    $res['domicilio'] = $res['domicilio'] + 1;
                    $vis['domicilio'] = 0;
                }
            }
            if((($res_c1 + $np_c1) < $av_c1) && (($res_c2 + $np_c2) < $av_c2)){}
            elseif((($res_c1 + $np_c1) == $av_c1) && (($res_c2 + $np_c2) < $av_c2)){
                $vis['cucina_1'] = 0;
            }elseif((($res_c2 + $np_c2) == $av_c2) && (($res_c1 + $np_c1) < $av_c1)){
                $vis['cucina_2'] = 0;
            }else{
                $vis['cucina_1'] = 0;
                $vis['cucina_2'] = 0;
            }
            $res['cucina_1'] += $np_c1;
            $res['cucina_2'] += $np_c2;
            
        }else{
            if(isset($order->comune)){
                if(($res['domicilio'] + 1) < $av['domicilio']){}
                else{
                    $vis['domicilio'] = 0;
                }
                $res['domicilio'] = $res['domicilio'] + 1;
            }else{
                if(($res['asporto'] + 1) < $av['asporto']){}
                else{
                    $vis['asporto'] = 0;
                }
                $res['asporto'] = $res['asporto'] + 1;
            }
        }
        $date->visible = json_encode($vis);
        $date->reserving = json_encode($res);
        $date->update();

        // Esegui la logica per aggiornare lo stato dell'ordine nel database
        
        $info = $order->name . ' ' . $order->surname .' ha ordinato *e PAGATO* per il ' . $order->date_slot . ": \n\n";
        $order_mess = "";
        $type_mess = "";
        $lastProduct = end($order->products);
        foreach ($order->products as $product) {
            // Aggiungi il nome e la quantità del prodotto
            $info .= "☞ ";
            $order_mess .= "☞ ";
            if ($product->pivot->quantity !== 1) {
                $info .= "** {$product->pivot->quantity}* ";
                $order_mess .= "** {$product->pivot->quantity}* ";
            }
            $info .= "*```" . $product->name. "```*";
            $order_mess .= "*```" . $product->name. "```*";

            // Gestisci le opzioni del prodotto
            if ($product->pivot->option !== '[]') {
                $options = json_decode($product->pivot->option);
                $info .= "\n ```Opzioni:``` " . implode(', ', $options);
                $order_mess .= " ```Opzioni:``` " . implode(', ', $options);
            }
            // Gestisci gli ingredienti aggiunti
            if ($product->pivot->add !== '[]') {
                $addedIngredients = json_decode($product->pivot->add);
                $info .= "\n ```Aggiunte:``` " . implode(', ', $addedIngredients);
                $order_mess .= " ```Aggiunte:``` " . implode(', ', $addedIngredients);
            }
            // Gestisci gli ingredienti rimossi
            if ($product->pivot->remove !== '[]') {
                $removedIngredients = json_decode($product->pivot->remove);
                $info .= "\n ```Rimossi:``` " . implode(', ', $removedIngredients);
                $order_mess .= " ```Rimossi:``` " . implode(', ', $removedIngredients);
            }
            // Separatore tra i prodotti
            $info .= " \n\n";
            $order_mess .= " " . " ";
        }
        if($order->comune){
            $info .= "Consegna a domicilio GIÀ PAGATO: {$order->address}, {$order->address_n}, {$order->comune} ";
            $type_mess .= "Consegna a domicilio GIÀ PAGATO: {$order->address}, {$order->address_n}, {$order->comune} ";
        }else{
            $info .= "Ritiro asporto";
            $type_mess .= "Ritiro asporto";
        }
        $link_id = config('configurazione.APP_URL') . '/admin/orders/' . $order->id;
        $t = $order->comune ? "Ordine a domicilio *GIÀ PAGATO*" : "Ordine d'asporto *GIÀ PAGATO*";
        $info = 'Contenuto della notifica: *_' . $t . "_* \n\n" . $info . "\n\n" .
            "📞 Chiama: " . $order->phone . "\n\n" .
            "🔗 Vedi dalla Dashboard: $link_id";
        // Definisci l'URL della richiesta
        $url = 'https://graph.facebook.com/v20.0/'. config('configurazione.WA_ID') . '/messages';

        $numbers_wa_set_s = Setting::where('name', 'wa')->firstOrFail();
        $numbers_wa_set = json_decode($numbers_wa_set_s->property, true);

        $data_i = [
            'messaging_product' => 'whatsapp',
            'to' => '',
            "type"=> "interactive",
            "interactive"=> [
                "type"=> "button",
                "header"=> [
                    "type" => "text",
                    "text"=>'Hai una nuova notifica!',
                ],  
                "footer"=> [
                    "text"=> "Powered by F+"
                ],
                "body"=> [
                "text"=> $info,
                ],
                    "action"=> [
                    "buttons"=> [
                        [
                            "type"=> "reply",
                            "reply"=> [
                                "id"=> "Conferma",
                                "title"=> "Conferma"
                            ]
                        ],
                            [
                            "type"=> "reply",
                            "reply"=> [
                                "id"=> "Annulla",
                                "title"=> "Annulla"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $data_t = [
            'messaging_product' => 'whatsapp',
            'to' => '',
            'category' => 'utility',
            'type' => 'template',
            'template' => [
                'name' => 'full_emoji',
                'language' => [
                    'code' => 'it'
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $order->comune ? 'Ordine a domicilio *GIÀ PAGATO*' : 'Ordine d\'asporto *GIÀ PAGATO*', 
                            ],
                            [
                                'type' => 'text',
                                'text' => $order->name . ' ' . $order->surname . ' ha ordinato per il ' . $order->date_slot  . ': '
                            ],
                            [
                                'type' => 'text',
                                'text' => $order_mess
                            ],
                            [
                                'type' => 'text',
                                'text' => $type_mess
                            ],
                            [
                                'type' => 'text',
                                'text' => $order->phone,  
                            ],
                            [
                                'type' => 'text',
                                'text' => $link_id,  
                            ],
                        ]
                    ]
                ]
            ]
        ];
        
        $n = 0;
        $messageId = [];
        $type_m_1 = false;
        $type_m_2 = false;
        foreach ($numbers_wa_set['numbers'] as $num) {
            $data_t['to'] = $num;
            $data_i['to'] = $num;
            if($this->isLastResponseWaWithin24Hours($n)){
                if($n == 1){
                    $type_m_1 = 0;
                }else{     
                    $type_m_2 = 0;
                }
                $response = Http::withHeaders([
                    'Authorization' => config('configurazione.WA_TO'),
                    'Content-Type' => 'application/json'
                ])->post($url, $data_i);
                $m_id = $response->json()['messages'][0]['id'] ?? null;
                if($m_id){
                    array_push($messageId, $m_id);
                }
            }else{
                if($n == 1){
                    $type_m_1 = 1;
                }else{     
                    $type_m_2 = 1;
                }
                $response = Http::withHeaders([
                    'Authorization' => config('configurazione.WA_TO'),
                    'Content-Type' => 'application/json'
                ])->post($url, $data_t);
                $m_id = $response->json()['messages'][0]['id'] ?? null;
                if($m_id){
                    array_push($messageId, $m_id);
                }
            }
            $n ++;
        }
        
        
        
        $order->whatsapp_message_id = json_encode($messageId);
        $order->update();

        $data_am1 = [        
            'wa_id' => $order->whatsapp_message_id,
            'type_1' => $type_m_1,
            'type_2' => $type_m_2,
            'source' => config('configurazione.APP_URL'),
        ];
        $order->checkout_session_id = $session->payment_intent;
        $order->status = 3;
        $order->update();
        $this->send_mail($order);
        // Log dei dati inviati
        Log::info('Invio richiesta POST a https://db-demo4.future-plus.it/api/messages', $data_am1);
        
        try {
            // Log dei dati inviati
            Log::info('Dati inviati alla API:', $data_am1);
            
            // Invio della richiesta POST
            $response_am1 = Http::post('https://db-demo4.future-plus.it/api/messages', $data_am1);
        
            // Controllo della risposta prima di restituirla
            if ($response_am1->successful()) {
                Log::info('Risposta ricevuta con successo:');
                Log::info($response_am1);
             //   Log::info('Risposta ricevuta con successo:', $response_am1);
                return response()->json([
                    'status' => 'success',
                    'success' => true,
                    'data' => $response_am1->json(),
                ]);
            } else {
                Log::error('Errore nella risposta API:', [
                    'status' => $response_am1->status(),
                    'body' => $response_am1->body(),
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Errore dalla API esterna.',
                ], $response_am1->status());
            }
        } catch (Exception $e) {
            // Gestione degli errori
            Log::error('Errore nell\'invio della richiesta POST:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return response()->json([
                'status' => 'error',
                'message' => 'Errore durante l\'invio della richiesta.',
            ], 500);
        }
        
    }

    protected function send_mail($order){
                                
        
        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        
        $bodymail = [
            'type' => 'or',
            'to' => 'admin',

            'title' =>  $order->name . ' ha appena ordinato e PAGATO un ordine ' . ($order->comune ? 'a domicilio' : 'd\'asporto'),
            'subtitle' => '',

            'order_id' => $order->id,
            'name' => $order->name,
            'surname' => $order->surname,
            'email' => $order->email,
            'date_slot' => $order->date_slot,
            'message' => $order->message,
            'phone' => $order->phone,
            'admin_phone' => $p_set['telefono'],
            
            'comune' => $order->comune,
            'address' => $order->address,
            'address_n' => $order->address_n,
            
            'status' => $order->status,
            'cart' => $order->products,
            'total_price' => $order->tot_price,
        ];

        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to(config('configurazione.mail'))->send($mail);

        $bodymail['to'] = 'user';
        $bodymail['whatsapp_message_id'] = $order->whatsapp_message_id;
        $bodymail['title'] = 'Ciao ' . $order->name . ', grazie per aver ordinato tramite il nostro sito web';
        $bodymail['subtitle'] = 'Il tuo ordine è nella nostra coda, a breve riceverai l\'esito del processamento';

        $mailAdmin = new confermaOrdineAdmin($bodymail);
        Mail::to($order->email)->send($mailAdmin);
    }
    protected function handlePaymentIntentFailed($paymentIntent){
        // Recupera l'ID dell'ordine dai metadata
        $orderId = $paymentIntent->metadata->order_id; // Assicurati che l'ID sia correttamente passato nei metadata
    
        // Trova l'ordine e, se esiste, eliminalo
        $order = Order::where('id', $orderId)->first();
    
        if ($order) {
            $order->delete();
        }
    }
    
    protected function isLastResponseWaWithin24Hours($n)
    {
        $setting = Setting::where('name', 'wa')->first();

        if ($setting) {
            // Decodifica il campo 'property' da JSON ad array
            $property = json_decode($setting->property, true);
            if($n == 0){
                 // Controlla se 'last_response_wa' è impostato
                if (isset($property['last_response_wa_1']) && !empty($property['last_response_wa_1'])) {
                    // Confronta la data salvata con le ultime 24 ore
                    $lastResponseDate = Carbon::parse($property['last_response_wa_1']);
                    return $lastResponseDate->greaterThanOrEqualTo(Carbon::now()->subHours(24));
                }
            }else{
                 // Controlla se 'last_response_wa' è impostato
                if (isset($property['last_response_wa_2']) && !empty($property['last_response_wa_2'])) {
                    // Confronta la data salvata con le ultime 24 ore
                    $lastResponseDate = Carbon::parse($property['last_response_wa_2']);
                    return $lastResponseDate->greaterThanOrEqualTo(Carbon::now()->subHours(24));
                }
            }
        }else{
            return false; // Se il record non esiste o la data non è impostata
        }
    }

}
