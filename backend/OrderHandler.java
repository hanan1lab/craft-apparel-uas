package backend;
import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;
import java.io.File;
import java.io.IOException;
import java.io.OutputStream;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;
import java.util.UUID;

public class OrderHandler implements HttpHandler {

    @Override
    public void handle(HttpExchange exchange) throws IOException {
        // Validasi Method HTTP
        if (!"POST".equalsIgnoreCase(exchange.getRequestMethod())) {
            exchange.sendResponseHeaders(405, -1); // Method Not Allowed
            return;
        }

        try {
            // 1. Membaca Request Body
            String requestBody = new String(exchange.getRequestBody().readAllBytes(), StandardCharsets.UTF_8);

            // 2. Ekstraksi Data dari JSON Sederhana
            String type = extractValue(requestBody, "type");
            int qty = Integer.parseInt(extractValue(requestBody, "qty"));
            boolean isCustom = Boolean.parseBoolean(extractValue(requestBody, "isCustom"));
            int complexity = Integer.parseInt(extractValue(requestBody, "complexity"));
            String notes = extractValue(requestBody, "notes");

            // 3. Kalkulasi Total Harga menggunakan Method Terpisah (Gampang di-Unit Test)
            double total = calculateTotalPrice(type, qty, isCustom, complexity);
            String orderId = UUID.randomUUID().toString();

            // 4. Menyimpan Rekap Pesanan ke File JSON
            saveOrderRecord(orderId, type, qty, total, notes);

            // 5. Mengirimkan HTTP Response 200 OK
            String responseBody = String.format(
                java.util.Locale.US,
                "{\"status\":\"success\",\"orderId\":\"%s\",\"totalPrice\":%.2f}", 
                orderId, total
            );
            
            exchange.getResponseHeaders().set("Content-Type", "application/json");
            byte[] responseBytes = responseBody.getBytes(StandardCharsets.UTF_8);
            exchange.sendResponseHeaders(200, responseBytes.length);

            try (OutputStream os = exchange.getResponseBody()) {
                os.write(responseBytes);
            }

        } catch (Exception e) {
            e.printStackTrace();
            // Antisipasi jika ada error internal (parsing, dsb) kirim status 500
            String errorResponse = "{\"status\":\"error\",\"message\":\"Internal Server Error\"}";
            exchange.getResponseHeaders().set("Content-Type", "application/json");
            exchange.sendResponseHeaders(500, errorResponse.length());
            try (OutputStream os = exchange.getResponseBody()) {
                os.write(errorResponse.getBytes());
            }
        }
    }

    /**
     * Core Engine Logika Kalkulasi Harga Pesanan.
     * Dipisahkan agar bisa diuji secara independen menggunakan JUnit.
     */
    public double calculateTotalPrice(String type, int qty, boolean isCustom, int complexity) {
        // VALIDASI ANTISIPASI: Jika kuantitas 0 atau negatif, total harga otomatis 0
        if (qty <= 0) {
            return 0.0;
        }

        double price = 0;

        // Menentukan Harga Dasar & Diskon Kuantitas
        if ("tshirt".equalsIgnoreCase(type)) {
            price = 50000;
            if (qty > 100) {
                price = price * 0.8;  // Diskon 20%
            } else if (qty > 50) {
                price = price * 0.9;  // Diskon 10%
            }
        } else if ("hoodie".equalsIgnoreCase(type)) {
            price = 150000;
            if (qty > 20) {
                price = price * 0.85; // Diskon 15%
            }
        }

        // Biaya Tambahan Kustomisasi Desain
        if (isCustom) {
            if (complexity == 1) {
                price += 10000;
            } else if (complexity == 2) {
                price += 25000;
            } else {
                if (qty < 10) {
                    price += 50000;
                } else {
                    price += 35000;
                }
            }
        }

        return price * qty;
    }

    /**
     * Menyimpan data pesanan ke data/orders.json dengan penanganan format array JSON yang aman.
     */
    public synchronized void saveOrderRecord(String orderId, String type, int qty, double total, String notes) {
        try {
            String pathStr = "./data/orders.json";
            File file = new File(pathStr);
            
            // Format data baru sebagai satu objek JSON
            String newOrderJson = String.format(
                "{\"id\":\"%s\",\"type\":\"%s\",\"qty\":%d,\"total\":%.2f,\"notes\":\"%s\"}",
                orderId, type, qty, total, notes
            );

            // Jika file tidak ada atau kosong, inisialisasi dengan array kosong
            if (!file.exists() || file.length() <= 2) {
                String initialArray = "[\n  " + newOrderJson + "\n]";
                Files.write(Paths.get(pathStr), initialArray.getBytes(StandardCharsets.UTF_8));
                return;
            }

            // Membaca konten yang sudah ada
            String content = new String(Files.readAllBytes(Paths.get(pathStr)), StandardCharsets.UTF_8).trim();
            
            // Cari posisi penutup array urutan paling belakang ']'
            int lastBracketIndex = content.lastIndexOf("]");
            if (lastBracketIndex != -1) {
                // Potong string sebelum ']', tambahkan koma, masukkan record baru, lalu tutup kembali dengan ']'
                String updatedContent = content.substring(0, lastBracketIndex).trim();
                if (updatedContent.endsWith("[")) {
                    updatedContent += "\n  " + newOrderJson + "\n]";
                } else {
                    updatedContent += ",\n  " + newOrderJson + "\n]";
                }
                Files.write(Paths.get(pathStr), updatedContent.getBytes(StandardCharsets.UTF_8), StandardOpenOption.TRUNCATE_EXISTING);
            }
        } catch (Exception e) {
            System.err.println("Gagal menulis ke orders.json: " + e.getMessage());
        }
    }

    /**
     * Helper Parser JSON Manual bawaan proyek.
     */
    private String extractValue(String json, String key) {
        String target = "\"" + key + "\":";
        int start = json.indexOf(target);
        if (start == -1)
            return "0";
        start += target.length();

        int end = json.indexOf(",", start);
        if (end == -1) {
            end = json.indexOf("}", start);
        }

        return json.substring(start, end).replaceAll("\"", "").replaceAll("\\[", "").replaceAll("\\]", "").trim();
    }
}