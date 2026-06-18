<?php

use App\Enums\Priority;
use App\Enums\WatchlistStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();

            $table->string('status')->default(WatchlistStatus::ToWatch->value);
            $table->string('priority')->default(Priority::Normal->value);
            $table->unsignedTinyInteger('rating')->nullable(); // user's personal 1-10 score
            $table->text('notes')->nullable();
            $table->timestamp('watched_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'movie_id']);
            $table->index(['user_id', 'status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('watchlist_items');
    }
};
