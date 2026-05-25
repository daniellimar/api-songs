<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Music;
use App\Services\AudioMetadataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MusicController extends Controller
{
    public function index(Request $request)
    {
        return Music::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'like', "%{$request->search}%")
                    ->orWhere('artist', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(20);
    }



    public function show(Music $music)
    {
        return response()->json($music);
    }

    public function update(
        Request $request,
        Music $music
    ) {

        $music->update([

            'title' => $request->title,

            'artist' => $request->artist,

            'album' => $request->album,
        ]);

        return response()->json($music);
    }

    public function destroy(Music $music)
    {

        if (
            $music->file_path &&
            Storage::exists($music->file_path)
        ) {
            Storage::delete($music->file_path);
        }

        $music->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function stream(Music $music)
    {

        $path = storage_path(
            'app/private/' . $music->file_path
        );

        if (!file_exists($path)) {

            return response()->json([
                'message' => 'Arquivo não encontrado'
            ], 404);
        }

        return response()->file($path, [
            'Content-Type' => $music->mime_type
        ]);
    }
}
