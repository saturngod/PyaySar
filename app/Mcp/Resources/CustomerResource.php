<?php

namespace App\Mcp\Resources;

use App\Mcp\Concerns\ResolvesUser;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Description('A single customer belonging to the authenticated user.')]
#[MimeType('application/json')]
class CustomerResource extends Resource implements HasUriTemplate
{
    use ResolvesUser;

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('customer://customers/{id}');
    }

    public function handle(Request $request): Response
    {
        $customer = $this->resolveUser($request)->customers()->findOrFail($request->integer('id'));

        return Response::json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'address' => $customer->address,
            'invoices' => $customer->invoices()->select('id', 'invoice_number', 'status', 'total')->get(),
        ]);
    }
}
