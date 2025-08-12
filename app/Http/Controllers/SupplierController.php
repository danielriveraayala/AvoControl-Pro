<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Handle statistics only request
            if ($request->has('stats_only')) {
                $totalBalance = Supplier::sum('balance_owed');
                $stats = [
                    'total' => Supplier::count(),
                    'active' => Supplier::where('status', 'active')->count(),
                    'balance' => number_format($totalBalance, 2),
                    'inactive' => Supplier::where('status', 'inactive')->count()
                ];
                return response()->json(['stats' => $stats]);
            }

            // Handle DataTables request
            $query = Supplier::query();
            
            // Search functionality for DataTables
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
                });
            }

            // Ordering for DataTables
            if ($request->has('order')) {
                $columns = ['name', 'contact_person', 'phone', 'email', 'city', 'status', 'balance_owed'];
                $orderColumn = $columns[$request->order[0]['column']] ?? 'name';
                $orderDirection = $request->order[0]['dir'] ?? 'asc';
                $query->orderBy($orderColumn, $orderDirection);
            } else {
                $query->orderBy('name', 'asc');
            }

            // Pagination for DataTables
            $totalRecords = $query->count();
            $suppliers = $query->skip($request->start ?? 0)
                             ->take($request->length ?? 25)
                             ->get();

            // Format data for DataTables
            $data = $suppliers->map(function($supplier) {
                // Status badge
                $statusBadge = $supplier->status === 'active' 
                    ? '<span class="badge badge-success">Activo</span>' 
                    : '<span class="badge badge-danger">Inactivo</span>';

                // Calculate actual balance
                $actualBalance = $supplier->getTotalPurchasesAttribute() - $supplier->getTotalPaidAttribute();
                
                // Balance with color coding
                $balance = '';
                if ($actualBalance > 0) {
                    $balance = '<span class="text-warning font-weight-bold">$'.number_format($actualBalance, 2).'</span>';
                    if ($actualBalance != $supplier->balance_owed) {
                        $balance .= '<br><small class="text-muted">BD: $'.number_format($supplier->balance_owed, 2).'</small>';
                    }
                } else {
                    $balance = '<span class="text-success">$0.00</span>';
                }

                // Actions
                $actions = '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info" onclick="viewSupplier('.$supplier->id.')" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="editSupplier('.$supplier->id.')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSupplier('.$supplier->id.', \''.addslashes($supplier->name).'\')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';

                return [
                    'name' => '<strong>'.$supplier->name.'</strong>',
                    'contact_person' => $supplier->contact_person ?: '-',
                    'phone' => $supplier->phone ?: '-',
                    'email' => $supplier->email ?: '-',
                    'city' => $supplier->city ?: '-',
                    'status' => $statusBadge,
                    'balance_owed' => $balance,
                    'actions' => $actions
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => Supplier::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        }

        $totalBalance = Supplier::sum('balance_owed');
        return view('suppliers.index', compact('totalBalance'));
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
            return response()->json($supplier);
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