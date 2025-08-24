<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Conferma Email</title>
    <style>
        span.im{
            color: #04001d !important;
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif; background-color: #e9f0fb; color: #161c3e; margin: 0; padding: 10px 0px 0px; width: 100%;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="900" style="max-width: 900px;">
                    <tr>
                        <td align="center">
                            @php
                            $locale = 'it_IT';
                                $dataOdierna = new DateTime();
                                $formatter = new IntlDateFormatter(
                                    $locale,
                                    IntlDateFormatter::FULL, // Stile della data (es. FULL, LONG, MEDIUM, SHORT)
                                    IntlDateFormatter::NONE  // Nessun orario
                                ); 
                            @endphp 
                            {{-- logo --}}

                            <img style="width: 80px; margin: 25px; background-color: #090333; border-radius: 26px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.272); padding: 2px; border: solid 2px #090333;" src="{{config('c.APP_URL') . '/public/favicon.png'}}" alt="">


                            {{-- heading --}}
                            <h1 style="color: #04001d; font-size: 28px; padding: 20px;">Contratto da leggere e firmare per avviare il servizio di Future Plus!</h1>
                            
                            @if($consumer['img_1'] !== NULL) 
                            <center>
                                <img style="max-width:450px; border-radius: 10px; width: 60%; margin-top: 2rem; margin-bottom: 2rem;" src="{{config('configurazione.APP_URL') . '/public/storage/' . $consumer['img_1']}}" alt="">
                            </center>  
                            @endif
                            
                            {{-- corpo --}}
                            <div style="margin: 30px 25px; font-size: 20px; color: #04001d; text-align:start" class="corpo">
                                <span style="text-align:start; color: #04001d;" >Ciao {{$consumer['owner_name']}},</span>
                                <p style="text-align:start; color: #04001d;" >Ecco il contratto che regola il rapporto di consulenza tra <strong style="color: #04001d;">Future+</strong>  e <strong style="color: #04001d;">{{$consumer['activity_name']}}</strong></p>
                            </div>
                            
                            {{-- ending --}}
                            <p style="color: #04001d; font-size: 22px; text-align: center; margin: 30px;">Per avere ulteriori chiarimenti puoi contattarci tramite telefono e/o whatsapp ai contatti sottoindicati oppure prenota una call traminte il segunte pulsante</p>
                            
                            <table cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                  <td align="center"  >
                                    <a href="https://calendly.com/futureplus-commerciale/scopri-come-restaurant-puo-svoltare-il-tuo-lavoro" target="_blank" 
                                       style="display: inline-block; font-weight: 800; font-size: 18px; color: #e9f0fb; text-decoration: none; padding: 10px 24px; border-radius: 10px; background-color: #04001d;">
                                        PRENOTA UNA CALL
                                    </a>
                                  </td>
                                </tr>
                              </table>
                    
                            <div class="sender" style="color: #04001d; margin: 50px 0">
                                <p style="font-weight: 900; font-size: 18px; margin: 1rem 2rem 0">Inviato da: "Team Assistenza Future+"</p>
                                <p style="font-size: 18px; margin: 1rem 2rem 1rem" class="date">Tel/Wa:  <a style="font-weight: 800; color: #04001d; text-decoration:none" href="tel:393271622244">+39 3271622244</a></p>
                                <p style="font-style: italic; font-size: 15px; margin: 0 2rem 2rem; color: #04001db3;" class="date">{{$formatter->format($dataOdierna)}}</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>



    <footer style=" margin: 50px 0 0; background-color: #090333; color: white; padding: 10px 20px; text-align: center; font-size: 12px;">
        <h5 style="font-size: 16px; margin: 5px 0 8px;">Seguici sui social</h5>
        <div style="padding: 0 0 20px "> 
            <a style="color: white; text-decoration: none; margin: 0 auto;" href="https://www.facebook.com/profile.php?id=61558817374447">
                Facebook: <span style="color: white; font-weight: 900"> Future plus</span>
            </a>
            <a style="color: white; text-decoration: none; margin: 0 auto;" href="https://www.instagram.com/future.plus_/?hl=it">
                Instagram: <span style="color: white; font-weight: 900"> @future.plus_</span>
            </a>
        </div>
        <p style="font-size: 12px; font-family: monospace; line-height: 1.5; margin: 10px 5px;">&copy; 2024 {{ config('configurazione.APP_NAME') }}. Tutti i diritti riservati.</p>
        <p style="font-size: 15px; line-height: 1.5; margin: 5px;" > Powered by <a style="color: white; text-decoration: none; font-weight:900;" href="https://future-plus.it">Future +</a></p>
    </footer>
</body>
</html>

