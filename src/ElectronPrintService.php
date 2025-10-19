<?php

namespace LaravelElectronPrinting;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ElectronPrintService
{
    protected $serviceUrl;
    protected $timeout;
    protected $enabled;
    protected $client;

    public function __construct($serviceUrl, $timeout = 30, $enabled = true)
    {
        $this->serviceUrl = rtrim($serviceUrl, '/');
        $this->timeout = $timeout;
        $this->enabled = $enabled;
        $this->client = new Client(['timeout' => $this->timeout]);
    }

    /**
     * Print HTML content
     *
     * @param string $html
     * @param string|null $printerName
     * @param array|string $options Profile name or options array
     * @return array
     */
    public function printHtml($html, $printerName = null, $options = [])
    {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'Print service is disabled'];
        }

        try {
            $response = $this->client->post($this->serviceUrl . '/print', [
                'json' => [
                    'html' => $html,
                    'printerName' => $printerName ?? config('electron-printing.default_printer'),
                    'options' => $this->resolveOptions($options)
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->info('Print job sent', [
                    'type' => 'html',
                    'printer' => $printerName,
                    'success' => $result['success'] ?? false
                ]);
            }

            return $result;
        } catch (GuzzleException $e) {
            $error = 'Print service error: ' . $e->getMessage();
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->error($error);
            }

            return ['success' => false, 'message' => $error];
        }
    }

    /**
     * Print a Blade view
     *
     * @param string $view
     * @param array $data
     * @param string|null $printerName
     * @param array|string $options Profile name or options array
     * @return array
     */
    public function printView($view, $data = [], $printerName = null, $options = [])
    {
        $html = view($view, $data)->render();
        return $this->printHtml($html, $printerName, $options);
    }

    /**
     * Get options from profile or use provided options
     *
     * @param array|string $options
     * @return array
     */
    protected function resolveOptions($options)
    {
        // If options is a string, treat it as a profile name
        if (is_string($options)) {
            $profile = config("electron-printing.profiles.{$options}");
            if ($profile) {
                return array_merge(config('electron-printing.default_options', []), $profile);
            }
        }

        // Otherwise merge with default options
        return array_merge(config('electron-printing.default_options', []), $options);
    }

    /**
     * Print from URL
     *
     * @param string $url
     * @param string|null $printerName
     * @param array $options
     * @return array
     */
    public function printUrl($url, $printerName = null, $options = [])
    {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'Print service is disabled'];
        }

        try {
            $response = $this->client->post($this->serviceUrl . '/print-url', [
                'json' => [
                    'url' => $url,
                    'printerName' => $printerName ?? config('electron-printing.default_printer'),
                    'options' => array_merge(config('electron-printing.default_options', []), $options)
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->info('Print job sent', [
                    'type' => 'url',
                    'url' => $url,
                    'printer' => $printerName,
                    'success' => $result['success'] ?? false
                ]);
            }

            return $result;
        } catch (GuzzleException $e) {
            $error = 'Print service error: ' . $e->getMessage();
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->error($error);
            }

            return ['success' => false, 'message' => $error];
        }
    }

    /**
     * Print PDF from URL
     *
     * @param string $pdfUrl
     * @param string|null $printerName
     * @param array $options
     * @return array
     */
    public function printPdfUrl($pdfUrl, $printerName = null, $options = [])
    {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'Print service is disabled'];
        }

        try {
            $response = $this->client->post($this->serviceUrl . '/print-pdf', [
                'json' => [
                    'pdfUrl' => $pdfUrl,
                    'printerName' => $printerName ?? config('electron-printing.default_printer'),
                    'options' => $options
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->info('Print job sent', [
                    'type' => 'pdf_url',
                    'url' => $pdfUrl,
                    'printer' => $printerName,
                    'success' => $result['success'] ?? false
                ]);
            }

            return $result;
        } catch (GuzzleException $e) {
            $error = 'Print service error: ' . $e->getMessage();
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->error($error);
            }

            return ['success' => false, 'message' => $error];
        }
    }

    /**
     * Print PDF from Base64 data
     *
     * @param string $pdfBase64
     * @param string|null $printerName
     * @param array $options
     * @return array
     */
    public function printPdfBase64($pdfBase64, $printerName = null, $options = [])
    {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'Print service is disabled'];
        }

        try {
            $response = $this->client->post($this->serviceUrl . '/print-pdf', [
                'json' => [
                    'pdfBase64' => $pdfBase64,
                    'printerName' => $printerName ?? config('electron-printing.default_printer'),
                    'options' => $options
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->info('Print job sent', [
                    'type' => 'pdf_base64',
                    'printer' => $printerName,
                    'success' => $result['success'] ?? false
                ]);
            }

            return $result;
        } catch (GuzzleException $e) {
            $error = 'Print service error: ' . $e->getMessage();
            
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->error($error);
            }

            return ['success' => false, 'message' => $error];
        }
    }

    /**
     * Print PDF from file path
     *
     * @param string $filePath
     * @param string|null $printerName
     * @param array $options
     * @return array
     */
    public function printPdfFile($filePath, $printerName = null, $options = [])
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'PDF file not found: ' . $filePath];
        }

        $pdfBase64 = base64_encode(file_get_contents($filePath));
        return $this->printPdfBase64($pdfBase64, $printerName, $options);
    }

    /**
     * Get available printers
     *
     * @return array
     */
    public function getPrinters()
    {
        if (!$this->enabled) {
            return [];
        }

        try {
            $response = $this->client->get($this->serviceUrl . '/printers');
            $data = json_decode($response->getBody(), true);
            
            return $data['printers'] ?? [];
        } catch (GuzzleException $e) {
            if (config('electron-printing.logging.enabled')) {
                Log::channel(config('electron-printing.logging.channel'))->error('Failed to get printers: ' . $e->getMessage());
            }

            return [];
        }
    }

    /**
     * Check service health
     *
     * @return bool
     */
    public function isHealthy()
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = $this->client->get($this->serviceUrl . '/health');
            $data = json_decode($response->getBody(), true);
            
            return $data['success'] ?? false;
        } catch (GuzzleException $e) {
            return false;
        }
    }
}
