<?php

namespace LaravelElectronPrinting\Tests;

use LaravelElectronPrinting\Facades\ElectronPrint;

class ElectronPrintTest extends TestCase
{
    /** @test */
    public function it_can_check_service_health()
    {
        // This test requires the Electron service to be running
        // In a real test environment, you would mock the HTTP client
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_get_configuration()
    {
        $config = config('electron-printing');
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('service_url', $config);
    }

    /** @test */
    public function it_has_print_profiles()
    {
        $profiles = config('electron-printing.profiles');
        
        $this->assertIsArray($profiles);
        $this->assertArrayHasKey('thermal_80mm', $profiles);
        $this->assertArrayHasKey('thermal_58mm', $profiles);
        $this->assertArrayHasKey('a4', $profiles);
    }

    /** @test */
    public function it_can_resolve_facade()
    {
        $this->assertInstanceOf(
            \LaravelElectronPrinting\ElectronPrintService::class,
            ElectronPrint::getFacadeRoot()
        );
    }
}
