# AvoControl Pro
## Plan de Desarrollo - Sistema de Gestión de Aguacate con Laravel 12

## 1. Resumen Ejecutivo

Desarrollo de una aplicación web completa utilizando Laravel 12 para gestionar las operaciones de compra y venta de aguacate en un empaque de Uruapan. El sistema ofrecerá las mismas funcionalidades del plugin WordPress pero con arquitectura moderna, mayor escalabilidad y mejor rendimiento.

## 2. Ventajas de Laravel 12 sobre WordPress Plugin

### Arquitectura Superior
- **MVC completo** con separación clara de responsabilidades
- **API RESTful** nativa para futuras integraciones
- **Sistema de autenticación** robusto con Laravel Breeze/Jetstream
- **Migraciones de base de datos** versionadas
- **Testing automatizado** integrado

### Rendimiento y Escalabilidad
- **Caché avanzado** con Redis/Memcached
- **Colas de trabajo** para procesos pesados
- **Optimización de consultas** con Eloquent ORM
- **Lazy loading** y eager loading optimizados

### Desarrollo Moderno
- **Blade Components** reutilizables
- **Livewire** para interactividad sin JavaScript complejo
- **Vite** para compilación de assets
- **Tailwind CSS** para diseño responsive

## 3. Arquitectura Técnica Laravel

### Stack Tecnológico
```
Backend:
- Laravel 12.x
- PHP 8.3+
- MySQL 8.0+
- Redis (caché y colas)

Frontend:
- Blade Templates
- Livewire 3.x
- Alpine.js
- Tailwind CSS 3.x
- Chart.js

DevOps:
- Docker (desarrollo)
- Laravel Forge/Vapor (producción)
- GitHub Actions (CI/CD)
```

### Estructura del Proyecto
```
avocado-management/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LotController.php
│   │   │   ├── SaleController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── CustomerController.php
│   │   │   └── ReportController.php
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   │   ├── Lot.php
│   │   ├── Sale.php
│   │   ├── Payment.php
│   │   ├── Supplier.php
│   │   └── Customer.php
│   ├── Services/
│   │   ├── LotService.php
│   │   ├── PaymentService.php
│   │   ├── ReportService.php
│   │   └── ProfitabilityService.php
│   ├── Repositories/
│   ├── Events/
│   ├── Listeners/
│   └── Jobs/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── lots/
│   │   ├── sales/
│   │   ├── payments/
│   │   ├── reports/
│   │   └── dashboard/
│   ├── js/
│   └── css/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
└── tests/
    ├── Feature/
    └── Unit/
```

## 4. Modelos y Migraciones

### Migration: Lots (Lotes)
```php
Schema::create('lots', function (Blueprint $table) {
    $table->id();
    $table->string('lot_code')->unique();
    $table->date('harvest_date');
    $table->dateTime('entry_date');
    $table->decimal('total_weight', 10, 2);
    $table->decimal('purchase_price_per_kg', 8, 2);
    $table->decimal('total_purchase_cost', 10, 2);
    $table->enum('quality_grade', ['Primera', 'Segunda', 'Tercera']);
    $table->enum('status', ['active', 'sold', 'partial']);
    $table->foreignId('supplier_id')->constrained();
    $table->decimal('weight_sold', 10, 2)->default(0);
    $table->decimal('weight_available', 10, 2);
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['status', 'harvest_date']);
    $table->index('lot_code');
});
```

### Migration: Suppliers (Proveedores)
```php
Schema::create('suppliers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('phone')->nullable();
    $table->text('notes')->nullable();
    $table->boolean('is_anonymous')->default(false);
    $table->string('identification')->nullable();
    $table->decimal('total_purchased', 12, 2)->default(0);
    $table->decimal('balance_owed', 10, 2)->default(0);
    $table->timestamps();
    $table->softDeletes();
});
```

### Migration: Customers (Clientes)
```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('company_name');
    $table->string('contact_person');
    $table->string('email')->unique();
    $table->string('phone');
    $table->text('address');
    $table->string('tax_id')->unique();
    $table->integer('payment_terms')->default(0);
    $table->decimal('credit_limit', 12, 2)->default(0);
    $table->decimal('current_balance', 12, 2)->default(0);
    $table->enum('status', ['active', 'suspended', 'inactive']);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('tax_id');
    $table->index('email');
});
```

