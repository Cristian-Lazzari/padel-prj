<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

</head>
<body style="font-family: Arial, sans-serif; background-color: #e6e6e6; color: #04001d; margin: 0; padding: 10px 0 0 0;">
    <div style="max-width: 600px; margin: 10px auto; width:85%; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        
        <!-- Informazioni automatizzate -->
        <p style="font-size: 10px;  margin: 5px; color: #04001d80;">* questa email viene automaticamente generata dal sistema, si prega di non rispondere a questa email</p>
        <center>
            <img style="width: 100px; margin: 25px;" src="https://free-sport-padel.future-plus.it/img/favicon.png" alt="">
            
        </center>


        <h1 style="text-transform :uppercase; color: #04001d; font-size: 24px; margin-bottom: 12px">{{$content_mail['title']}}</h1>
        @if (isset($content_mail['subtitle']))
        <h4 style="color: #04001db9; font-size: 16px; margin-top: 0px">{{$content_mail['subtitle']}}</h4>
        @endif
        @php
            use Carbon\Carbon;
            //$dateString = "31/03/2025 18:00"; 
            $dateString = $content_mail['date_slot']; 
            $formattedDate = Carbon::createFromFormat('Y-m-d H:i', $dateString)
                ->locale('it')
                ->translatedFormat('l j F \a\l\l\e H:i');
        @endphp 
        <!-- Data prenotata -->
        <p style="color: #04001d; font-size: 18px; ">Data prenotata: 
            <strong style="color: #04001d; font-size: 20px; ">{{ ucfirst($formattedDate) }}</strong>
        </p>
        <p style="color: #04001d; font-size: 18px; ">Campo scelto: 
            <strong style="color: #04001d; font-size: 20px; ">{{ $content_mail['field'] }}</strong>
        </p>
        


   
        <!-- Messaggio opzionale -->
        @if($content_mail['message'] !== NULL)
            <h4 style="color: #04001d; font-size: 16px;  margin: 5px;">Messaggio:</h4>
            <p style="color: #04001d; font-size: 16px;  margin: 5px;">{{$content_mail['message']}}</p>
        @endif


        <!-- Se destinatario è admin -->
        @if($content_mail['to'] == 'admin')             
            <!-- Bottone per chiamare -->
            <a href="tel:{{$content_mail['phone']}}" style="display: block; width: 80%; text-align: center; padding: .8rem 1.6rem; background-color: #159478; font-size: 20px; font-weight:700; color: #f4f4f4; text-decoration: none; border-radius: 5px; margin: 5px auto;">Chiama {{$content_mail['name']}}</a>
            <!-- Bottone per visualizzare nella dashboard -->
            <a href="{{config('c.APP_URL')}}/admin/reservations/{{$content_mail['res_id']}}" style="display: block; width: 80%; text-align: center; padding: .8rem 1.6rem; background-color: #04001d; font-size: 20px; font-weight:700; color: #f4f4f4; text-decoration: none; border-radius: 5px; margin: 5px auto;">Visualizza nella Dashboard</a>

        @endif
        <!-- Se destinatario è user e la prenotazione è fatta a piu di 24 h dalla prenotazione -->
        @if ($content_mail['to'] == 'user' && $content_mail['status'] == 1 && $dateString >= now()->addHours($content_mail['max_delay_default'])->format('d/m/Y H:i'))
            <p style="font-size: 13px; color: #04001d; opacity: .7;" >* Entro e non oltre {{$content_mail['max_delay_default']}} ore dalla data prenotata puoi annullare la prenotazione in autonomia premendo questo bottone </p>
            <p style="margin: 5px 10px;">
                <a href="{{config('c.APP_URL')}}/api/client_default/?code={{$content_mail['res_id'] . '.' . $content_mail['booking_subject_id']}}" style="background-color: #9f2323d8; color: rgb(255, 255, 255); padding: 5px 16px; text-align: center; text-decoration: none; border-radius: 8px; font-size: 14px;">Annulla</a>
            </p>
        @endif


        
    </div>
    <!-- Footer -->
    <div style="margin: 50px auto 0; background-color: #04001d; color: white; padding: 10px; text-align: center; font-size: 12px;">
        @if ($content_mail['to'] == 'user')
            <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;">
                Per assistenza o informazioni contatta il nostro numero
            </p>
            <p style="color: #ffffff; line-height: 1.5; margin: 15px;">
                <a href="tel:{{$content_mail['admin_phone']}}" style="background-color: #ffffff; color: rgb(0, 0, 0); padding: 8px 12px; text-align: center; text-decoration: none; border-radius: 8px; font-size: 18px;">Chiama {{config('c.APP_NAME')}}</a>
            </p>
        @endif
        <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;">&copy; 2025 {{ config('c.APP_NAME') }}. Tutti i diritti riservati.</p>
        <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;" > Powered by <a style="color: white; text-decoration: none" href="https://future-plus.it">Future +</a></p>
    </div>
    
</body>
</html>
