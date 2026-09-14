<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beta_reading_matches', function (Blueprint $table) {
            $table->foreignId('ended_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('beta_reading_matches', fn (Blueprint $table) => $table->dropConstrainedForeignId('ended_by_user_id'));
    }
};
