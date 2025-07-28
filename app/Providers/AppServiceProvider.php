<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('barcode-generator', function () {
            return new BarcodeGenerator();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register barcode generator helper function
        if (!function_exists('generate_barcode')) {
            function generate_barcode($code) {
                $generator = new BarcodeGeneratorPNG();
                return $generator->getBarcode($code, $generator::TYPE_CODE_128);
            }
        }
    }
}
