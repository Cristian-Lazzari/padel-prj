<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gestione della foto profilo dei giocatori.
 * Il ridimensionamento è delegato a ImageResizer.
 */
class AvatarService
{
    /** Lato massimo dell'immagine salvata, in pixel. */
    public const MAX_SIZE = 512;

    /** Cartella sul disco "public". */
    public const FOLDER = 'avatars';

    public function __construct(private ImageResizer $resizer)
    {
    }

    /**
     * Salva la foto profilo del giocatore, elimina la precedente e
     * restituisce il path relativo sul disco pubblico.
     */
    public function store(UploadedFile $file, Player $player): string
    {
        $resized = $this->resizer->resizeToJpeg($file, self::MAX_SIZE);

        $path = self::FOLDER.'/'.$player->id.'_'.Str::random(12).'.jpg';

        Storage::disk('public')->put($path, $resized, 'public');

        // La vecchia immagine va rimossa solo dopo che la nuova è a posto.
        $player->deleteImgFile();

        return $path;
    }
}