### Migration: Sales (Ventas)
```php
Schema::create('sales', function (Blueprint $table) {
    $table->id();
    $table->string('sale_code')->unique();
    $table->foreignId('customer_id')->constrained();
    $table->date('sale_date');
    $table->decimal('total_weight', 10, 2);
    $table->decimal('total_amount', 12, 2);
    $table->string('invoice_number')->nullable();
    $table->date('delivery_date')->nullable();
    $table->enum('status', ['pending', 'delivered', 'cancelled', 'partial']);
    $table->enum('payment_status', ['pending', 'partial', 'paid']);
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['status', 'sale_date']);
    $table->index('sale_code');
});
```

### Migration: Sale Items (Detalle de Ventas)
```php
Schema::create('sale_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sale_id')->constrained()->onDelete('cascade');
    $table->foreignId('lot_id')->constrained();
    $table->decimal('weight', 10, 2);
    $table->decimal('price_per_kg', 8, 2);
    $table->decimal('subtotal', 10, 2);
    $table->timestamps();
    
    $table->index(['sale_id', 'lot_id']);
});
```

### Migration: Payments (Pagos)
```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->string('payment_code')->unique();
    $table->enum('type', ['income', 'expense']);
    $table->enum('concept', ['sale_payment', 'lot_purchase']);
    $table->morphs('payable');
    $table->decimal('amount', 12, 2);
    $table->date('payment_date');
    $table->enum('payment_method', ['cash', 'transfer', 'check', 'credit']);
    $table->string('reference')->nullable();
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['type', 'payment_date']);
    $table->index('payment_code');
});
```

## 5. Modelos Eloquent

### Modelo Lot
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lot extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'lot_code', 'harvest_date', 'entry_date', 'total_weight',
        'purchase_price_per_kg', 'total_purchase_cost', 'quality_grade',
        'status', 'supplier_id', 'weight_sold', 'weight_available', 'metadata'
    ];
    
    protected $casts = [
        'harvest_date' => 'date',
        'entry_date' => 'datetime',
        'metadata' => 'array',
        'total_weight' => 'decimal:2',
        'purchase_price_per_kg' => 'decimal:2',
        'total_purchase_cost' => 'decimal:2',
        'weight_sold' => 'decimal:2',
        'weight_available' => 'decimal:2',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($lot) {
            $lot->lot_code = 'LOT-' . date('Ymd') . '-' . str_pad(Lot::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);
            $lot->weight_available = $lot->total_weight;
            $lot->total_purchase_cost = $lot->total_weight * $lot->purchase_price_per_kg;
        });
    }
    
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
    
    public function updateAvailableWeight(): void
    {
        $this->weight_sold = $this->saleItems()->sum('weight');
        $this->weight_available = $this->total_weight - $this->weight_sold;
        $this->status = $this->weight_available == 0 ? 'sold' : 
                       ($this->weight_sold > 0 ? 'partial' : 'active');
        $this->save();
    }
    
    public function getProfitabilityAttribute(): array
    {
        $totalSold = $this->saleItems()->sum('subtotal');
        $costSold = $this->weight_sold * $this->purchase_price_per_kg;
        $profit = $totalSold - $costSold;
        $margin = $costSold > 0 ? ($profit / $costSold) * 100 : 0;
        
        return [
            'revenue' => $totalSold,
            'cost' => $costSold,
            'profit' => $profit,
            'margin' => $margin,
            'status' => $profit > 0 ? 'profitable' : 'loss'
        ];
    }
}
```

## 6. Controladores Principales

### LotController
```php
namespace App\Http\Controllers;

