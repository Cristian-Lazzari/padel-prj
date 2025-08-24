<?php



namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Date;
use App\Models\Post;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Consumer;
use App\Models\Ingredient;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{

    public function admin() {
        $consumers = Consumer::all();
        $users = User::with('consumers')->get();

        $statusCounts = [
            'Essentials' => Consumer::where('status', 1)->count(),
            'Work On' => Consumer::where('status', 2)->count(),
            'Boost Up' => Consumer::where('status', 3)->count(),
            'Prova Gratis' => Consumer::where('status', '>', 3)->count(),
        ];

        $subscriptions = Consumer::select(
            DB::raw("DATE(created_at) as date"),
            DB::raw("SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as essentials"),
            DB::raw("SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as work_on"),
            DB::raw("SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as boost_up"),
        )
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->get();

        // Estrai i dati per il grafico
        $subscriptions_chart = [
            'labels' => $subscriptions->pluck('date'),
            'essentials' => $subscriptions->pluck('essentials'),
            'WorkOn' => $subscriptions->pluck('work_on'),
            'BoostUp' => $subscriptions->pluck('boost_up'),
        ];
        $subscriptions_chart = json_encode($subscriptions_chart);

        // Passiamo i dati alla vista

        return view('admin.dashboard', compact('consumers', 'statusCounts', 'users', 'subscriptions_chart'));
    }


}

