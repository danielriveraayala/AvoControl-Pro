<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    protected string $backupPath = 'backups';
    protected array $allowedTables = [
        'users', 'roles', 'permissions', 'role_user', 'permission_role',
        'suppliers', 'customers', 'lots', 'sales', 'sale_items', 'payments',
        'configurations', 'notifications', 'push_subscriptions'
    ];

    public function __construct()
    {
        // Backup directory initialization
        if (!Storage::exists($this->backupPath)) {
            Storage::makeDirectory($this->backupPath);
        }
    }

    /**
     * Display backup management interface
     */
    public function index()
    {
        $backups = $this->getBackupFiles();
        $systemInfo = $this->getSystemInfo();
        $lastBackup = $this->getLastBackupInfo();
        
        return view('developer.backups.index', compact('backups', 'systemInfo', 'lastBackup'));
    }

    /**
     * Create a new backup
     */
    public function create(Request $request)
    {
        try {
            $type = $request->input('type', 'full'); // full, database, files
            $description = $request->input('description', 'Manual backup');
            
            $backupData = $this->createBackup($type, $description);
            
            return response()->json([
                'success' => true,
                'message' => 'Respaldo creado exitosamente',
                'backup' => $backupData
            ]);
        } catch (\Exception $e) {
            \Log::error('Backup creation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el respaldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a backup file
     */
    public function download($filename)
    {
        try {
            $filePath = $this->backupPath . '/' . $filename;
            
            if (!Storage::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo de respaldo no encontrado'
                ], 404);
            }
            
            $fullPath = Storage::path($filePath);
            
            return Response::download($fullPath, $filename, [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            \Log::error('Backup download error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar el respaldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a backup file
     */
    public function delete($filename)
    {
        try {
            $filePath = $this->backupPath . '/' . $filename;
            
            if (!Storage::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo de respaldo no encontrado'
                ], 404);
            }
            
            Storage::delete($filePath);
            
            return response()->json([
                'success' => true,
                'message' => 'Respaldo eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Backup deletion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el respaldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request, $filename)
    {
        try {
            $filePath = $this->backupPath . '/' . $filename;
            
            if (!Storage::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo de respaldo no encontrado'
                ], 404);
            }
            
            $restoreResult = $this->restoreFromBackup($filePath);
            
            return response()->json([
                'success' => true,
                'message' => 'Restauración completada exitosamente',
                'details' => $restoreResult
            ]);
        } catch (\Exception $e) {
            \Log::error('Backup restore error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system information for backup
     */
    public function systemInfo()
    {
        return response()->json($this->getSystemInfo());
    }

    /**
     * Clean old backups
     */
    public function cleanup(Request $request)
    {
        try {
            $daysToKeep = $request->input('days', 30);
            $cutoffDate = Carbon::now()->subDays($daysToKeep);
            
            $deletedCount = 0;
            $backups = $this->getBackupFiles();
            
            foreach ($backups as $backup) {
                if (Carbon::parse($backup['created_at'])->lt($cutoffDate)) {
                    Storage::delete($this->backupPath . '/' . $backup['filename']);
                    $deletedCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} respaldos antiguos",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Backup cleanup error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar respaldos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create backup based on type
     */
    protected function createBackup(string $type, string $description): array
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "avocontrol_backup_{$type}_{$timestamp}.zip";
        $tempDir = storage_path('app/temp/backup_' . $timestamp);
        
        // Create temporary directory
        File::makeDirectory($tempDir, 0755, true);
        
        try {
            $backupInfo = [
                'filename' => $filename,
                'type' => $type,
                'description' => $description,
                'created_at' => Carbon::now()->toISOString(),
                'created_by' => auth()->user()->name,
                'size' => 0,
                'tables_included' => [],
                'files_included' => []
            ];
            
            // Create database backup
            if ($type === 'full' || $type === 'database') {
                $sqlFile = $this->createDatabaseBackup($tempDir);
                $backupInfo['tables_included'] = $this->allowedTables;
            }
            
            // Create files backup
            if ($type === 'full' || $type === 'files') {
                $this->createFilesBackup($tempDir);
                $backupInfo['files_included'] = ['uploads', 'storage/app/public', '.env'];
            }
            
            // Create info file
            File::put($tempDir . '/backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT));
            
            // Create ZIP file
            $zipPath = $this->createZipFromDirectory($tempDir, $filename);
            $backupInfo['size'] = File::size(Storage::path($this->backupPath . '/' . $filename));
            
            // Clean up temporary directory
            File::deleteDirectory($tempDir);
            
            return $backupInfo;
        } catch (\Exception $e) {
            // Clean up on error
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            throw $e;
        }
    }

    /**
     * Create database backup
     */
    protected function createDatabaseBackup(string $tempDir): string
    {
        $sqlFile = $tempDir . '/database.sql';
        $sqlContent = "-- AvoControl Pro Database Backup\n";
        $sqlContent .= "-- Created: " . Carbon::now()->toDateTimeString() . "\n";
        $sqlContent .= "-- Tables: " . implode(', ', $this->allowedTables) . "\n\n";
        
        $sqlContent .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        foreach ($this->allowedTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $sqlContent .= $this->getTableBackup($table);
            }
        }
        
        $sqlContent .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        File::put($sqlFile, $sqlContent);
        
        return $sqlFile;
    }

    /**
     * Get backup data for a specific table
     */
    protected function getTableBackup(string $table): string
    {
        $sql = "\n-- Table: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // Get table structure
        $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0];
        $sql .= $createTable->{'Create Table'} . ";\n\n";
        
        // Get table data
        $rows = DB::table($table)->get();
        
        if ($rows->count() > 0) {
            $sql .= "INSERT INTO `{$table}` VALUES\n";
            $values = [];
            
            foreach ($rows as $row) {
                $rowData = [];
                foreach ((array)$row as $value) {
                    if ($value === null) {
                        $rowData[] = 'NULL';
                    } else {
                        $rowData[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowData) . ')';
            }
            
            $sql .= implode(",\n", $values) . ";\n\n";
        }
        
        return $sql;
    }

    /**
     * Create files backup
     */
    protected function createFilesBackup(string $tempDir): void
    {
        $filesDir = $tempDir . '/files';
        File::makeDirectory($filesDir, 0755, true);
        
        // Copy important files
        $filesToBackup = [
            '.env' => base_path('.env'),
            'composer.json' => base_path('composer.json'),
            'package.json' => base_path('package.json')
        ];
        
        foreach ($filesToBackup as $name => $source) {
            if (File::exists($source)) {
                File::copy($source, $filesDir . '/' . $name);
            }
        }
        
        // Copy uploads directory if exists
        $uploadsPath = public_path('uploads');
        if (File::exists($uploadsPath)) {
            File::copyDirectory($uploadsPath, $filesDir . '/uploads');
        }
        
        // Copy storage/app/public if exists
        $publicStoragePath = storage_path('app/public');
        if (File::exists($publicStoragePath)) {
            File::copyDirectory($publicStoragePath, $filesDir . '/storage_public');
        }
    }

    /**
     * Create ZIP file from directory
     */
    protected function createZipFromDirectory(string $sourceDir, string $filename): string
    {
        $zipPath = Storage::path($this->backupPath . '/' . $filename);
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception('No se pudo crear el archivo ZIP');
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $filePath);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
        
        return $zipPath;
    }

    /**
     * Restore from backup file
     */
    protected function restoreFromBackup(string $backupPath): array
    {
        $tempDir = storage_path('app/temp/restore_' . time());
        File::makeDirectory($tempDir, 0755, true);
        
        try {
            // Extract ZIP file
            $zip = new ZipArchive();
            if ($zip->open(Storage::path($backupPath)) !== TRUE) {
                throw new \Exception('No se pudo abrir el archivo de respaldo');
            }
            
            $zip->extractTo($tempDir);
            $zip->close();
            
            $result = ['database' => false, 'files' => false];
            
            // Restore database if exists
            if (File::exists($tempDir . '/database.sql')) {
                $this->restoreDatabase($tempDir . '/database.sql');
                $result['database'] = true;
            }
            
            // Restore files if exist
            if (File::exists($tempDir . '/files')) {
                $this->restoreFiles($tempDir . '/files');
                $result['files'] = true;
            }
            
            // Clean up
            File::deleteDirectory($tempDir);
            
            return $result;
        } catch (\Exception $e) {
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            throw $e;
        }
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabase(string $sqlFile): void
    {
        $sql = File::get($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) { return !empty($stmt); }
        );
        
        DB::transaction(function() use ($statements) {
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    DB::unprepared($statement);
                }
            }
        });
    }

    /**
     * Restore files from backup
     */
    protected function restoreFiles(string $filesDir): void
    {
        // Restore configuration files
        if (File::exists($filesDir . '/.env')) {
            File::copy($filesDir . '/.env', base_path('.env'));
        }
        
        // Restore uploads
        if (File::exists($filesDir . '/uploads')) {
            $uploadsPath = public_path('uploads');
            if (File::exists($uploadsPath)) {
                File::deleteDirectory($uploadsPath);
            }
            File::copyDirectory($filesDir . '/uploads', $uploadsPath);
        }
        
        // Restore storage public
        if (File::exists($filesDir . '/storage_public')) {
            $storagePath = storage_path('app/public');
            if (File::exists($storagePath)) {
                File::deleteDirectory($storagePath);
            }
            File::copyDirectory($filesDir . '/storage_public', $storagePath);
        }
    }

    /**
     * Get list of backup files
     */
    protected function getBackupFiles(): array
    {
        $files = Storage::files($this->backupPath);
        $backups = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $filename = basename($file);
                $backups[] = [
                    'filename' => $filename,
                    'size' => Storage::size($file),
                    'size_human' => $this->formatBytes(Storage::size($file)),
                    'created_at' => Carbon::createFromTimestamp(Storage::lastModified($file)),
                    'type' => $this->getBackupTypeFromFilename($filename)
                ];
            }
        }
        
        return collect($backups)->sortByDesc('created_at')->values()->toArray();
    }

    /**
     * Get backup type from filename
     */
    protected function getBackupTypeFromFilename(string $filename): string
    {
        if (strpos($filename, '_full_') !== false) return 'full';
        if (strpos($filename, '_database_') !== false) return 'database';
        if (strpos($filename, '_files_') !== false) return 'files';
        return 'unknown';
    }

    /**
     * Get system information
     */
    protected function getSystemInfo(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
                'tables_count' => count($this->allowedTables)
            ],
            'storage' => [
                'backup_path' => storage_path('app/' . $this->backupPath),
                'available_space' => $this->formatBytes(disk_free_space(storage_path())),
                'total_space' => $this->formatBytes(disk_total_space(storage_path()))
            ]
        ];
    }

    /**
     * Get last backup information
     */
    protected function getLastBackupInfo(): ?array
    {
        $backups = $this->getBackupFiles();
        return !empty($backups) ? $backups[0] : null;
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