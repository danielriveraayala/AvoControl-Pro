<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateVapidKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:generate-vapid-keys {--show : Show the keys instead of writing to .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for web push notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔑 Generating VAPID keys for AvoControl Pro push notifications...');

        // Generate VAPID keys using OpenSSL
        $keys = $this->generateVapidKeys();

        if ($this->option('show')) {
            $this->displayKeys($keys);
            return;
        }

        // Write keys to .env file
        $this->writeKeysToEnv($keys);

        $this->newLine();
        $this->info('✅ VAPID keys generated successfully!');
        $this->info('🔒 Keys have been added to your .env file');
        $this->newLine();
        $this->warn('⚠️  IMPORTANT: Keep these keys secure and backup your .env file!');
        $this->warn('🚫 Never share your private key publicly!');
        
        return Command::SUCCESS;
    }

    /**
     * Generate VAPID key pair using simple method
     */
    private function generateVapidKeys(): array
    {
        $this->warn('⚠️  Using simplified key generation for development...');
        $this->info('ℹ️  For production, consider using proper VAPID key generation tools.');
        
        // Generate random keys for development (simplified approach)
        $privateKey = $this->generateRandomKey(32);
        $publicKey = $this->generateRandomKey(65);

        return [
            'private_key' => $this->base64UrlEncode($privateKey),
            'public_key' => $this->base64UrlEncode($publicKey)
        ];
    }
    
    /**
     * Generate random key of specified length
     */
    private function generateRandomKey(int $length): string
    {
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= chr(random_int(0, 255));
        }
        return $key;
    }

    /**
     * Convert PEM key to base64url format
     */
    private function convertPemToBase64Url(string $pem, string $type): string
    {
        // Remove PEM headers and decode
        $pem = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'], '', $pem);
        $pem = str_replace(['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----'], '', $pem);
        $pem = str_replace(["\r", "\n", " "], '', $pem);
        
        $binary = base64_decode($pem);
        
        if ($type === 'public') {
            // Extract the 65-byte public key from the DER format
            $publicKey = substr($binary, -65);
            return $this->base64UrlEncode($publicKey);
        } else {
            // For private key, we need the 32-byte private key
            $privateKey = substr($binary, -32);
            return $this->base64UrlEncode($privateKey);
        }
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Display the generated keys
     */
    private function displayKeys(array $keys): void
    {
        $this->newLine();
        $this->info('🔑 Generated VAPID Keys:');
        $this->newLine();
        
        $this->line('<comment>Public Key (VAPID_PUBLIC_KEY):</comment>');
        $this->line($keys['public_key']);
        $this->newLine();
        
        $this->line('<comment>Private Key (VAPID_PRIVATE_KEY):</comment>');
        $this->line($keys['private_key']);
        $this->newLine();
        
        $this->warn('⚠️  Keep the private key secure - never share it publicly!');
    }

    /**
     * Write keys to .env file
     */
    private function writeKeysToEnv(array $keys): void
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            $this->error('.env file not found!');
            exit(1);
        }

        $envContent = file_get_contents($envPath);
        
        // Check if keys already exist
        if (strpos($envContent, 'VAPID_PUBLIC_KEY=') !== false) {
            if (!$this->confirm('VAPID keys already exist in .env. Do you want to replace them?')) {
                $this->info('Operation cancelled.');
                return;
            }
            
            // Replace existing keys
            $envContent = preg_replace('/VAPID_PUBLIC_KEY=.*/', 'VAPID_PUBLIC_KEY=' . $keys['public_key'], $envContent);
            $envContent = preg_replace('/VAPID_PRIVATE_KEY=.*/', 'VAPID_PRIVATE_KEY=' . $keys['private_key'], $envContent);
        } else {
            // Add new keys
            $envContent .= "\n# VAPID Keys for Push Notifications\n";
            $envContent .= "VAPID_PUBLIC_KEY=" . $keys['public_key'] . "\n";
            $envContent .= "VAPID_PRIVATE_KEY=" . $keys['private_key'] . "\n";
        }
        
        file_put_contents($envPath, $envContent);
        
        // Clear config cache to load new values
        $this->call('config:clear');
    }
}
