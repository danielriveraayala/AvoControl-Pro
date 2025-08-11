<?php

namespace App\Http\Controllers;

use App\Models\QualityGrade;
use App\Models\Setting;
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
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/i',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Set default sort_order if not provided
        if (!isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === '') {
            $validated['sort_order'] = QualityGrade::max('sort_order') + 1;
        }
        
        // Normalize color to lowercase
        if (isset($validated['color'])) {
            $validated['color'] = strtolower($validated['color']);
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
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/i',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ]);

        // Set default sort_order if not provided
        if (!isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === '') {
            $validated['sort_order'] = $qualityGrade->sort_order ?? 0;
        }
        
        // Normalize color to lowercase
        if (isset($validated['color'])) {
            $validated['color'] = strtolower($validated['color']);
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
        if ($request->ajax() && $request->has('ajax')) {
            // Handle DataTables request
            $query = QualityGrade::query();
            
            // Search functionality for DataTables
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            // Ordering for DataTables
            if ($request->has('order')) {
                $columns = ['sort_order', 'name', 'color', 'caliber_min', 'weight_min', 'active'];
                $orderColumn = $columns[$request->order[0]['column']] ?? 'sort_order';
                $orderDirection = $request->order[0]['dir'] ?? 'asc';
                $query->orderBy($orderColumn, $orderDirection);
            } else {
                $query->orderBy('sort_order', 'asc');
            }

            // Pagination for DataTables
            $totalRecords = $query->count();
            $qualities = $query->skip($request->start ?? 0)
                             ->take($request->length ?? 10)
                             ->get();

            // Format data for DataTables
            $data = $qualities->map(function($quality) {
                return [
                    'sort_order' => '<span class="badge badge-secondary">'.$quality->sort_order.'</span>',
                    'name' => '<strong>'.$quality->name.'</strong>',
                    'color' => '<div class="d-flex align-items-center">
                                    <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: '.$quality->color.'; border-radius: 4px; border: 1px solid #ccc;"></div>
                                    <small class="text-muted">'.$quality->color.'</small>
                                </div>',
                    'caliber' => $quality->caliber_min && $quality->caliber_max 
                                ? $quality->caliber_min . ' - ' . $quality->caliber_max 
                                : ($quality->caliber_min ?: ($quality->caliber_max ?: 'Sin especificar')),
                    'weight' => $quality->weight_min && $quality->weight_max 
                               ? $quality->weight_min . 'g - ' . $quality->weight_max . 'g'
                               : ($quality->weight_min ? $quality->weight_min . 'g+' : ($quality->weight_max ? $quality->weight_max . 'g-' : 'Sin especificar')),
                    'status' => '<span class="badge badge-'.($quality->active ? 'success' : 'secondary').'">'.($quality->active ? 'Activo' : 'Inactivo').'</span>',
                    'actions' => '<div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-info" onclick="editQuality('.$quality->id.')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteQuality('.$quality->id.', \''.addslashes($quality->name).'\')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                  </div>'
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => QualityGrade::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        }
        
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

    public function getCompanyConfig(Request $request)
    {
        try {
            // Get company configuration from settings table
            $config = [
                'company_name' => Setting::get('company_name', 'AvoControl-Pro', 'company'),
                'company_rfc' => Setting::get('company_rfc', '', 'company'),
                'company_address' => Setting::get('company_address', '', 'company'),
                'company_city' => Setting::get('company_city', 'Uruapan', 'company'),
                'company_state' => Setting::get('company_state', 'Michoacán', 'company'),
                'company_postal_code' => Setting::get('company_postal_code', '', 'company'),
                'company_phone' => Setting::get('company_phone', '', 'company'),
                'company_email' => Setting::get('company_email', '', 'company'),
                'company_website' => Setting::get('company_website', '', 'company'),
                'company_logo' => Setting::get('company_logo', '', 'company'),
                'company_description' => Setting::get('company_description', '', 'company'),
            ];

            return response()->json([
                'success' => true,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCompanyConfig(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_rfc' => 'nullable|string|max:13',
            'company_address' => 'nullable|string|max:500',
            'company_city' => 'nullable|string|max:255',
            'company_state' => 'nullable|string|max:255',
            'company_postal_code' => 'nullable|string|max:10',
            'company_phone' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_logo' => 'nullable|url|max:255',
            'company_description' => 'nullable|string|max:1000',
        ]);

        try {
            // Save each setting to the database
            foreach ($validated as $key => $value) {
                Setting::set($key, $value, 'company', $this->getCompanyFieldDescription($key));
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración de empresa guardada correctamente'
                ]);
            }

            return redirect()->back()->with('success', 'Configuración de empresa guardada correctamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar la configuración: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al guardar la configuración');
        }
    }

    /**
     * Get description for company configuration fields
     */
    private function getCompanyFieldDescription($field)
    {
        $descriptions = [
            'company_name' => 'Nombre de la empresa',
            'company_rfc' => 'RFC de la empresa',
            'company_address' => 'Dirección de la empresa',
            'company_city' => 'Ciudad donde se ubica la empresa',
            'company_state' => 'Estado donde se ubica la empresa',
            'company_postal_code' => 'Código postal de la empresa',
            'company_phone' => 'Teléfono de contacto de la empresa',
            'company_email' => 'Email de contacto de la empresa',
            'company_website' => 'Sitio web de la empresa',
            'company_logo' => 'URL del logo de la empresa',
            'company_description' => 'Descripción de la empresa para reportes'
        ];

        return $descriptions[$field] ?? '';
    }

    /**
     * Get email configuration
     */
    public function getEmailConfig(Request $request)
    {
        try {
            // Try to get from database first, fallback to .env
            $config = [
                'mail_mailer' => Setting::get('mail_mailer', env('MAIL_MAILER', 'smtp'), 'email'),
                'mail_host' => Setting::get('mail_host', env('MAIL_HOST', ''), 'email'),
                'mail_port' => Setting::get('mail_port', env('MAIL_PORT', 587), 'email'),
                'mail_username' => Setting::get('mail_username', env('MAIL_USERNAME', ''), 'email'),
                'mail_password' => Setting::get('mail_password', env('MAIL_PASSWORD', ''), 'email'),
                'mail_encryption' => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls'), 'email'),
                'mail_from_address' => Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', ''), 'email'),
                'mail_from_name' => Setting::get('mail_from_name', env('MAIL_FROM_NAME', ''), 'email'),
                'notification_email_enabled' => Setting::get('notification_email_enabled', env('NOTIFICATION_EMAIL_ENABLED', true), 'email'),
                'notification_daily_report_time' => Setting::get('notification_daily_report_time', env('NOTIFICATION_DAILY_REPORT_TIME', '08:00'), 'email'),
            ];

            return response()->json([
                'success' => true,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar configuración de email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store email configuration
     */
    public function storeEmailConfig(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string|in:smtp,sendmail,mailgun,postmark',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'notification_email_enabled' => 'nullable|boolean',
            'notification_daily_report_time' => 'nullable|string|max:5',
        ]);

        try {
            // Save to database first (primary source)
            foreach ($validated as $key => $value) {
                Setting::set($key, $value, 'email', $this->getEmailFieldDescription($key));
            }
            
            // Also update .env file as backup/fallback
            $this->updateEnvFile([
                'MAIL_MAILER' => $validated['mail_mailer'],
                'MAIL_HOST' => $validated['mail_host'],
                'MAIL_PORT' => $validated['mail_port'],
                'MAIL_USERNAME' => $validated['mail_username'] ?? '',
                'MAIL_PASSWORD' => $validated['mail_password'] ?? '',
                'MAIL_ENCRYPTION' => $validated['mail_encryption'] ?? '',
                'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
                'MAIL_FROM_NAME' => '"' . $validated['mail_from_name'] . '"',
                'NOTIFICATION_EMAIL_ENABLED' => isset($validated['notification_email_enabled']) ? ($validated['notification_email_enabled'] ? 'true' : 'false') : 'true',
                'NOTIFICATION_DAILY_REPORT_TIME' => '"' . ($validated['notification_daily_report_time'] ?? '08:00') . '"',
            ]);

            // Clear config cache to reload new values
            \Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Configuración de email guardada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test email configuration
     */
    public function testEmailConfig(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email|max:255'
        ]);

        try {
            // Test using dynamic mail service (from database)
            $result = \App\Services\DynamicMailService::testConnection($validated['test_email']);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'] . ' (configuración desde base de datos)'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email de prueba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update .env file with new values
     */
    private function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            // Handle boolean and null values
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = '';
            }

            // Escape value if it contains spaces and is not already quoted
            if (is_string($value) && str_contains($value, ' ') && !str_starts_with($value, '"')) {
                $value = '"' . $value . '"';
            }

            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                // Update existing key
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                // Add new key at the end
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    /**
     * Get description for email configuration fields
     */
    private function getEmailFieldDescription($field)
    {
        $descriptions = [
            'mail_mailer' => 'Proveedor de email (smtp, sendmail, etc.)',
            'mail_host' => 'Servidor SMTP para envío de emails',
            'mail_port' => 'Puerto del servidor SMTP',
            'mail_username' => 'Usuario para autenticación SMTP',
            'mail_password' => 'Contraseña para autenticación SMTP',
            'mail_encryption' => 'Tipo de encriptación (tls, ssl)',
            'mail_from_address' => 'Dirección de email remitente',
            'mail_from_name' => 'Nombre del remitente para emails',
            'notification_email_enabled' => 'Estado de las notificaciones por email',
            'notification_daily_report_time' => 'Hora para envío de reportes diarios'
        ];

        return $descriptions[$field] ?? '';
    }
}
