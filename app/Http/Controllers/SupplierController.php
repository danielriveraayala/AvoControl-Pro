<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Filters
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('city') && $request->city !== '') {
            $query->where('city', 'like', "%{$request->city}%");
        }

        $suppliers = $query->latest()->paginate(20);
        $totalBalance = Supplier::sum('balance_owed');

        // Handle AJAX requests
        if ($request->ajax()) {
            $stats = [
                'total' => $suppliers->total(),
                'active' => Supplier::where('status', 'active')->count(),
                'balance' => number_format($totalBalance, 2),
                'inactive' => Supplier::where('status', 'inactive')->count()
            ];

            return response()->json([
                'html' => view('suppliers.partials.table', compact('suppliers'))->render(),
                'stats' => $stats
            ]);
        }

        return view('suppliers.index', compact('suppliers', 'totalBalance'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        $supplier = Supplier::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'supplier' => $supplier
            ]);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['lots' => function($query) {
            $query->latest('harvest_date')->take(10);
        }]);

        if (request()->ajax()) {
            return view('suppliers.partials.details', compact('supplier'));
        }

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        if (request()->ajax()) {
            return view('suppliers.partials.form', compact('supplier'));
        }

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        $supplier->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente'
            ]);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            // Check if supplier has lots
            if ($supplier->lots()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proveedor porque tiene lotes asociados'
                ]);
            }

            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el proveedor'
            ]);
        }
    }
}