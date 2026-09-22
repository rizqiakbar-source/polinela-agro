<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public static function generate(string $html, string $filename = 'document', bool $stream = true, string $paper = 'A4', string $orientation = 'portrait')
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        if ($stream) {
            $dompdf->stream($filename . '.pdf', ['Attachment' => 0]); // 0 = view in browser, 1 = download
            exit();
        }

        return $dompdf->output();
    }
}
