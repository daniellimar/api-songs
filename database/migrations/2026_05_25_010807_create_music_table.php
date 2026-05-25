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
        Schema::create('musics', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('album')->nullable();

            $table->string('file_name');
            $table->string('file_path');

            $table->bigInteger('file_size');
            $table->string('mime_type');

            $table->integer('duration')->nullable();
            $table->string('extension');

            $table->json('metadata')->nullable();

            $table->boolean('processed')->default(false);

            $table->timestamps();

            $table->index('title');
            $table->index('artist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music');
    }
};
