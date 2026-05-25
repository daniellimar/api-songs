<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('original_name');
            $table->integer('total_chunks');
            $table->integer('uploaded_chunks')->default(0);

            $table->string('extension');
            $table->bigInteger('file_size');

            $table->enum('status', [
                'uploading',
                'processing',
                'completed',
                'failed'
            ])->default('uploading');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
    }
};
