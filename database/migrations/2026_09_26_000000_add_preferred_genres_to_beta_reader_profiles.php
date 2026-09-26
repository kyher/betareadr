<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->json('preferred_genres')->nullable());
        DB::table('beta_reader_profiles')->update(['preferred_genres' => '[]']);
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->json('preferred_genres')->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->dropColumn('preferred_genres'));
    }
};
