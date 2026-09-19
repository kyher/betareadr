<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', fn (Blueprint $table) => $table->json('content_warnings')->nullable());
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->json('content_boundaries')->nullable());

        DB::table('books')->update(['content_warnings' => '[]']);
        DB::table('beta_reader_profiles')->update(['content_boundaries' => '[]']);

        Schema::table('books', fn (Blueprint $table) => $table->json('content_warnings')->nullable(false)->change());
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->json('content_boundaries')->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('books', fn (Blueprint $table) => $table->dropColumn('content_warnings'));
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->dropColumn('content_boundaries'));
    }
};
