<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Player;
use App\Services\ListingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Bacheca annunci: consultazione pubblica e gestione dei propri annunci.
 *
 * Nessun pagamento online e nessuna messaggistica interna: i contatti
 * sono diretti e vengono mostrati solo se il venditore ha acconsentito.
 */
class ListingController extends Controller
{
    public function __construct(private ListingService $listings)
    {
    }

    // ==========================================================
    // Lettura pubblica
    // ==========================================================

    /**
     * GET api/listings
     */
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'category'  => ['nullable', Rule::in(Listing::CATEGORIES)],
            'condition' => ['nullable', Rule::in(Listing::CONDITIONS)],
            'q'         => 'nullable|string|max:80',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'sort'      => 'nullable|in:recent,price_asc,price_desc',
            'per_page'  => 'nullable|integer|min:1|max:48',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $query = Listing::visible()
            ->with(['player:id,nickname,name,surname,img', 'images'])
            ->withCount('images');

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($condition = $request->input('condition')) {
            $query->where('condition', $condition);
        }

        if ($term = $request->input('q')) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }

        // Gli annunci senza prezzo ("trattabile") restano fuori dal filtro
        // sul prezzo: non avrebbero un valore con cui confrontarsi.
        if (($min = $request->input('price_min')) !== null && $min !== '') {
            $query->whereNotNull('price')->where('price', '>=', $min);
        }

        if (($max = $request->input('price_max')) !== null && $max !== '') {
            $query->whereNotNull('price')->where('price', '<=', $max);
        }

        match ($request->input('sort')) {
            'price_asc' => $query->orderByRaw('price IS NULL, price ASC'),
            'price_desc' => $query->orderByRaw('price IS NULL, price DESC'),
            default => $query->orderByDesc('created_at'),
        };

        $page = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => collect($page->items())->map(fn ($l) => $this->summary($l)),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * GET api/listings/{listing}
     * Incrementa il contatore di visualizzazioni.
     */
    public function show(Request $request, Listing $listing)
    {
        $isOwner = (int) $request->input('user_id') === (int) $listing->player_id;

        // Solo il proprietario può vedere un annuncio non pubblicato.
        if (! $listing->isVisible() && ! $isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Questo annuncio non è più disponibile',
            ], 404);
        }

        if (! $isOwner) {
            $listing->increment('views');
        }

        $listing->load(['player:id,nickname,name,surname,img', 'images']);

        return response()->json([
            'success' => true,
            'data' => $this->detail($listing, $isOwner),
        ]);
    }

    // ==========================================================
    // Gestione dei propri annunci
    // ==========================================================

    /**
     * POST api/listings
     */
    public function store(Request $request)
    {
        $player = Player::find($request->input('user_id'));

        if (! $player) {
            return response()->json(['success' => false, 'message' => 'Utente non trovato']);
        }

        $validator = validator($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ]);
        }

        if ($this->listings->activeCount($player->id) >= Listing::MAX_ACTIVE_PER_PLAYER) {
            return response()->json([
                'success' => false,
                'message' => 'Hai già '.Listing::MAX_ACTIVE_PER_PLAYER.' annunci attivi. Chiudine uno prima di pubblicarne un altro.',
            ]);
        }

        $listing = new Listing($validator->validated());
        $listing->player_id = $player->id;
        $listing->status = 'pending';   // passa dalla moderazione
        $listing->expires_at = now()->addDays(Listing::defaultDurationDays());
        // Se il venditore non li indica, si usano i contatti del profilo.
        $listing->contact_phone = $request->input('contact_phone') ?: $player->phone;
        $listing->contact_mail = $request->input('contact_mail') ?: $player->mail;
        $listing->save();

        $this->saveImages($request, $listing);

        return response()->json([
            'success' => true,
            'message' => 'Annuncio inviato: sarà visibile in bacheca dopo l\'approvazione della struttura.',
            'data' => $this->detail($listing->fresh(['images', 'player']), true),
        ]);
    }

    /**
     * PUT api/listings/{listing}
     */
    public function update(Request $request, Listing $listing)
    {
        if (! $this->owns($request, $listing)) {
            return response()->json(['success' => false, 'message' => 'Non puoi modificare questo annuncio'], 403);
        }

        $validator = validator($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ]);
        }

        $listing->fill($validator->validated());

        // Un annuncio già pubblicato torna in moderazione dopo una modifica.
        if ($listing->status === 'published') {
            $listing->status = 'pending';
        }

        $listing->save();

        // Immagini da rimuovere, indicate per id.
        foreach ((array) $request->input('remove_images', []) as $imageId) {
            $image = $listing->images()->find($imageId);

            if ($image) {
                $this->listings->deleteImage($image);
            }
        }

        $this->saveImages($request, $listing);

        return response()->json([
            'success' => true,
            'message' => 'Annuncio aggiornato: tornerà online dopo una nuova approvazione.',
            'data' => $this->detail($listing->fresh(['images', 'player']), true),
        ]);
    }

    /**
     * DELETE api/listings/{listing}
     */
    public function destroy(Request $request, Listing $listing)
    {
        if (! $this->owns($request, $listing)) {
            return response()->json(['success' => false, 'message' => 'Non puoi eliminare questo annuncio'], 403);
        }

        $this->listings->deleteAllImages($listing);
        $listing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Annuncio eliminato',
        ]);
    }

    /**
     * POST api/listings/{listing}/sold
     */
    public function sold(Request $request, Listing $listing)
    {
        if (! $this->owns($request, $listing)) {
            return response()->json(['success' => false, 'message' => 'Non puoi modificare questo annuncio'], 403);
        }

        $listing->status = 'sold';
        $listing->save();

        return response()->json([
            'success' => true,
            'message' => 'Annuncio segnato come venduto',
            'data' => $this->detail($listing->fresh(['images', 'player']), true),
        ]);
    }

    /**
     * POST api/account/listings
     */
    public function mine(Request $request)
    {
        $playerId = (int) $request->input('user_id');

        if (! $playerId) {
            return response()->json(['success' => false, 'message' => 'Utente non identificato']);
        }

        $listings = Listing::where('player_id', $playerId)
            ->with(['player:id,nickname,name,surname,img', 'images'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $listings->map(fn ($l) => $this->detail($l, true)),
            'meta' => [
                'active' => $this->listings->activeCount($playerId),
                'max_active' => Listing::MAX_ACTIVE_PER_PLAYER,
                'max_images' => Listing::MAX_IMAGES,
            ],
        ]);
    }

    // ==========================================================
    // Helper
    // ==========================================================

    private function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:120',
            'description' => 'required|string|min:10|max:3000',
            'category' => ['required', Rule::in(Listing::CATEGORIES)],
            'condition' => ['required', Rule::in(Listing::CONDITIONS)],
            'price' => 'nullable|numeric|min:0|max:99999',
            'contact_phone' => 'nullable|string|max:25',
            'contact_mail' => 'nullable|email|max:120',
            'show_phone' => 'nullable|boolean',
            'show_mail' => 'nullable|boolean',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.ListingService::MAX_KB,
        ];
    }

    private function owns(Request $request, Listing $listing): bool
    {
        return (int) $request->input('user_id') === (int) $listing->player_id;
    }

    private function saveImages(Request $request, Listing $listing): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        try {
            $this->listings->storeImages($listing, (array) $request->file('images'));
        } catch (\Throwable $e) {
            // Un'immagine illeggibile non deve far perdere l'annuncio.
            Log::warning('Immagini annuncio non salvate', [
                'listing_id' => $listing->id,
                'exception' => $e,
            ]);
        }
    }

    /** Dati mostrati in griglia. */
    private function summary(Listing $l): array
    {
        return [
            'id' => $l->id,
            'title' => $l->title,
            'category' => $l->category,
            'condition' => $l->condition,
            'price' => $l->price !== null ? (float) $l->price : null,
            'price_label' => $l->priceLabel(),
            'cover_url' => $l->images->first()?->url,
            'images_count' => $l->images->count(),
            'views' => $l->views,
            'created_at' => optional($l->created_at)->format('Y-m-d H:i'),
            'seller' => $l->player ? [
                'nickname' => $l->player->nickname,
                'img_url' => $l->player->img_url,
            ] : null,
        ];
    }

    /**
     * Dettaglio completo. I contatti compaiono solo se il venditore ha
     * acconsentito; al proprietario vengono mostrati sempre.
     */
    private function detail(Listing $l, bool $isOwner = false): array
    {
        $data = $this->summary($l);

        $data['description'] = $l->description;
        $data['status'] = $l->status;
        $data['status_label'] = $l->statusLabel();
        $data['expires_at'] = optional($l->expires_at)->format('Y-m-d H:i');
        $data['images'] = $l->images->map(fn ($i) => ['id' => $i->id, 'url' => $i->url])->values();
        $data['is_owner'] = $isOwner;

        // Anti-scraping di base: i contatti pubblici viaggiano codificati e
        // il frontend li decodifica solo su clic dell'utente, così non
        // finiscono nel DOM iniziale né in una risposta leggibile a colpo
        // d'occhio dai raccoglitori di indirizzi più semplici.
        $phone = $l->show_phone ? $l->contact_phone : null;
        $mail = $l->show_mail ? $l->contact_mail : null;

        $data['contact'] = [
            'has_phone' => (bool) $phone,
            'has_mail' => (bool) $mail,
            'phone_b64' => $phone ? base64_encode($phone) : null,
            'mail_b64' => $mail ? base64_encode($mail) : null,
            'show_phone' => $l->show_phone,
            'show_mail' => $l->show_mail,
        ];

        // Al proprietario servono in chiaro per precompilare la modifica.
        if ($isOwner) {
            $data['contact']['phone'] = $l->contact_phone;
            $data['contact']['mail'] = $l->contact_mail;
        }

        if ($isOwner) {
            $data['reject_reason'] = $l->reject_reason;
        }

        return $data;
    }
}
