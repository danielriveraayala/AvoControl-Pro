<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Add quality_grade_id as foreign key
            $table->foreignId('quality_grade_id')->nullable()->after('quality_grade');
            $table->foreign('quality_grade_id')->references('id')->on('quality_grades');
        });

        // Migrate existing data - map quality_grade string to quality_grade_id
        $qualityGrades = \App\Models\QualityGrade::all();
        foreach ($qualityGrades as $grade) {
            // Update lots that have this quality name (including old names like "asdasdasd")
            \DB::table('lots')
                ->where('quality_grade', $grade->name)
                ->update(['quality_grade_id' => $grade->id]);
        }

        // For any remaining lots with quality_grade that doesn't match, try to find or create
        $unmatchedLots = \DB::table('lots')
            ->whereNull('quality_grade_id')
            ->whereNotNull('quality_grade')
            ->distinct()
            ->pluck('quality_grade');

        foreach ($unmatchedLots as $qualityName) {
            // Try to find a quality grade that might have been renamed
            // For now, assign to "Cuarta" or create a new one if needed
            $grade = \App\Models\QualityGrade::where('name', 'Cuarta')->first();
            
            if (!$grade) {
                // Create a new quality grade if Cuarta doesn't exist
                $grade = \App\Models\QualityGrade::create([
                    'name' => $qualityName,
                    'active' => true,
                    'sort_order' => 999
                ]);
            }

            \DB::table('lots')
                ->where('quality_grade', $qualityName)
                ->update(['quality_grade_id' => $grade->id]);
        }

        // After migration, we can drop the old quality_grade column in a future migration
        // For now, keep it for backward compatibility
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeign(['quality_grade_id']);
            $table->dropColumn('quality_grade_id');
        });
    }
};
