<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Consumer;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'activity_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required','string'],
            'role_agency' => ['required'],
        ]);
       
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'name' => $request->name,
            'surname' => $request->surname,
            'role_agency' => $request->role_agency,
        ]);
        $r_property = [
            'day_service' => [],
            'r_type' => [],
            'activation_date' => '',
            'renewal_date' => '',
            'stripe_id' => '',
            'subscription_id' => ''
        ];
        $consumer = Consumer::create([
            'user_id' => $user->id,
            'activity_name' => $request->activity_name,
            'r_property' => json_encode($r_property)
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::home());
    }
}
