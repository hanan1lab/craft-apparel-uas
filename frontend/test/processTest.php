<?php
use PHPUnit\Framework\TestCase;

// KARENA FILE TEST ADA DI 'frontend/test/', CUKUP MUNDUR 1 LEVEL UNTUK KETEMU 'process.php'
require_once __DIR__ . '/../process.php';

class ProcessTest extends TestCase {

    /**
     * Menguji apakah fungsi berhasil menyusun string JSON yang valid
     * dengan tipe data yang sesuai kebutuhan Backend API Java.
     */
    public function testBuildPayloadFormatIsCorrectJson() {
        $type = "hoodie";
        $qty = "5"; // Input string dari form
        $isCustom = "true";
        $complexity = "2";
        $notes = "Desain logo depan";

        // Jalankan fungsi
        $jsonResult = buildOrderPayload($type, $qty, $isCustom, $complexity, $notes);
        
        // Assert 1: Pastikan outputnya adalah string berformat JSON valid
        $this->assertJson($jsonResult);

        // Dekode kembali untuk memeriksa struktur internal datanya
        $decoded = json_decode($jsonResult, true);

        // Assert 2: Pastikan konversi tipe data (casting) berjalan benar di BFF
        $this->assertEquals("hoodie", $decoded['type']);
        $this->assertSame(5, $decoded['qty']); // Harus integer
        $this->assertTrue($decoded['isCustom']); // Harus boolean murni
        $this->assertSame(2, $decoded['complexity']); // Harus integer
        $this->assertEquals("Desain logo depan", $decoded['notes']);
    }

    /**
     * Menguji penanganan jika catatan pesanan dikirim dalam keadaan kosong.
     */
    public function testBuildPayloadWithEmptyNotes() {
        $jsonResult = buildOrderPayload("tshirt", 10, "false", 1, "");
        
        $decoded = json_decode($jsonResult, true);
        $this->assertSame("", $decoded['notes']);
    }
}