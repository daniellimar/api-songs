<?php

namespace App\Services;

use App\Jobs\ProcessMusicJob;
use App\Models\Music;
use App\Models\UploadSession;
use Illuminate\Support\Facades\Storage;

class MusicUploadService
{
    public function __construct(
        private ChunkService $chunkService
    ) {}

    public function finalize(UploadSession $session): Music
    {
        $path = $this->chunkService->mergeChunks($session);

        $music = Music::create([
            'title' => pathinfo($session->original_name, PATHINFO_FILENAME),

            'file_name' => basename($path),

            'file_path' => $path,

            'file_size' => $session->file_size,

            'mime_type' => Storage::mimeType($path),

            'extension' => $session->extension,

            'processed' => false
        ]);

        ProcessMusicJob::dispatch($music);

        $this->chunkService->deleteChunks($session);

        $session->update([
            'status' => 'completed'
        ]);

        return $music;
    }
}
