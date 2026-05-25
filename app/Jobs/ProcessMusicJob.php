<?php

namespace App\Jobs;

use App\Models\Music;
use App\Services\AudioMetadataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessMusicJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private Music $music
    ) {}

    public function handle(
        AudioMetadataService $metadataService
    ): void {
        $fullPath = Storage::path($this->music->file_path);

        $metadata = $metadataService->extract($fullPath);

        $this->music->update([
            'duration' => $metadata['duration'],
            'metadata' => $metadata,
            'processed' => true
        ]);
    }
}
