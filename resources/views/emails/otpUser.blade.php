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
            <img style="width: 80px; margin: 25px; background-color: #090333; border-radius: 26px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.272); padding: 2px; border: solid 2px #090333;" src="{{config('configurazione.domain') . '/img/favicon.png'}}" alt="">
            
        </center>


        <h1 style="text-transform :uppercase; color: #04001d; font-size: 24px; margin-bottom: 12px; text-align: center">Ciao {{$consumer['nickname']}} inserisci questo codice per effettuare accedere con il tuo account <br> {{$consumer['otp']}}</h1>
        
    </div>
    <!-- Footer -->
    <div style="margin: 50px auto 0; background-color: #04001d; color: white; padding: 10px; text-align: center; font-size: 12px;">

        <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;">
            Per assistenza o informazioni contatta il nostro numero
        </p>
        <p style="color: #ffffff; line-height: 1.5; margin: 15px;">
            <a href="tel:{{$consumer['admin_phone']}}" style="background-color: #ffffff; color: rgb(0, 0, 0); padding: 8px 12px; text-align: center; text-decoration: none; border-radius: 8px; font-size: 18px;">Chiama {{config('configurazione.APP_NAME')}}</a>
        </p>

        <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;">&copy; 2025 {{ config('configurazione.APP_NAME') }}. Tutti i diritti riservati.</p>
        <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;" > Powered by <a style="color: white; text-decoration: none" href="https://future-plus.it">Future +</a></p>
    </div>
    
</body>
</html>
