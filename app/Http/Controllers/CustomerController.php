<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Handle statistics only request
            if ($request->has('stats_only')) {
                $totalBalance = Customer::sum('current_balance');
                $stats = [
                    'total' => Customer::count(),
                    'active' => Customer::where('status', 'active')->count(),
                    'balance' => number_format($totalBalance, 2),
                    'mayoristas' => Customer::where('customer_type', 'mayorista')->count()
                ];
                return response()->json(['stats' => $stats]);
            }

            // Handle DataTables request
            $query = Customer::query();
            
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
                $columns = ['name', 'customer_type', 'contact_person', 'phone', 'city', 'status', 'current_balance'];
                $orderColumn = $columns[$request->order[0]['column']] ?? 'name';
                $orderDirection = $request->order[0]['dir'] ?? 'asc';
                $query->orderBy($orderColumn, $orderDirection);
            } else {
                $query->orderBy('name', 'asc');
            }

            // Pagination for DataTables
            $totalRecords = $query->count();
            $customers = $query->skip($request->start ?? 0)
                             ->take($request->length ?? 25)
                             ->get();

            // Format data for DataTables
            $data = $customers->map(function($customer) {
                // Calculate credit info
                $creditUtilization = $customer->getCreditUtilizationAttribute();
                $creditStatus = $customer->getCreditStatusAttribute();
                $availableCredit = $customer->getAvailableCreditAttribute();
                
                $typeBadge = [
                    'mayorista' => '<span class="badge badge-primary">Mayorista</span>',
                    'minorista' => '<span class="badge badge-info">Minorista</span>',
                    'distribuidor' => '<span class="badge badge-warning">Distribuidor</span>'
                ][$customer->customer_type] ?? '<span class="badge badge-secondary">'.ucfirst($customer->customer_type).'</span>';

                // Status with credit warning
                $statusBadge = $customer->status === 'active' 
                    ? '<span class="badge badge-success">Activo</span>' 
                    : '<span class="badge badge-danger">Inactivo</span>';

                // Add credit status indicator
                if ($customer->status === 'active') {
                    if ($creditStatus === 'exceeded') {
                        $statusBadge .= '<br><small class="badge badge-danger mt-1"><i class="fas fa-exclamation-triangle"></i> Crédito Excedido</small>';
                    } elseif ($creditStatus === 'critical') {
                        $statusBadge .= '<br><small class="badge badge-warning mt-1"><i class="fas fa-exclamation-circle"></i> Crédito Crítico</small>';
                    }
                }

                // Balance with credit context
                $balance = '';
                if ($customer->current_balance > 0) {
                    $color = $creditStatus === 'exceeded' ? 'danger' : ($creditStatus === 'critical' ? 'warning' : 'info');
                    $balance = '<span class="text-'.$color.' font-weight-bold">$'.number_format($customer->current_balance, 2).'</span>';
                    $balance .= '<br><small class="text-muted">Crédito: $'.number_format($customer->credit_limit, 2).'</small>';
                } else {
                    $balance = '<span class="text-success">$0.00</span>';
                }

                // Actions with credit status color
                $actionColor = $creditStatus === 'exceeded' ? 'danger' : ($creditStatus === 'critical' ? 'warning' : 'info');
                $actions = '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-'.$actionColor.'" onclick="viewCustomer('.$customer->id.')" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="editCustomer('.$customer->id.')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>';
                
                // Show credit adjustment for exceeded credit
                if ($creditStatus === 'exceeded') {
                    $actions .= '<button class="btn btn-sm btn-warning" onclick="adjustCreditLimit('.$customer->id.')" title="Ajustar Crédito">
                            <i class="fas fa-exclamation-triangle"></i>
                        </button>';
                } else {
                    $actions .= '<button class="btn btn-sm btn-danger" onclick="deleteCustomer('.$customer->id.', \''.addslashes($customer->name).'\')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>';
                }
                
                $actions .= '</div>';

                $nameWithCredit = '<strong>'.$customer->name.'</strong>';
                if ($creditStatus === 'exceeded') {
                    $nameWithCredit .= '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Excede crédito por $'.number_format(abs($availableCredit), 2).'</small>';
                } elseif ($creditStatus === 'critical') {
                    $nameWithCredit .= '<br><small class="text-warning"><i class="fas fa-exclamation-circle"></i> '.number_format($creditUtilization, 1).'% crédito usado</small>';
                }

                return [
                    'name' => $nameWithCredit,
                    'customer_type' => $typeBadge,
                    'contact_person' => $customer->contact_person ?: '-',
                    'phone' => $customer->phone ?: '-',
                    'city' => $customer->city ?: '-',
                    'status' => $statusBadge,
                    'current_balance' => $balance,
                    'actions' => $actions
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => Customer::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        }

        $totalBalance = Customer::sum('current_balance');
        return view('customers.index', compact('totalBalance'));
    }

    public function create()
    {
        return view('customers.create');
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
            'customer_type' => 'required|in:mayorista,minorista,distribuidor',
            'credit_limit' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        $customer = Customer::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'customer' => $customer
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Cliente creado exitosamente');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => function($query) {
            $query->latest('created_at')->take(10);
        }]);

        if (request()->ajax()) {
            return view('customers.partials.details', compact('customer'));
        }

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        if (request()->ajax()) {
            return response()->json($customer);
        }

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        // Handle credit limit only update (for quick adjustments)
        if ($request->has('credit_limit') && count($request->all()) <= 3) { // _method, _token, credit_limit
            $request->validate([
                'credit_limit' => 'required|numeric|min:0'
            ]);
            
            $oldLimit = $customer->credit_limit;
            $customer->update(['credit_limit' => $request->credit_limit]);
            
            // Update balance to reflect new credit status
            $customer->updateBalance();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Límite de crédito actualizado de $".number_format($oldLimit, 2)." a $".number_format($request->credit_limit, 2)
                ]);
            }
        }

        // Full customer update
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
            'customer_type' => 'required|in:mayorista,minorista,distribuidor',
            'credit_limit' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        $customer->update($validated);

        // Update balance after any changes
        $customer->updateBalance();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente'
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has sales
            if ($customer->sales()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente porque tiene ventas asociadas'
                ]);
            }

            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el cliente'
            ]);
        }
    }
}