<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\CustomerService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update an existing customer belonging to the authenticated user.')]
class UpdateCustomerTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected CustomerService $customers) {}

    public function handle(Request $request): ResponseFactory
    {
        $customer = $this->resolveUser($request)->customers()->findOrFail($request->integer('customer_id'));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
        ]);

        $customer = $this->customers->update($customer, $validated);

        return Response::structured([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'customer_id' => $schema->integer()->description('ID of the customer to update.')->required(),
            'name' => $schema->string()->required(),
            'email' => $schema->string(),
            'address' => $schema->string(),
        ];
    }
}
