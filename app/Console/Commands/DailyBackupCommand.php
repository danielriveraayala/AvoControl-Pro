<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Developer\BackupController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DailyBackupCommand extends Command
{
    protected $signature = 'backup:daily 
                           {--type=full : Tipo de respaldo (full, database, files)}
                           {--cleanup=true : Eliminar respaldos anteriores después de crear el nuevo}';

    protected $description = 'Crear respaldo diario automático del sistema y limpiar respaldos antiguos';

    public function handle()
    {
        $this->info('🚀 Iniciando respaldo diario automático...');
        
        try {
            $type = $this->option('type');
            $cleanup = $this->option('cleanup') === 'true';
            
            // Crear una instancia del BackupController
            $backupController = new BackupController();
            
            // Obtener respaldos anteriores antes de crear el nuevo
            $previousBackups = $this->getPreviousBackups($type);
            
            $this->info("📦 Creando respaldo tipo: {$type}");
            
            // Crear el nuevo respaldo usando el método protegido
            $backupData = $this->createBackupViaController($type);
            
            if ($backupData) {
                $this->info("✅ Respaldo creado exitosamente: {$backupData['filename']}");
                $this->info("📊 Tamaño: " . $this->formatBytes($backupData['size']));
                
                // Solo limpiar respaldos anteriores si el nuevo se creó exitosamente
                if ($cleanup && !empty($previousBackups)) {
                    $this->cleanupPreviousBackups($previousBackups, $type);
                }
                
                Log::info('Daily backup completed successfully', [
                    'filename' => $backupData['filename'],
                    'type' => $type,
                    'size' => $backupData['size'],
                    'cleanup' => $cleanup
                ]);
                
                $this->info('🎉 Respaldo diario completado exitosamente');
                return Command::SUCCESS;
                
            } else {
                throw new \Exception('Error al crear el respaldo');
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error en respaldo diario: " . $e->getMessage());
            Log::error('Daily backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Crear respaldo usando el controlador
     */
    private function createBackupViaController(string $type): array
    {
        $backupController = new BackupController();
        $reflection = new \ReflectionClass($backupController);
        
        // Acceder al método protegido createBackup
        $method = $reflection->getMethod('createBackup');
        $method->setAccessible(true);
        
        $description = "Respaldo diario automático - " . Carbon::now()->format('d/m/Y H:i');
        
        return $method->invoke($backupController, $type, $description);
    }
    
    /**
     * Obtener respaldos anteriores del mismo tipo
     */
    private function getPreviousBackups(string $type): array
    {
        $backupPath = 'backups';
        $files = Storage::files($backupPath);
        $previousBackups = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $filename = basename($file);
                
                // Verificar si es del mismo tipo
                if ($this->isBackupOfType($filename, $type)) {
                    $previousBackups[] = [
                        'filename' => $filename,
                        'path' => $file,
                        'created_at' => Carbon::createFromTimestamp(Storage::lastModified($file))
                    ];
                }
            }
        }
        
        // Ordenar por fecha (más reciente primero)
        usort($previousBackups, function($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });
        
        return $previousBackups;
    }
    
    /**
     * Verificar si un archivo de respaldo es del tipo especificado
     */
    private function isBackupOfType(string $filename, string $type): bool
    {
        if (strpos($filename, "_full_") !== false) return $type === 'full';
        if (strpos($filename, "_database_") !== false) return $type === 'database';
        if (strpos($filename, "_files_") !== false) return $type === 'files';
        return false;
    }
    
    /**
     * Limpiar respaldos anteriores del mismo tipo
     */
    private function cleanupPreviousBackups(array $previousBackups, string $type): void
    {
        $deletedCount = 0;
        
        // Mantener solo el respaldo más reciente (el primero en el array ordenado)
        // Eliminar todos los demás del mismo tipo
        for ($i = 1; $i < count($previousBackups); $i++) {
            $backup = $previousBackups[$i];
            
            try {
                Storage::delete($backup['path']);
                $deletedCount++;
                
                $this->info("🗑️  Eliminado respaldo anterior: {$backup['filename']}");
                
            } catch (\Exception $e) {
                $this->warn("⚠️  No se pudo eliminar: {$backup['filename']} - {$e->getMessage()}");
            }
        }
        
        if ($deletedCount > 0) {
            $this->info("🧹 Se eliminaron {$deletedCount} respaldos anteriores del tipo '{$type}'");
        } else {
            $this->info("ℹ️  No había respaldos anteriores para eliminar");
        }
    }
    
    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}