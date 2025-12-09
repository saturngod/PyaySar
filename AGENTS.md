# Development Commands

## Build/Lint/Test

- `npm run dev` - Start development server with Vite
- `npm run build` - Build for production
- `npm run lint` - Run ESLint with auto-fix
- `npm run types` - TypeScript type checking
- `npm run format` - Format code with Prettier
- `composer test` - Run all tests (Pest)
- `./vendor/bin/pest tests/Feature/YourTest.php` - Run single test file

## Code Style Guidelines

### PHP/Laravel

- Use Laravel Pint for PHP formatting (`./vendor/bin/pint`)
- Follow PSR-4 autoloading, PascalCase for classes
- Use resource controllers with standard CRUD methods
- Models: fillable arrays, proper relationships, casts for dates/decimals
- Always validate user ownership: `if ($model->user_id !== Auth::id()) abort(403);`

### React/TypeScript

- Single quotes, semicolons, 4-space tabs (Prettier config)
- Import organization: prettier-plugin-organize-imports
- Use Radix UI + Tailwind CSS with clsx/tailwind-merge
- React 19+ with JSX runtime (no React in scope)
- TypeScript strict mode, no prop-types required
- Install shadcn/ui components: `npx shadcn@latest add`

### Testing

- Pest PHP with RefreshDatabase trait for feature tests
- SQLite in-memory database for testing
- Test files in tests/Feature/ and tests/Unit/
