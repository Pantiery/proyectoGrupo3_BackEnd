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

        $sql = "
            INSERT INTO cliente
            (
                nombre,
                correo,
                usuario,
                contrasena,
                tipo,
                pregunta_seguridad,
                respuesta_seguridad,
                empresa
            )
            VALUES
            (
                ?, ?, ?, ?, 'TECNICO',
                ?, ?,
                NULL
            )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data["nombre"],
            $data["correo"],
            $data["usuario"],
            $data["contrasena"],
            $data["pregunta_seguridad"],
            $data["respuesta_seguridad"]
        ]);
    }
}

