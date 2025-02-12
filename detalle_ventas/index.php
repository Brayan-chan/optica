<?php
include '../Config/config.php';

$sql = "SELECT Venta.*, Sucursal.Nombre AS SucursalNombre FROM Venta JOIN Sucursal ON Venta.FK_IdSucursal = Sucursal.IdS";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Ventas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        .table-row {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            cursor: pointer;
        }
        
        .checkbox:checked {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto rounded-3xl bg-[#121212] p-8">
        <h1 class="text-4xl font-bold text-center mb-12 tracking-wider">DETALLES DE VENTAS</h1>
        
        <div class="rounded-2xl overflow-hidden border border-white/10">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="p-4 text-left">
                            <input type="checkbox" class="checkbox">
                        </th>
                        <th class="p-4 text-left text-gray-400 font-medium">Método de Venta</th>
                        <th class="p-4 text-left text-gray-400 font-medium">Cliente</th>
                        <th class="p-4 text-left text-gray-400 font-medium">Teléfono del Cliente</th>
                        <th class="p-4 text-left text-gray-400 font-medium">Sucursal</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="table-row hover:bg-white/5">
                            <td class="p-4"><input type="checkbox" class="checkbox"></td>
                            <td class="p-4"><?php echo $row['MetodoVenta'] ? 'Contado' : 'Abono'; ?></td>
                            <td class="p-4"><?php echo $row['Cliente']; ?></td>
                            <td class="p-4"><?php echo $row['TelefonoCliente']; ?></td>
                            <td class="p-4"><?php echo $row['SucursalNombre']; ?></td>
                            <td class="p-4"><button onclick="masDetalles()" class="px-4 py-1 rounded-full bg-white/10 hover:bg-white/20 text-sm">Detalles</button></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-4 mt-8">
            <button class="px-6 py-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                Anterior
            </button>
            <button class="px-6 py-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                Siguiente
            </button>
        </div>
    </div>
</body>

<script>
    function masDetalles() {
        window.location.href = '../mas_detalles/index.php';
    }
</script>

</html>