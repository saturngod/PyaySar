# Phase 2: Core Data Models & Relationships

## Overview
This phase focuses on creating all the database models, migrations, and relationships that form the backbone of the invoice management system.

## Objectives
- Create all database migrations for the application schema
- Build Eloquent models with proper relationships
- Implement factories for testing data generation
- Create seeders for initial data
- Set up model events and observers

## Tasks

### 2.1 Database Migrations
- [ ] Create `items` table migration
- [ ] Create `customers` table migration
- [ ] Create `quotes` table migration
- [ ] Create `quote_items` table migration
- [ ] Create `invoices` table migration
- [ ] Create `invoice_items` table migration
- [ ] Create `settings` table migration
- [ ] Add foreign key constraints
- [ ] Add indexes for performance

### 2.2 Eloquent Models
- [ ] Create `Item` model with relationships
- [ ] Create `Customer` model with relationships
- [ ] Create `Quote` model with relationships
- [ ] Create `QuoteItem` model with relationships
- [ ] Create `Invoice` model with relationships
- [ ] Create `InvoiceItem` model with relationships
- [ ] Create `Setting` model with relationships
- [ ] Enhance existing `User` model with relationships

### 2.3 Model Relationships
- [ ] User has many Items, Customers, Quotes, Invoices, Settings
- [ ] Customer has many Quotes and Invoices
- [ ] Quote belongs to User and Customer, has many QuoteItems
- [ ] QuoteItem belongs to Quote and Item
- [ ] Invoice belongs to User and Customer, may belong to Quote
- [ ] InvoiceItem belongs to Invoice and Item
- [ ] Setting belongs to User

### 2.4 Model Attributes & Methods
- [ ] Implement accessors and mutators for currency formatting
- [ ] Add calculated fields (sub_total, total)
- [ ] Create scopes for common queries (by status, by user, etc.)
- [ ] Implement model validation rules
- [ ] Add custom methods for business logic

### 2.5 Factories & Seeders
- [ ] Create `ItemFactory`
- [ ] Create `CustomerFactory`
- [ ] Create `QuoteFactory`
- [ ] Create `InvoiceFactory`
- [ ] Create `SettingFactory`
- [ ] Create `DatabaseSeeder` with sample data
- [ ] Add realistic test data generation

### 2.6 Model Events & Observers
- [ ] Set up model observers for logging
- [ ] Implement cascading deletes where appropriate
- [ ] Add automatic total calculations on save
- [ ] Handle status changes with events

## Database Schema

### Items Table
```sql
- id (Primary Key, Auto-increment)
- user_id (Foreign Key to users.id)
- name (string, 255)
- description (text, nullable)
- price (decimal, 10, 2)
- currency (string, 3) - ISO 4217 currency codes
- created_at (timestamp)
- updated_at (timestamp)
```

### Customers Table
```sql
- id (Primary Key, Auto-increment)
- user_id (Foreign Key to users.id)
- name (string, 255)
- contact_person (string, 255, nullable)
- contact_phone (string, 50, nullable)
- contact_email (string, 255, nullable)
- address (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### Quotes Table
```sql
- id (Primary Key, Auto-increment)
- user_id (Foreign Key to users.id)
- customer_id (Foreign Key to customers.id)
- title (string, 255)
- quote_number (string, 50, nullable, unique)
- po_number (string, 50, nullable)
- date (date)
- currency (string, 3)
- status (enum: 'Draft', 'Sent', 'Seen')
- sub_total (decimal, 10, 2)
- discount_amount (decimal, 10, 2, default: 0)
- total (decimal, 10, 2)
- terms (text, nullable)
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### Quote Items Table
```sql
- id (Primary Key, Auto-increment)
- quote_id (Foreign Key to quotes.id)
- item_id (Foreign Key to items.id)
- price (decimal, 10, 2)
- qty (integer, default: 1)
- created_at (timestamp)
- updated_at (timestamp)
```

