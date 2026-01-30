<?php
require_once __DIR__ . '/../repository/ClienteRepository.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class TecnicoService {

    public function crearTecnico(array $data): void {

        $repo = new ClienteRepository();

        // 🔍 Validaciones básicas
        if (empty($data["nombre"])) {
            throw new ValidationException("El nombre es obligatorio", "nombre");
        }

        if (empty($data["correo"]) || !filter_var($data["correo"], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Correo inválido", "correo");
        }

        if (empty($data["usuario"])) {
            throw new ValidationException("El usuario es obligatorio", "usuario");
        }

        if (empty($data["contrasena"]) || strlen($data["contrasena"]) < 6) {
            throw new ValidationException("Contraseña mínima 6 caracteres", "contrasena");
        }

        // 🔒 Duplicados
        if ($repo->existeCorreo($data["correo"])) {
            throw new DuplicateException("El correo ya existe", "correo");
        }

        if ($repo->existeUsuario($data["usuario"])) {
            throw new DuplicateException("El usuario ya existe", "usuario");
        }

        // 🔐 Hash
        $hash = password_hash($data["contrasena"], PASSWORD_DEFAULT);

        // 🔴 INSERT usando cliente
        $repo->crearTecnico([
            "nombre" => $data["nombre"],
            "correo" => $data["correo"],
            "usuario" => $data["usuario"],
            "contrasena" => $hash
        ]);
    }
}
