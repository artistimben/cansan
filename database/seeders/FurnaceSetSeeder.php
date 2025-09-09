<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FurnaceSet;

/**
 * Ocak Seti Seeder
 * Fabrikadaki 3 seti oluşturur
 */
class FurnaceSetSeeder extends Seeder
{
    /**
     * Ocak setlerini oluştur
     */
    public function run(): void
    {
        $sets = [
            [
                'name' => 'Set 1',
                'set_number' => 1,
                'description' => 'Birinci set - Ocak 1 ve Ocak 2',
                'is_active' => true
            ],
            [
                'name' => 'Set 2',
                'set_number' => 2,
                'description' => 'İkinci set - Ocak 3 ve Ocak 4',
                'is_active' => true
            ],
            [
                'name' => 'Set 3',
                'set_number' => 3,
                'description' => 'Üçüncü set - Ocak 5 ve Ocak 6',
                'is_active' => true
            ]
        ];

        foreach ($sets as $setData) {
            FurnaceSet::create($setData);
        }

        $this->command->info('Ocak setleri başarıyla oluşturuldu.');
    }
}
