<?php

namespace App\Services;

use getID3;

class AudioMetadataService
{
    public function analyze(string $path): array
    {
        $getID3 = new getID3();

        $data = $getID3->analyze($path);

        return [

            'title' => $data['tags']['id3v2']['title'][0]
                ?? null,

            'artist' => $data['tags']['id3v2']['artist'][0]
                ?? null,

            'album' => $data['tags']['id3v2']['album'][0]
                ?? null,

            'duration' => isset($data['playtime_seconds'])
                ? round($data['playtime_seconds'])
                : null,

            'mime_type' => $data['mime_type']
                ?? null,

            'bitrate' => $data['audio']['bitrate']
                ?? null,

            'sample_rate' => $data['audio']['sample_rate']
                ?? null,

            'channels' => $data['audio']['channels']
                ?? null,

            'codec' => $data['audio']['dataformat']
                ?? null,
        ];
    }
}
