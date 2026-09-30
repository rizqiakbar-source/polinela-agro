<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size'     => round($file->getSize() / 1024, 2) . ' KB',
                    'date'     => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $backups,
        ]);
    }

    public function create(Request $request)
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $key = 'Tables_in_' . $dbName;

        $sql = "-- ========================================================\n";
        $sql .= "-- POLINELA AGRO DIGITAL - BACKUP DATABASE OTOMATIS\n";
        $sql .= "-- Waktu Pembuatan: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Politeknik Negeri Lampung\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$key ?? array_values((array)$tableObj)[0];

            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createTableSql = ((array)$createTable[0])['Create Table'] ?? '';

            $sql .= "\n-- Struktur tabel `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTableSql . ";\n\n";

            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data tabel `{$tableName}`\n";
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $escaped = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes((string)$val) . "'";
                    }, array_values($rowArray));

                    $sql .= "INSERT INTO `{$tableName}` (`" . implode('`, `', array_keys($rowArray)) . "`) VALUES (" . implode(', ', $escaped) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_polinela_agro_' . date('Ymd_His') . '.sql';
        File::put($backupDir . '/' . $filename, $sql);

        ActivityLog::create([
            'user_id'   => $request->user()->id,
            'aksi'      => 'Backup Database',
            'modul'     => 'Database Master',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Membuat file cadangan database {$filename}",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Cadangan database SQL berhasil dibuat.',
            'data'    => [
                'filename' => $filename,
                'sql'      => $sql,
            ]
        ]);
    }
}
