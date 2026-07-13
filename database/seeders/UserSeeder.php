<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'role_id' => 1,
            'warehouse_id' => null,
            'branch_id' => null,

            'first_name' => 'Juan',
            'last_name' => 'Carlos',

            'identification' => '0000000000',
            'phone' => '0999999999',

            'username' => 'JuanCarlos',
            'email' => 'admin@eofertil.com',

            'password' => Hash::make('Trinchera2025'),

            'bank' => null,
            'account_type' => null,
            'account_number' => null,

            'is_active' => true,
        ]);
    }
}
