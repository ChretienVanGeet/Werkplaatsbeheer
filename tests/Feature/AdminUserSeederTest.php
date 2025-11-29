<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    public function test_admin_user_is_created_when_none_exists(): void
    {
        // Ensure no admin users exist
        $this->assertDatabaseMissing('users', [
            'role' => UserRole::Administrator->value,
        ]);

        // Run the seeder
        $this->seed(AdminUserSeeder::class);

        // Assert admin user was created
        $this->assertDatabaseHas('users', [
            'role'         => UserRole::Administrator->value,
            'email'        => config('admin.email'),
            'organisation' => config('admin.organisation'),
        ]);

        // Verify the admin user has correct attributes
        $admin = User::where('role', UserRole::Administrator)->first();
        $this->assertNotNull($admin);
        $this->assertEquals('Administrator', $admin->name);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check(config('admin.password'), $admin->password));
    }

    public function test_admin_user_is_not_duplicated_when_already_exists(): void
    {
        // Create an existing admin user
        User::factory()->admin()->create([
            'email' => 'existing-admin@example.com',
        ]);

        // Count existing admin users
        $initialAdminCount = User::where('role', UserRole::Administrator)->count();
        $this->assertEquals(1, $initialAdminCount);

        // Run the seeder
        $this->seed(AdminUserSeeder::class);

        // Assert no new admin was created
        $finalAdminCount = User::where('role', UserRole::Administrator)->count();
        $this->assertEquals(1, $finalAdminCount);

        // Verify the original admin still exists
        $this->assertDatabaseHas('users', [
            'email' => 'existing-admin@example.com',
            'role'  => UserRole::Administrator->value,
        ]);
    }

    public function test_admin_user_uses_config_values(): void
    {
        // Set custom config values
        Config::set('admin.email', 'custom-admin@test.com');
        Config::set('admin.password', 'custom-password');
        Config::set('admin.mobile', '1234567890');
        Config::set('admin.organisation', 'Test Org');

        // Run the seeder
        $this->seed(AdminUserSeeder::class);

        // Assert admin was created with custom config values
        $this->assertDatabaseHas('users', [
            'email'        => 'custom-admin@test.com',
            'mobile'       => '1234567890',
            'organisation' => 'Test Org',
            'role'         => UserRole::Administrator->value,
        ]);

        $admin = User::where('email', 'custom-admin@test.com')->first();
        $this->assertTrue(Hash::check('custom-password', $admin->password));
    }

    public function test_admin_user_has_email_verified(): void
    {
        // Run the seeder
        $this->seed(AdminUserSeeder::class);

        // Get the created admin
        $admin = User::where('role', UserRole::Administrator)->first();

        // Assert email is verified
        $this->assertNotNull($admin->email_verified_at);
    }
}
