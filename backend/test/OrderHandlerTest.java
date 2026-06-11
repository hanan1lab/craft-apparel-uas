package backend.test;

import backend.OrderHandler;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.io.TempDir;

import java.io.File;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;

import static org.junit.jupiter.api.Assertions.*;

public class OrderHandlerTest {

    private OrderHandler handler;

    @BeforeEach
    public void setUp() {
        handler = new OrderHandler();
    }

    @TempDir
    Path sharedTempDir;

    // ==========================================================
    // TEST calculateTotalPrice()
    // ==========================================================

    @Test
    public void testTshirtQtyLebihDari100Diskon20Persen() {
        double total = handler.calculateTotalPrice("tshirt", 105, false, 1);
        assertEquals(4200000.0, total);
    }

    @Test
    public void testTshirtQtyAntara51Dan100Diskon10Persen() {
        double total = handler.calculateTotalPrice("tshirt", 60, false, 1);
        assertEquals(2700000.0, total);
    }

    @Test
    public void testTshirtTanpaDiskon() {
        double total = handler.calculateTotalPrice("tshirt", 10, false, 1);
        assertEquals(500000.0, total);
    }

    @Test
    public void testHoodieDiskon15Persen() {
        double total = handler.calculateTotalPrice("hoodie", 25, false, 1);
        assertEquals(3187500.0, total);
    }

    @Test
    public void testHoodieTanpaDiskon() {
        double total = handler.calculateTotalPrice("hoodie", 10, false, 1);
        assertEquals(1500000.0, total);
    }

    @Test
    public void testCustomComplexity1() {
        double total = handler.calculateTotalPrice("hoodie", 5, true, 1);
        assertEquals(800000.0, total);
    }

    @Test
    public void testCustomComplexity2() {
        double total = handler.calculateTotalPrice("tshirt", 5, true, 2);
        assertEquals(375000.0, total);
    }

    @Test
    public void testCustomComplexity3QtyKurang10() {
        double total = handler.calculateTotalPrice("tshirt", 5, true, 3);
        assertEquals(500000.0, total);
    }

    @Test
    public void testCustomComplexity3QtyMinimal10() {
        double total = handler.calculateTotalPrice("tshirt", 15, true, 3);
        assertEquals(1275000.0, total);
    }

    @Test
    public void testQtyNol() {
        double total = handler.calculateTotalPrice("tshirt", 0, false, 1);
        assertEquals(0.0, total);
    }

    @Test
    public void testQtyNegatif() {
        double total = handler.calculateTotalPrice("tshirt", -5, false, 1);
        assertEquals(0.0, total);
    }

    @Test
    public void testProdukTidakDikenal() {
        double total = handler.calculateTotalPrice("sepatu", 10, false, 1);
        assertEquals(0.0, total);
    }

    // ==========================================================
    // TEST saveOrderRecord()
    // ==========================================================

    @Test
    public void testSaveOrderRecordPertama() {

        handler.saveOrderRecord(
                "ORDER-001",
                "tshirt",
                2,
                100000,
                "Test Pertama"
        );

        File file = new File("./data/orders.json");

        assertTrue(file.exists());
        assertTrue(file.length() > 0);
    }

    @Test
    public void testSaveOrderRecordDuaKali() {

        handler.saveOrderRecord(
                "ORDER-001",
                "tshirt",
                2,
                100000,
                "Order Pertama"
        );

        handler.saveOrderRecord(
                "ORDER-002",
                "hoodie",
                1,
                150000,
                "Order Kedua"
        );

        File file = new File("./data/orders.json");

        assertTrue(file.exists());
        assertTrue(file.length() > 0);
    }

    // ==========================================================
    // TEST SIMULASI JSON FILE
    // ==========================================================

    @Test
    public void testJsonFileValid() throws IOException {

        Path fakeOrdersPath = sharedTempDir.resolve("orders.json");

        String jsonContent =
                "[{\"id\":\"TEST123\",\"type\":\"tshirt\",\"qty\":2,\"total\":100000.00}]";

        Files.write(
                fakeOrdersPath,
                jsonContent.getBytes(StandardCharsets.UTF_8)
        );

        assertTrue(Files.exists(fakeOrdersPath));

        String content = Files.readString(fakeOrdersPath);

        assertTrue(content.contains("TEST123"));
        assertTrue(content.contains("tshirt"));
    }
}