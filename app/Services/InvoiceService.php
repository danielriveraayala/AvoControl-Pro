<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate invoice PDF for a subscription payment
     */
    public function generateInvoicePDF(SubscriptionPayment $payment, bool $saveToStorage = true): ?string
    {
        try {
            $subscription = $payment->subscription;
            $user = $subscription->user;
            $tenant = $subscription->tenant;

            // Prepare invoice data
            $invoiceData = $this->prepareInvoiceData($payment, $subscription, $user, $tenant);
            
            // Generate PDF
            $pdf = PDF::loadView('pdfs.invoice', $invoiceData);
            $pdf->setPaper('legal', 'portrait'); // Legal size for Mexico standards
            
            $pdfContent = $pdf->output();
            
            if ($saveToStorage) {
                $filename = $this->generateInvoiceFilename($payment);
                $path = "invoices/{$subscription->id}/{$filename}";
                
                Storage::disk('local')->put($path, $pdfContent);
                
                // Update payment record with invoice path
                $payment->update([
                    'invoice_path' => $path,
                    'invoice_generated_at' => Carbon::now()
                ]);
                
                Log::info('Invoice PDF generated successfully', [
                    'payment_id' => $payment->id,
                    'subscription_id' => $subscription->id,
                    'file_path' => $path
                ]);
                
                return $path;
            }
            
            return $pdfContent;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Generate invoice for registration confirmation email
     */
    public function generateRegistrationInvoice(Subscription $subscription, SubscriptionPayment $payment = null): ?string
    {
        if (!$payment) {
            // Get the most recent successful payment
            $payment = $subscription->payments()
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        if (!$payment) {
            Log::warning('No payment found for registration invoice', [
                'subscription_id' => $subscription->id
            ]);
            return null;
        }
        
        return $this->generateInvoicePDF($payment, true);
    }

    /**
     * Get invoice PDF content for email attachment
     */
    public function getInvoiceForEmail(SubscriptionPayment $payment): ?array
    {
        try {
            $pdfPath = $this->generateInvoicePDF($payment, false);
            
            if (!$pdfPath) {
                return null;
            }
            
            $filename = $this->generateInvoiceFilename($payment);
            
            return [
                'content' => $pdfPath,
                'filename' => $filename,
                'mime_type' => 'application/pdf'
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get invoice for email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Prepare invoice data for PDF generation
     */
    private function prepareInvoiceData(SubscriptionPayment $payment, Subscription $subscription, User $user, Tenant $tenant = null): array
    {
        // Invoice number generation
        $invoiceNumber = $this->generateInvoiceNumber($payment);
        
        // Company information
        $companyInfo = [
            'name' => 'Kreativos Pro',
            'legal_name' => 'Kreativos Pro - Agencia de Marketing Digital y Desarrollo Web',
            'address_line_1' => 'Morelia, Michoacán',
            'address_line_2' => 'México',
            'tax_id' => 'RFC: RIAD850101XXX', // Replace with actual RFC
            'email' => 'avocontrol@kreativos.pro',
            'phone' => '+52 443 123 4567',
            'website' => 'https://kreativos.pro'
        ];
        
        // Customer information
        $customerInfo = [
            'name' => $user->name,
            'company' => $tenant ? $tenant->name : null,
            'email' => $user->email,
            'address' => $tenant ? ($tenant->address ?? 'No especificada') : 'No especificada',
            'tax_id' => $tenant ? ($tenant->tax_id ?? 'No especificado') : 'No especificado'
        ];
        
        // Invoice items
        $items = [];
        
        // Main subscription item
        $description = $this->getSubscriptionDescription($subscription);
        $unitPrice = abs($payment->amount);
        $quantity = 1;
        
        $items[] = [
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $quantity
        ];
        
        // Calculate totals
        $subtotal = collect($items)->sum('total');
        $taxRate = 0.16; // 16% IVA Mexico
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal + $taxAmount;
        
        // Payment information
        $paymentInfo = [
            'method' => 'PayPal',
            'transaction_id' => $payment->paypal_payment_id,
            'payment_date' => $payment->payment_date,
            'status' => $payment->status,
            'currency' => $payment->currency ?? 'USD'
        ];
        
        return [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $payment->payment_date ?? $payment->created_at,
            'due_date' => $payment->payment_date ?? $payment->created_at, // Immediate for subscriptions
            'company' => $companyInfo,
            'customer' => $customerInfo,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'payment' => $paymentInfo,
            'subscription' => $subscription,
            'notes' => $this->getInvoiceNotes($subscription),
            'terms' => $this->getInvoiceTerms()
        ];
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(SubscriptionPayment $payment): string
    {
        $year = $payment->created_at->format('Y');
        $month = $payment->created_at->format('m');
        $subscriptionId = str_pad($payment->subscription_id, 4, '0', STR_PAD_LEFT);
        $paymentId = str_pad($payment->id, 4, '0', STR_PAD_LEFT);
        
        return "AVO-{$year}{$month}-{$subscriptionId}-{$paymentId}";
    }

    /**
     * Generate invoice filename
     */
    private function generateInvoiceFilename(SubscriptionPayment $payment): string
    {
        $invoiceNumber = $this->generateInvoiceNumber($payment);
        $date = $payment->created_at->format('Y-m-d');
        
        return "Factura_{$invoiceNumber}_{$date}.pdf";
    }

    /**
     * Get subscription description for invoice
     */
    private function getSubscriptionDescription(Subscription $subscription): string
    {
        $planNames = [
            'trial' => 'Plan Trial - Prueba Gratuita',
            'basic' => 'Plan Básico - AvoControl Pro',
            'premium' => 'Plan Premium - AvoControl Pro',
            'enterprise' => 'Plan Enterprise - AvoControl Pro',
            'corporate' => 'Plan Corporativo - AvoControl Pro'
        ];
        
        $planName = $planNames[$subscription->plan] ?? 'Plan AvoControl Pro';
        $billingCycle = $subscription->billing_cycle === 'yearly' ? 'Anual' : 'Mensual';
        
        $period = '';
        if ($subscription->current_period_start && $subscription->current_period_end) {
            $start = Carbon::parse($subscription->current_period_start)->format('d/m/Y');
            $end = Carbon::parse($subscription->current_period_end)->format('d/m/Y');
            $period = " (Período: {$start} - {$end})";
        }
        
        return "{$planName} - Suscripción {$billingCycle}{$period}";
    }

    /**
     * Get invoice notes
     */
    private function getInvoiceNotes(Subscription $subscription): array
    {
        $notes = [];
        
        if ($subscription->plan === 'trial') {
            $notes[] = 'Esta es una suscripción de prueba gratuita de 7 días.';
        }
        
        $notes[] = 'Servicio de software como servicio (SaaS) para gestión de centros de acopio.';
        $notes[] = 'Pago procesado automáticamente vía PayPal.';
        
        if ($subscription->billing_cycle === 'yearly') {
            $notes[] = 'Facturación anual con descuento aplicado.';
        }
        
        $notes[] = 'Para soporte técnico contacte: avocontrol@kreativos.pro';
        
        return $notes;
    }

    /**
     * Get invoice terms and conditions
     */
    private function getInvoiceTerms(): array
    {
        return [
            'Los servicios son proporcionados bajo los términos y condiciones de uso de AvoControl Pro.',
            'El acceso al servicio está sujeto al pago puntual de la suscripción.',
            'Las cancelaciones deben realizarse antes del próximo período de facturación.',
            'Los reembolsos están sujetos a la política de reembolsos de la empresa.',
            'Este documento constituye una factura válida para efectos fiscales.'
        ];
    }

    /**
     * Get invoice by path
     */
    public function getInvoiceByPath(string $path): ?string
    {
        try {
            if (Storage::disk('local')->exists($path)) {
                return Storage::disk('local')->get($path);
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve invoice', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Delete invoice file
     */
    public function deleteInvoice(string $path): bool
    {
        try {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to delete invoice', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get invoice URL for download
     */
    public function getInvoiceDownloadUrl(SubscriptionPayment $payment): ?string
    {
        if (!$payment->invoice_path) {
            return null;
        }
        
        // Generate temporary signed URL for security
        return route('invoices.download', [
            'payment' => $payment->id,
            'signature' => hash_hmac('sha256', $payment->id, config('app.key'))
        ]);
    }
}