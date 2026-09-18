<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        // SQLite table rebuilds must disable foreign keys outside a transaction.
        $this->withinTransaction = DB::getDriverName() !== 'sqlite';
    }

    public function up(): void
    {
        // Detached Conversations cannot be restored to deleted profiles.
        DB::table('beta_reading_matches')->whereNull('beta_reading_listing_decision_id')->delete();
        DB::table('conversation_messages')->whereNull('sender_id')->delete();
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->unsignedBigInteger('sender_id')->nullable(false)->change();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('beta_reading_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('remaining_user_id');
            $table->dropColumn(['remaining_role', 'retained_book_title']);
            $table->unsignedBigInteger('beta_reading_listing_decision_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('beta_reading_matches', function (Blueprint $table) {
            $table->unsignedBigInteger('beta_reading_listing_decision_id')->nullable()->change();
            $table->foreignId('remaining_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('remaining_role')->nullable();
            $table->string('retained_book_title')->nullable();
        });
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->unsignedBigInteger('sender_id')->nullable()->change();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
