<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', fn (Blueprint $table) => $table->string('language', 2)->nullable());
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->json('reading_languages')->nullable());

        DB::table('books')->update(['language' => 'en']);
        DB::table('beta_reader_profiles')->update(['reading_languages' => '["*"]']);

        Schema::table('books', fn (Blueprint $table) => $table->string('language', 2)->nullable(false)->change());
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->json('reading_languages')->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('books', fn (Blueprint $table) => $table->dropColumn('language'));
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->dropColumn('reading_languages'));
    }
};
