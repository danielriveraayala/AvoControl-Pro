<?php

namespace App\Http\Controllers;

use App\Models\QualityGrade;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $qualityGrades = QualityGrade::ordered()->get();
            
            if ($request->ajax()) {
                $html = view('configuration.partials.quality_table', compact('qualityGrades'))->render();
                return response()->json([
                    'html' => $html,
                    'count' => $qualityGrades->count(),
                    'success' => true
                ]);
            }
            
            return view('configuration.index', compact('qualityGrades'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar las calidades: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al cargar la configuración');
        }
    }

    public function storeQuality(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'caliber_min' => 'nullable|integer|min:1',
            'caliber_max' => 'nullable|integer|min:1',
            'weight_min' => 'nullable|integer|min:1',
            'weight_max' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Set default sort_order if not provided
        if (!isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === '') {
            $validated['sort_order'] = QualityGrade::max('sort_order') + 1;
        }

        QualityGrade::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Calidad creada exitosamente']);
        }

        return redirect()->back()->with('success', 'Calidad creada exitosamente');
    }

    public function updateQuality(Request $request, QualityGrade $qualityGrade)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'caliber_min' => 'nullable|integer|min:1',
            'caliber_max' => 'nullable|integer|min:1',
            'weight_min' => 'nullable|integer|min:1',
            'weight_max' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ]);

        // Set default sort_order if not provided
        if (!isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === '') {
            $validated['sort_order'] = $qualityGrade->sort_order ?? 0;
        }

        $qualityGrade->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Calidad actualizada exitosamente']);
        }

        return redirect()->back()->with('success', 'Calidad actualizada exitosamente');
    }

    public function destroyQuality(Request $request, QualityGrade $qualityGrade)
    {
        $qualityGrade->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Calidad eliminada exitosamente']);
        }

        return redirect()->back()->with('success', 'Calidad eliminada exitosamente');
    }

    public function showQuality(Request $request, QualityGrade $qualityGrade)
    {
        if ($request->wantsJson()) {
            return response()->json($qualityGrade);
        }

        return view('configuration.quality.show', compact('qualityGrade'));
    }

    public function getQualitiesTable(Request $request)
    {
        try {
            $qualityGrades = QualityGrade::ordered()->get();
            $html = view('configuration.partials.quality_table', compact('qualityGrades'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $qualityGrades->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las calidades: ' . $e->getMessage()
            ], 500);
        }
    }
}
