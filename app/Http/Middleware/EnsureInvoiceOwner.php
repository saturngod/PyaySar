<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureInvoiceOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $invoice = $request->route('invoice');

        if (! $invoice instanceof Invoice) {
            abort(404);
        }

        if ($invoice->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        return $next($request);
    }
}
