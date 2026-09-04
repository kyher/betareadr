<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', fn (Blueprint $table) => $table->string('language', 2)->default('en'));
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->json('reading_languages')->default('["*"]'));
    }

    public function down(): void
    {
        Schema::table('books', fn (Blueprint $table) => $table->dropColumn('language'));
        Schema::table('beta_reader_profiles', fn (Blueprint $table) => $table->dropColumn('reading_languages'));
    }
};