use App\Models\Lot;
use App\Services\LotService;
use App\Http\Requests\LotRequest;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function __construct(
        private LotService $lotService
    ) {}
    
    public function index(Request $request)
    {
        $lots = Lot::with(['supplier', 'saleItems'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->quality, fn($q) => $q->where('quality_grade', $request->quality))
            ->when($request->date_from, fn($q) => $q->whereDate('harvest_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('harvest_date', '<=', $request->date_to))
            ->latest()
            ->paginate(20);
            
        return view('lots.index', compact('lots'));
    }
    
    public function create()
    {
        return view('lots.create', [
            'suppliers' => Supplier::active()->get()
        ]);
    }
    
    public function store(LotRequest $request)
    {
        $lot = $this->lotService->createLot($request->validated());
        
        return redirect()
            ->route('lots.show', $lot)
            ->with('success', 'Lote registrado exitosamente');
    }
    
    public function show(Lot $lot)
    {
        $lot->load(['supplier', 'saleItems.sale.customer', 'payments']);
        
        return view('lots.show', compact('lot'));
    }
}
```

## 7. Servicios de Negocio

### LotService
```php
namespace App\Services;

use App\Models\Lot;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class LotService
{
    public function createLot(array $data): Lot
    {
        return DB::transaction(function () use ($data) {
            $lot = Lot::create($data);
            
            // Si se paga de inmediato
            if ($data['payment_status'] === 'paid') {
                Payment::create([
                    'type' => 'expense',
                    'concept' => 'lot_purchase',
                    'payable_type' => Lot::class,
                    'payable_id' => $lot->id,
                    'amount' => $lot->total_purchase_cost,
                    'payment_date' => now(),
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'created_by' => auth()->id()
                ]);
                
                // Actualizar balance del proveedor
                $lot->supplier->decrement('balance_owed', $lot->total_purchase_cost);
            } else {
                // Incrementar deuda con proveedor
                $lot->supplier->increment('balance_owed', $lot->total_purchase_cost);
            }
            
            return $lot;
        });
    }
    
    public function calculateLotMetrics(Lot $lot): array
    {
        $profitability = $lot->profitability;
        $paymentStatus = $this->getLotPaymentStatus($lot);
        
        return [
            'weight_metrics' => [
                'total' => $lot->total_weight,
                'sold' => $lot->weight_sold,
                'available' => $lot->weight_available,
                'sold_percentage' => ($lot->weight_sold / $lot->total_weight) * 100
            ],
            'financial_metrics' => [
                'purchase_cost' => $lot->total_purchase_cost,
                'revenue' => $profitability['revenue'],
                'profit' => $profitability['profit'],
                'margin' => $profitability['margin'],
                'roi' => $lot->total_purchase_cost > 0 ? 
                    ($profitability['profit'] / $lot->total_purchase_cost) * 100 : 0
            ],
            'payment_status' => $paymentStatus
        ];
    }
    
    private function getLotPaymentStatus(Lot $lot): array
    {
        $totalPaid = $lot->payments()->where('type', 'expense')->sum('amount');
        $pending = $lot->total_purchase_cost - $totalPaid;
        
        return [
            'total_cost' => $lot->total_purchase_cost,
            'paid' => $totalPaid,
            'pending' => $pending,
            'status' => $pending == 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending')
        ];
    }
}
```

### ReportService
```php
namespace App\Services;

use App\Models\Lot;
use App\Models\Sale;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDashboardMetrics(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();
        
        return [
            'inventory' => $this->getInventoryMetrics(),
            'financial' => $this->getFinancialMetrics($startDate, $endDate),
            'operations' => $this->getOperationalMetrics($startDate, $endDate),
            'alerts' => $this->getAlerts()
        ];
    }
    
    private function getInventoryMetrics(): array
    {
        return [
            'total_weight' => Lot::where('status', '!=', 'sold')->sum('weight_available'),
            'active_lots' => Lot::where('status', 'active')->count(),
            'partial_lots' => Lot::where('status', 'partial')->count(),
            'quality_distribution' => Lot::where('status', '!=', 'sold')
                ->select('quality_grade', DB::raw('SUM(weight_available) as weight'))
                ->groupBy('quality_grade')
                ->get()
        ];
    }
    
    private function getFinancialMetrics(Carbon $start, Carbon $end): array
    {
        $purchases = Payment::where('type', 'expense')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');
            
        $sales = Payment::where('type', 'income')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');
            
        $accountsReceivable = Sale::where('payment_status', '!=', 'paid')
            ->sum(DB::raw('total_amount - COALESCE((SELECT SUM(amount) FROM payments WHERE payable_type = "App\\\\Models\\\\Sale" AND payable_id = sales.id), 0)'));
            
        $accountsPayable = DB::table('lots')
            ->join('suppliers', 'lots.supplier_id', '=', 'suppliers.id')
            ->where('suppliers.balance_owed', '>', 0)
            ->sum('suppliers.balance_owed');
        
        return [
            'total_purchases' => $purchases,
            'total_sales' => $sales,
            'net_profit' => $sales - $purchases,
            'accounts_receivable' => $accountsReceivable,
            'accounts_payable' => $accountsPayable,
            'cash_flow' => $sales - $purchases
        ];
    }
    
    public function getProfitabilityReport(Carbon $startDate, Carbon $endDate): array
    {
        $lots = Lot::with(['saleItems.sale'])
            ->whereBetween('harvest_date', [$startDate, $endDate])
            ->get()
            ->map(function ($lot) {
                $metrics = $lot->profitability;
                return [
                    'lot' => $lot,
                    'metrics' => $metrics,
                    'days_in_inventory' => $lot->created_at->diffInDays(now())
                ];
            })
            ->sortByDesc('metrics.profit');
            
        return [
            'lots' => $lots,
            'summary' => [
                'total_lots' => $lots->count(),
                'profitable_lots' => $lots->where('metrics.status', 'profitable')->count(),
                'total_profit' => $lots->sum('metrics.profit'),
                'average_margin' => $lots->avg('metrics.margin'),
                'best_performing' => $lots->first(),
                'worst_performing' => $lots->last()
            ]
        ];
    }
}
```

## 8. Componentes Livewire

### Componente de Dashboard
```php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\ReportService;

class Dashboard extends Component
{
    public $period = 'month';
    public $metrics;
    
    public function mount(ReportService $reportService)
    {
        $this->loadMetrics($reportService);
    }
    
    public function updatedPeriod($value)
    {
        $this->loadMetrics(app(ReportService::class));
    }
    
    private function loadMetrics(ReportService $reportService)
    {
        $dates = $this->getPeriodDates();
        $this->metrics = $reportService->getDashboardMetrics($dates['start'], $dates['end']);
    }
    
    private function getPeriodDates(): array
    {
        return match($this->period) {
            'today' => ['start' => now()->startOfDay(), 'end' => now()->endOfDay()],
            'week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear()],
        };
    }
    
    public function render()
    {
        return view('livewire.dashboard');
    }
}
```

### Componente de Venta Rápida
```php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Lot;
use App\Services\SaleService;

class QuickSale extends Component
{
    public $customer_id;
    public $sale_items = [];
    public $available_lots;
    
    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'sale_items.*.lot_id' => 'required|exists:lots,id',
        'sale_items.*.weight' => 'required|numeric|min:0.01',
        'sale_items.*.price_per_kg' => 'required|numeric|min:0.01',
    ];
    
    public function mount()
    {
        $this->available_lots = Lot::where('status', '!=', 'sold')
            ->where('weight_available', '>', 0)
            ->get();
        $this->addItem();
    }
    
    public function addItem()
    {
        $this->sale_items[] = [
            'lot_id' => '',
            'weight' => '',
            'price_per_kg' => '',
            'max_weight' => 0
        ];
    }
    
    public function removeItem($index)
    {
        unset($this->sale_items[$index]);
        $this->sale_items = array_values($this->sale_items);
    }
    
    public function updatedSaleItems($value, $name)
    {
        if (str_contains($name, 'lot_id')) {
            $index = explode('.', $name)[0];
            $lot = Lot::find($value);
            if ($lot) {
                $this->sale_items[$index]['max_weight'] = $lot->weight_available;
                $this->sale_items[$index]['price_per_kg'] = $lot->purchase_price_per_kg * 1.3; // 30% markup default
            }
        }
    }
    
    public function save(SaleService $saleService)
    {
        $this->validate();
        
        try {
            $sale = $saleService->createSale([
                'customer_id' => $this->customer_id,
                'items' => $this->sale_items
            ]);
            
            session()->flash('success', 'Venta registrada exitosamente');
            return redirect()->route('sales.show', $sale);
            
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.quick-sale', [
            'customers' => Customer::active()->get(),
            'total' => collect($this->sale_items)->sum(fn($item) => 
                ($item['weight'] ?? 0) * ($item['price_per_kg'] ?? 0)
            )
        ]);
    }
}
```

## 9. Vistas Blade

### Layout Principal
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Gestión Aguacate') }} - @yield('title')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-green-600">🥑 Gestión Aguacate</h1>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('lots.index')" :active="request()->routeIs('lots.*')">
                                Lotes
                            </x-nav-link>
                            <x-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*')">
                                Ventas
                            </x-nav-link>
                            <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">
                                Pagos
                            </x-nav-link>
                            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                                Reportes
                            </x-nav-link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    Perfil
                                </x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        Cerrar Sesión
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </nav>
        
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    @yield('header')
                </h2>
            </div>
        </header>
        
        <main>
            @if (session('success'))
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            
            @yield('content')
        </main>
    </div>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>
```

