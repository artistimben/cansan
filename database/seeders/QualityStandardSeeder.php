<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityStandard;

/**
 * Kalite Standardı Seeder
 * Yaygın çelik sınıfları için kalite standartları oluşturur
 */
class QualityStandardSeeder extends Seeder
{
    /**
     * Kalite standartlarını oluştur
     */
    public function run(): void
    {
        $standards = [
            [
                'steel_grade' => 'ST37',
                'name' => 'Yapı Çeliği ST37',
                'carbon_min' => 0.12,
                'carbon_max' => 0.20,
                'manganese_min' => 0.30,
                'manganese_max' => 1.20,
                'silicon_min' => 0.10,
                'silicon_max' => 0.40,
                'phosphorus_min' => null,
                'phosphorus_max' => 0.045,
                'sulfur_min' => null,
                'sulfur_max' => 0.045,
                'chromium_min' => null,
                'chromium_max' => 0.30,
                'nickel_min' => null,
                'nickel_max' => 0.30,
                'molybdenum_min' => null,
                'molybdenum_max' => 0.08,
                'is_active' => true,
                'description' => 'Genel yapı çeliği standardı'
            ],
            [
                'steel_grade' => 'ST52',
                'name' => 'Yüksek Mukavemetli Yapı Çeliği ST52',
                'carbon_min' => 0.18,
                'carbon_max' => 0.25,
                'manganese_min' => 0.50,
                'manganese_max' => 1.50,
                'silicon_min' => 0.15,
                'silicon_max' => 0.50,
                'phosphorus_min' => null,
                'phosphorus_max' => 0.040,
                'sulfur_min' => null,
                'sulfur_max' => 0.040,
                'chromium_min' => null,
                'chromium_max' => 0.35,
                'nickel_min' => null,
                'nickel_max' => 0.35,
                'molybdenum_min' => null,
                'molybdenum_max' => 0.10,
                'is_active' => true,
                'description' => 'Yüksek mukavemetli yapı çeliği standardı'
            ],
            [
                'steel_grade' => 'S235JR',
                'name' => 'Avrupa Standardı S235JR',
                'carbon_min' => 0.12,
                'carbon_max' => 0.22,
                'manganese_min' => 0.30,
                'manganese_max' => 1.40,
                'silicon_min' => 0.10,
                'silicon_max' => 0.35,
                'phosphorus_min' => null,
                'phosphorus_max' => 0.045,
                'sulfur_min' => null,
                'sulfur_max' => 0.045,
                'chromium_min' => null,
                'chromium_max' => 0.30,
                'nickel_min' => null,
                'nickel_max' => 0.30,
                'molybdenum_min' => null,
                'molybdenum_max' => 0.08,
                'is_active' => true,
                'description' => 'EN 10025-2 standardına göre yapı çeliği'
            ],
            [
                'steel_grade' => 'S355JR',
                'name' => 'Avrupa Standardı S355JR',
                'carbon_min' => 0.18,
                'carbon_max' => 0.24,
                'manganese_min' => 0.50,
                'manganese_max' => 1.60,
                'silicon_min' => 0.15,
                'silicon_max' => 0.50,
                'phosphorus_min' => null,
                'phosphorus_max' => 0.040,
                'sulfur_min' => null,
                'sulfur_max' => 0.040,
                'chromium_min' => null,
                'chromium_max' => 0.35,
                'nickel_min' => null,
                'nickel_max' => 0.35,
                'molybdenum_min' => null,
                'molybdenum_max' => 0.10,
                'is_active' => true,
                'description' => 'EN 10025-2 standardına göre yüksek mukavemetli çelik'
            ],
            [
                'steel_grade' => 'A36',
                'name' => 'ASTM A36 Yapı Çeliği',
                'carbon_min' => 0.15,
                'carbon_max' => 0.26,
                'manganese_min' => 0.60,
                'manganese_max' => 1.35,
                'silicon_min' => 0.15,
                'silicon_max' => 0.40,
                'phosphorus_min' => null,
                'phosphorus_max' => 0.040,
                'sulfur_min' => null,
                'sulfur_max' => 0.050,
                'chromium_min' => null,
                'chromium_max' => 0.30,
                'nickel_min' => null,
                'nickel_max' => 0.30,
                'molybdenum_min' => null,
                'molybdenum_max' => 0.08,
                'is_active' => true,
                'description' => 'Amerikan ASTM standardı yapı çeliği'
            ],
            [
                'steel_grade' => 'CUSTOM',
                'name' => 'Müşteri Özel Standardı',
                'carbon_min' => 0.10,
                'carbon_max' => 0.30,
                'manganese_min' => 0.25,
                'manganese_max' => 1.80,
                'silicon_min' => 0.05,
                'silicon_max' => 0.60,
                'phosphorus_min' => null,
                'phosphorus_max' => 0.050,
                'sulfur_min' => null,
                'sulfur_max' => 0.050,
                'chromium_min' => null,
                'chromium_max' => 0.50,
                'nickel_min' => null,
                'nickel_max' => 0.50,
                'molybdenum_min' => null,
                'molybdenum_max' => 0.15,
                'is_active' => true,
                'description' => 'Müşteri taleplerini karşılamak için esnek standart'
            ]
        ];

        foreach ($standards as $standardData) {
            QualityStandard::create($standardData);
        }

        $this->command->info('Kalite standartları başarıyla oluşturuldu.');
        $this->command->info('Toplam ' . count($standards) . ' standart eklendi.');
    }
}
