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

#[Description('Update an inventory item belonging to the authenticated user.')]
class UpdateItemTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected ItemService $items) {}

    public function handle(Request $request): ResponseFactory
    {
        $item = $this->resolveUser($request)->items()->findOrFail($request->integer('item_id'));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $item = $this->items->update($item, $validated);

        return Response::structured(['id' => $item->id, 'name' => $item->name, 'price' => $item->price]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'item_id' => $schema->integer()->description('ID of the item to update.')->required(),
            'name' => $schema->string()->required(),
            'price' => $schema->number()->required(),
            'description' => $schema->string(),
        ];
    }
}
