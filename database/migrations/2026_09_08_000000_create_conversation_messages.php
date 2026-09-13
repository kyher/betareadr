<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beta_reading_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['beta_reading_match_id', 'id']);
        });

        Schema::table('beta_reading_matches', function (Blueprint $table) {
            $table->unsignedBigInteger('author_read_message_id')->default(0);
            $table->unsignedBigInteger('reader_read_message_id')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('beta_reading_matches', function (Blueprint $table) {
            $table->dropColumn(['author_read_message_id', 'reader_read_message_id']);
        });
        Schema::dropIfExists('conversation_messages');
    }
};
