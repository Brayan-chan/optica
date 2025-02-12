<?php
include '../Config/config.php';
session_start();

$cart = $_SESSION['cart'];
$products = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    $stmt = $conn->prepare("SELECT * FROM Producto WHERE IdP IN ($ids)");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
        $total += $row['Precio'] * $cart[$row['IdP']];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $metodoPago = $_POST['metodo_pago'];
    $pagoInicial = isset($_POST['pago_inicial']) ? $_POST['pago_inicial'] : $total;
    $deuda = $total - $pagoInicial;
    $estado = $deuda > 0 ? 0 : 1;
    $fechaPago = date('Y-m-d H:i:s');

    // Insertar en OrdenCompra
    $stmt = $conn->prepare("INSERT INTO OrdenCompra (TotalGlobal, fechaCompra) VALUES (?, ?)");
    $stmt->bind_param("ds", $total, $fechaPago);
    $stmt->execute();
    $idOrdenCompra = $stmt->insert_id;

    // Insertar en Relacion_OC_P
    foreach ($cart as $productId => $quantity) {
        $stmt = $conn->prepare("INSERT INTO Relacion_OC_P (FK_IdOC, FK_IdP) VALUES (?, ?)");
        $stmt->bind_param("ii", $idOrdenCompra, $productId);
        $stmt->execute();
    }

    // Insertar en Venta
    $cliente = $_POST['cliente'];
    $telefonoCliente = $_POST['telefono_cliente'];
    $sucursalId = 1; // Aquí puedes obtener el ID de la sucursal de alguna manera
    $stmt = $conn->prepare("INSERT INTO Venta (MetodoVenta, Entrega, Cliente, TelefonoCliente, FK_IdSucursal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $metodoPago, $fechaPago, $cliente, $telefonoCliente, $sucursalId);
    $stmt->execute();
    $idVenta = $stmt->insert_id;

    // Insertar en Relacion_V_OC
    $stmt = $conn->prepare("INSERT INTO Relacion_V_OC (FK_IdOC, FK_IdV, MetodoPago, PagoInicial, Estado, Deuda, FechaPago) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisdids", $idOrdenCompra, $idVenta, $metodoPago, $pagoInicial, $estado, $deuda, $fechaPago);
    $stmt->execute();

    // Limpiar el carrito
    $_SESSION['cart'] = [];

    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        .input-field {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            width: 100%;
        }
        
        .input-field:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .payment-toggle {
            appearance: none;
        }
        
        .payment-toggle:checked + label {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto rounded-3xl bg-[#121212] p-8">
        <h1 class="text-4xl font-bold text-center mb-12 tracking-wider">PAGO</h1>
        
        <form method="post" action="">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Left Column - Customer Information -->
                <div>
                    <h2 class="text-2xl font-bold mb-6">Información del cliente</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <input type="text" name="cliente" placeholder="Nombre" class="input-field" required>
                        </div>
                        
                        <div>
                            <input type="tel" name="telefono_cliente" placeholder="Teléfono" class="input-field" required>
                        </div>
                        
                        <div class="flex gap-4">
                            <div class="flex items-center">
                                <input type="radio" name="metodo_pago" id="efectivo" value="Efectivo" class="payment-toggle hidden" required>
                                <label for="efectivo" class="px-6 py-2 rounded-full border border-white/10 cursor-pointer hover:bg-white/5">
                                    Efectivo
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="metodo_pago" id="credito" value="Credito" class="payment-toggle hidden" required>
                                <label for="credito" class="px-6 py-2 rounded-full border border-white/10 cursor-pointer hover:bg-white/5">
                                    Crédito
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="metodo_pago" id="debito" value="Debito" class="payment-toggle hidden" required>
                                <label for="debito" class="px-6 py-2 rounded-full border border-white/10 cursor-pointer hover:bg-white/5">
                                    Débito
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4" id="abono_container" style="display: none;">
                            <label class="text-xl">Monto:</label>
                            <input type="number" name="pago_inicial" class="input-field" min="0" max="<?php echo $total; ?>" step="0.01">
                        </div>
                    </div>
                </div>

                <!-- Right Column - Cart & Payment Methods -->
                <div>
                    <!-- Cart Items -->
                    <div class="space-y-4 mb-8">
                        <?php foreach ($products as $product): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="w-16 h-16 rounded-2xl border border-white/10 flex items-center justify-center">
                                            <img src="../assets/<?php echo strtolower($product['Tipo']); ?>.png" alt="Lentes" class="w-12 h-12 object-contain">
                                        </div>
                                        <span class="absolute -top-2 -right-2 bg-white/10 w-6 h-6 rounded-full flex items-center justify-center text-sm"><?php echo $cart[$product['IdP']]; ?></span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold">LENTES <?php echo strtoupper($product['Tipo']); ?></h3>
                                        <p class="text-sm">KYBALION 1224</p>
                                    </div>
                                </div>
                                <span class="text-xl">$<?php echo number_format($product['Precio'] * $cart[$product['IdP']], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Payment Methods -->
                    <div class="space-y-4">
                        <h3 class="text-lg mb-4">Selecciona tu método de pago</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-white/10">
                                <span>Tarjeta de Débito</span>
                                <span class="text-sm opacity-60">VISA</span>
                            </div>
                            <button class="px-4 py-3 rounded-xl border border-white/10 hover:bg-white/5">
                                Ingresar tarjeta de Débito
                            </button>
                            
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-white/10">
                                <span>Tarjeta de Crédito</span>
                                <span class="text-sm opacity-60">mastercard</span>
                            </div>
                            <button class="px-4 py-3 rounded-xl border border-white/10 hover:bg-white/5">
                                Ingresar tarjeta de Crédito
                            </button>
                            
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-white/10">
                                <span>Efectivo</span>
                                <span class="text-sm opacity-60">OXXO</span>
                            </div>
                            <button class="px-4 py-3 rounded-xl border border-white/10 hover:bg-white/5">
                                Sucursal Oxxo más cercana
                            </button>
                        </div>

                        <!-- Shipping -->
                        <div class="flex items-center justify-between mt-8">
                            <span>Envío</span>
                            <button class="text-right text-sm opacity-60 hover:opacity-100">
                                Introducir la dirección de envío
                            </button>
                        </div>

                        <!-- Total -->
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-xl">Total</span>
                            <span class="text-xl">MXN $<?php echo number_format($total, 2); ?></span>
                        </div>

                        <!-- Confirm Button -->
                        <div class="flex justify-end mt-8">
                            <button type="submit" name="pay" class="px-8 py-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-lg">
                                Confirmar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const abonoContainer = document.getElementById('abono_container');
                if (this.value === 'Credito') {
                    abonoContainer.style.display = 'flex';
                } else {
                    abonoContainer.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>