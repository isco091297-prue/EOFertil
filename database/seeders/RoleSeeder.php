<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'Administrator',
            'description' => 'Full access to the system',
            'is_active' => true,
        ]);

        Role::create([
            'name' => 'Perchero',
            'description' => 'Mobile application user',
            'is_active' => true,
        ]);
    }
}