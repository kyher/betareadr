<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('books')->whereNull('genre')->update(['genre' => 'other']);
        Schema::table('books', fn (Blueprint $table) => $table->string('genre')->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('books', fn (Blueprint $table) => $table->string('genre')->nullable()->change());
    }
};
