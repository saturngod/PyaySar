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
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->text('pdf_footer_message')->nullable();
            $table->string('pdf_paper_size')->default('a4');
            $table->string('pdf_font')->nullable();
            $table->string('pdf_primary_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'pdf_footer_message',
                'pdf_paper_size',
                'pdf_font',
                'pdf_primary_color',
            ]);
        });
    }
};
