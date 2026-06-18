<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('imdb_id')->nullable()->unique();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->text('overview')->nullable();
            $table->string('tagline')->nullable();
            $table->date('release_date')->nullable();
            $table->unsignedSmallInteger('runtime')->nullable(); // minutes
            $table->json('genres')->nullable();
            $table->string('original_language', 10)->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->decimal('vote_average', 4, 2)->nullable();
            $table->unsignedInteger('vote_count')->nullable();
            $table->decimal('popularity', 10, 3)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('cached_at')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
