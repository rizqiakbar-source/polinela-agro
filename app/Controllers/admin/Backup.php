<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;

class Backup extends BaseController
{
    public function index()
    {
        $backupDir = WRITEPATH . 'backups/';
        if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);

        $files = glob($backupDir . '*.sql');
        $backups = [];
        foreach ($files as $f) {
            $backups[] = [
                'filename' => basename($f),
                'size'     => round(filesize($f) / 1024, 2) . ' KB',
                'date'     => date('Y-m-d H:i:s', filemtime($f)),
            ];
        }

        $data = [
            'title'   => 'Backup & Restore Database - Polinela Agro Digital',
            'backups' => $backups,
        ];

        return view('admin/backup/index', $data);
    }

    public function download()
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();

        $sql = "-- POLINELA AGRO DIGITAL DATABASE BACKUP\n";
        $sql .= "-- Waktu Pembuatan: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Politeknik Negeri Lampung\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            $sql .= "\n-- Struktur tabel `{$table}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTable['Create Table'] . ";\n\n";

            $rows = $db->table($table)->get()->getResultArray();
            if (!empty($rows)) {
                $sql .= "-- Data untuk tabel `{$table}`\n";
                foreach ($rows as $row) {
                    $escapedValues = array_map(function($v) use ($db) {
                        if ($v === null) return 'NULL';
                        return "'" . $db->escapeString($v) . "'";
                    }, array_values($row));

                    $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', array_keys($row)) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_polinela_agro_' . date('Ymd_His') . '.sql';

        // Simpan juga salinan di writable/backups
        $backupDir = WRITEPATH . 'backups/';
        if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
        file_put_contents($backupDir . $filename, $sql);

        log_system_activity('Backup Database', 'Database', "Membuat dan mengunduh cadangan database {$filename}");

        return $this->response->download($filename, $sql);
    }
}
