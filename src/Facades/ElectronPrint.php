<?php

namespace LaravelElectronPrinting\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array printHtml(string $html, string|null $printerName = null, array $options = [])
 * @method static array printView(string $view, array $data = [], string|null $printerName = null, array $options = [])
 * @method static array printUrl(string $url, string|null $printerName = null, array $options = [])
 * @method static array printPdfUrl(string $pdfUrl, string|null $printerName = null, array $options = [])
 * @method static array printPdfBase64(string $pdfBase64, string|null $printerName = null, array $options = [])
 * @method static array printPdfFile(string $filePath, string|null $printerName = null, array $options = [])
 * @method static array getPrinters()
 * @method static bool isHealthy()
 *
 * @see \LaravelElectronPrinting\ElectronPrintService
 */
class ElectronPrint extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'electron-print';
    }
}
