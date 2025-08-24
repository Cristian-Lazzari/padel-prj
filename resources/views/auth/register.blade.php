@extends('layouts.guestNoNav')

@section('contents')
<div class="register-page">
    <div class="hero">
        <div class="center_hero reg_c">
            <img src="{{ asset('public/logo.png') }}" alt="">
            <div class="right">
                <form method="POST" class="registration " action="{{ route('register') }}">
                    @csrf
                    <div class="split">
                        <div class="input_form">
                            
                            <label for="name" class="form-label">Nome</label>
                            <input
                            type="text"
                            id="name"
                            placeholder="Inserisci il tuo nome"
                            name="name"
                            required
                            autofocus
                            autocomplete="name"
                            value="{{ old('name') }}"
                            >
                        </div>
                        <div class="input_form">
                            <label for="surname" class="form-label">Cognome</label>
                            <input
                            type="text"
                            id="surname"
                            placeholder="Inserisci il tuo cognome"
                            name="surname"
                            required
                            autofocus
                            autocomplete="lastname"
                            value="{{ old('surname') }}"
                            >
                        </div>
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                        @error('surname') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="split">
                        <div class="input_form">
                            <label for="activity_name" class="form-label">Nome attività</label>
                            <input
                                type="text"
                                id="activity_name"
                                placeholder="Inserisci il nome della tua attività"
                                name="activity_name"
                                required
                                autofocus
                                value="{{ old('activity_name') }}"
                            >
                        </div>
                        <div class="input_form">
                            <label for="role_agency" class="form-label">Ruolo nell'azienda</label>
                            @php
                                $role = ['Proprietario o Socio' , 'Dipendente', 'SMM'];
                            @endphp
                            <select name="role_agency" id="role_agency">
                                <option disabled selected class="disabled" >Seleziona il tuo ruolo</option>
                                @foreach ($role as $key => $item)
                                <option
                                @if (old('role_agency')== $item ) selected @endif
                                value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                           @error('activity_name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_form">
                        
                        <label for="phone" class="form-label">Telefono</label>
                        <input
                        type="text"
                        id="phone"
                        placeholder="Inserisci il tuo numero"
                        name="phone"
                        required
                        autofocus
                        autocomplete="phone"
                        value="{{ old('phone') }}"
                        >
                    </div>
                    
                    @error('phone') <p class="error">{{ $message }}</p> @enderror
                    <div class="input_form">
                        
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="text"
                            id="email"
                            placeholder="Inserisci la tua email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            value="{{ old('email') }}"
                        >
                    </div>
                    @error('email') <p class="error">{{ $message }}</p> @enderror


            
                    <div class="split">
                        <div class="input_form">
                            <label for="password" class="form-label">Password</label>
                            <input
                                type="password"
                                id="password"
                                placeholder="Crea una nuova password"
                                name="password"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                        <div class="input_form">
                            <label for="password" class="form-label">Conferma Password</label>
                            <input
                                type="password"
                                id="password"
                                placeholder="Conferma la password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                    </div>
                    @error('password') <p class="error">{{ $message }}</p> @enderror
                    <div class="act">

                        
                        <button type="submit" class="my_btn_7">Registrati</button>
                        <a class="my_link " href="{{ route('login') }}">
                            Ti sei già registrato?
                        </a>
                    </div>
                </form> 
            </div>
        </div>

    </div>

    
</div>




@endsection
