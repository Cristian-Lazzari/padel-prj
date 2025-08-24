
@extends('layouts.baseClient')

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

                <form class="form-reg" method="post" action="{{ route('client.profile.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')
                    <div class="split">
                        <div class="input_form">
                            <label for="name" :value="__('Name')" > Nome </label>
                            <input id="name" name="name" type="text" value="{{old('name', $user->name)}}" required  autocomplete="name" />
                        </div>
                        <div class="input_form">
                            <label for="surname" :value="__('Name')" > Cognome </label>
                            <input id="surname" name="surname" type="text" value="{{old('surname', $user->surname)}}" required  autocomplete="surname" />
                        </div>
                        @error('name') <p class="error w-100">{{ $message }}</p> @enderror
                        @error('surname') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="full">

                            <label for="email" :value="__('Name')" > Email </label>
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


                    <button type="submit" class="my_btn_4">Modifica</button>

                        @if (session('status') === 'profile-updated')
                            <p class="my_btn_6 mt-3 mb-5 w-100 m-auto">Modifica avventa riuscita con successo</p>
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
                            <label for="password"  :value="__('Confirm Password')"> Ripeti nuova password </label>
                            <input id="password" name="password" type="text" required  autocomplete="password" />
                        </div>
                        @error('password_confirmation') <p class="error w-100">{{ $message }}</p> @enderror




                        <button type="submit" class="my_btn_4">Modifica</button>

                        @if (session('status') === 'password-updated')
                            <p class="my_btn_6 mt-3 mb-5 w-100 m-auto">Modifica avventa riuscita con successo</p>
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
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">


            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Update Password') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Ensure your account is using a long, random password to stay secure.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('client.password.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('put')

                            <div>
                                <x-input-label for="current_password" :value="__('Current Password')" />
                                <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('New Password')" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('status') === 'password-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600"
                                    >Salvataggio riuscito con successo</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section class="space-y-6">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Delete Account') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
                            </p>
                        </header>

                        <x-danger-button
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        >{{ __('Delete Account') }}</x-danger-button>

                        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                            <form method="post" action="{{ route('client.admin.profile.destroy') }}" class="p-6">
                                @csrf
                                @method('delete')

                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('Are you sure you want to delete your account?') }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                                </p>

                                <div class="mt-6">
                                    <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                                    <x-text-input
                                        id="password"
                                        name="password"
                                        type="password"
                                        class="mt-1 block w-3/4"
                                        placeholder="{{ __('Password') }}"
                                    />

                                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <x-secondary-button x-on:click="$dispatch('close')">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>

                                    <x-danger-button class="ml-3">
                                        {{ __('Delete Account') }}
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    </section>

                </div>
            </div>
        </div>
    </div> --}}

@endsection


