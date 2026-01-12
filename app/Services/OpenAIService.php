<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $model;

    private string $apiKey;

    private string $url;

    public function __construct()
    {
        $this->model = config('openai.model');
        $this->apiKey = config('openai.api_key');
        $this->url = config('openai.url');
    }

    public function chatWithFunctions(User $user, string $prompt, array $conversationHistory = []): array
    {
        $userContext = $this->getUserContext($user);

        // Build context about user's existing data
        $customers = $user->customers()->pluck('name')->take(20)->join(', ');
        $invoices = $user->invoices()->with('customer')->latest()->take(5)->get();
        $invoiceContext = $invoices->map(fn ($i) => "{$i->invoice_number} ({$i->customer?->name}, {$i->status})")->join(', ');

        $dataContext = '';
        if ($customers) {
            $dataContext .= "User's customers: {$customers}. ";
        }
        if ($invoiceContext) {
            $dataContext .= "Recent invoices: {$invoiceContext}. ";
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an invoice management assistant. Help users create, view, and manage their invoices. '
                    .'Use the available functions to handle invoice operations. '
                    ."\n\n## INVOICE CREATION FLOW\n"
                    .'When user wants to create an invoice, you need:\n'
                    .'1. **Customer name** - Ask "Who is this invoice for?" if not provided\n'
                    .'2. **Items** - Ask "What items/services to include?" if not provided\n'
                    .'3. **Currency** - Detect from item prices (e.g., "17 MMK" = MMK) or ask\n'
                    ."\n## ITEM PARSING\n"
                    .'Parse items from natural language. Examples:\n'
                    .'- "MPT - 17 MMK x 120" → item_name: "MPT", price: 17, qty: 120\n'
                    .'- "ATOM - 17 MMK x 12300" → item_name: "ATOM", price: 17, qty: 12300\n'
                    .'- "Web design $500" → item_name: "Web design", price: 500, qty: 1\n'
                    .'- "3 hours consulting at 100/hr" → item_name: "Consulting", price: 100, qty: 3\n'
                    ."\n## CURRENCY DETECTION\n"
                    .'Detect currency from prices: MMK, USD ($), EUR (€), etc. Default to user preference if not detected.\n'
                    ."\n## BEHAVIORS\n"
                    .'- If user provides ALL info in one message, call create_invoice immediately\n'
                    .'- If info is missing, ask ONE clarifying question at a time\n'
                    .'- Always confirm total before creating: "Invoice for [Customer]: [items]. Total: [amount]. Shall I create it?"\n'
                    .'- Status options: draft, sent, paid, overdue, cancelled\n'
                    ."\n".$dataContext.$userContext,
            ],
            ...$conversationHistory,
            ['role' => 'user', 'content' => $prompt],
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'tools' => $this->getFunctionSchemas(),
            'tool_choice' => 'auto',
        ];

        $response = $this->request('POST', '/chat/completions', $payload);

        Log::debug('OpenAI Function Calling Response', ['response' => $response]);

        $message = $response['choices'][0]['message'] ?? [];

        if (isset($message['tool_calls']) && ! empty($message['tool_calls'])) {
            $toolCall = $message['tool_calls'][0];
            $functionName = $toolCall['function']['name'] ?? '';
            $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode function arguments: '.json_last_error_msg());
            }

            return [
                'function' => $functionName,
                'arguments' => $arguments,
                'assistant_message' => [
                    'role' => 'assistant',
                    'content' => $message['content'] ?? '',
                    'tool_calls' => $message['tool_calls'],
                ],
            ];
        }

        $assistantMessage = [
            'role' => 'assistant',
            'content' => $message['content'] ?? '',
        ];

        return [
            'function' => 'chat',
            'arguments' => ['response' => $message['content'] ?? ''],
            'assistant_message' => $assistantMessage,
        ];
    }

    private function getUserContext(User $user): string
    {
        $now = now();

        return sprintf(
            'Current Date: %s. Current Time: %s.',
            $now->format('Y-m-d'),
            $now->format('H:i:s')
        );
    }

    private function getFunctionSchemas(): array
    {
        return [
            $this->createInvoiceSchema(),
            $this->listInvoicesSchema(),
            $this->getInvoiceDetailsSchema(),
        ];
    }

    private function createInvoiceSchema(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'create_invoice',
                'description' => 'Create a new invoice for a customer. '
                    .'Parse items from natural language like "MPT - 17 MMK x 120" means item_name=MPT, price=17, qty=120. '
                    .'Required fields: customer_name, items (array of {item_name, qty, price}), currency. '
                    .'Optional: due_date, notes, status (default: draft).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'customer_name' => [
                            'type' => 'string',
                            'description' => 'The name of the customer. Will match existing customer or create new one.',
                        ],
                        'items' => [
                            'type' => 'array',
                            'description' => 'List of invoice line items. Parse from formats like "ItemName - Price Currency x Qty".',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'item_name' => ['type' => 'string', 'description' => 'Name of the item/service'],
                                    'qty' => ['type' => 'number', 'description' => 'Quantity'],
                                    'price' => ['type' => 'number', 'description' => 'Unit price (numeric only, no currency symbol)'],
                                    'description' => ['type' => 'string', 'description' => 'Optional item description'],
                                ],
                                'required' => ['item_name', 'qty', 'price'],
                            ],
                        ],
                        'currency' => [
                            'type' => 'string',
                            'description' => 'Currency code detected from prices (e.g., MMK, USD, EUR). Extract from expressions like "17 MMK" or "$500".',
                        ],
                        'due_date' => [
                            'type' => 'string',
                            'description' => 'Due date in YYYY-MM-DD format. Optional.',
                        ],
                        'notes' => [
                            'type' => 'string',
                            'description' => 'Additional notes for the invoice.',
                        ],
                        'status' => [
                            'type' => 'string',
                            'description' => 'Invoice status: draft, sent, paid, overdue, cancelled. Defaults to draft.',
                            'enum' => ['draft', 'sent', 'paid', 'overdue', 'cancelled'],
                        ],
                    ],
                    'required' => ['customer_name', 'items'],
                ],
            ],
        ];
    }

    private function listInvoicesSchema(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'list_invoices',
                'description' => 'List invoices with optional filters. Use for queries like "show my invoices", "recent invoices", "paid invoices".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => [
                            'type' => 'string',
                            'description' => 'Filter by status: draft, sent, paid, overdue, cancelled',
                            'enum' => ['draft', 'sent', 'paid', 'overdue', 'cancelled'],
                        ],
                        'customer_name' => [
                            'type' => 'string',
                            'description' => 'Filter by customer name.',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of invoices to return (default: 10).',
                        ],
                    ],
                    'required' => [],
                ],
            ],
        ];
    }

    private function getInvoiceDetailsSchema(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'get_invoice_details',
                'description' => 'Get details of a specific invoice by invoice number or ID.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'invoice_number' => [
                            'type' => 'string',
                            'description' => 'The invoice number (e.g., INV-123).',
                        ],
                    ],
                    'required' => ['invoice_number'],
                ],
            ],
        ];
    }

    private function request(string $method, string $endpoint, array $payload): array
    {
        $url = rtrim($this->url, '/').$endpoint;

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$this->apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL error: '.$error);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('OpenAI API error: HTTP '.$httpCode.' - '.$response);
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode API response: '.json_last_error_msg());
        }

        return $result;
    }
}
