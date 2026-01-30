<?php

class TecnicoRepository {

    public function existeCorreo(string $correo): bool {
        global $pdo;

        $stmt = $pdo->prepare(
            "SELECT 1 FROM cliente WHERE correo = ? LIMIT 1"
        );
        $stmt->execute([$correo]);

        return $stmt->fetch() !== false;
    }

    public function existeUsuario(string $usuario): bool {
        global $pdo;

        $stmt = $pdo->prepare(
            "SELECT 1 FROM cliente WHERE usuario = ? LIMIT 1"
        );
        $stmt->execute([$usuario]);

        return $stmt->fetch() !== false;
    }

    public function crear(array $data): void {
        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO cliente
            (nombre, correo, usuario, contrasena, tipo)
            VALUES (?, ?, ?, ?, 'TECNICO')
        ");

        $stmt->execute([
            $data["nombre"],
            $data["correo"],
            $data["usuario"],
            $data["contrasena"]
        ]);
    }
}
