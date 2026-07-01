<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\CustomerService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete a customer belonging to the authenticated user.')]
#[IsDestructive]
class DeleteCustomerTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected CustomerService $customers) {}

    public function handle(Request $request): Response
    {
        $customer = $this->resolveUser($request)->customers()->findOrFail($request->integer('customer_id'));

        $this->customers->delete($customer);

        return Response::text("Customer {$customer->name} deleted.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'customer_id' => $schema->integer()->description('ID of the customer to delete.')->required(),
        ];
    }
}
