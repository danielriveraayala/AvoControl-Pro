<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityGrade;

class UpdateQualityGradeColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultColors = [
            'Primera' => '#28a745',  // Green - highest quality
            'Segunda' => '#ffc107',  // Yellow/amber  
            'Tercera' => '#17a2b8',  // Blue/info
            'Cuarta' => '#007bff',   // Primary blue
            'Quinta' => '#6f42c1',   // Purple
            'Industrial' => '#6c757d' // Gray
        ];

        foreach ($defaultColors as $name => $color) {
            QualityGrade::where('name', $name)->update(['color' => $color]);
        }

        $this->command->info('Default colors assigned to quality grades');
    }
}