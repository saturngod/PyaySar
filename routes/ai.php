<?php

use App\Mcp\Servers\PyaysarServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', PyaysarServer::class)->middleware(['auth:sanctum']);

Mcp::local('pyaysar', PyaysarServer::class);
