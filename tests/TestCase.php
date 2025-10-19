<?php

namespace LaravelElectronPrinting\Tests;

use LaravelElectronPrinting\ElectronPrintingServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ElectronPrintingServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'ElectronPrint' => \LaravelElectronPrinting\Facades\ElectronPrint::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default configuration
        $app['config']->set('electron-printing.enabled', true);
        $app['config']->set('electron-printing.service_url', 'http://localhost:3000');
    }
}
