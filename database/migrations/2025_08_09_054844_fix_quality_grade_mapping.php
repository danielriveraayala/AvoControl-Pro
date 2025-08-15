<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Arreglar el mapeo de calidades
        // Basado en los datos que vemos:
        // - "asdadsa" y "Cuarta" deben apuntar al mismo ID (9)
        // - "Primera" debe apuntar a ID 1 (Primeras)
        // - "Segunda" ya está bien (ID 2)
        // - "Tercera" ya está bien (ID 3)

        // Mapeo correcto de nombres incorrectos a IDs correctos
        $corrections = [
            // Corregir "Primera" que tiene ID 9 incorrecto a ID 1
            ['old_name' => 'Primera', 'correct_id' => 1, 'correct_name' => 'Primeras'],
            
            // Los nombres "asdadsa" y "Cuarta" que ya tienen ID 9 correcto
            // Solo necesitamos estandarizar el nombre a "Cuarta"
            ['old_name' => 'asdadsa', 'correct_id' => 9, 'correct_name' => 'Cuarta'],
            
            // Segunda y Tercera ya están correctas, pero asegurémonos
            ['old_name' => 'Segunda', 'correct_id' => 2, 'correct_name' => 'Segunda'], 
            ['old_name' => 'Tercera', 'correct_id' => 3, 'correct_name' => 'Tercera'],
        ];

        foreach ($corrections as $correction) {
            DB::table('lots')
                ->where('quality_grade', $correction['old_name'])
                ->update([
                    'quality_grade_id' => $correction['correct_id'],
                    'quality_grade' => $correction['correct_name']
                ]);
                
            echo "Corregido: {$correction['old_name']} -> {$correction['correct_name']} (ID: {$correction['correct_id']})\n";
        }

        // Verificar que no hay registros sin quality_grade_id
        $orphaned = DB::table('lots')->whereNull('quality_grade_id')->count();
        if ($orphaned > 0) {
            echo "ADVERTENCIA: {$orphaned} lotes sin quality_grade_id\n";
        }
        
        echo "Migración completada. Verificando resultados...\n";
        
        // Mostrar el estado final
        $results = DB::select("SELECT quality_grade, quality_grade_id, COUNT(*) as count FROM lots GROUP BY quality_grade, quality_grade_id ORDER BY quality_grade");
        foreach ($results as $result) {
            echo "- {$result->quality_grade} (ID: {$result->quality_grade_id}): {$result->count} lotes\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No podemos revertir fácilmente sin pérdida de datos
        // porque no sabemos qué registros tenían originalmente "asdadsa" vs "Cuarta"
        echo "Esta migración no se puede revertir automáticamente.\n";
    }
};