### Vista Dashboard
```blade
{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Panel de Control')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @livewire('dashboard')
    </div>
</div>
@endsection
```

### Componente Dashboard Livewire
```blade
{{-- resources/views/livewire/dashboard.blade.php --}}
<div>
    <div class="mb-4 flex justify-end">
        <select wire:model="period" class="rounded-md border-gray-300">
            <option value="today">Hoy</option>
            <option value="week">Esta Semana</option>
            <option value="month">Este Mes</option>
            <option value="year">Este Año</option>
        </select>
    </div>
    
    <!-- Métricas Principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Inventario Total
                            </dt>
                            <dd class="text-lg font-medium text-gray-900">
                                {{ number_format($metrics['inventory']['total_weight'], 2) }} kg
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Ventas del Período
                            </dt>
                            <dd class="text-lg font-medium text-gray-900">
                                ${{ number_format($metrics['financial']['total_sales'], 2) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Cuentas por Cobrar
                            </dt>
                            <dd class="text-lg font-medium text-gray-900">
                                ${{ number_format($metrics['financial']['accounts_receivable'], 2) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Cuentas por Pagar
                            </dt>
                            <dd class="text-lg font-medium text-gray-900">
                                ${{ number_format($metrics['financial']['accounts_payable'], 2) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribución por Calidad -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Distribución por Calidad</h3>
                <canvas id="qualityChart" wire:ignore></canvas>
            </div>
        </div>
        
        <!-- Flujo de Caja -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Flujo de Caja</h3>
                <canvas id="cashFlowChart" wire:ignore></canvas>
            </div>
        </div>
    </div>
    
    <!-- Alertas -->
    @if(count($metrics['alerts']) > 0)
    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Alertas del Sistema</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($metrics['alerts'] as $alert)
                            <li>{{ $alert['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:load', function () {
        // Gráfico de Distribución por Calidad
        const qualityCtx = document.getElementById('qualityChart').getContext('2d');
        const qualityData = @json($metrics['inventory']['quality_distribution']);
        
        new Chart(qualityCtx, {
            type: 'doughnut',
            data: {
                labels: qualityData.map(item => item.quality_grade),
                datasets: [{
                    data: qualityData.map(item => item.weight),
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        
        // Actualizar gráficos cuando cambie el período
        Livewire.on('metricsUpdated', metrics => {
            // Actualizar gráficos con nuevos datos
        });
    });
</script>
@endpush
```

