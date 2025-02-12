<?php
include '../Config/config.php';

$idVenta = $_GET['idVenta'];

$sql = "SELECT Venta.*, Sucursal.Nombre AS SucursalNombre, Relacion_V_OC.MetodoPago, Relacion_V_OC.PagoInicial, Relacion_V_OC.Deuda, Relacion_V_OC.FechaPago, Relacion_V_OC.Estado 
        FROM Venta 
        JOIN Sucursal ON Venta.FK_IdSucursal = Sucursal.IdS
        JOIN Relacion_V_OC ON Venta.IdV = Relacion_V_OC.FK_IdV
        WHERE Venta.IdV = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idVenta);
$stmt->execute();
$result = $stmt->get_result();
$venta = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Más Detalles</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
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
        
        .table-container {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto rounded-3xl bg-[#121212] p-8">
        <h1 class="text-4xl font-bold text-center mb-12 tracking-wider">MÁS DETALLES</h1>
        
        <div class="table-container rounded-2xl overflow-hidden mb-8">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="p-4 text-left">
                            <input type="checkbox" class="checkbox">
                        </th>
                        <th class="p-4 text-left text-gray-400 font-medium">Fecha de Pago</th>
                        <th class="p-4 text-left text-gray-400 font-medium">Método de Pago</th>
                        <th class="p-4 text-left text-gray-400 font-medium">Pago Inicial</th>
                        <th class="p-4 text-left text-gray-400 font-medium">Deuda</th>
                        <th class="p-4 text-left text-gray-400 font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-white/5">
                        <td class="p-4">
                            <input type="checkbox" class="checkbox">
                        </td>
                        <td class="p-4"><?php echo date('d/m/y', strtotime($venta['FechaPago'])); ?></td>
                        <td class="p-4"><?php echo $venta['MetodoPago']; ?></td>
                        <td class="p-4"><?php echo $venta['PagoInicial']; ?></td>
                        <td class="p-4"><?php echo $venta['Deuda']; ?></td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full bg-white/10 text-sm"><?php echo $venta['Estado'] ? 'Pagado' : 'Pendiente'; ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-start">
            <button onclick="window.history.back()" class="px-6 py-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                Anterior
            </button>
        </div>
    </div>
</body>
</html>