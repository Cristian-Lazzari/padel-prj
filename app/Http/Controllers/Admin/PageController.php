<?php



namespace App\Http\Controllers\Admin;


use App\Models\User;

use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{

    public function admin() {
        $players  = Player::all();
        $settings = Setting::all()->keyBy('name');
        $users    = User::all();
        $calendar = [];


        return view('admin.dashboard', compact('players', 'calendar', 'settings', 'users'));

    }


}

