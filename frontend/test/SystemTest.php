<?php
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Chrome\ChromeOptions;

class SystemTest extends TestCase {
    private $driver;

protected function setUp(): void {
    $options = new \Facebook\WebDriver\Chrome\ChromeOptions();
    $options->addArguments([
        '--headless',
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--window-size=1280,720'
    ]);

    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(
        \Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY,
        $options
    );

    $this->driver = RemoteWebDriver::create(
        'http://localhost:4444',
        $capabilities
    );
}

    protected function tearDown(): void {
        $this->driver->quit();
    }

    /**
     * TC-e2e-01: Alur pemesanan T-Shirt normal tanpa custom
     */
    public function testOrderTShirtNormalBerhasil() {
        $this->driver->get('http://localhost:8000/index.php');

        // Pilih T-Shirt
        $select = new \Facebook\WebDriver\WebDriverSelect(
            $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="select-type"]'))
        );
        $select->selectByValue('tshirt');

        // Isi qty
        $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="input-qty"]'))
            ->clear()->sendKeys('10');

        // Pilih tidak custom
        $selectCustom = new \Facebook\WebDriver\WebDriverSelect(
            $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="select-custom"]'))
        );
        $selectCustom->selectByValue('false');

        // Isi catatan
        $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="input-notes"]'))
            ->sendKeys('Test order otomatis');

        // Klik submit
        $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="btn-submit"]'))->click();

        // Tunggu halaman process.php
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('[data-testid="api-response"]')
            )
        );

        // Validasi response sukses
        $response = $this->driver->findElement(
            WebDriverBy::cssSelector('[data-testid="api-response"]')
        )->getText();

        $this->assertStringContainsString('success', $response);
    }

    /**
     * TC-e2e-02: Validasi error qty nol di frontend
     */
    public function testValidasiQtyNolMunculError() {
        $this->driver->get('http://localhost:8000/index.php');

        // Isi qty = 0
        $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="input-qty"]'))
            ->clear()->sendKeys('0');

        // Klik submit
        $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="btn-submit"]'))->click();

        // Tunggu pesan error muncul
        $this->driver->wait(5)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('[data-testid="error-message"]')
            )
        );

        // Validasi pesan error
        $errorMsg = $this->driver->findElement(
            WebDriverBy::cssSelector('[data-testid="error-message"]')
        )->getText();

        $this->assertStringContainsString('Kuantitas tidak boleh nol atau negatif', $errorMsg);
    }

    /**
     * TC-e2e-03: Validasi error qty melebihi stok Hoodie
     */
    public function testValidasiQtyHoodieMelebihiStok() {
        $this->driver->get('http://localhost:8000/index.php');

        // Pilih Hoodie
        $select = new \Facebook\WebDriver\WebDriverSelect(
            $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="select-type"]'))
        );
        $select->selectByValue('hoodie');

        // Isi qty = 51
        $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="input-qty"]'))
            ->clear()->sendKeys('51');

        // Klik submit
        $this->driver->findElement(WebDriverBy::cssSelector('[data-testid="btn-submit"]'))->click();

        // Validasi pesan error
        $errorMsg = $this->driver->findElement(
            WebDriverBy::cssSelector('[data-testid="error-message"]')
        )->getText();

        $this->assertStringContainsString('Stok Hoodie tidak mencukupi', $errorMsg);
    }
}