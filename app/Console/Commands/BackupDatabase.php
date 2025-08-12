<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Developer\BackupController;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avocontrol:backup 
                            {--type=database : Backup type (database, files, full)}
                            {--description= : Backup description}
                            {--cleanup : Clean old backups after creating new one}
                            {--days=30 : Days to keep old backups when cleaning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create automatic backups for AvoControl Pro system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando proceso de respaldo...');
        
        try {
            $type = $this->option('type');
            $description = $this->option('description') ?: 'Respaldo automático via comando';
            
            // Validate type
            if (!in_array($type, ['database', 'files', 'full'])) {
                $this->error('❌ Tipo de respaldo inválido. Use: database, files, o full');
                return Command::FAILURE;
            }
            
            $this->info("📦 Creando respaldo tipo: {$type}");
            
            // Create backup using the controller logic
            $backupController = new BackupController();
            $backupData = $this->callProtectedMethod($backupController, 'createBackup', [$type, $description]);
            
            $this->info('✅ Respaldo creado exitosamente:');
            $this->line("   📁 Archivo: {$backupData['filename']}");
            $this->line("   📊 Tamaño: " . $this->formatBytes($backupData['size']));
            $this->line("   🕐 Fecha: {$backupData['created_at']}");
            
            if (!empty($backupData['tables_included'])) {
                $this->line("   🗄️  Tablas: " . implode(', ', $backupData['tables_included']));
            }
            
            if (!empty($backupData['files_included'])) {
                $this->line("   📂 Archivos: " . implode(', ', $backupData['files_included']));
            }
            
            // Cleanup old backups if requested
            if ($this->option('cleanup')) {
                $this->info('🧹 Limpiando respaldos antiguos...');
                $this->cleanupOldBackups($backupController);
            }
            
            $this->newLine();
            $this->info('🎉 Proceso de respaldo completado exitosamente!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error durante el respaldo: ' . $e->getMessage());
            \Log::error('Backup command error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Clean old backups
     */
    protected function cleanupOldBackups($backupController): void
    {
        try {
            $days = (int) $this->option('days');
            $cutoffDate = Carbon::now()->subDays($days);
            
            $this->line("   🗓️  Eliminando respaldos anteriores a: {$cutoffDate->format('Y-m-d')}");
            
            $backups = $this->callProtectedMethod($backupController, 'getBackupFiles', []);
            $deletedCount = 0;
            
            foreach ($backups as $backup) {
                if (Carbon::parse($backup['created_at'])->lt($cutoffDate)) {
                    \Storage::delete('backups/' . $backup['filename']);
                    $deletedCount++;
                    $this->line("   🗑️  Eliminado: {$backup['filename']}");
                }
            }
            
            if ($deletedCount > 0) {
                $this->info("✅ Se eliminaron {$deletedCount} respaldos antiguos");
            } else {
                $this->line("   ℹ️  No hay respaldos antiguos para eliminar");
            }
            
        } catch (\Exception $e) {
            $this->warn('⚠️  Error durante la limpieza: ' . $e->getMessage());
        }
    }
    
    /**
     * Call protected method using reflection
     */
    protected function callProtectedMethod($object, $method, $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
    
    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}
