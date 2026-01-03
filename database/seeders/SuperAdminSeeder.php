<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the first super admin
        AdminUser::updateOrCreate(
            ['email' => 'admin@styledream.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123!'),
                'role' => AdminUser::ROLE_SUPER_ADMIN,
            ]
        );

        $this->command->info('Super admin created successfully!');
        $this->command->info('Email: admin@styledream.com');
        $this->command->info('Password: Admin@123!');
        $this->command->warn('Please change this password immediately after first login!');
    }
}
