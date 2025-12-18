<?php
require_once 'bd.php';

try {
    $conexion = BD::conectar();
    echo "Conexión correcta";

    // Consulta de prueba
    $stmt = $conexion->query("SELECT NOW() AS fecha_actual");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Fecha actual desde la base de datos: " . $resultado['fecha_actual'];
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage();
}
