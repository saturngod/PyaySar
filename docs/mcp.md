# Pyaysar MCP Guide

Pyaysar exposes its full feature set — customers, inventory items, invoices (with line items and status history), and dashboard — over the **Model Context Protocol** using the [`laravel/mcp`](https://laravel.com/docs/mcp) package. Any MCP-compatible AI client (Claude Desktop, Cursor, OpenCode, etc.) can read and manage invoice data on a user's behalf.

This document describes the two transports, authentication, the complete catalog of tools/resources/prompts, and how to test and extend them.

---

## Table of contents

1. [Architecture](#architecture)
2. [Setup](#setup)
   - [Web transport (remote clients)](#web-transport-remote-clients)
   - [Local transport (Artisan)](#local-transport-artisan)
3. [Connecting an MCP client](#connecting-an-mcp-client)
4. [User resolution & security](#user-resolution--security)
5. [Primitive catalog](#primitive-catalog)
   - [Tools](#tools)
   - [Resources](#resources)
   - [Prompts](#prompts)
6. [Testing](#testing)
7. [Extending](#extending)

---

## Architecture

Everything lives under `app/Mcp/`:

| Path | Responsibility |
| --- | --- |
| `Servers/PyaysarServer.php` | The single MCP server. Registers all tools, resources, and prompts. |
| `Concerns/ResolvesUser.php` | Trait that resolves the acting user per transport. |
| `Tools/*Tool.php` | 14 tools (writes/actions). |
| `Resources/*Resource.php` | 4 resources (reads), three of them URI templates. |
| `Prompts/*Prompt.php` | 2 prompt templates. |

The server is registered in `routes/ai.php` for **both** transports:

```php
Mcp::web('/mcp', PyaysarServer::class)->middleware(['auth:sanctum']);
Mcp::local('pyaysar', PyaysarServer::class);
```

All MCP primitives operate on the **same business logic** as the HTTP controllers, via the shared service classes in `app/Services/` (`InvoiceService`, `CustomerService`, `ItemService`). There is no duplicated logic — add a behaviour to the service and both the web UI and MCP get it.

---

## Setup

### Prerequisites

MCP support is already installed (`composer require laravel/mcp laravel/sanctum`). The Sanctum `personal_access_tokens` migration has run and `User` uses the `HasApiTokens` trait.

### Web transport (remote clients)

The web transport is an HTTP endpoint at `POST /mcp` protected by Sanctum token auth. No additional setup is required — it is enabled by `routes/ai.php`.

To grant a client access, issue a token for a user:

```php
$token = $user->createToken('claude-desktop')->plainTextToken;
```

The plaintext token is shown once; store it securely. Requests must send it as a Bearer token:

```
Authorization: Bearer <plain-text-token>
```

### Local transport (Artisan)

The local transport runs the server as an Artisan process (`php artisan mcp:start pyaysar`). There is no HTTP session and therefore no authenticated user, so you must tell the server which user to act as.

Set `MCP_LOCAL_USER_ID` in `.env` to the ID of the user whose data the server should operate on:

```env
MCP_LOCAL_USER_ID=1
```

Without this value, every primitive returns an error instructing you to set it.

---

## Connecting an MCP client

### Claude Desktop / Cursor (remote, web transport)

Point the client at the web endpoint with a Sanctum token:

```json
{
  "mcpServers": {
    "pyaysar": {
      "url": "http://localhost:8000/mcp",
      "headers": {
        "Authorization": "Bearer <your-sanctum-token>"
      }
    }
  }
}
```

> Serve the app first with `composer run dev` (or any host that reaches `artisan serve`).

### Claude Desktop / OpenCode (local transport)

Run the local server over `stdio`:

```json
{
  "mcpServers": {
    "pyaysar": {
      "command": "php",
      "args": ["/absolute/path/to/pyaysar/artisan", "mcp:start", "pyaysar"]
    }
  }
}
```

The server resolves the user from `MCP_LOCAL_USER_ID`.

---

## User resolution & security

The `App\Mcp\Concerns\ResolvesUser` trait resolves the acting user for every primitive:

1. **Web transport** — `$request->user()` returns the Sanctum-authenticated user.
2. **Local transport** — falls back to the user with ID `config('mcp.local_user_id')` (env `MCP_LOCAL_USER_ID`).

Every query is **user-scoped**: invoices, customers, and items are loaded through `$user->invoices()`, `$user->customers()`, `$user->items()`. A request for another user's record (e.g. `invoice://invoices/99` where invoice 99 belongs to someone else) resolves to `null` and returns an error — **records never leak across users**. This mirrors the manual `abort(403)` ownership checks used in the HTTP controllers.

---

## Primitive catalog

### Tools

Tools perform actions. Inputs are validated against JSON Schema + Laravel rules. Tools that only read data are annotated `readOnly`; delete tools are annotated `destructive`.

#### Invoices

| Tool | Arguments | Description |
| --- | --- | --- |
| `list-invoices` | `status?`, `date_from?`, `date_to?`, `customer_id?` | List invoices, optionally filtered. *(readOnly)* |
| `create-invoice` | `invoice_number`, `customer_id`, `open_date`, `due_date?`, `status`, `currency`, `items[]`, `notes?`, `bank_account_info?` | Create an invoice; totals are computed from line items. |
| `update-invoice` | `invoice_id`, `invoice_number`, `customer_id`, `open_date`, `due_date?`, `status`, `currency`, `items[]`, `notes?`, `bank_account_info?` | Replace line items and recalculate totals; records a status-history entry when the status changes. |
| `change-invoice-status` | `invoice_id`, `status` | Transition status (`Draft`, `Sent`, `Received`, `Reject`); records history. |
| `delete-invoice` | `invoice_id` | Delete an invoice. *(destructive)* |
| `search-invoice-items` | `query` | Search previously-invoiced item names by keyword. *(readOnly)* |

`status` ∈ `Draft | Sent | Received | Reject`. `currency` ∈ `USD | MMK`.

Each `items[]` entry:

```json
{ "item_name": "Consulting", "description": "Optional", "qty": 2, "price": 50 }
```

#### Customers

| Tool | Arguments | Description |
| --- | --- | --- |
| `list-customers` | — | List customers. *(readOnly)* |
| `create-customer` | `name`, `email?`, `address?` | Create a customer. |
| `update-customer` | `customer_id`, `name`, `email?`, `address?` | Update a customer. |
| `delete-customer` | `customer_id` | Delete a customer (and its avatar). *(destructive)* |

> Avatars are not exposed over MCP (binary upload). Use the web UI to manage avatars.

#### Items (catalog)

| Tool | Arguments | Description |
| --- | --- | --- |
| `list-items` | `page?`, `per_page?` | Paginated list of catalog items. *(readOnly)* |
| `create-item` | `name`, `price`, `description?` | Create an item. |
| `update-item` | `item_id`, `name`, `price`, `description?` | Update an item. |
| `delete-item` | `item_id` | Delete an item. *(destructive)* |

### Resources

Resources return read-only JSON context. The three entity resources are **URI templates**.

| Resource | URI | Returns |
| --- | --- | --- |
| `InvoiceResource` | `invoice://invoices/{id}` | Invoice + line items + customer + status history |
| `CustomerResource` | `customer://customers/{id}` | Customer + their invoices (id, number, status, total) |
| `ItemResource` | `item://items/{id}` | Single catalog item |
| `DashboardResource` | `pyaysar://dashboard` | Customer/invoice counts + 10 most recent draft invoices |

Example — a client reads an invoice by requesting the URI `invoice://invoices/42`.

### Prompts

Prompts are reusable templates that yield a ready-to-use assistant message.

| Prompt | Arguments | Description |
| --- | --- | --- |
| `overdue-invoice-reminder` | `invoice_id`, `tone?` | Builds a payment-reminder prompt for an invoice past its due date. `tone` examples: `friendly`, `firm`, `formal`. |
| `receivables-summary` | — | Builds a prompt summarising outstanding receivables grouped by status. |

---

## Testing

MCP primitives are tested in `tests/Feature/Mcp/PyaysarServerTest.php` using the package's test helper, which bypasses the JSON-RPC handshake and calls the server directly:

```php
use App\Mcp\Servers\PyaysarServer;
use App\Mcp\Tools\CreateItemTool;

// Web-transport style: authenticate as a user
PyaysarServer::actingAs($user)
    ->tool(CreateItemTool::class, ['name' => 'Widget', 'price' => 9.99])
    ->assertOk()
    ->assertSee('Widget');

// Resources take the URI template variables:
PyaysarServer::actingAs($user)
    ->resource(InvoiceResource::class, ['id' => $invoice->id])
    ->assertOk();

// Local-transport style: bind the user via config instead
config(['mcp.local_user_id' => $user->id]);
PyaysarServer::tool(CreateItemTool::class, ['name' => 'Local', 'price' => 5])
    ->assertOk();
```

Available assertions: `assertOk()`, `assertSee($text)`, `assertDontSee($text)`, `assertStructuredContent(...)`, `assertHasErrors()`, `->dump()` / `->dd()`.

Run the suite:

```bash
composer run test                 # full suite
./vendor/bin/pest tests/Feature/Mcp/PyaysarServerTest.php   # MCP only
```

---

## Extending

1. **Add the logic to a service.** `app/Services/*Service.php` are the single source of truth. If the behaviour is new, add a method there.
2. **Create the primitive.** `php artisan make:mcp-tool ListDeliveriesTool` (or `make:mcp-resource`, `make:mcp-prompt`).
3. **Resolve the user** with `use App\Mcp\Concerns\ResolvesUser;` and call `$this->resolveUser($request)`. Scope all queries through that user.
4. **Return type matters.** A tool that returns `Response::structured(...)` must declare `handle(): Response|ResponseFactory` and `use Laravel\Mcp\ResponseFactory;`. Tools returning only `Response::text()` / `Response::json()` may keep `: Response`.
5. **Register it** in `app/Mcp/Servers/PyaysarServer.php` (`$tools`, `$resources`, or `$prompts`).
6. **Test it** with the helper above, including a cross-user isolation case.

### End-to-end debugging

The MCP package ships an interactive inspector for exploring a server's primitives over a real transport:

```bash
# Against the web transport:
npx @modelcontextprotocol/inspector

# Or inspect the local server directly:
php artisan mcp:start pyaysar
```
