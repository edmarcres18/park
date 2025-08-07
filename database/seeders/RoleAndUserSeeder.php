<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $attendantRole = Role::create(['name' => 'attendant']);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        // Create attendant user
        $attendant = User::create([
            'name' => 'Attendant User',
            'email' => 'attendant@example.com',
            'email_verified_at' => now(),
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $attendant->assignRole($attendantRole);
    }
}
