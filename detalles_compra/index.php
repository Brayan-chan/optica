<?php
include '../Config/config.php';
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$products = [];

if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    $stmt = $conn->prepare("SELECT * FROM Producto WHERE IdP IN ($ids)");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $productId = $_POST['product_id'];
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]++;
        } else {
            $_SESSION['cart'][$productId] = 1;
        }
    } elseif (isset($_POST['remove_from_cart'])) {
        $productId = $_POST['product_id'];
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]--;
            if ($_SESSION['cart'][$productId] <= 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }
    } elseif (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $productId => $quantity) {
            if ($quantity > 0) {
                $_SESSION['cart'][$productId] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
        }
    } elseif (isset($_POST['order'])) {
        header("Location: ../pago/index.php");
        exit();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Compra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        .quantity-input {
            background: transparent;
            border: none;
            width: 40px;
            text-align: center;
            color: white;
            font-size: 1rem;
        }
        
        .quantity-input::-webkit-inner-spin-button,
        .quantity-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .quantity-container {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-4xl mx-auto rounded-3xl bg-[#121212] p-8">
        <h1 class="text-4xl font-bold text-center mb-12 tracking-wider">DETALLES DE COMPRA</h1>
        
        <form method="post" action="" id="cart-form">
            <?php foreach ($products as $product): ?>
                <div class="flex items-center justify-between mb-8 p-4">
                    <div class="flex items-center gap-8">
                        <div class="w-24 h-24 rounded-2xl border border-white/10 flex items-center justify-center p-2">
                            <img src="../assets/<?php echo strtolower($product['Tipo']); ?>.png" alt="Lentes" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold mb-1">LENTES <?php echo strtoupper($product['Tipo']); ?></h2>
                            <p class="text-xl">KYBALION 1224</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-8">
                        <span class="text-2xl">$<?php echo number_format($product['Precio'], 2); ?></span>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-400 mr-2">Cantidad</span>
                            <div class="quantity-container flex items-center rounded-full px-4 py-1">
                                <button type="button" class="text-xl px-2 hover:opacity-75" onclick="updateQuantity(<?php echo $product['IdP']; ?>, -1)">-</button>
                                <input type="number" name="quantities[<?php echo $product['IdP']; ?>]" value="<?php echo $cart[$product['IdP']]; ?>" min="1" class="quantity-input" onchange="updateCart()">
                                <button type="button" class="text-xl px-2 hover:opacity-75" onclick="updateQuantity(<?php echo $product['IdP']; ?>, 1)">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-end mt-8">
                <button type="submit" name="update_cart" class="px-8 py-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-lg">
                    Actualizar Carrito
                </button>
            </div>

            <div class="flex justify-end mt-8">
                <button type="submit" name="order" class="px-8 py-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                    Ordenar
                </button>
            </div>
        </form>
    </div>

    <script>
        function updateQuantity(productId, change) {
            const input = document.querySelector(`input[name="quantities[${productId}]"]`);
            let currentValue = parseInt(input.value);
            if (change !== 0) {
                currentValue += change;
                if (currentValue < 1) currentValue = 1;
                input.value = currentValue;
            }
            updateCart();
        }

        function updateCart() {
            const form = document.getElementById('cart-form');
            const formData = new FormData(form);
            formData.append('update_cart', true);

            fetch('', {
                method: 'POST',
                body: formData
            }).then(response => response.text()).then(data => {
                // Optionally handle the response data
            });
        }
    </script>
</body>
</html>