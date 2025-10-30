# Phase 3: CRUD Operations & Routing

## Overview
This phase implements all the controllers, routes, and business logic for creating, reading, updating, and deleting the core entities of the invoice management system.

## Objectives
- Create resource controllers for all main entities
- Implement comprehensive routing system
- Add validation and business logic
- Handle file uploads and document generation
- Implement filtering and search functionality

## Tasks

### 3.1 Item Management CRUD
- [ ] Create `ItemController` with all CRUD methods
- [ ] Implement item validation rules
- [ ] Add item listing with pagination
- [ ] Create item form views (create/edit)
- [ ] Implement item search and filtering
- [ ] Add currency handling

### 3.2 Customer Management CRUD
- [ ] Create `CustomerController` with all CRUD methods
- [ ] Implement customer validation rules
- [ ] Add customer listing with search
- [ ] Create customer form views
- [ ] Implement customer duplicate detection
- [ ] Add customer contact management

### 3.3 Quote Management CRUD
- [ ] Create `QuoteController` with all CRUD methods
- [ ] Implement quote status management
- [ ] Add quote number auto-generation
- [ ] Create quote item management
- [ ] Implement quote calculations
- [ ] Add quote filtering by status and date

### 3.4 Invoice Management CRUD
- [ ] Create `InvoiceController` with all CRUD methods
- [ ] Implement invoice status management
- [ ] Add invoice number auto-generation
- [ ] Create invoice item management
- [ ] Implement quote-to-invoice conversion
- [ ] Add invoice filtering and search

### 3.5 Settings Management
- [ ] Create `SettingController` for user settings
- [ ] Implement company information CRUD
- [ ] Add default currency and terms management
- [ ] Create PDF customization options
- [ ] Handle logo file uploads

### 3.6 Route Implementation
- [ ] Set up all resource routes
- [ ] Add custom routes for special functionality
- [ ] Implement route model binding
- [ ] Add route caching for performance
- [ ] Configure middleware for different route groups

### 3.7 Advanced Functionality
- [ ] Implement quote to invoice conversion
- [ ] Add item reuse in quotes/invoices
- [ ] Create bulk operations (delete, status change)
- [ ] Implement export functionality
- [ ] Add duplicate quote/invoice functionality

### 3.8 Form Requests & Validation
- [ ] Create `StoreItemRequest`
- [ ] Create `UpdateItemRequest`
- [ ] Create `StoreCustomerRequest`
- [ ] Create `StoreQuoteRequest`
- [ ] Create `StoreInvoiceRequest`
- [ ] Create `UpdateSettingRequest`

## Controllers Implementation

### ItemController
```php
class ItemController extends Controller
{
    public function index() // List items with search and pagination
    public function create() // Show create item form
    public function store(StoreItemRequest $request) // Save new item
    public function show(Item $item) // Show item details
    public function edit(Item $item) // Show edit item form
    public function update(UpdateItemRequest $request, Item $item) // Update item
    public function destroy(Item $item) // Delete item
}
```

### QuoteController
```php
class QuoteController extends Controller
{
    public function index() // List quotes with filters
    public function create() // Show create quote form
    public function store(StoreQuoteRequest $request) // Save new quote
    public function show(Quote $quote) // Show quote details
    public function edit(Quote $quote) // Show edit quote form
    public function update(UpdateQuoteRequest $request, Quote $quote) // Update quote
    public function destroy(Quote $quote) // Delete quote
    public function download(Quote $quote) // Download PDF
    public function addItem(Quote $quote) // Add item to quote
    public function removeItem(QuoteItem $quoteItem) // Remove item from quote
}
```

### InvoiceController
```php
class InvoiceController extends Controller
{
    public function index() // List invoices with filters
    public function create() // Show create invoice form
    public function createFromQuote(Quote $quote) // Create from quote
    public function store(StoreInvoiceRequest $request) // Save new invoice
    public function show(Invoice $invoice) // Show invoice details
    public function edit(Invoice $invoice) // Show edit invoice form
    public function update(UpdateInvoiceRequest $request, Invoice $invoice) // Update invoice
    public function destroy(Invoice $invoice) // Delete invoice
    public function download(Invoice $invoice) // Download PDF
    public function markPaid(Invoice $invoice) // Mark as paid
    public function addItem(Invoice $invoice) // Add item to invoice
    public function removeItem(InvoiceItem $invoiceItem) // Remove item from invoice
}
```

## Routes Configuration

