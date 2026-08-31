<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('beta_reading_listings')) {
            return;
        }

        Schema::create('beta_reading_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->text('reading_brief');
            $table->unsignedInteger('word_count');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'closed_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beta_reading_listings');
    }
};
