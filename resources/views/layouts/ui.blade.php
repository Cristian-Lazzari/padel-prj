{{--
    Shell del back office nel nuovo linguaggio visivo.
    Vive accanto a layouts/base.blade.php e non lo sostituisce: le pagine si
    convertono una famiglia alla volta cambiando @extends, e quelle non ancora
    convertite continuano a girare intatte sul layout storico.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#090333">
    <link rel="shortcut icon" href="{{ asset('public/favicon.png') }}" type="image/x-icon">
    <title>@yield('title', 'F+ - db+')</title>

    {{-- app.scss porta ancora Bootstrap (griglia, modali) e le classi storiche --}}
    @vite('resources/js/app.js')

    @include('admin.partials.ui-style')
    @yield('styles')
</head>
<body class="ui-body">

    @include('admin.partials.ui-nav')

    {{-- Le larghezze di colonna della schermata viaggiano inline in --ui-cols --}}
    <main class="ui-page" @hasSection('page_vars') style="@yield('page_vars')" @endif>
        @yield('contents')
    </main>

    <script>
        // Il foglio globale inverte i colori su data-theme="dark": il back office
        // resta chiaro-su-scuro, ma l'attributo va tenuto allineato al layout storico
        // per non far saltare le pagine non ancora convertite.
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>

    @yield('scripts')
</body>
</html>