### Main Routes Structure
```php
Route::middleware(['auth'])->group(function () {
    // Items
    Route::resource('items', ItemController::class);
    Route::get('items/search', [ItemController::class, 'search'])->name('items.search');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');

    // Quotes
    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');
    Route::post('quotes/{quote}/items', [QuoteController::class, 'addItem'])->name('quotes.items.add');
    Route::delete('quotes/items/{quoteItem}', [QuoteController::class, 'removeItem'])->name('quotes.items.remove');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('invoices/from-quote/{quote}', [InvoiceController::class, 'createFromQuote'])->name('invoices.from.quote');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem'])->name('invoices.items.add');
    Route::delete('invoices/items/{invoiceItem}', [InvoiceController::class, 'removeItem'])->name('invoices.items.remove');

    // Settings
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
```

## Business Logic Implementation

### Quote Number Generation
```php
protected function generateQuoteNumber()
{
    $prefix = 'Q-' . date('Y');
    $latest = Quote::where('quote_number', 'like', $prefix . '%')
                   ->where('user_id', auth()->id())
                   ->orderBy('quote_number', 'desc')
                   ->first();

    $number = $latest ? intval(substr($latest->quote_number, -4)) + 1 : 1;
    return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
}
```

### Quote to Invoice Conversion
```php
public function createFromQuote(Quote $quote)
{
    $invoice = $quote->replicate();
    $invoice->invoice_number = $this->generateInvoiceNumber();
    $invoice->quote_id = $quote->id;
    $invoice->status = 'Draft';
    $invoice->save();

    foreach ($quote->quoteItems as $quoteItem) {
        $invoiceItem = $quoteItem->replicate();
        $invoiceItem->invoice_id = $invoice->id;
        $invoiceItem->save();
    }

    return redirect()->route('invoices.edit', $invoice);
}
```

## File Structure (New Files)
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ItemController.php
│   │   ├── CustomerController.php
│   │   ├── QuoteController.php
│   │   ├── InvoiceController.php
│   │   └── SettingController.php
│   └── Requests/
│       ├── StoreItemRequest.php
│       ├── UpdateItemRequest.php
│       ├── StoreCustomerRequest.php
│       ├── UpdateCustomerRequest.php
│       ├── StoreQuoteRequest.php
│       ├── UpdateQuoteRequest.php
│       ├── StoreInvoiceRequest.php
│       ├── UpdateInvoiceRequest.php
│       └── UpdateSettingRequest.php
├── Services/
│   ├── NumberGeneratorService.php
│   ├── QuoteToInvoiceService.php
│   └── PdfGenerationService.php (basic setup)
resources/
├── views/
│   ├── items/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── customers/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── quotes/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   └── partials/
│   │       └── item-form.blade.php
│   ├── invoices/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── create-from-quote.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   └── partials/
│   │       └── item-form.blade.php
│   └── settings/
│       └── edit.blade.php
routes/
└── web.php (updated)
tests/
├── Feature/
│   ├── ItemCRUDTest.php
│   ├── CustomerCRUDTest.php
│   ├── QuoteCRUDTest.php
│   ├── InvoiceCRUDTest.php
│   └── SettingTest.php
```

## Validation Rules Examples

### StoreQuoteRequest
```php
public function rules()
{
    return [
        'customer_id' => 'required|exists:customers,id',
        'title' => 'required|string|max:255',
        'po_number' => 'nullable|string|max:50',
        'date' => 'required|date',
        'currency' => 'required|string|size:3',
        'terms' => 'nullable|string',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.item_id' => 'required|exists:items,id',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.qty' => 'required|integer|min:1',
    ];
}
```

## Acceptance Criteria
- [ ] All CRUD operations work for Items, Customers, Quotes, Invoices
- [ ] Form validation prevents invalid data entry
- [ ] Quote numbers generate automatically and are unique
- [ ] Invoice numbers generate automatically and are unique
- [ ] Quote to invoice conversion preserves all data
- [ ] Calculations for totals and subtotals are accurate
- [ ] Search and filtering functions work correctly
- [ ] File uploads for company logos work
- [ ] All controller methods have test coverage
- [ ] Form requests validate all inputs properly
- [ ] Route model binding works for all entities

## Dependencies
- Phase 1 and 2 completion
- Laravel's resource controllers
- Form request validation
- Route model binding

## Estimated Time
4-5 days

## Next Phase
After completing Phase 3, the application will have full CRUD functionality for all entities with proper validation and business logic, ready for implementing the user interface in Phase 4.