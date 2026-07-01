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

#[Description('A single inventory item belonging to the authenticated user.')]
#[MimeType('application/json')]
class ItemResource extends Resource implements HasUriTemplate
{
    use ResolvesUser;

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('item://items/{id}');
    }

    public function handle(Request $request): Response
    {
        $item = $this->resolveUser($request)->items()->findOrFail($request->integer('id'));

        return Response::json([
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
        ]);
    }
}
