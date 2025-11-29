# Summa TechTrack
# start de queue: php artisan queue:work
## Requirements

- PHP 8.3+
- Composer 2
- MySQL 8.0+
- Node.js 18+
- [Laravel Herd](https://herd.laravel.com/) (for local development)

## Setting up the application

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd werkplaatsbeheer
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Install PHP dependencies:
```bash
composer install
```

4. Install Node dependencies:
```bash
npm install
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure required environment variables in `.env`:

**Database:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=werkplaatsbeheer
DB_USERNAME=herd
DB_PASSWORD=herd
```

**Application URL (for Laravel Herd):**
```env
APP_URL=http://werkplaatsbeheer.test
```

**Cache & Sessions:**
```env
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

**Email (optional - use Mailtrap for testing):**
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

7. Run migrations:
```bash
php artisan migrate
```

8. **Seed the database**:

For production/first-time installation (creates admin user only):
```bash
php artisan db:seed
```

Optionally, configure the admin credentials in `.env` before seeding:
```env
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=password
ADMIN_MOBILE=
ADMIN_ORGANISATION=Summa
```

> **Note**: The admin user is only created if no administrator exists yet. Running this seeder multiple times is safe.

For development with sample data (creates admin user + test data):
```bash
php artisan db:seed --class=DevelopmentSeeder
```

9. Build frontend assets:
```bash
npm run build
```

### Laravel Herd

This application is served using Laravel Herd. Once Herd is installed and the project is linked, the site will be automatically available at:

```
https://werkplaatsbeheer.test
```

No additional server configuration is needed.

### Storage Configuration

Create the storage symlink:
```bash
php artisan storage:link
```

### Queue Workers (Laravel Horizon)

Start Horizon for background job processing:
```bash
php artisan horizon
```

Or add the site to Herd's queue management for automatic queue processing.

## Development

### Database Seeding for Development

For local development, it's recommended to seed the database with sample data using the `DevelopmentSeeder`:

```bash
php artisan db:seed --class=DevelopmentSeeder
```

This will create:
- An admin user (info@fruitcake.nl / password)
- 15 additional test users
- 4 groups with user assignments
- 15 companies with random notes
- 15 participants with random notes
- 5 activities with linked companies and participants
- 15 workflow templates with steps and workflows

**Resetting the database:**

If you need to reset your development database and reseed:
```bash
php artisan migrate:fresh --seed --seeder=DevelopmentSeeder
```

> **Note**: The default `DatabaseSeeder` only creates an admin user. Use `DevelopmentSeeder` when you need sample data for development and testing.

### Frontend Development

For live asset compilation during development:
```bash
npm run dev
```

Or use the combined development environment:
```bash
composer run dev
```

This will start the Laravel server, queue worker, log viewer (Pail), and Vite concurrently.

### Running Tests

Run all tests:
```bash
php artisan test
```

Run specific test file:
```bash
php artisan test tests/Feature/ExampleTest.php
```

### Code Quality

This project uses PHPStan/Larastan for static analysis:
```bash
./vendor/bin/phpstan analyse
```

Format code with Laravel Pint:
```bash
./vendor/bin/pint
```

## Credentials

### Production/First-Time Installation
After running `php artisan db:seed` (see step 8 in Installation), use these default credentials:

Default credentials (configurable via `.env`):
- Email: `admin@example.com`
- Password: `password`

**Important**: Change the password immediately after first login!

### Development (with sample data)
If you ran the `DevelopmentSeeder` for development:
- Email: `info@fruitcake.nl`
- Password: `password`

## Translations

To add new localized strings:
1. Use English as the translation key:
```php
__('Activity')
```
2. Add Dutch translation in `lang/nl/*.php`:
```php
"Activity" => "Activiteit"
```

## Tech Stack

- **Backend**: Laravel 12 with PHP 8.3
- **Frontend**: Livewire 3 with Volt, Flux UI (Free & Pro), Alpine.js 3, Tailwind CSS 4
- **Admin Panel**: Filament 4
- **Database**: MySQL
- **Queue**: Laravel Horizon
- **Assets**: Vite
- **Testing**: PHPUnit
- **Static Analysis**: Larastan
