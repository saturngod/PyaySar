<?php

namespace App\Mcp\Concerns;

use App\Models\User;
use Laravel\Mcp\Request;
use RuntimeException;

trait ResolvesUser
{
    /**
     * Resolve the acting user: the Sanctum-authenticated user on the web
     * transport, or the configured local user on the Artisan transport.
     *
     * @throws RuntimeException
     */
    protected function resolveUser(Request $request): User
    {
        $user = $request->user();

        if ($user instanceof User) {
            return $user;
        }

        $id = (int) config('mcp.local_user_id', 0);

        if ($id < 1) {
            throw new RuntimeException(
                'No authenticated MCP user. Authenticate via Sanctum (web transport) or set MCP_LOCAL_USER_ID (local transport).'
            );
        }

        return User::findOrFail($id);
    }
}
