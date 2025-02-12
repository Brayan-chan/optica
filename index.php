<?php
include 'Config/config.php';

function getProductsByType($type) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Producto WHERE Tipo = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    return $stmt->get_result();
}

session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]++;
    } else {
        $_SESSION['cart'][$productId] = 1;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?type=" . $_GET['type']);
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : 'Oftálmico';
$products = getProductsByType($type);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Óptica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'CustomFont';
            src: url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        }
        
        body {
            font-family: 'CustomFont', sans-serif;
            background-color: #1a1a1a;
            color: white;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .filters-button {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto rounded-3xl bg-[#121212] p-8 relative overflow-hidden">
        <header class="flex justify-between items-start mb-12">
            <div class="text-sm opacity-80">
                <p>Sucursal LERMA</p>
                <p>C. 19, Lerma Centro, 24500</p>
            </div>
            <h1 class="text-4xl font-bold absolute left-1/2 -translate-x-1/2">ÓPTICA</h1>
            <div class="flex gap-4">
                <button onclick="detallesVentas()" class="p-2 rounded-full bg-white/10">
                    <svg class="w-6 h-6" fill="white" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M32 32c17.7 0 32 14.3 32 32l0 336c0 8.8 7.2 16 16 16l400 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L80 480c-44.2 0-80-35.8-80-80L0 64C0 46.3 14.3 32 32 32zm96 96c0-17.7 14.3-32 32-32l192 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-192 0c-17.7 0-32-14.3-32-32zm32 64l128 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-128 0c-17.7 0-32-14.3-32-32s14.3-32 32-32zm0 96l256 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32s14.3-32 32-32z"/>
                    </svg>
                </button>
                <button onclick="window.location.href='detalles_compra/index.php'" class="p-2 rounded-full bg-white/10 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="absolute top-0 right-0 inline-block w-6 h-6 text-center text-white bg-red-600 rounded-full">
                        <?php echo array_sum($_SESSION['cart']); ?>
                    </span>
                </button>
            </div>
        </header>

        <select id="product-filter" class="filters-button px-6 py-2 rounded-full mb-8 flex items-center gap-2" onchange="filterProducts()">
            <option value="Oftálmico" <?php echo $type == 'Oftálmico' ? 'selected' : ''; ?>>Oftálmicos</option>
            <option value="Solar" <?php echo $type == 'Solar' ? 'selected' : ''; ?>>Solares</option>
            <option value="Contacto" <?php echo $type == 'Contacto' ? 'selected' : ''; ?>>De contacto</option>
        </select>

        <h2 class="text-2xl font-bold mb-8">Lentes <?php echo $type; ?></h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card rounded-2xl p-4 relative">
                    <span class="absolute top-4 right-4 text-sm opacity-60">
                        <?php echo $product['Disponible'] > 0 ? $product['Disponible'] . ' pz' : 'Agotado'; ?>
                    </span>
                    <img src="assets/<?php echo strtolower($product['Tipo']); ?>.png" alt="Lentes <?php echo $product['Tipo']; ?>" class="w-full h-48 object-contain mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold">$<?php echo number_format($product['Precio'], 2); ?></span>
                        <form method="post" action="">
                            <input type="hidden" name="product_id" value="<?php echo $product['IdP']; ?>">
                            <button type="submit" name="add_to_cart" class="px-4 py-1 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                                Agregar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <footer class="mt-12 text-right text-sm opacity-60">
            <p>Contáctanos</p>
            <p>981-115-4756</p>
            <p>opticalerma@gmail.com</p>
        </footer>
    </div>
    <script>
        function filterProducts() {
            const type = document.getElementById('product-filter').value;
            window.location.href = `index.php?type=${type}`;
        }

        function detallesVentas() {
            window.location.href = 'detalle_ventas/index.php';
        }
    </script>
</body>
</html>