
@extends('layouts.base')

@section('contents')
<div class="page_nav"  id="modal1" >

    <div class="registration-modal">
        <div class="top">
            <h1>Informazioni sul tuo account Future+</h1>
            
        </div>
        <div class="body">
                <h3>Modifica i tuoi dati</h3>

                <form  id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                

                <form class="form-reg" method="post" action="{{ route('admin.profile.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')
                    <div class="split">
                        <div class="input_form">
                            <label for="name" :value="__('Name')" > Nome </label>
                            <input id="name" name="name" type="text" value="{{old('name', $user->name)}}" required  autocomplete="name" />
                        </div>

                        @error('name') <p class="error w-100">{{ $message }}</p> @enderror
                        @error('surname') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="full">

                            <label for="email" :value="__('Email')" > Email </label>
                            <input id="email" name="email" type="text" value="{{old('email', $user->email)}}" required  autocomplete="email" />
                            @error('email') <p class="error w-100">{{ $message }}</p> @enderror


                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div>
                                <p class="text-sm mt-2 text-gray-800">
                                    {{ __('Your email address is unverified.') }}

                                    <button form="send-verification" class="my_btn_1">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </button>
                                </p>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 font-medium text-sm text-green-600">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <div class="full">
                        {{-- scegli un colore per il tuo accaount (flag) --}}
                        <label for="flag" :value="__('Flag')" > Colore account <span style="--flag: {{$user->flag}}" class="square"> </span> </label>
                        <style>
                            .square{
                                --flag : #23B792;
                                width:20px;
                                height:20px;
                                margin-left: 3px;
                                display:inline-block;
                                margin-right:10px;
                                border-radius: 3px;
                                background-color: var(--flag);
                            }
                        </style>
                        <select id="flag" name="flag" required>
                            @php
                                $colors = [
                                    'future +' => '#23B792',
                                    'giallo oro' => '#F5C542',
                                    'giallo lime' => '#D6FF3F',
                                    'verde menta' => '#3FF4C6',
                                    'turchese acceso' => '#1DE9B6',
                                    'azzurro elettrico' => '#4DA3FF',
                                    'viola neon' => '#9B5CFF',
                                    'fucsia' => '#FF4FD8',
                                    'corallo' => '#FF6B4A',
                                    'arancio acceso' => '#FF9F1C',
                                    'rosso lampone' => '#E63946',
                                ];
                            @endphp
                            @foreach ($colors as $key => $value)
                                <option class="square" value="{{$value}}" {{ old('flag', $user->flag) == $value ? 'selected' : '' }}>{{$key}} </option>
                            @endforeach
                        </select>
                    </div>


                    <button type="submit" class="my_btn_4">Modifica</button>

                        @if (session('status') === 'profile-updated')

                            <div class="alert alert-dismissible fade show alert-primary m-auto" role="alert">
                                Modifica avventa riuscita con successo
                            </div>

                        @endif

                </form>

                <h3>Modifica la tua password</h3>



                <form class="form-reg" method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('put')

                        <div class="input_form">
                            <label for="current_password"  :value="__('Current Password')"> Password attuale </label>
                            <input id="current_password" name="current_password" type="text" required  autocomplete="current_password" />
                        </div>
                        @error('current_password') <p class="error w-100">{{ $message }}</p> @enderror

                        <div class="input_form">
                            <label for="password" :value="__('New Password')"> Nuova password </label>
                            <input id="password" name="password" type="text" required  autocomplete="password" />
                        </div>
                        @error('password') <p class="error w-100">{{ $message }}</p> @enderror
                        <div class="input_form">
                            <label for="password_confirmation"  :value="__('Confirm Password')"> Ripeti nuova password </label>
                            <input id="password_confirmation" name="password_confirmation" type="text" required  autocomplete="password_confirmation" />
                        </div>
                        @error('password_confirmation') <p class="error w-100">{{ $message }}</p> @enderror




                        <button type="submit" class="my_btn_4">Modifica</button>

                        @if (session('status') === 'password-updated')

                            <div class="alert alert-dismissible fade show alert-primary m-auto" role="alert">
                                Modifica avventa riuscita con successo
                            </div>

                        @endif

                </form>
                <p>Esci da questo account</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="my_btn_7" type="submit">Logout</button>
                </form>     

        </div>

    </div>

</div>
   

@endsection





