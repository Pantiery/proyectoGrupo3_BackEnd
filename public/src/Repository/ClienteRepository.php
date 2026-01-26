<?php
require_once __DIR__ . '/../entity/cliente.php';



class ClienteRepository {
//Comprobacion de correo duplicado
public function existeCorreo(string $correo):bool{
    global $pdo;

    $sql = "SELECT 1 FROM cliente WHERE correo = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$correo]);

    return $stmt->fetch() !== false;

}


//Comprobacion de usuario duplicado
public function existeUsuario(string $usuario):bool{
global $pdo;

$sql = "SELECT 1 FROM cliente WHERE usuario = ? LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([$usuario]);

return $stmt->fetch() !== false;

}

    public function crearCliente(Cliente $cliente): void {
        global $pdo;

        $sql = "INSERT INTO cliente 
                (nombre, correo, empresa, usuario, contrasena, tipo, pregunta_seguridad, respuesta_seguridad)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $cliente->getNombre(),
            $cliente->getCorreo(),
            $cliente->getEmpresa(),
            $cliente->getUsuario(),
            $cliente->getContrasena(),
            $cliente->getTipo(),
            $cliente->getPreguntaSeguridad(),
            $cliente->getRespuestaSeguridad()
        ]);
    }
}
