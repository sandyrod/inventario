<?php

namespace App\Helpers;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorJPG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeGenerator
{
    public function generate($code, $type = 'PNG')
    {
        $code = (string) $code;
        
        // Asegurarse de que el código no esté vacío
        if (empty($code)) {
            $code = 'NOCODE';
        }

        try {
            $generator = new BarcodeGeneratorPNG();
            $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128);
            
            return $barcode;
        } catch (\Exception $e) {
            // En caso de error, devolver un código de barras de error
            $generator = new BarcodeGeneratorPNG();
            return $generator->getBarcode('ERROR', $generator::TYPE_CODE_128);
        }
    }
}
