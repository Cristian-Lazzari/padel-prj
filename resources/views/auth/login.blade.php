@extends('layouts.guestNoNav')

@section('contents')
<div class="login-page">
    <div class="hero">
        <div class="center_hero">
            <img src="{{ asset('public/logo.png') }}" alt="">
            <div class="right">
                <form method="post" action="{{ route('login') }}">
                    @csrf
            
                    <div class="input_form input_login">
                        <label for="email" class="">Email</label>
                        <input
                            type="email"
                            class=""
                            id="email"
                            aria-describedby="emailHelp"
                            name="email"
                            placeholder="Inserisci la tua email"
                            required
                            autocomplete="username"
                            value="{{ old('email') }}"
                        >
                    </div>
            
                    <div class="input_form input_login">
                        <label for="password" class="">Password</label>
                        <input
                            type="password"
                            class=""
                            id="password"
                            name="password"
                            placeholder="Inserisci la tua password"
                            required
                            autocomplete="current-password"
                        >
                    </div>
            
                    <div class=" form-check">
                        <input type="checkbox" class="form-check-input" id="remember"  name="remember">
                        <label class="form-check-label" for="remember">Rimani registrato</label>
                    </div>
            
                    <div class="act">

                        {{-- <a href="{{route('register')}}" class="my_btn_5 ">Registrati</a> --}}
                        <button type="submit" class="my_btn_7">Login</button>
                    </div>
                </form>    
            </div>
        </div>

    </div>

    
</div>


@endsection
