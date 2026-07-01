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

#[Description('Create a new customer for the authenticated user.')]
class CreateCustomerTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected CustomerService $customers) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
        ]);

        $customer = $this->customers->create($this->resolveUser($request), $validated);

        return Response::structured([
            'id' => $customer->id,
            'name' => $customer->name,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Customer display name.')->required(),
            'email' => $schema->string()->description('Contact email address.'),
            'address' => $schema->string()->description('Postal or billing address.'),
        ];
    }
}
