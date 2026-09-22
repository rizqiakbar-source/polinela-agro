<?php
/**
 * Template Laporan Excel / CSV Polinela Agro Digital
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Laporan Polinela Agro') ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #2e7d32; color: #ffffff; }
    </style>
</head>
<body>
    <h2>POLITEKNIK NEGERI LAMPUNG</h2>
    <h3><?= esc($title ?? 'Laporan') ?></h3>
    <p>Tanggal Cetak: <?= date('d/m/Y H:i') ?></p>
    <table>
        <thead>
            <tr>
                <?php foreach (($headers ?? []) as $h): ?>
                    <th><?= esc($h) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($rows ?? []) as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?= esc($cell) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