### Invoices Table
```sql
- id (Primary Key, Auto-increment)
- user_id (Foreign Key to users.id)
- customer_id (Foreign Key to customers.id)
- quote_id (Foreign Key to quotes.id, nullable)
- title (string, 255)
- invoice_number (string, 50, nullable, unique)
- po_number (string, 50, nullable)
- date (date)
- due_date (date, nullable)
- currency (string, 3)
- status (enum: 'Draft', 'Sent', 'Paid', 'Cancel')
- sub_total (decimal, 10, 2)
- discount_amount (decimal, 10, 2, default: 0)
- total (decimal, 10, 2)
- terms (text, nullable)
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### Invoice Items Table
```sql
- id (Primary Key, Auto-increment)
- invoice_id (Foreign Key to invoices.id)
- item_id (Foreign Key to items.id)
- price (decimal, 10, 2)
- qty (integer, default: 1)
- created_at (timestamp)
- updated_at (timestamp)
```

### Settings Table
```sql
- id (Primary Key, Auto-increment)
- user_id (Foreign Key to users.id)
- company_logo (string, 255, nullable)
- company_name (string, 255, nullable)
- company_address (text, nullable)
- company_email (string, 255, nullable)
- currency (string, 3, nullable)
- default_terms (text, nullable)
- default_notes (text, nullable)
- pdf_settings (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

## File Structure (New Files)
```
app/
├── Models/
│   ├── Item.php
│   ├── Customer.php
│   ├── Quote.php
│   ├── QuoteItem.php
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   ├── Setting.php
│   └── User.php (enhanced)
├── Observers/
│   ├── ItemObserver.php
│   ├── QuoteObserver.php
│   └── InvoiceObserver.php
database/
├── migrations/
│   ├── 2024_XX_XX_XXXXXX_create_items_table.php
│   ├── 2024_XX_XX_XXXXXX_create_customers_table.php
│   ├── 2024_XX_XX_XXXXXX_create_quotes_table.php
│   ├── 2024_XX_XX_XXXXXX_create_quote_items_table.php
│   ├── 2024_XX_XX_XXXXXX_create_invoices_table.php
│   ├── 2024_XX_XX_XXXXXX_create_invoice_items_table.php
│   └── 2024_XX_XX_XXXXXX_create_settings_table.php
├── factories/
│   ├── ItemFactory.php
│   ├── CustomerFactory.php
│   ├── QuoteFactory.php
│   ├── InvoiceFactory.php
│   └── SettingFactory.php
└── seeders/
    ├── DatabaseSeeder.php (enhanced)
    ├── ItemSeeder.php
    ├── CustomerSeeder.php
    └── SettingSeeder.php
tests/
├── Unit/
│   ├── ItemTest.php
│   ├── CustomerTest.php
│   ├── QuoteTest.php
│   └── InvoiceTest.php
└── Feature/
    ├── ModelRelationshipTest.php
    └── ModelValidationTest.php
```

## Model Methods Example

### Quote Model Key Methods
```php
public function calculateTotal()
{
    $this->sub_total = $this->quoteItems->sum(function ($item) {
        return $item->price * $item->qty;
    });
    $this->total = $this->sub_total - $this->discount_amount;
}

public function generateQuoteNumber()
{
    $prefix = 'Q-' . date('Y');
    $latest = self::where('quote_number', 'like', $prefix . '%')
                ->orderBy('quote_number', 'desc')
                ->first();

    $number = $latest ? intval(substr($latest->quote_number, -4)) + 1 : 1;
    $this->quote_number = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
}
```

## Acceptance Criteria
- [ ] All database migrations run successfully
- [ ] Models have proper relationships configured
- [ ] Foreign key constraints prevent orphaned records
- [ ] Model factories generate valid test data
- [ ] Seeders populate database with sample data
- [ ] Model calculations (totals, subtotals) work correctly
- [ ] Scopes return expected filtered results
- [ ] All models have comprehensive test coverage
- [ ] Model observers handle events properly

## Dependencies
- Phase 1 completion
- Laravel's built-in migration system
- Eloquent ORM
- Laravel's factory and seeder system

## Estimated Time
3-4 days

## Next Phase
After completing Phase 2, the application will have a complete data model with all necessary tables, relationships, and business logic, ready for implementing CRUD operations in Phase 3.