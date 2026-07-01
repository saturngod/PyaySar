<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemService
{
    /**
     * @param  array<string, mixed>  $pagination
     */
    public function list(User $user, array $pagination = []): LengthAwarePaginator
    {
        return $user->items()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($pagination['per_page'] ?? 10, page: $pagination['page'] ?? 1);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Item
    {
        return $user->items()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Item $item, array $data): Item
    {
        $item->update($data);

        return $item->fresh();
    }

    public function delete(Item $item): void
    {
        $item->delete();
    }
}
