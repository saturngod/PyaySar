<?php

namespace App\Mcp\Prompts;

use App\Mcp\Concerns\ResolvesUser;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;

#[Description('Summarizes the authenticated user\'s outstanding receivables by status and total amount owed.')]
class ReceivablesSummaryPrompt extends Prompt
{
    use ResolvesUser;

    public function handle(Request $request): Response
    {
        $invoices = $this->resolveUser($request)->invoices()->get();

        $byStatus = $invoices->groupBy('status')->map(fn ($group) => [
            'count' => $group->count(),
            'total' => $group->sum('total'),
        ]);

        $totals = $byStatus->map(fn ($data, $status) => "{$status}: {$data['count']} invoices, {$data['total']}")->implode('; ');

        $system = 'You are a billing assistant. Summarize the following receivables snapshot into a concise status report, '.
            'highlighting amounts still outstanding (e.g. Sent and Draft statuses) versus collected. '.
            "Snapshot: {$totals}. Total invoices: {$invoices->count()}.";

        return Response::text($system)->asAssistant();
    }
}
