<?php
require_once __DIR__ . '/../entity/cliente.php';



class ClienteRepository {

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
