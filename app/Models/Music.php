<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $table = 'musics';

    protected $fillable = [
        'title',
        'artist',
        'album',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'duration',
        'extension',
        'metadata',
        'processed'
    ];

    protected $casts = [
        'metadata' => 'array',
        'processed' => 'boolean'
    ];
}
