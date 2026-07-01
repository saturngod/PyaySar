<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\ResolvesUser;
use App\Services\ItemService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List inventory items (catalog) for the authenticated user, paginated.')]
#[IsReadOnly]
class ListItemsTool extends Tool
{
    use ResolvesUser;

    public function __construct(protected ItemService $items) {}

    public function handle(Request $request): ResponseFactory
    {
        $paginator = $this->items->list($this->resolveUser($request), $request->only(['page', 'per_page']));

        return Response::structured([
            'items' => $paginator->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
            ]),
            'page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ]);
    }
}
