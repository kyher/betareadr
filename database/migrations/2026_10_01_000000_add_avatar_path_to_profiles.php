<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['author_profiles', 'beta_reader_profiles'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('avatar_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['author_profiles', 'beta_reader_profiles'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('avatar_path');
            });
        }
    }
};
