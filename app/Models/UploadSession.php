<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadSession extends Model
{
    protected $table = 'upload_sessions';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [

        'id',

        'original_name',

        'total_chunks',

        'uploaded_chunks',

        'extension',

        'file_size'
    ];
}
