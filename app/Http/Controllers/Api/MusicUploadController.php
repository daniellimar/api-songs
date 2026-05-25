<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChunkUploadRequest;
use App\Models\UploadSession;
use App\Services\ChunkService;
use App\Services\MusicUploadService;

class MusicUploadController extends Controller
{
    public function upload(
        ChunkUploadRequest $request,
        ChunkService $chunkService,
        MusicUploadService $musicUploadService
    )
    {
        $session = UploadSession::firstOrCreate(

            [
                'id' => $request->upload_id
            ],

            [
                'original_name' => $request->file_name,

                'total_chunks' => (int)
                $request->total_chunks,

                'extension' => pathinfo(
                    $request->file_name,
                    PATHINFO_EXTENSION
                ),

                'file_size' => (int)
                $request->file_size,

                'uploaded_chunks' => 0
            ]
        );

        $chunkService->storeChunk(

            $session,

            $request->file('chunk'),

            (int) $request->chunk_index
        );

        /*
        |--------------------------------------------------------------------------
        | Atualiza contador corretamente
        |--------------------------------------------------------------------------
        */

        $session->uploaded_chunks =
            $session->uploaded_chunks + 1;

        $session->save();

        $session->refresh();

        /*
        |--------------------------------------------------------------------------
        | Finaliza upload
        |--------------------------------------------------------------------------
        */

        if (
            $session->uploaded_chunks >=
            $session->total_chunks
        ) {

            $music = $musicUploadService
                ->finalize($session);

            return response()->json([

                'completed' => true,

                'uploaded_chunks' =>
                    $session->uploaded_chunks,

                'total_chunks' =>
                    $session->total_chunks,

                'music' => $music
            ]);
        }

        return response()->json([

            'completed' => false,

            'uploaded_chunks' =>
                $session->uploaded_chunks,

            'total_chunks' =>
                $session->total_chunks
        ]);
    }
}
