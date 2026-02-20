<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL supports MODIFY COLUMN
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Sent', 'Reject', 'Received', 'Paid', 'Rejected') NOT NULL DEFAULT 'Draft'");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN from_status ENUM('Draft', 'Sent', 'Reject', 'Received', 'Paid', 'Rejected') NULL DEFAULT NULL");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN to_status ENUM('Draft', 'Sent', 'Reject', 'Received', 'Paid', 'Rejected') NOT NULL");
        }

        // Update status values for all databases
        DB::table('invoices')->where('status', 'Received')->update(['status' => 'Paid']);
        DB::table('invoices')->where('status', 'Reject')->update(['status' => 'Rejected']);

        DB::table('invoice_status_histories')->where('from_status', 'Received')->update(['from_status' => 'Paid']);
        DB::table('invoice_status_histories')->where('from_status', 'Reject')->update(['from_status' => 'Rejected']);
        DB::table('invoice_status_histories')->where('to_status', 'Received')->update(['to_status' => 'Paid']);
        DB::table('invoice_status_histories')->where('to_status', 'Reject')->update(['to_status' => 'Rejected']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Sent', 'Paid', 'Rejected') NOT NULL DEFAULT 'Draft'");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN from_status ENUM('Draft', 'Sent', 'Paid', 'Rejected') NULL DEFAULT NULL");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN to_status ENUM('Draft', 'Sent', 'Paid', 'Rejected') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Sent', 'Reject', 'Received', 'Paid', 'Rejected') NOT NULL DEFAULT 'Draft'");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN from_status ENUM('Draft', 'Sent', 'Reject', 'Received', 'Paid', 'Rejected') NULL DEFAULT NULL");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN to_status ENUM('Draft', 'Sent', 'Reject', 'Received', 'Paid', 'Rejected') NOT NULL");
        }

        DB::table('invoices')->where('status', 'Paid')->update(['status' => 'Received']);
        DB::table('invoices')->where('status', 'Rejected')->update(['status' => 'Reject']);

        DB::table('invoice_status_histories')->where('from_status', 'Paid')->update(['from_status' => 'Received']);
        DB::table('invoice_status_histories')->where('from_status', 'Rejected')->update(['from_status' => 'Reject']);
        DB::table('invoice_status_histories')->where('to_status', 'Paid')->update(['to_status' => 'Received']);
        DB::table('invoice_status_histories')->where('to_status', 'Rejected')->update(['to_status' => 'Reject']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Sent', 'Received', 'Reject') NOT NULL DEFAULT 'Draft'");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN from_status ENUM('Draft', 'Sent', 'Received', 'Reject') NULL DEFAULT NULL");
            DB::statement("ALTER TABLE invoice_status_histories MODIFY COLUMN to_status ENUM('Draft', 'Sent', 'Received', 'Reject') NOT NULL");
        }
    }
};
