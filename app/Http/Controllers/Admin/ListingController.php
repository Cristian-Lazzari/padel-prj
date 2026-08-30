<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Setting;
use App\Services\ListingNotifier;
use App\Services\ListingService;
use Illuminate\Http\Request;

/**
 * Moderazione della bacheca annunci dal back office.
 */
class ListingController extends Controller
{
    public function __construct(
        private ListingService $listings,
        private ListingNotifier $notifier
    ) {
    }

    public function index(Request $request)
    {
        $status = $request->input('status');

        $query = Listing::with(['player:id,nickname,name,surname', 'images'])
            ->withCount('images')
            ->orderByRaw("FIELD(status, 'pending', 'published', 'sold', 'expired', 'rejected')")
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['pending', 'published', 'sold', 'rejected', 'expired'], true)) {
            $query->where('status', $status);
        }

        $listings = $query->get();

        $counts = Listing::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $default_days = Listing::defaultDurationDays();

        return view('admin.Listings.index', compact('listings', 'counts', 'status', 'default_days'));
    }

    public function show(Listing $listing)
    {
        $listing->load(['player', 'images']);

        return view('admin.Listings.show', compact('listing'));
    }

    /** Approvazione: l'annuncio va online e parte la mail al venditore. */
    public function approve(Request $request, Listing $listing)
    {
        $request->validate(['days' => 'nullable|integer|min:1|max:365']);

        $days = (int) ($request->input('days') ?: Listing::defaultDurationDays());

        $listing->status = 'published';
        $listing->reject_reason = null;
        $listing->expires_at = now()->addDays($days);
        $listing->save();

        $this->notifier->approved($listing);

        return back()->with('message', 'Annuncio pubblicato: resterà in bacheca fino al '
            .$listing->expires_at->format('d/m/Y'));
    }

    /** Rifiuto con motivo, comunicato al venditore via email. */
    public function reject(Request $request, Listing $listing)
    {
        $request->validate(['reason' => 'required|string|min:3|max:255']);

        $listing->status = 'rejected';
        $listing->reject_reason = $request->input('reason');
        $listing->save();

        $this->notifier->rejected($listing, $request->input('reason'));

        return back()->with('message', 'Annuncio rifiutato: il venditore è stato avvisato');
    }

    /** Cambio di stato senza notifica (rimozione dalla bacheca, ripristino). */
    public function status(Request $request, Listing $listing)
    {
        $request->validate(['status' => 'required|in:pending,published,sold,expired']);

        $listing->status = $request->input('status');

        if ($listing->status === 'published' && (! $listing->expires_at || $listing->expires_at->isPast())) {
            $listing->expires_at = now()->addDays(Listing::defaultDurationDays());
        }

        $listing->save();

        return back()->with('message', 'Stato aggiornato: '.$listing->statusLabel());
    }

    public function destroy(Listing $listing)
    {
        $title = $listing->title;

        $this->listings->deleteAllImages($listing);
        $listing->delete();

        return to_route('admin.listings.index')
            ->with('message', 'Annuncio "'.$title.'" eliminato');
    }

    /** Durata di default prima della scadenza, in giorni. */
    public function updateSettings(Request $request)
    {
        $request->validate(['default_days' => 'required|integer|min:1|max:365']);

        Setting::updateOrCreate(
            ['name' => 'Bacheca annunci'],
            [
                'status' => 1,
                'property' => json_encode(['default_days' => (int) $request->input('default_days')]),
            ]
        );

        return back()->with('message', 'Durata di default aggiornata: '
            .$request->input('default_days').' giorni');
    }
}
