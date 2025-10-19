<?php

namespace LaravelElectronPrinting\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'electron-printing:install {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Electron Printing Service';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('📦 Installing 6amTech Electron Printing Service...');
        $this->newLine();

        // Publish config
        $this->info('📄 Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'electron-printing-config',
            '--force' => $this->option('force')
        ]);

        // Publish Electron app
        $this->info('⚡ Publishing Electron application...');
        $this->call('vendor:publish', [
            '--tag' => 'electron-printing-app',
            '--force' => $this->option('force')
        ]);

        // Create .env entries
        $this->newLine();
        $this->info('🔧 Configuration:');
        $this->line('Add these to your .env file:');
        $this->newLine();
        
        $this->line('# Electron Printing Service');
        $this->line('PRINT_SERVICE_ENABLED=true');
        $this->line('PRINT_SERVICE_URL=http://localhost:3000');
        $this->line('PRINT_USE_WEBSOCKET=false');
        $this->line('PRINT_BROADCAST_CHANNEL=printing');
        
        $this->newLine();
        $this->info('📚 Next Steps:');
        $this->line('1. Update your .env file with the configuration above');
        $this->line('2. Navigate to: cd electron-print-service');
        $this->line('3. Install dependencies: npm install');
        $this->line('4. Start the service: npm start');
        $this->line('5. Test printing: php artisan electron-printing:test');
        
        $this->newLine();
        $this->info('✅ Installation complete!');
        $this->line('📖 Documentation: https://github.com/6amtech/laravel-electron-printing');

        return 0;
    }
}
