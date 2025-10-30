<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // quote_created, invoice_paid, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data payload
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable(); // URL to navigate when clicked
            $table->string('action_text')->nullable(); // Button text for action
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};