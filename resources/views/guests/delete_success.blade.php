<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('public/favicon.png') }}" type="image/x-icon">
    <title>{{config('configurazione.APP_NAME')}}</title>
    @vite('resources/js/app.js')
</head>
<body>
    <header>
        <div class="container text-center my-5" >
            <h1>
                La tua prenotazione è stata annullata con successo
            </h1>

        </div>
    </header>


    

</body>
</html>
