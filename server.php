<?php
// Función para calcular el total de un producto con IVA
function calcularTotalProducto($precio, $cantidad, $aplicaIVA) {
    $subtotal = $precio * $cantidad;
    $iva = $aplicaIVA ? $subtotal * 0.15 : 0;
    $total = $subtotal + $iva;
    return [$subtotal, $iva, $total];
}

$facturaGenerada = false;
$productos = [];
$subtotalGeneral = 0;
$totalIVA = 0;
$totalPagar = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreCliente = $_POST['nombre_cliente'];
    $correo = $_POST['correo'];
    $fecha = $_POST['fecha'];
    $comentarios = $_POST['comentarios'];

    $nombres = $_POST['producto_nombre'];
    $precios = $_POST['producto_precio'];
    $cantidades = $_POST['producto_cantidad'];
    $categorias = $_POST['producto_categoria'];
    $ivas = isset($_POST['producto_iva']) ? $_POST['producto_iva'] : [];

    for ($i = 0; $i < count($nombres); $i++) {
        $aplicaIVA = in_array($i, $ivas) || in_array((string)$i, $ivas);
        list($subtotal, $iva, $total) = calcularTotalProducto($precios[$i], $cantidades[$i], $aplicaIVA);

        $productos[] = [
            'nombre' => $nombres[$i],
            'precio' => $precios[$i],
            'cantidad' => $cantidades[$i],
            'categoria' => $categorias[$i],
            'aplicaIVA' => $aplicaIVA,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total
        ];

        $subtotalGeneral += $subtotal;
        $totalIVA += $iva;
        $totalPagar += $total;
    }
    $facturaGenerada = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura</title>
    <link rel="stylesheet" href="bootstrap-4.3.1/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-4">Generador de Facturas</h1>
    <?php if ($facturaGenerada): ?>
        <div class="alert alert-success">¡Factura generada correctamente!</div>
        <h2>Factura para <?php echo htmlspecialchars($nombreCliente); ?></h2>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo); ?></p>
        <p><strong>Fecha:</strong> <?php echo htmlspecialchars($fecha); ?></p>
        <p><strong>Comentarios:</strong> <?php echo nl2br(htmlspecialchars($comentarios)); ?></p>
        <table class="table table-bordered mt-4">
            <thead class="thead-light">
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>IVA</th>
                    <th>Subtotal</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                    <td><?php echo number_format($producto['precio'], 2); ?></td>
                    <td><?php echo $producto['cantidad']; ?></td>
                    <td><?php echo $producto['aplicaIVA'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo number_format($producto['subtotal'], 2); ?></td>
                    <td><?php echo number_format($producto['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mt-3">
            <p><strong>Subtotal General:</strong> <?php echo number_format($subtotalGeneral, 2); ?></p>
            <p><strong>Total IVA:</strong> <?php echo number_format($totalIVA, 2); ?></p>
            <p><strong>Total a Pagar:</strong> <?php echo number_format($totalPagar, 2); ?></p>
        </div>
        <a href="server.php" class="btn btn-primary mt-3">Generar nueva factura</a>
    <?php else: ?>
        <form method="post" action="" class="mb-5">
            <h2>Datos del Cliente</h2>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nombre_cliente">Nombre</label>
                    <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="correo">Correo</label>
                    <input type="email" class="form-control" id="correo" name="correo" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="comentarios">Comentarios</label>
                    <input type="text" class="form-control" id="comentarios" name="comentarios">
                </div>
            </div>
            <h2 class="mt-4">Productos</h2>
            <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="border rounded p-3 mb-3">
                <h5>Producto <?php echo $i + 1; ?></h5>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="producto_nombre[]" <?php echo $i == 0 ? 'required' : ''; ?> >
                    </div>
                    <div class="form-group col-md-2">
                        <label>Precio</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="producto_precio[]" <?php echo $i == 0 ? 'required' : ''; ?> >
                    </div>
                    <div class="form-group col-md-2">
                        <label>Cantidad</label>
                        <input type="number" min="1" class="form-control" name="producto_cantidad[]" <?php echo $i == 0 ? 'required' : ''; ?> >
                    </div>
                    <div class="form-group col-md-3">
                        <label>Categoría</label>
                        <input type="text" class="form-control" name="producto_categoria[]">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="producto_iva[]" value="<?php echo $i; ?>">
                            <label class="form-check-label">Aplica IVA</label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
            <button type="submit" class="btn btn-success">Generar Factura</button>
        </form>
    <?php endif; ?>
    <script src="bootstrap-4.3.1/js/bootstrap.min.js"></script>
</body>
</html>