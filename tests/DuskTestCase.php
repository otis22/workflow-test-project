<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     *
     * In CI (DUSK_DRIVER_URL=http://localhost:9515): starts the bundled
     * ChromeDriver binary.
     * In Docker local (DUSK_DRIVER_URL=http://selenium:4444/wd/hub): the
     * Selenium container manages Chrome, so we skip ChromeDriver start.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        $driverUrl = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL', '');

        // Only start ChromeDriver if pointing at localhost:9515 (CI) or unset.
        if (str_contains((string) $driverUrl, '9515') || $driverUrl === '') {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    #[\Override]
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1920,1080',
            '--disable-gpu',
            '--headless=new',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ]);

        $driverUrl = $_ENV['DUSK_DRIVER_URL']
            ?? env('DUSK_DRIVER_URL')
            ?? 'http://localhost:9515';

        return RemoteWebDriver::create(
            $driverUrl,
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
