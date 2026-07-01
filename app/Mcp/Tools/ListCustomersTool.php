<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\CustomerService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List customers for the authenticated user.')]
#[IsReadOnly]
class ListCustomersTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected CustomerService $customers) {}

    public function handle(Request $request): ResponseFactory
    {
        $customers = $this->customers->list($this->resolveUser($request));

        return Response::structured([
            'customers' => collect($customers)->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'address' => $customer->address,
            ]),
        ]);
    }
}
