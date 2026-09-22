<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Trigger manual database backup generation.
     */
    public function create(Request $request)
    {
        try {
            $result = $this->backupService->createBackup();

            return redirect()->back()->with('success', __('Database backup created successfully: :filename', ['filename' => $result['filename']]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to create database backup: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Download a specific backup SQL file.
     */
    public function download(string $filename)
    {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        $filepath = DatabaseBackupService::getBackupDir().DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($filepath)) {
            return redirect()->back()->with('error', __('Backup file not found.'));
        }

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Restore database from a stored backup file.
     */
    public function restore(Request $request, string $filename)
    {
        $filename = basename($filename);
        $filepath = DatabaseBackupService::getBackupDir().DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($filepath)) {
            return redirect()->back()->with('error', __('Backup file not found.'));
        }

        try {
            $this->backupService->restoreBackup($filepath);

            return redirect()->back()->with('success', __('Database successfully restored from :filename', ['filename' => $filename]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Database restore failed: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Delete a specific backup file.
     */
    public function destroy(string $filename)
    {
        $filename = basename($filename);
        $filepath = DatabaseBackupService::getBackupDir().DIRECTORY_SEPARATOR.$filename;

        if (File::exists($filepath)) {
            File::delete($filepath);

            return redirect()->back()->with('success', __('Backup file deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Backup file not found.'));
    }
}
