<?php

namespace App\Services;

use App\Mail\ListingNotification;
use App\Models\Listing;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Notifiche al venditore sull'esito della moderazione.
 * L'invio avviene dopo la risposta: un SMTP lento non blocca il gestore.
 */
class ListingNotifier
{
    public function approved(Listing $listing): void
    {
        $this->send($listing, [
            'subject' => 'Il tuo annuncio è online: '.$listing->title,
            'title' => 'Annuncio pubblicato',
            'subtitle' => 'Il tuo annuncio è stato approvato ed è visibile in bacheca.',
        ]);
    }

    public function rejected(Listing $listing, ?string $reason = null): void
    {
        $this->send($listing, [
            'subject' => 'Annuncio non approvato: '.$listing->title,
            'title' => 'Annuncio non approvato',
            'subtitle' => 'Il tuo annuncio non è stato pubblicato in bacheca.',
            'reason' => $reason ?: $listing->reject_reason,
        ]);
    }

    private function send(Listing $listing, array $body): void
    {
        $player = $listing->relationLoaded('player') ? $listing->player : $listing->player()->first();
        $to = $player?->mail;

        if (! $to) {
            return;
        }

        $contactSetting = Setting::where('name', 'Contatti')->first();
        $contact = $contactSetting ? json_decode($contactSetting->property, true) : [];

        $payload = [
            'subject' => $body['subject'],
            'title' => $body['title'],
            'subtitle' => $body['subtitle'],
            'reason' => $body['reason'] ?? null,
            'listing_title' => $listing->title,
            'category' => ucfirst($listing->category),
            'price' => $listing->priceLabel(),
            'expires_label' => optional($listing->expires_at)->format('d/m/Y'),
            'admin_phone' => $contact['phone'] ?? null,
        ];

        $listingId = $listing->id;

        dispatch(function () use ($to, $payload, $listingId) {
            try {
                Mail::to($to)->send(new ListingNotification($payload));
            } catch (\Throwable $e) {
                Log::warning('Notifica annuncio non inviata', [
                    'listing_id' => $listingId,
                    'to' => $to,
                    'exception' => $e,
                ]);
            }
        })->afterResponse();
    }
}
