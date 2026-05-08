<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $user = User::firstOrCreate(
            ['email' => 'yahiaalqadhi2004@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('12345678'),
            ]
        );

        if (! $user->hasRole('super_admin')) {
            $user->assignRole($superAdminRole);
        }
    }
}