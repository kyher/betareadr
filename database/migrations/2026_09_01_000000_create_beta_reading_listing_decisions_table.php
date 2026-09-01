<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beta_reading_listing_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beta_reader_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beta_reading_listing_id')->constrained()->cascadeOnDelete();
            $table->string('decision', 16);
            $table->timestamps();

            $table->unique(
                ['beta_reader_profile_id', 'beta_reading_listing_id'],
                'beta_reader_listing_decision_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beta_reading_listing_decisions');
    }
};
