<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceReport;
use App\Models\User;
use App\Services\InvoiceReportService;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;

// ──────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────

/**
 * Create a bare-minimum invoice row directly (no InvoiceFactory exists).
 */
function makeInvoice(User $user, array $overrides = []): Invoice
{
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    return Invoice::create(array_merge([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-TEST-'.fake()->unique()->numerify('###'),
        'currency' => 'MMK',
        'status' => 'Draft',
        'open_date' => Carbon::today()->toDateString(),
        'sub_total' => 100.00,
        'discount' => 0,
        'total' => 100.00,
    ], $overrides));
}

// ──────────────────────────────────────────────
// InvoiceReportService — unit-style feature tests
// ──────────────────────────────────────────────

describe('InvoiceReportService', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->service = app(InvoiceReportService::class);
    });

    test('recomputeForDate creates a report row when none exists', function () {
        $today = Carbon::today()->toDateString();
        makeInvoice($this->user, ['open_date' => $today, 'total' => 200.00]);

        $this->service->recomputeForDate($this->user, Carbon::today());

        $this->assertDatabaseHas('invoice_reports', [
            'user_id' => $this->user->id,
            'report_date' => $today,
            'invoice_count' => 1,
            'total_amount' => '200.00',
        ]);
    });

    test('recomputeForDate updates an existing row idempotently', function () {
        $today = Carbon::today()->toDateString();
        makeInvoice($this->user, ['open_date' => $today, 'total' => 100.00]);
        $this->service->recomputeForDate($this->user, Carbon::today());

        // Add a second invoice and recompute — must not duplicate rows.
        makeInvoice($this->user, ['open_date' => $today, 'total' => 50.00]);
        $this->service->recomputeForDate($this->user, Carbon::today());

        $this->assertEquals(1, InvoiceReport::where('user_id', $this->user->id)->count());
        $this->assertDatabaseHas('invoice_reports', [
            'user_id' => $this->user->id,
            'report_date' => $today,
            'invoice_count' => 2,
            'total_amount' => '150.00',
        ]);
    });

    test('recomputeForDate counts only Received invoices in received_amount', function () {
        $today = Carbon::today()->toDateString();
        makeInvoice($this->user, ['open_date' => $today, 'status' => 'Draft',    'total' => 100.00]);
        makeInvoice($this->user, ['open_date' => $today, 'status' => 'Received', 'total' => 200.00]);

        $this->service->recomputeForDate($this->user, Carbon::today());

        $this->assertDatabaseHas('invoice_reports', [
            'user_id' => $this->user->id,
            'report_date' => $today,
            'total_amount' => '300.00',
            'received_amount' => '200.00',
            'invoice_count' => 2,
            'received_count' => 1,
        ]);
    });

    test('recomputeForDate is scoped to user', function () {
        $otherUser = User::factory()->create();
        $today = Carbon::today()->toDateString();

        makeInvoice($this->user, ['open_date' => $today, 'total' => 100.00]);
        makeInvoice($otherUser, ['open_date' => $today, 'total' => 500.00]);

        $this->service->recomputeForDate($this->user, Carbon::today());

        $this->assertDatabaseHas('invoice_reports', [
            'user_id' => $this->user->id,
            'invoice_count' => 1,
            'total_amount' => '100.00',
        ]);
        // Other user's data should not be in our report.
        $this->assertDatabaseMissing('invoice_reports', ['user_id' => $otherUser->id]);
    });

    test('zeroes out the report row when no invoices remain for that date', function () {
        $today = Carbon::today()->toDateString();
        $invoice = makeInvoice($this->user, ['open_date' => $today, 'total' => 100.00]);

        $this->service->recomputeForDate($this->user, Carbon::today());
        $this->assertDatabaseHas('invoice_reports', ['user_id' => $this->user->id, 'report_date' => $today]);

        // Remove the invoice and recompute — row should have zero values.
        $invoice->delete();
        $this->service->recomputeForDate($this->user, Carbon::today());

        $this->assertDatabaseHas('invoice_reports', [
            'user_id' => $this->user->id,
            'report_date' => $today,
            'invoice_count' => 0,
            'total_amount' => '0.00',
        ]);
    });

    test('getSummary returns correct daily, monthly, and yearly aggregates', function () {
        $today = Carbon::today()->toDateString();
        $lastMonth = Carbon::today()->subMonth()->toDateString();
        $lastYear = Carbon::today()->subYear()->toDateString();

        // Today — shows in daily + monthly + yearly
        makeInvoice($this->user, ['open_date' => $today, 'status' => 'Received', 'total' => 100.00]);
        $this->service->recomputeForDate($this->user, Carbon::today());

        // Last month — shows in yearly only
        makeInvoice($this->user, ['open_date' => $lastMonth, 'total' => 200.00]);
        $this->service->recomputeForDate($this->user, Carbon::parse($lastMonth));

        // Last year — not included in yearly (>= start of current year)
        makeInvoice($this->user, ['open_date' => $lastYear, 'total' => 999.00]);
        $this->service->recomputeForDate($this->user, Carbon::parse($lastYear));

        $summary = $this->service->getSummary($this->user);

        expect($summary['daily']['total_amount'])->toBe(100.0)
            ->and($summary['daily']['received_amount'])->toBe(100.0)
            ->and($summary['daily']['invoice_count'])->toBe(1);

        expect($summary['monthly']['total_amount'])->toBe(100.0)
            ->and($summary['monthly']['invoice_count'])->toBe(1);

        expect($summary['yearly']['total_amount'])->toBe(300.0)
            ->and($summary['yearly']['invoice_count'])->toBe(2);
    });
});

