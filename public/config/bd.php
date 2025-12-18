<?php
class BD {
    private static $conexion = null;

    public static function conectar() {
        if (self::$conexion === null) {
            try {
                self::$conexion = new PDO(
                    "mysql:host=localhost;dbname=gestion_incidencias;charset=utf8",
                    "root",
                    ""
                );
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
