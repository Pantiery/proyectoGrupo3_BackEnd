<?php
require_once __DIR__ . '/../repository/clienteRepository.php';

class GestionUsuario {

    public function login(array $data): array {

        if (empty($data["correo"]) || empty($data["contrasena"])) {
            throw new ValidationException("Usuario y contraseña obligatorios", "login");
        }

        $repo = new ClienteRepository();
        $user = $repo->login($data["correo"]);

        if (!$user || !password_verify($data["contrasena"], $user["contrasena"])) {
            throw new ValidationException("Credenciales incorrectas", "login");
        }

        return $user;
    }
}