## 10. API RESTful

### Rutas API
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('lots', Api\LotController::class);
    Route::apiResource('sales', Api\SaleController::class);
    Route::apiResource('payments', Api\PaymentController::class);
    
    Route::get('reports/dashboard', [Api\ReportController::class, 'dashboard']);
    Route::get('reports/profitability', [Api\ReportController::class, 'profitability']);
    Route::get('reports/inventory', [Api\ReportController::class, 'inventory']);
});
```

### API Controller
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LotResource;
use App\Models\Lot;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function index(Request $request)
    {
        $lots = Lot::with(['supplier', 'saleItems'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from_date, fn($q) => $q->whereDate('harvest_date', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('harvest_date', '<=', $request->to_date))
            ->paginate($request->per_page ?? 20);
            
        return LotResource::collection($lots);
    }
    
    public function show(Lot $lot)
    {
        $lot->load(['supplier', 'saleItems.sale', 'payments']);
        return new LotResource($lot);
    }
}
```

## 11. Testing

### Test de Lotes
```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lot;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LotManagementTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_lot()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        
        $response = $this->actingAs($user)->post('/lots', [
            'harvest_date' => now()->subDays(2),
            'entry_date' => now(),
            'total_weight' => 1000,
            'purchase_price_per_kg' => 25.50,
            'quality_grade' => 'Primera',
            'supplier_id' => $supplier->id,
            'payment_status' => 'pending'
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('lots', [
            'total_weight' => 1000,
            'purchase_price_per_kg' => 25.50,
            'total_purchase_cost' => 25500
        ]);
    }
    
    public function test_lot_profitability_calculation()
    {
        $lot = Lot::factory()->create([
            'total_weight' => 1000,
            'purchase_price_per_kg' => 25,
            'weight_sold' => 800
        ]);
        
        // Crear ventas
        $lot->saleItems()->create([
            'sale_id' => Sale::factory()->create()->id,
            'weight' => 800,
            'price_per_kg' => 35,
            'subtotal' => 28000
        ]);
        
        $profitability = $lot->profitability;
        
        $this->assertEquals(28000, $profitability['revenue']);
        $this->assertEquals(20000, $profitability['cost']);
        $this->assertEquals(8000, $profitability['profit']);
        $this->assertEquals(40, $profitability['margin']);
    }
}
```

