<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CraftApparel - Order Form</title>
</head>
<body>
    <h1>Formulir Pemesanan CraftApparel</h1>
    <form id="orderForm" action="process.php" method="POST">
        <label for="productType">Jenis Pakaian:</label>
        <select name="type" id="productType" data-testid="select-type">
            <option value="tshirt">T-Shirt (Max 100)</option>
            <option value="hoodie">Hoodie (Max 50)</option>
            <option value="Batik">Batik (Max 50)</option>
        </select>
        <br><br>

        <label for="quantity">Jumlah:</label>
        <input type="number" name="qty" id="quantity" data-testid="input-qty" required>
        <br><br>

        <label for="isCustom">Desain Custom:</label>
        <select name="isCustom" id="isCustom" data-testid="select-custom">
            <option value="false">Tidak</option>
            <option value="true">Ya</option>
        </select>
        <br><br>

        <label for="complexity">Tingkat Kerumitan Desain (1-3):</label>
        <input type="number" name="complexity" id="complexity" data-testid="input-complexity" value="1">
        <br><br>

        <label for="notes">Catatan Pesanan:</label>
        <textarea name="notes" id="notes" data-testid="input-notes"></textarea>
        <br><br>

        <button type="submit" data-testid="btn-submit">Proses Pesanan</button>
    </form>
    <div id="errorMessage" style="color: red;" data-testid="error-message"></div>

    <script src="script.js"></script>
</body>
</html>