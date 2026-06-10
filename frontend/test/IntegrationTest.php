<?php
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase {

    private $backendUrl = 'http://localhost:8080/api/order';
    private $seedFile;
    private $testFile;

    protected function setUp(): void {
        // CT Stage: Tentukan path file seed dan test
        $this->seedFile = __DIR__ . '/../../data/orders.seed.json';
        $this->testFile = __DIR__ . '/../../data/orders.json';

        // Salin seed data bersih sebelum tiap tes dimulai
        copy($this->seedFile, $this->testFile);
    }

    protected function tearDown(): void {
        // CT Stage: Kembalikan orders.json ke kondisi bersih setelah tiap tes
        file_put_contents($this->testFile, '[]');
    }

    public function testPhpToJavaCommunicationReturnsValidContract() {
        $payloadData = [
            "type" => "tshirt",
            "qty" => 5,
            "isCustom" => true,
            "complexity" => 2,
            "notes" => "Integrasi antar layanan lancar"
        ];
        $jsonPayload = json_encode($payloadData);

        $ch = curl_init($this->backendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            $this->fail("Gagal terhubung ke Java Backend. Error: " . $error_msg);
        }
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $this->assertJson($response);

        $decodedResponse = json_decode($response, true);
        $this->assertArrayHasKey('status', $decodedResponse);
        $this->assertArrayHasKey('orderId', $decodedResponse);
        $this->assertArrayHasKey('totalPrice', $decodedResponse);
        $this->assertEquals('success', $decodedResponse['status']);
        $this->assertNotEmpty($decodedResponse['orderId']);
        $this->assertEquals(375000, $decodedResponse['totalPrice']);
    }

    public function testBackendRejectsGetMethod() {
        $ch = curl_init($this->backendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(405, $httpCode);
    }

    public function testOrderHoodieWithDiscount() {
        $payloadData = [
            "type" => "hoodie",
            "qty" => 21,
            "isCustom" => false,
            "complexity" => 1,
            "notes" => "Test diskon hoodie"
        ];
        $jsonPayload = json_encode($payloadData);

        $ch = curl_init($this->backendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $decodedResponse = json_decode($response, true);
        $this->assertEquals('success', $decodedResponse['status']);
        $this->assertEquals(2677500, $decodedResponse['totalPrice']);
    }

    public function testOrderTShirtCustomComplexity3() {
        $payloadData = [
            "type" => "tshirt",
            "qty" => 10,
            "isCustom" => true,
            "complexity" => 3,
            "notes" => "Test complexity 3"
        ];
        $jsonPayload = json_encode($payloadData);

        $ch = curl_init($this->backendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $decodedResponse = json_decode($response, true);
        $this->assertEquals('success', $decodedResponse['status']);
        $this->assertEquals(850000, $decodedResponse['totalPrice']);
    }

    public function testOrderWithZeroQtyReturnsZeroTotal() {
        $payloadData = [
            "type" => "tshirt",
            "qty" => 0,
            "isCustom" => false,
            "complexity" => 1,
            "notes" => "Test qty nol"
        ];
        $jsonPayload = json_encode($payloadData);

        $ch = curl_init($this->backendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $decodedResponse = json_decode($response, true);
        $this->assertEquals('success', $decodedResponse['status']);
        $this->assertEquals(0, $decodedResponse['totalPrice']);
    }
}