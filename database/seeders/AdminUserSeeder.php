<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user for first-time installation.
     */
    public function run(): void
    {
        // Check if an admin user already exists
        if (User::where('role', UserRole::Administrator)->exists()) {
            $this->command->info('Admin user already exists. Skipping creation.');

            return;
        }

        // Create the admin user
        $admin = User::create([
            'name'              => 'Administrator',
            'email'             => config('admin.email'),
            'email_verified_at' => now(),
            'password'          => Hash::make(config('admin.password')),
            'role'              => UserRole::Administrator,
            'mobile'            => config('admin.mobile'),
            'organisation'      => config('admin.organisation'),
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: '.$admin->email);
        $this->command->warn('Password: '.config('admin.password'));
        $this->command->warn('Please change the password after first login!');
    }
}
