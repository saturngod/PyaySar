<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\OverdueInvoiceReminderPrompt;
use App\Mcp\Prompts\ReceivablesSummaryPrompt;
use App\Mcp\Resources\CustomerResource;
use App\Mcp\Resources\DashboardResource;
use App\Mcp\Resources\InvoiceResource;
use App\Mcp\Resources\ItemResource;
use App\Mcp\Tools\ChangeInvoiceStatusTool;
use App\Mcp\Tools\CreateCustomerTool;
use App\Mcp\Tools\CreateInvoiceTool;
use App\Mcp\Tools\CreateItemTool;
use App\Mcp\Tools\DeleteCustomerTool;
use App\Mcp\Tools\DeleteInvoiceTool;
use App\Mcp\Tools\DeleteItemTool;
use App\Mcp\Tools\ListCustomersTool;
use App\Mcp\Tools\ListInvoicesTool;
use App\Mcp\Tools\ListItemsTool;
use App\Mcp\Tools\SearchInvoiceItemsTool;
use App\Mcp\Tools\UpdateCustomerTool;
use App\Mcp\Tools\UpdateInvoiceTool;
use App\Mcp\Tools\UpdateItemTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Pyaysar')]
#[Version('1.0.0')]
#[Instructions('Manage customers, inventory items, and invoices (with line items and status history) for the authenticated user.')]
class PyaysarServer extends Server
{
    protected array $tools = [
        ListInvoicesTool::class,
        CreateInvoiceTool::class,
        UpdateInvoiceTool::class,
        DeleteInvoiceTool::class,
        ChangeInvoiceStatusTool::class,
        SearchInvoiceItemsTool::class,
        ListCustomersTool::class,
        CreateCustomerTool::class,
        UpdateCustomerTool::class,
        DeleteCustomerTool::class,
        ListItemsTool::class,
        CreateItemTool::class,
        UpdateItemTool::class,
        DeleteItemTool::class,
    ];

    protected array $resources = [
        InvoiceResource::class,
        CustomerResource::class,
        ItemResource::class,
        DashboardResource::class,
    ];

    protected array $prompts = [
        OverdueInvoiceReminderPrompt::class,
        ReceivablesSummaryPrompt::class,
    ];
}
