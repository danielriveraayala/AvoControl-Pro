<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Filters
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('customer_type') && $request->customer_type !== '') {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('city') && $request->city !== '') {
            $query->where('city', 'like', "%{$request->city}%");
        }

        $customers = $query->latest()->paginate(20);
        $totalBalance = Customer::sum('current_balance');

        // Handle AJAX requests
        if ($request->ajax()) {
            $stats = [
                'total' => $customers->total(),
                'active' => Customer::where('status', 'active')->count(),
                'balance' => number_format($totalBalance, 2),
                'mayoristas' => Customer::where('customer_type', 'mayorista')->count()
            ];

            return response()->json([
                'html' => view('customers.partials.table', compact('customers'))->render(),
                'stats' => $stats
            ]);
        }

        return view('customers.index', compact('customers', 'totalBalance'));
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
            $query->latest('sale_date')->take(10);
        }]);

        if (request()->ajax()) {
            return view('customers.partials.details', compact('customer'));
        }

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        if (request()->ajax()) {
            return view('customers.partials.form', compact('customer'));
        }

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
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

        $customer->update($validated);

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