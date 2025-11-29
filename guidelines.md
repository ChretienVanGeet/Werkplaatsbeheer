# PROJECT\_FOUNDATION\_GUIDELINES

---

# 1. Technology Stack

This project uses the following stack:

- **PHP** 8.3.24
- **Laravel** 12
- **Livewire** 3
- **Volt** (single-file Livewire components)
- **Filament** for all admin/backend functionality
- **Flux UI Pro** for frontend UI
- **TailwindCSS** 4
- **MySQL** database
- **Herd** for local development
- **Laravel Boost**, **Larastan**, **Pint**, **Pest** for performance & quality

---

# 2. General Code Conventions

## 2.1 Naming

- Use descriptive variable and method names (e.g., `isRegisteredForDiscounts()` instead of `discount()`).
- Use consistent, conventional names for controllers, actions, models, Livewire components, etc.
- Follow PSR-12 for naming, spacing, and structure.

## 2.2 File Organization

- Place classes in logical directories, following Laravel norm.
- Before creating new components (Livewire, Blade partials, or Flux components), **check for existing ones**.
- Keep related files together.

## 2.3 Documentation

- Prefer code clarity over excessive comments.
- Use PHPDoc where type inference is insufficient.
- Avoid redundant or outdated comments.

---

# 3. PHP Rules

## 3.1 Strict Types

All PHP files must start with:

```php
declare(strict_types=1);
```

## 3.2 Type Declarations

- Always type properties, parameters, and return types.
- Use modern PHP features: constructor property promotion, readonly properties (when appropriate), match expressions.

## 3.3 Enums

PHP enums are **allowed**. Guidelines:

- Use enums for domain concepts with a finite number of values.
- Prefer backing values (`string` or `int`).
- Store enum references in the database using backed values.
- Example:

```php
enum Status: string {
    case Active = 'active';
    case Inactive = 'inactive';
}
```

---

# 4. Laravel Framework Conventions

## 4.1 Routing

- Use **named routes** everywhere.
- Place route definitions in `routes/web.php`.
- Prefer single-action controllers only when needed; otherwise, use Livewire pages.

## 4.2 Eloquent

- Prefer Eloquent models for queries.
- Use relationships instead of manual joins when possible.
- Use `->with()` for eager loading and avoid N+1 issues.
- Keep model classes clean; push logic to actions/services where needed.

## 4.3 Service Container

- Resolve dependencies via constructor injection.
- Avoid resolving heavy dependencies early (e.g., inside service providers).

## 4.4 Environment & Config

- Never call `env()` outside `config/*.php`.
- Always retrieve configuration using `config('...')`.

---

# 5. Livewire 3

## 5.1 State

- Track UI-state inside Livewire components.
- Avoid heavy logic in `mount()`; delegate to actions.

## 5.2 Events

- Use `$this->dispatch(...)` for browser and component events.

## 5.3 Data Binding

- For dynamic lists, always apply `wire:key`.
- Use `wire:model.live` for user-typed fields.

## 5.4 Pagination

- Use Livewire’s built-in pagination.
- Keep pagination state inside the component.

---

# 6. Volt (Single-File Components)

- Use Volt components for page-level UI.
- Keep templates simple and use extracted components (Flux) where possible.
- Volt + Pest integration via `Volt::test()` is recommended.

---

# 7. Filament Admin Panel

Filament is used for all backend/admin functionality.

## 7.1 Guiding Principles

- The **frontend** is Livewire + Flux.
- The **backend/admin** is **Filament**.
- Do not mix frontend patterns into Filament resources.
- Use Filament only for authenticated staff/admin workflows.

## 7.2 Resources

- Each Eloquent model that requires CRUD management gets a Filament Resource.
- Customize forms via Filament form components (Select, TextInput, DatePicker, Toggle, Repeater, etc.).
- Use Tables for index views; include filters and actions.

## 7.3 Pages

- For complex workflows, create Custom Pages inside the Filament resource.
- Keep Page logic small; offload domain logic to Actions or Services.

## 7.4 Navigation

- Keep navigation clean and categorized.
- Only expose necessary resources to each role.

## 7.5 Actions

- Use Filament Actions (Create, Edit, Delete, Bulk Actions) and avoid custom solutions when existing actions suffice.
- Prefer Inline Actions when operating on row-level.

## 7.6 Authorization

- Use Laravel gates or policies for all Filament resources.
- Never expose sensitive admin screens through Livewire frontend logic.

## 7.7 Styling

- Use Filament’s native design; no heavy customization unless needed.

---

# 8. Flux UI Pro

This project uses **Flux UI Pro**, which includes all Free components.
Guidelines:

- Use Pro components where relevant (modals, tables, dropdowns, forms).
- Do not duplicate UI elements—always reuse existing Flux patterns.
- Follow Tailwind utility-first conventions.

---

# 8. Performance Guidelines

## 8.1 Laravel Boost

- Must be regenerated on deployment using:

```bash
php artisan boost:generate
```

- Avoid heavy logic during bootstrap (service providers, config files).

## 8.2 Caching

- Use route cache and config cache.
- Only use manual caching when beneficial.

## 8.3 Lazy Loading

- Avoid unnecessary DB queries early in the request lifecycle.

---

# 9. Testing Rules (Unified Section)

## 9.1 General

- Every change must be tested.
- Use Pest for all tests.
- Follow a descriptive naming style.

## 9.2 Database

- Use `DatabaseTransactions` for integration tests.
- Do *not* use `RefreshDatabase` for this project.
- Use a dedicated testing database.

## 9.3 Livewire / Volt Tests

- Use `livewire()` for classic components.
- Use `Volt::test()` for single-file components.
- Assertions should focus on behavior, not implementation.

## 9.4 Fixtures & Factories

- Prefer factories for creating models.
- Keep fixtures minimal.

---

# 10. Tools

## Pint

Run Pint before committing:

```bash
./vendor/bin/pint
```

## Larastan

Run Larastan regularly:

```bash
./vendor/bin/phpstan analyse
```

## Tinker

Tinker **may be used** for ad-hoc debugging or data inspection.
**It must not** replace tests or be used for persistent “verification scripts.”

---

# 11. Final Notes

- Stick to Laravel conventions as much as possible.
- Keep code minimal, explicit, and readable.
- Reuse components and patterns instead of reimplementing.
- Always check sibling files for style and naming before adding new code.

These guidelines form the working foundation for the project’s architecture, coding style, testing approach, and overall maintainability.

