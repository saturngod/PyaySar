<?php

namespace App\Mcp\Resources;

use App\Mcp\Concerns\ResolvesUser;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Uri('pyaysar://dashboard')]
#[Description('Dashboard summary: customer and invoice counts plus the most recent draft invoices for the authenticated user.')]
#[MimeType('application/json')]
class DashboardResource extends Resource
{
    use ResolvesUser;

    public function handle(Request $request): Response
    {
        $user = $this->resolveUser($request);

        $drafts = $user->invoices()
            ->where('status', 'Draft')
            ->with('customer:id,name')
            ->latest('id')
            ->take(10)
            ->get();

        return Response::json([
            'total_customers' => $user->customers()->count(),
            'total_invoices' => $user->invoices()->count(),
            'draft_invoices' => $drafts->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'customer' => $invoice->customer?->name,
            ]),
        ]);
    }
}