## 12. Configuración y Deployment

### Docker Development
```dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install
RUN npm install && npm run build

CMD php artisan serve --host=0.0.0.0 --port=8000
```

### docker-compose.yml
```yaml
version: '3.8'
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=avocado_db
      - DB_USERNAME=root
      - DB_PASSWORD=secret
    depends_on:
      - mysql
      - redis
      
  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      - MYSQL_DATABASE=avocado_db
      - MYSQL_ROOT_PASSWORD=secret
    volumes:
      - mysql_data:/var/lib/mysql
      
  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
      
volumes:
  mysql_data:
```

## 13. Seguridad

### Middleware de Autorización
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            abort(403, 'No autorizado');
        }
        
        return $next($request);
    }
}
```

### Políticas de Acceso
```php
namespace App\Policies;

use App\Models\User;
use App\Models\Lot;

class LotPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
    
    public function view(User $user, Lot $lot): bool
    {
        return true;
    }
    
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operator');
    }
    
    public function update(User $user, Lot $lot): bool
    {
        return $user->hasRole('admin');
    }
    
    public function delete(User $user, Lot $lot): bool
    {
        return $user->hasRole('admin') && $lot->saleItems()->count() === 0;
    }
}
```

## 14. Optimizaciones de Rendimiento

### Caché de Consultas
```php
namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function getDashboardMetrics($userId)
    {
        return Cache::remember("dashboard_metrics_{$userId}", 300, function () {
            return app(ReportService::class)->getDashboardMetrics();
        });
    }
    
    public function clearDashboardCache($userId)
    {
        Cache::forget("dashboard_metrics_{$userId}");
    }
}
```

### Jobs para Procesos Pesados
```php
namespace App\Jobs;

use App\Models\Sale;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateInvoice implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        protected Sale $sale
    ) {}
    
    public function handle(InvoiceService $invoiceService)
    {
        $invoiceService->generate($this->sale);
    }
}
```

## 15. Cronograma de Implementación

### Fase 1: Configuración Base (1 semana)
- Instalación Laravel 12 y configuración
- Estructura de base de datos y migraciones
- Autenticación con Laravel Breeze
- Configuración Docker

### Fase 2: Modelos y Lógica de Negocio (2 semanas)
- Implementación de modelos Eloquent
- Servicios de negocio
- Repositorios
- Seeders con datos de prueba

### Fase 3: Interfaces de Usuario (2 semanas)
- Vistas Blade principales
- Componentes Livewire
- Dashboard interactivo
- Formularios CRUD

### Fase 4: Reportes y Análisis (1 semana)
- Sistema de reportes
- Gráficos con Chart.js
- Exportación PDF/Excel
- API endpoints

### Fase 5: Testing y Optimización (1 semana)
- Suite de pruebas completa
- Optimización de consultas
- Implementación de caché
- Documentación

### Fase 6: Deployment (3 días)
- Configuración servidor producción
- CI/CD con GitHub Actions
- Monitoreo con Laravel Telescope
- Capacitación usuarios

## 16. Presupuesto Adicional Laravel

### Costos de Infraestructura
- **Servidor VPS**: $40-80/mes (DigitalOcean/AWS)
- **Base de datos**: MySQL incluido o RDS $30/mes
- **Redis**: $15/mes
- **Backups**: $10/mes
- **Dominio y SSL**: $50/año

### Mantenimiento
- **Actualizaciones mensuales**: 4 horas
- **Soporte técnico**: 8 horas/mes
- **Nuevas funcionalidades**: Según requerimiento

## 17. Conclusiones

La implementación en Laravel 12 ofrece:

1. **Mayor escalabilidad** que el plugin WordPress
2. **Mejor rendimiento** para operaciones complejas
3. **Arquitectura moderna** y mantenible
4. **API RESTful** para integraciones futuras
5. **Testing automatizado** integrado
6. **Seguridad avanzada** con middleware y políticas
7. **Interfaz más rica** con Livewire
8. **Reportes avanzados** con procesamiento asíncrono

El sistema Laravel es ideal para un negocio en crecimiento que requiere flexibilidad y escalabilidad a largo plazo.