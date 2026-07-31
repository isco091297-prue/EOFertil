<?php

namespace Database\Seeders;

use App\Models\RewardType;
use Illuminate\Database\Seeder;

class RewardTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [

            [
                'codigo' => 'cashback_multiplier',
                'nombre' => 'Multiplicador de Cashback',
            ],

            [
                'codigo' => 'money',
                'nombre' => 'Dinero',
            ],

            [
                'codigo' => 'product',
                'nombre' => 'Producto',
            ],

            [
                'codigo' => 'gift_card',
                'nombre' => 'Gift Card',
            ],

            [
                'codigo' => 'other',
                'nombre' => 'Otro',
            ],
        ];

        foreach ($types as $type) {

            RewardType::updateOrCreate(

                [
                    'codigo' => $type['codigo'],
                ],

                [
                    'nombre' => $type['nombre'],
                    'activo' => true,
                ]
            );
        }
    }
}
