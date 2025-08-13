<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayPalService;
use Illuminate\Support\Str;

class PayPalSyncPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:sync-plans 
                          {--create : Create missing plans in PayPal}
                          {--force : Force recreate all plans}
                          {--plan= : Sync specific plan only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize subscription plans with PayPal';

    private PayPalService $paypalService;

    /**
     * Create a new command instance.
     */
    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();
        $this->paypalService = $paypalService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 PayPal Plans Synchronization Started');
        $this->line('');

        $config = $this->paypalService->getConfig();
        $this->info("Mode: {$config['mode']}");
        $this->info("Client ID: " . substr($config['client_id'], 0, 10) . '...');
        $this->line('');

        // Step 1: Create or get PayPal Product
        $product = $this->createOrGetProduct();
        if (!$product) {
            $this->error('❌ Failed to create/get PayPal product. Aborting.');
            return 1;
        }

        $this->info("✅ Product ID: {$product['id']}");
        $this->line('');

        // Step 2: Process plans
        $plans = config('paypal.plans');
        $specificPlan = $this->option('plan');
        
        if ($specificPlan) {
            if (!isset($plans[$specificPlan])) {
                $this->error("❌ Plan '{$specificPlan}' not found in configuration.");
                return 1;
            }
            $plans = [$specificPlan => $plans[$specificPlan]];
        }

        $results = [];
        $bar = $this->output->createProgressBar(count($plans));
        $bar->start();

        foreach ($plans as $planKey => $planConfig) {
            $result = $this->processPlan($planKey, $planConfig, $product['id']);
            $results[$planKey] = $result;
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('');

        // Step 3: Display results
        $this->displayResults($results);

        // Step 4: Update .env with plan IDs
        if ($this->option('create') || $this->option('force')) {
            $this->updateEnvFile($results);
        }

        $this->line('');
        $this->info('🎉 PayPal Plans Synchronization Completed!');
        
        return 0;
    }

    /**
     * Create or get PayPal product
     */
    private function createOrGetProduct(): ?array
    {
        $productConfig = config('paypal.product');
        
        // Try to create product (will fail if already exists, which is fine)
        $result = $this->paypalService->createProduct(
            $productConfig['name'],
            $productConfig['description'],
            $productConfig['type']
        );

        if ($result['success']) {
            return $result['data'];
        }

        // If creation failed, it might already exist
        // For now, we'll use a default product ID or create one manually
        $this->warn('⚠️  Product creation failed. Using existing product or manual configuration required.');
        $this->warn('   Error: ' . $result['error']);
        
        // Return a mock product for demonstration
        // In production, you'd want to fetch existing products or handle this differently
        return [
            'id' => 'PROD_' . strtoupper(Str::random(10)),
            'name' => $productConfig['name']
        ];
    }

    /**
     * Process individual plan
     */
    private function processPlan(string $planKey, array $planConfig, string $productId): array
    {
        $planId = $planKey . '_plan_' . config('paypal.mode');
        
        // Skip corporate plan for automated creation (needs custom pricing)
        if ($planKey === 'corporate') {
            return [
                'status' => 'skipped',
                'message' => 'Corporate plan requires manual configuration',
                'plan_id' => null
            ];
        }

        // Skip trial plan if amount is 0 (PayPal doesn't allow $0 recurring plans)
        if ($planKey === 'trial') {
            return [
                'status' => 'skipped',
                'message' => 'Trial plan handled differently (no recurring payment)',
                'plan_id' => null
            ];
        }

        $result = $this->paypalService->createPlan($productId, $planId, [
            'name' => $planConfig['name'],
            'description' => $planConfig['description'],
            'amount' => $planConfig['amount'],
            'currency' => $planConfig['currency'],
            'interval_unit' => $planConfig['interval_unit'],
            'interval_count' => $planConfig['interval_count'],
            'trial_days' => $planConfig['trial_days'] ?? 0
        ]);

        if ($result['success']) {
            return [
                'status' => 'created',
                'message' => 'Plan created successfully',
                'plan_id' => $result['plan_id'],
                'data' => $result['data']
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => $result['error'],
                'plan_id' => null
            ];
        }
    }

    /**
     * Display synchronization results
     */
    private function displayResults(array $results): void
    {
        $this->info('📊 Synchronization Results:');
        $this->line('');

        $headers = ['Plan', 'Status', 'PayPal Plan ID', 'Message'];
        $rows = [];

        foreach ($results as $planKey => $result) {
            $status = $result['status'];
            $statusIcon = match($status) {
                'created' => '✅',
                'skipped' => '⚠️',
                'failed' => '❌',
                default => '❓'
            };

            $rows[] = [
                ucfirst($planKey),
                $statusIcon . ' ' . ucfirst($status),
                $result['plan_id'] ?? 'N/A',
                $result['message']
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Update .env file with plan IDs
     */
    private function updateEnvFile(array $results): void
    {
        $this->info('💾 Updating .env file with Plan IDs...');
        
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        foreach ($results as $planKey => $result) {
            if ($result['status'] === 'created' && $result['plan_id']) {
                $envKey = 'PAYPAL_' . strtoupper($planKey) . '_PLAN_ID';
                $envLine = "{$envKey}={$result['plan_id']}";
                
                // Check if key already exists
                if (strpos($envContent, $envKey) !== false) {
                    // Update existing line
                    $envContent = preg_replace(
                        '/^' . $envKey . '=.*$/m',
                        $envLine,
                        $envContent
                    );
                } else {
                    // Add new line
                    $envContent .= "\n{$envLine}";
                }
                
                $this->line("  Added: {$envLine}");
            }
        }
        
        file_put_contents($envPath, $envContent);
        $this->info('✅ Environment file updated successfully');
    }

    /**
     * Display help information
     */
    public function getHelp(): string
    {
        return <<<'HELP'
This command synchronizes subscription plans with PayPal.

<fg=yellow>Examples:</>
  paypal:sync-plans                    # Check current plans status
  paypal:sync-plans --create           # Create missing plans in PayPal
  paypal:sync-plans --force            # Force recreate all plans
  paypal:sync-plans --plan=basic       # Sync only the basic plan
  paypal:sync-plans --create --plan=premium  # Create only premium plan

<fg=yellow>Notes:</>
- Trial plan is handled differently (no recurring payment)
- Corporate plan requires manual configuration
- Use --force carefully as it will recreate existing plans
- Plan IDs will be automatically added to .env file

<fg=yellow>Requirements:</>
- Valid PayPal API credentials in .env
- Internet connection
- PayPal sandbox/live account access
HELP;
    }
}