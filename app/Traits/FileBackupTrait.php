<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait FileBackupTrait
{
    /**
     * Backup the existing files (controller, model, view) to a backup directory.
     *
     * @param string $studlyCase
     * @return void
     */
    public function backupExistingFiles(string $studlyCase): void
    {
        $modelDirectory = app_path("Models/{$studlyCase}");
        $controllerDirectory = app_path("Http/Controllers");
        $viewDirectory = resource_path("views/{$studlyCase}");

        $backupDirectory = base_path("_backup_files/{$studlyCase}/" . now()->format('Y-m-d_H-i-s'));

        // Ensure backup directory exists
        File::makeDirectory($backupDirectory, 0755, true);

        // Backup Model
        $modelPath = "{$modelDirectory}/{$studlyCase}.php";
        if (File::exists($modelPath)) {
            $this->backupFile($modelPath, "{$backupDirectory}/Models/{$studlyCase}.php");
        }

        // Backup Controller
        $controllerPath = "{$controllerDirectory}/{$studlyCase}Controller.php";
        if (File::exists($controllerPath)) {
            $this->backupFile($controllerPath, "{$backupDirectory}/Controllers/{$studlyCase}Controller.php");
        }

        // Backup Views
        if (File::exists($viewDirectory)) {
            File::copyDirectory($viewDirectory, "{$backupDirectory}/Views");
        }
    }

    /**
     * Backup a single file to the backup directory.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @return void
     */
    private function backupFile(string $sourcePath, string $destinationPath): void
    {
        $destinationDir = dirname($destinationPath);
        if (!File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }
        File::copy($sourcePath, $destinationPath);
    }
}
