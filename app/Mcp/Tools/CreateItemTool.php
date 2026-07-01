<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\ItemService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new inventory item (product or service) for the authenticated user.')]
class CreateItemTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected ItemService $items) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $item = $this->items->create($this->resolveUser($request), $validated);

        return Response::structured(['id' => $item->id, 'name' => $item->name]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Item name.')->required(),
            'price' => $schema->number()->description('Unit price.')->required(),
            'description' => $schema->string()->description('Optional description.'),
        ];
    }
}
