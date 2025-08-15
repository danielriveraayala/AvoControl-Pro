<?php

namespace Database\Seeders;

use App\Models\QualityGrade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QualityGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $qualities = [
            [
                'name' => 'Primera',
                'caliber_min' => 16,
                'caliber_max' => 24,
                'weight_min' => 200,
                'weight_max' => 350,
                'description' => 'Aguacate de primera calidad, sin defectos, para exportación',
                'active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Segunda',
                'caliber_min' => 12,
                'caliber_max' => 20,
                'weight_min' => 150,
                'weight_max' => 250,
                'description' => 'Aguacate de segunda calidad, con defectos menores, para mercado nacional',
                'active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Tercera',
                'caliber_min' => 8,
                'caliber_max' => 16,
                'weight_min' => 100,
                'weight_max' => 200,
                'description' => 'Aguacate de tercera calidad, para procesamiento industrial',
                'active' => true,
                'sort_order' => 3
            ]
        ];

        foreach ($qualities as $quality) {
            QualityGrade::create($quality);
        }
    }
}
