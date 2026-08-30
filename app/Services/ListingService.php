<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Immagini degli annunci: ridimensionamento e salvataggio.
 * Il resize passa da ImageResizer, lo stesso usato da avatar e copertine.
 */
class ListingService
{
    /** Lato massimo dell'immagine salvata, in pixel. */
    public const MAX_SIZE = 1200;

    /** Dimensione massima del file caricato, in KB. */
    public const MAX_KB = 6144;

    public const FOLDER = 'listings';

    public function __construct(private ImageResizer $resizer)
    {
    }

    /**
     * Salva le immagini caricate rispettando il limite per annuncio.
     *
     * @param  UploadedFile[]  $files
     * @return int numero di immagini effettivamente salvate
     */
    public function storeImages(Listing $listing, array $files): int
    {
        $existing = $listing->images()->count();
        $free = max(0, Listing::MAX_IMAGES - $existing);

        if (! $free) {
            return 0;
        }

        $saved = 0;

        foreach (array_slice($files, 0, $free) as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $binary = $this->resizer->resizeToJpeg($file, self::MAX_SIZE);
            $path = self::FOLDER.'/'.$listing->id.'_'.Str::random(12).'.jpg';

            Storage::disk('public')->put($path, $binary, 'public');

            ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $path,
                // La prima immagine in assoluto è la copertina (position 0).
                'position' => $existing + $saved,
            ]);

            $saved++;
        }

        return $saved;
    }

    /** Elimina un'immagine e il relativo file. */
    public function deleteImage(ListingImage $image): void
    {
        $image->deleteFile();
        $image->delete();
    }

    /** Elimina tutte le immagini di un annuncio (file compresi). */
    public function deleteAllImages(Listing $listing): void
    {
        foreach ($listing->images as $image) {
            $this->deleteImage($image);
        }
    }

    /**
     * Annunci che contano verso il limite del giocatore.
     * Gli annunci scaduti, venduti o rifiutati non occupano uno slot.
     */
    public function activeCount(int $playerId): int
    {
        return Listing::where('player_id', $playerId)
            ->whereIn('status', Listing::ACTIVE_STATUSES)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();
    }
}
