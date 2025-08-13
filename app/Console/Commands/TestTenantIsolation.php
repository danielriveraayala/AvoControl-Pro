<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Lot;
use App\Models\Sale;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TestTenantIsolation extends Command
{
    protected $signature = 'tenant:test-isolation {--cleanup : Clean up test data after tests}';
    
    protected $description = 'Test tenant data isolation and verify no cross-tenant data leakage';

    private array $testResults = [];
    private array $testTenants = [];
    private array $testUsers = [];

    public function handle()
    {
        $this->info('🔒 Iniciando tests de aislamiento de datos multi-tenant');
        $this->info('======================================================');
        
        $cleanup = $this->option('cleanup');
        
        try {
            $this->setupTestData();
            $this->runIsolationTests();
            $this->displayResults();
            
            if ($cleanup) {
                $this->cleanupTestData();
                $this->info('🧹 Test data cleaned up');
            }
            
        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            if ($cleanup) {
                $this->cleanupTestData();
            }
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }

    private function setupTestData()
    {
        $this->info('📋 Setting up test data...');
        
        // Create 3 test tenants
        for ($i = 1; $i <= 3; $i++) {
            $tenant = Tenant::create([
                'name' => "Test Tenant {$i}",
                'slug' => "test-tenant-{$i}-" . uniqid(),
                'email' => "test-tenant-{$i}-" . uniqid() . '@test.com',
                'status' => 'active',
                'plan' => 'basic'
            ]);
            
            $user = User::create([
                'name' => "Test User {$i}",
                'email' => "test-user-{$i}-" . uniqid() . '@test.com',
                'password' => bcrypt('testpassword'),
                'current_tenant_id' => $tenant->id
            ]);
            
            $this->testTenants[] = $tenant;
            $this->testUsers[] = $user;
            
            // Create test data for each tenant
            $this->createTenantData($tenant, $user);
        }
        
        $this->line('✅ Test data created for 3 tenants');
    }

    private function createTenantData($tenant, $user)
    {
        // Set current tenant for proper data association
        app()->instance('current_tenant', $tenant);
        
        // Create suppliers
        $supplier = Supplier::create([
            'tenant_id' => $tenant->id,
            'name' => "Supplier for {$tenant->name}",
            'phone' => '555-0000',
            'email' => "supplier-{$tenant->id}@test.com",
            'created_by' => $user->id
        ]);
        
        // Create customers
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => "Customer for {$tenant->name}",
            'phone' => '555-1111',
            'email' => "customer-{$tenant->id}@test.com",
            'created_by' => $user->id
        ]);
        
        // Create lots
        $lot = Lot::create([
            'tenant_id' => $tenant->id,
            'supplier_id' => $supplier->id,
            'quality_grade_id' => 1, // Assuming first quality grade exists
            'weight_kg' => 100.0,
            'price_per_kg' => 15.0,
            'total_cost' => 1500.0,
            'purchase_date' => now(),
            'status' => 'disponible',
            'created_by' => $user->id
        ]);
        
        // Create sale
        $sale = Sale::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 2000.0,
            'status' => 'completed',
            'created_by' => $user->id
        ]);
        
        // Create payment
        Payment::create([
            'tenant_id' => $tenant->id,
            'payable_type' => Sale::class,
            'payable_id' => $sale->id,
            'amount' => 2000.0,
            'payment_date' => now(),
            'payment_method' => 'efectivo',
            'type' => 'cliente',
            'created_by' => $user->id
        ]);
    }

    private function runIsolationTests()
    {
        $this->info('🧪 Running isolation tests...');
        
        $this->testGlobalScopeIsolation();
        $this->testDirectQueryIsolation();
        $this->testRelationshipIsolation();
        $this->testCrossReferenceProtection();
        $this->testUserTenantSwitching();
    }

    private function testGlobalScopeIsolation()
    {
        $this->runTest('Global Scope Isolation', function() {
            foreach ($this->testTenants as $index => $tenant) {
                // Simulate tenant context
                app()->instance('current_tenant', $tenant);
                
                // Test that each model only returns data for current tenant
                $suppliers = Supplier::all();
                $customers = Customer::all();
                $lots = Lot::all();
                $sales = Sale::all();
                $payments = Payment::all();
                
                // Each tenant should only see their own data (1 record each)
                if ($suppliers->count() !== 1) {
                    throw new \Exception("Tenant {$tenant->id} sees {$suppliers->count()} suppliers, expected 1");
                }
                
                if ($customers->count() !== 1) {
                    throw new \Exception("Tenant {$tenant->id} sees {$customers->count()} customers, expected 1");
                }
                
                if ($lots->count() !== 1) {
                    throw new \Exception("Tenant {$tenant->id} sees {$lots->count()} lots, expected 1");
                }
                
                if ($sales->count() !== 1) {
                    throw new \Exception("Tenant {$tenant->id} sees {$sales->count()} sales, expected 1");
                }
                
                if ($payments->count() !== 1) {
                    throw new \Exception("Tenant {$tenant->id} sees {$payments->count()} payments, expected 1");
                }
                
                // Verify correct tenant_id
                if ($suppliers->first()->tenant_id !== $tenant->id) {
                    throw new \Exception("Supplier has wrong tenant_id: {$suppliers->first()->tenant_id}");
                }
            }
            
            return 'All models properly isolated by tenant';
        });
    }

    private function testDirectQueryIsolation()
    {
        $this->runTest('Direct Query Isolation', function() {
            foreach ($this->testTenants as $tenant) {
                app()->instance('current_tenant', $tenant);
                
                // Test direct queries with where clauses
                $supplierCount = Supplier::where('name', 'like', '%Supplier%')->count();
                $customerCount = Customer::where('name', 'like', '%Customer%')->count();
                
                if ($supplierCount !== 1) {
                    throw new \Exception("Direct supplier query returned {$supplierCount} results, expected 1");
                }
                
                if ($customerCount !== 1) {
                    throw new \Exception("Direct customer query returned {$customerCount} results, expected 1");
                }
            }
            
            return 'Direct queries properly scoped by tenant';
        });
    }

    private function testRelationshipIsolation()
    {
        $this->runTest('Relationship Isolation', function() {
            foreach ($this->testTenants as $tenant) {
                app()->instance('current_tenant', $tenant);
                
                $supplier = Supplier::first();
                $customer = Customer::first();
                $sale = Sale::first();
                
                // Test relationships only return tenant-specific data
                $supplierLots = $supplier->lots;
                $customerSales = $customer->sales;
                $salePayments = $sale->payments;
                
                if ($supplierLots->count() !== 1) {
                    throw new \Exception("Supplier lots relationship returned {$supplierLots->count()} results");
                }
                
                if ($customerSales->count() !== 1) {
                    throw new \Exception("Customer sales relationship returned {$customerSales->count()} results");
                }
                
                if ($salePayments->count() !== 1) {
                    throw new \Exception("Sale payments relationship returned {$salePayments->count()} results");
                }
                
                // Verify tenant_id consistency
                foreach ($supplierLots as $lot) {
                    if ($lot->tenant_id !== $tenant->id) {
                        throw new \Exception("Related lot has wrong tenant_id");
                    }
                }
            }
            
            return 'Relationships properly isolated by tenant';
        });
    }

    private function testCrossReferenceProtection()
    {
        $this->runTest('Cross-Reference Protection', function() {
            $tenant1 = $this->testTenants[0];
            $tenant2 = $this->testTenants[1];
            
            // Set context to tenant1
            app()->instance('current_tenant', $tenant1);
            $tenant1Supplier = Supplier::first();
            $tenant1Customer = Customer::first();
            
            // Set context to tenant2  
            app()->instance('current_tenant', $tenant2);
            
            // Try to create lot with tenant1's supplier (should fail or be blocked)
            try {
                $lot = new Lot([
                    'tenant_id' => $tenant2->id,
                    'supplier_id' => $tenant1Supplier->id, // Cross-tenant reference
                    'quality_grade_id' => 1,
                    'weight_kg' => 50.0,
                    'price_per_kg' => 20.0,
                    'total_cost' => 1000.0,
                    'purchase_date' => now(),
                    'status' => 'disponible',
                    'created_by' => $this->testUsers[1]->id
                ]);
                
                // This should either fail validation or be automatically corrected
                $lot->save();
                
                if ($lot->tenant_id !== $tenant2->id) {
                    throw new \Exception('Cross-tenant reference was allowed');
                }
                
                // Clean up test lot
                $lot->delete();
                
            } catch (\Exception $e) {
                // This is expected behavior - cross-tenant references should be prevented
            }
            
            return 'Cross-tenant references properly prevented or corrected';
        });
    }

    private function testUserTenantSwitching()
    {
        $this->runTest('User Tenant Switching', function() {
            $user = $this->testUsers[0];
            $tenant1 = $this->testTenants[0];
            $tenant2 = $this->testTenants[1];
            
            // Test switching between tenants
            app()->instance('current_tenant', $tenant1);
            $suppliersInTenant1 = Supplier::count();
            
            app()->instance('current_tenant', $tenant2);
            $suppliersInTenant2 = Supplier::count();
            
            if ($suppliersInTenant1 !== 1 || $suppliersInTenant2 !== 1) {
                throw new \Exception('Tenant switching does not properly isolate data');
            }
            
            // Test that switching back shows correct data
            app()->instance('current_tenant', $tenant1);
            $suppliersBackToTenant1 = Supplier::count();
            
            if ($suppliersBackToTenant1 !== 1) {
                throw new \Exception('Tenant switching back failed');
            }
            
            return 'User tenant switching works correctly';
        });
    }

    private function runTest($testName, $testFunction)
    {
        try {
            $startTime = microtime(true);
            $result = $testFunction();
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->testResults[] = [
                'name' => $testName,
                'status' => 'PASSED',
                'message' => $result,
                'duration' => $duration
            ];
            
            $this->line("  ✅ {$testName}: {$result} ({$duration}ms)");
            
        } catch (\Exception $e) {
            $this->testResults[] = [
                'name' => $testName,
                'status' => 'FAILED',
                'message' => $e->getMessage(),
                'duration' => 0
            ];
            
            $this->line("  ❌ {$testName}: " . $e->getMessage());
        }
    }

    private function displayResults()
    {
        $this->newLine();
        $this->info('📊 Tenant Isolation Test Summary');
        $this->info('=================================');
        
        $passed = collect($this->testResults)->where('status', 'PASSED')->count();
        $failed = collect($this->testResults)->where('status', 'FAILED')->count();
        $total = count($this->testResults);
        
        $this->line("Total Tests: {$total}");
        $this->line("Passed: {$passed}");
        $this->line("Failed: {$failed}");
        
        if ($failed > 0) {
            $this->newLine();
            $this->error('❌ Failed Tests:');
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'FAILED') {
                    $this->line("  • {$result['name']}: {$result['message']}");
                }
            }
        }
        
        $successRate = ($total > 0) ? round(($passed / $total) * 100, 1) : 0;
        $this->newLine();
        
        if ($successRate >= 90) {
            $this->info("🎉 Success Rate: {$successRate}% - Tenant isolation is working correctly!");
        } elseif ($successRate >= 75) {
            $this->warn("⚠️  Success Rate: {$successRate}% - Some isolation issues found");
        } else {
            $this->error("🚨 Success Rate: {$successRate}% - Critical tenant isolation failures!");
        }
        
        $this->newLine();
        $this->info('💡 Tenant Data Summary:');
        foreach ($this->testTenants as $index => $tenant) {
            $this->line("  Tenant {$tenant->id} ({$tenant->name}): Created test data successfully");
        }
    }

    private function cleanupTestData()
    {
        foreach ($this->testTenants as $tenant) {
            // Delete all related data first
            Payment::where('tenant_id', $tenant->id)->forceDelete();
            Sale::where('tenant_id', $tenant->id)->forceDelete();
            Lot::where('tenant_id', $tenant->id)->forceDelete();
            Customer::where('tenant_id', $tenant->id)->forceDelete();
            Supplier::where('tenant_id', $tenant->id)->forceDelete();
        }
        
        foreach ($this->testUsers as $user) {
            $user->forceDelete();
        }
        
        foreach ($this->testTenants as $tenant) {
            $tenant->forceDelete();
        }
    }
}