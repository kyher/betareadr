<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('beta_reading_listing_decisions', 'decision')
            && ! Schema::hasColumn('beta_reading_listing_decisions', 'reader_decision')) {
            Schema::table('beta_reading_listing_decisions', fn (Blueprint $table) => $table->renameColumn('decision', 'reader_decision'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('beta_reading_listing_decisions', 'reader_decision')
            && ! Schema::hasColumn('beta_reading_listing_decisions', 'decision')) {
            Schema::table('beta_reading_listing_decisions', fn (Blueprint $table) => $table->renameColumn('reader_decision', 'decision'));
        }
    }
};
