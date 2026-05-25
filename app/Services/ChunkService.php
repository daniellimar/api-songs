<?php

namespace App\Services;

use App\Models\UploadSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ChunkService
{
    private string $disk = 'local';

    public function storeChunk(
        UploadSession $session,
        UploadedFile $chunk,
        int $index
    ): void {

        $directory =
            storage_path(
                'app/chunks/' . $session->id
            );

        if (!file_exists($directory)) {

            mkdir($directory, 0777, true);
        }

        $chunk->move(
            $directory,
            (string) $index
        );
    }

    public function mergeChunks(
        UploadSession $session
    ): string
    {
        $finalName =
            uniqid() . '.' . $session->extension;

        $finalPath =
            "musics/{$finalName}";

        $tempFile = tmpfile();

        for (
            $i = 0;
            $i < $session->total_chunks;
            $i++
        ) {

            $chunkPath = $this->chunkPath(
                $session->id,
                $i
            );

            if (
                !Storage::disk($this->disk)
                    ->exists($chunkPath)
            ) {

                throw new \Exception(
                    "Chunk {$i} não encontrado."
                );
            }

            $content = Storage::disk($this->disk)
                ->get($chunkPath);

            fwrite($tempFile, $content);
        }

        rewind($tempFile);

        Storage::disk($this->disk)->put(
            $finalPath,
            stream_get_contents($tempFile)
        );

        fclose($tempFile);

        return $finalPath;
    }

    public function deleteChunks(
        UploadSession $session
    ): void
    {
        for (
            $i = 0;
            $i < $session->total_chunks;
            $i++
        ) {

            Storage::disk($this->disk)
                ->delete(
                    $this->chunkPath(
                        $session->id,
                        $i
                    )
                );
        }

        Storage::disk($this->disk)
            ->deleteDirectory(
                "chunks/{$session->id}"
            );
    }

    private function chunkPath(
        string $uploadId,
        int $index
    ): string
    {
        return "chunks/{$uploadId}/{$index}.part";
    }
}
