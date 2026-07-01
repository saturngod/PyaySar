<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local MCP Server User
    |--------------------------------------------------------------------------
    |
    | When the MCP server runs via the local (Artisan) transport, there is no
    | HTTP request and therefore no authenticated session. Set MCP_LOCAL_USER_ID
    | to the ID of the user whose data the local server should operate on.
    | The web transport ignores this value and uses the Sanctum-authenticated
    | user instead.
    |
    */

    'local_user_id' => env('MCP_LOCAL_USER_ID'),

];
