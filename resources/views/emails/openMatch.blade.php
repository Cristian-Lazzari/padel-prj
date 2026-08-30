<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body style="font-family: Arial, sans-serif; background-color: #e6e6e6; color: #04001d; margin: 0; padding: 10px 0 0 0;">
    <div style="max-width: 600px; margin: 10px auto; width:85%; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">

        <p style="font-size: 10px; margin: 5px; color: #04001d80;">* questa email viene automaticamente generata dal sistema, si prega di non rispondere a questa email</p>

        <center>
            <img style="width: 100px; margin: 25px;" src="https://free-sport-padel.future-plus.it/img/favicon.png" alt="">
        </center>

        <h1 style="text-transform: uppercase; color: #04001d; font-size: 24px; margin-bottom: 12px">{{ $content_mail['title'] }}</h1>

        @if (!empty($content_mail['subtitle']))
            <h4 style="color: #04001db9; font-size: 16px; margin-top: 0px">{{ $content_mail['subtitle'] }}</h4>
        @endif

        <p style="color: #04001d; font-size: 18px;">Quando:
            <strong style="font-size: 20px;">{{ $content_mail['date_label'] }}</strong>
        </p>

        <p style="color: #04001d; font-size: 18px;">Campo:
            <strong style="font-size: 20px;">{{ $content_mail['field'] }}</strong>
        </p>

        <p style="color: #04001d; font-size: 18px;">Tipo:
            <strong style="font-size: 20px;">{{ $content_mail['category'] }}</strong>
        </p>

        <p style="color: #04001d; font-size: 18px;">Giocatori:
            <strong style="font-size: 20px;">{{ $content_mail['slots_taken'] }} / {{ $content_mail['slots_total'] }}</strong>
            <span style="color: #04001d80; font-size: 14px;">({{ $content_mail['level_label'] }})</span>
        </p>

        <div style="margin: 25px 0; padding: 15px; background-color: #f4f0f9; border-radius: 8px;">
            <p style="margin: 0; color: #04001d; font-size: 15px;">
                Puoi vedere l'elenco aggiornato degli iscritti nella sezione
                <strong>Partite aperte</strong> della web-app.
            </p>
        </div>

        @if (!empty($content_mail['admin_phone']))
            <center>
                <a href="tel:{{ $content_mail['admin_phone'] }}"
                   style="display: inline-block; background-color: #04001d; color: #ffffff; padding: 12px 18px; text-decoration: none; border-radius: 8px; font-size: 16px; margin-top: 10px;">
                    Chiama la struttura
                </a>
            </center>
        @endif
    </div>
</body>
</html>
