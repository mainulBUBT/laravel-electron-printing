<?php

namespace LaravelElectronPrinting\Console;

use Illuminate\Console\Command;
use LaravelElectronPrinting\Facades\ElectronPrint;

class TestPrintCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'electron-printing:test 
                            {--printer= : Specific printer name}
                            {--type=html : Print type (html, view, url, pdf)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Electron Printing Service';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🧪 Testing Electron Printing Service...');
        $this->newLine();

        // Check service health
        $this->info('🔍 Checking service health...');
        if (!ElectronPrint::isHealthy()) {
            $this->error('❌ Print service is not responding!');
            $this->line('Make sure the Electron service is running:');
            $this->line('  cd electron-print-service && npm start');
            return 1;
        }
        $this->info('✅ Service is healthy');
        $this->newLine();

        // Get printers
        $this->info('🖨️  Available printers:');
        $printers = ElectronPrint::getPrinters();
        
        if (empty($printers)) {
            $this->warn('⚠️  No printers found');
        } else {
            foreach ($printers as $printer) {
                $default = $printer['isDefault'] ?? false;
                $this->line('  • ' . $printer['name'] . ($default ? ' (default)' : ''));
            }
        }
        $this->newLine();

        // Test print
        $printer = $this->option('printer');
        $type = $this->option('type');

        $this->info("📄 Sending test print ({$type})...");
        
        $result = match($type) {
            'html' => ElectronPrint::printHtml(
                '<h1>Test Print</h1><p>6amTech Electron Printing Service</p><p>Test successful!</p>',
                $printer
            ),
            'view' => $this->testView($printer),
            'url' => $this->testUrl($printer),
            'pdf' => $this->testPdf($printer),
            default => ['success' => false, 'message' => 'Invalid type']
        };

        if ($result['success']) {
            $this->info('✅ Test print sent successfully!');
            $this->line('Check your printer for output.');
        } else {
            $this->error('❌ Test print failed!');
            $this->line('Error: ' . ($result['message'] ?? 'Unknown error'));
        }

        return $result['success'] ? 0 : 1;
    }

    protected function testView($printer)
    {
        // Create a simple test view
        $html = view()->make('electron-printing::test', [
            'title' => 'Test Print',
            'message' => 'This is a test from Blade view'
        ])->render();

        return ElectronPrint::printHtml($html, $printer);
    }

    protected function testUrl($printer)
    {
        $url = $this->ask('Enter URL to print', 'https://example.com');
        return ElectronPrint::printUrl($url, $printer);
    }

    protected function testPdf($printer)
    {
        $url = $this->ask('Enter PDF URL', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf');
        return ElectronPrint::printPdfUrl($url, $printer);
    }
}
