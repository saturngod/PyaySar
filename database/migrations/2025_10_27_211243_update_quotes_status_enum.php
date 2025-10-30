<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'Converted' status
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('Draft', 'Sent', 'Seen', 'Converted') DEFAULT 'Draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum without 'Converted'
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('Draft', 'Sent', 'Seen') DEFAULT 'Draft'");
    }
};
