# Pyaysar MCP Guide

MCP (Model Context Protocol) lets AI tools (Claude Desktop, Cursor, etc.) talk to your Pyaysar app. The AI can create invoices, list customers, check dashboard — same as the web UI.

This guide shows you **how to set it up** and **how to use it**.

---

## Two ways to connect

| Way | How it works | When to use |
|-----|-------------|-------------|
| **Remote (HTTP)** | AI sends HTTP requests to your server | Your app is running on a server, AI is on another machine |
| **Local (Artisan)** | AI runs a command on your machine directly | You are developing locally, same machine |

---

## Way 1: Remote (HTTP) — for production or remote access

### Step 1: Start your app

```bash
composer run dev
```

Your app runs at `http://localhost:8000`.

### Step 2: Create an API token

Open `php artisan tinker` and run:

```php
$user = App\Models\User::find(1);
$token = $user->createToken('my-ai-client')->plainTextToken;
echo $token;
```

Copy the token. You need it next.

### Step 3: Tell your AI client to connect

**Claude Desktop** — edit `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "pyaysar": {
      "url": "http://localhost:8000/mcp",
      "headers": {
        "Authorization": "Bearer YOUR_TOKEN_HERE"
      }
    }
  }
}
```

Replace `YOUR_TOKEN_HERE` with the token from Step 2.

**Cursor** — same config in `.cursor/mcp.json`.

### Step 4: Use it

Open Claude Desktop (or Cursor). The AI can now:
- "Show me all invoices" → it calls `list-invoices`
- "Create a new customer named ABC" → it calls `create-customer`
- "What's on my dashboard?" → it reads `pyaysar://dashboard`

---

## Way 2: Local (Artisan) — for local development

### Step 1: Set your user ID

In your `.env` file, add:

```
MCP_LOCAL_USER_ID=1
```

This tells the MCP server: "Act as user ID 1."

### Step 2: Tell your AI client to connect

**Claude Desktop** — edit `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "pyaysar": {
      "command": "php",
      "args": ["/full/path/to/pyaysar/artisan", "mcp:start", "pyaysar"]
    }
  }
}
```

Replace `/full/path/to/pyaysar/` with your actual project path. Example:

```json
{
  "mcpServers": {
    "pyaysar": {
      "command": "php",
      "args": ["/Users/you/Code/pyaysar/artisan", "mcp:start", "pyaysar"]
    }
  }
}
```

**OpenCode** — same config in `opencode.json`:

```json
{
  "mcp": {
    "servers": {
      "pyaysar": {
        "type": "local",
        "command": "php",
        "args": ["/Users/you/Code/pyaysar/artisan", "mcp:start", "pyaysar"]
      }
    }
  }
}
```

### Step 3: Use it

Same as remote — the AI can now manage your invoices, customers, and items.

---

## What can the AI do?

### Tools (actions — the AI can create, update, delete)

#### Invoices

| Tool name | What it does |
|-----------|-------------|
| `list-invoices` | List all your invoices. Can filter by status, date, customer. |
| `create-invoice` | Create a new invoice with line items. |
| `update-invoice` | Update an existing invoice. |
| `change-invoice-status` | Change status: Draft → Sent → Received or Reject. |
| `delete-invoice` | Delete an invoice. |
| `search-invoice-items` | Search items you invoiced before. |

#### Customers

| Tool name | What it does |
|-----------|-------------|
| `list-customers` | List all customers. |
| `create-customer` | Create a customer (name, email, address). |
| `update-customer` | Update a customer. |
| `delete-customer` | Delete a customer. |

#### Items (your product catalog)

| Tool name | What it does |
|-----------|-------------|
| `list-items` | List catalog items (paginated). |
| `create-item` | Create an item (name, price, description). |
| `update-item` | Update an item. |
| `delete-item` | Delete an item. |

### Resources (read-only data — the AI can read)

| Resource | What it returns |
|----------|----------------|
| `invoice://invoices/{id}` | One invoice with all details, line items, customer info, status history. |
| `customer://customers/{id}` | One customer with their invoice list. |
| `item://items/{id}` | One catalog item. |
| `pyaysar://dashboard` | Summary: customer count, invoice count, recent drafts. |

### Prompts (templates for common AI tasks)

| Prompt | What it does |
|--------|-------------|
| `overdue-invoice-reminder` | Writes a payment reminder email for an overdue invoice. |
| `receivables-summary` | Summarizes all outstanding invoices grouped by status. |

---

## How to check if it works

### Test with the inspector tool

```bash
# Test remote (HTTP) — make sure your app is running first
npx @modelcontextprotocol/inspector

# Test local (Artisan)
php artisan mcp:start pyaysar
```

### Test with Claude Desktop

1. Add the config above to `claude_desktop_config.json`
2. Restart Claude Desktop
3. Ask: "List my invoices" or "Show my dashboard"
4. If it responds with your data, it works!

---

## Security notes

- **Remote access** requires a Sanctum token. No token = no access.
- **Local access** uses the user ID from `.env`. Only use this on your own machine.
- The AI can only see **your data**. It cannot see other users' invoices or customers.
- If something is wrong, the AI gets an error — it never sees data from other users.

---

## FAQ

**Q: The AI says "connection refused" or "no response"**
A: For remote — make sure `composer run dev` is running. For local — check that the path to `artisan` is correct in the config.

**Q: The AI says "user not found" or "set MCP_LOCAL_USER_ID"**
A: You are using local mode but forgot to set `MCP_LOCAL_USER_ID=1` in `.env`.

**Q: Can I use this with my production server?**
A: Yes, use the remote (HTTP) way. Create a token for each AI client that needs access.

**Q: Can the AI upload files or avatars?**
A: No. Avatars are binary files — use the web UI for that.

---

## Quick reference

| What you want | Config to use |
|--------------|---------------|
| AI on same machine as your code | Local mode (`MCP_LOCAL_USER_ID` + artisan command) |
| AI on different machine / cloud | Remote mode (HTTP URL + Sanctum token) |
| Claude Desktop | `claude_desktop_config.json` |
| Cursor | `.cursor/mcp.json` |
| OpenCode | `opencode.json` |

---

## For developers: how to add new features

1. Add logic to `app/Services/*Service.php` (e.g. `InvoiceService`)
2. Create tool: `php artisan make:mcp-tool MyNewTool`
3. In the tool, use `use App\Mcp\Concerns\ResolvesUser;` and call `$this->resolveUser($request)`
4. Register it in `app/Mcp/Servers/PyaysarServer.php`
5. Test it with `PyaysarServer::actingAs($user)->tool(MyNewTool::class, [...])`

```bash
# Run tests
composer run test

# Run only MCP tests
./vendor/bin/pest tests/Feature/Mcp/PyaysarServerTest.php
```
