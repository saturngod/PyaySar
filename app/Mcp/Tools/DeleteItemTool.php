<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\ItemService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete an inventory item belonging to the authenticated user.')]
#[IsDestructive]
class DeleteItemTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected ItemService $items) {}

    public function handle(Request $request): Response
    {
        $item = $this->resolveUser($request)->items()->findOrFail($request->integer('item_id'));

        $this->items->delete($item);

        return Response::text("Item {$item->name} deleted.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'item_id' => $schema->integer()->description('ID of the item to delete.')->required(),
        ];
    }
}
