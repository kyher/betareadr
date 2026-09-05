<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beta_reading_listing_decisions', function (Blueprint $table) {
            $table->string('author_decision', 16)->nullable();
        });

        Schema::create('beta_reading_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beta_reading_listing_decision_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beta_reading_matches');
        Schema::table('beta_reading_listing_decisions', fn (Blueprint $table) => $table->dropColumn('author_decision'));
    }
};