// ──────────────────────────────────────────────
// Dashboard Inertia prop
// ──────────────────────────────────────────────

describe('Dashboard reportSummary prop', function () {
    test('dashboard renders with correct reportSummary structure', function () {
        $user = User::factory()->create();
        actingAs($user);

        get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('reportSummary')
                ->has('reportSummary.daily')
                ->has('reportSummary.monthly')
                ->has('reportSummary.yearly')
                ->has('reportSummary.daily.total_amount')
                ->has('reportSummary.daily.received_amount')
                ->has('reportSummary.daily.invoice_count')
                ->has('reportSummary.daily.received_count')
            );
    });

    test('dashboard reportSummary reflects invoices created today', function () {
        $user = User::factory()->create();
        actingAs($user);

        $today = Carbon::today()->toDateString();
        makeInvoice($user, ['open_date' => $today, 'status' => 'Received', 'total' => 250.00]);
        app(InvoiceReportService::class)->recomputeForDate($user, Carbon::today());

        get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('reportSummary.daily.total_amount', 250)
                ->where('reportSummary.daily.received_amount', 250)
                ->where('reportSummary.daily.invoice_count', 1)
                ->where('reportSummary.daily.received_count', 1)
            );
    });
});

// ──────────────────────────────────────────────
// reports:backfill command
// ──────────────────────────────────────────────

describe('reports:backfill command', function () {
    test('backfill creates report rows for all users', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        makeInvoice($user1, ['open_date' => Carbon::today()->toDateString(), 'total' => 100.00]);
        makeInvoice($user2, ['open_date' => Carbon::today()->toDateString(), 'total' => 200.00]);

        artisan('reports:backfill')->assertSuccessful();

        $this->assertDatabaseHas('invoice_reports', ['user_id' => $user1->id, 'invoice_count' => 1]);
        $this->assertDatabaseHas('invoice_reports', ['user_id' => $user2->id, 'invoice_count' => 1]);
    });

    test('backfill accepts --user option to limit scope', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        makeInvoice($user1, ['open_date' => Carbon::today()->toDateString(), 'total' => 100.00]);
        makeInvoice($user2, ['open_date' => Carbon::today()->toDateString(), 'total' => 200.00]);

        artisan('reports:backfill', ['--user' => $user1->id])->assertSuccessful();

        $this->assertDatabaseHas('invoice_reports', ['user_id' => $user1->id]);
        $this->assertDatabaseMissing('invoice_reports', ['user_id' => $user2->id]);
    });

    test('backfill is idempotent — running twice yields the same result', function () {
        $user = User::factory()->create();
        makeInvoice($user, ['open_date' => Carbon::today()->toDateString(), 'total' => 100.00]);

        artisan('reports:backfill')->assertSuccessful();
        artisan('reports:backfill')->assertSuccessful();

        $this->assertEquals(1, InvoiceReport::where('user_id', $user->id)->count());
    });
});
