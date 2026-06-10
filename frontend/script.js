document.getElementById('orderForm').addEventListener('submit', function(event){
    const type = document.getElementById('productType').value;
    const qty = parseInt(document.getElementById('quantity').value, 10);
    const errorDiv = document.getElementById('errorMessage');

    errorDiv.innerHTML = "";

    if (qty <= 0) {
        event.preventDefault();
        errorDiv.innerHTML = "Kuantitas tidak boleh nol atau negatif.";
        return;
    }

    if (type === 'tshirt' && qty > 100) {
        event.preventDefault();
        errorDiv.innerHTML = "Stok T-Shirt tidak mencukupi (Max 100).";
        return;
    }

    if (type === 'hoodie' && qty > 50) {
        event.preventDefault();
        errorDiv.innerHTML = "Stok Hoodie tidak mencukupi (Max 50).";
        return;
    }
});