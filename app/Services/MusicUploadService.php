<?php

namespace App\Services;

use App\Models\Music;
use App\Models\UploadSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MusicUploadService
{
    public function finalize(
        UploadSession $session
    ): Music
    {

        /*
        |--------------------------------------------------------------------------
        | Diretórios
        |--------------------------------------------------------------------------
        */

        $chunksPath =
            storage_path(
                'app/chunks/' . $session->id
            );

        $fileName =
            Str::random(13) . '.' . $session->extension;

        $relativePath =
            'musics/' . $fileName;

        $finalPath =
            storage_path(
                'app/private/' . $relativePath
            );

        /*
        |--------------------------------------------------------------------------
        | Cria diretório final
        |--------------------------------------------------------------------------
        */

        if (!file_exists(dirname($finalPath))) {

            mkdir(
                dirname($finalPath),
                0777,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Abre arquivo final
        |--------------------------------------------------------------------------
        */

        $output = fopen($finalPath, 'ab');

        /*
        |--------------------------------------------------------------------------
        | Junta chunks
        |--------------------------------------------------------------------------
        */

        for (
            $i = 0;
            $i < $session->total_chunks;
            $i++
        ) {

            $chunkFile =
                $chunksPath . '/' . $i;

            if (!file_exists($chunkFile)) {
                continue;
            }

            $input = fopen($chunkFile, 'rb');

            while (!feof($input)) {

                fwrite(
                    $output,
                    fread($input, 1024 * 1024)
                );
            }

            fclose($input);

            unlink($chunkFile);
        }

        fclose($output);

        /*
        |--------------------------------------------------------------------------
        | Remove pasta chunks
        |--------------------------------------------------------------------------
        */

        @rmdir($chunksPath);

        /*
        |--------------------------------------------------------------------------
        | Cria música
        |--------------------------------------------------------------------------
        */

        $music = Music::create([

            'title' =>
                pathinfo(
                    $session->original_name,
                    PATHINFO_FILENAME
                ),

            'file_name' => $fileName,

            'file_path' => $relativePath,

            'file_size' => filesize($finalPath),

            'mime_type' => mime_content_type($finalPath),

            'extension' => $session->extension,

            'processed' => false,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Remove sessão upload
        |--------------------------------------------------------------------------
        */

        $session->delete();

        return $music;
    }
}
