<?php
require_once __DIR__ . '/../service/gestionUsuario.php';

class AuthController {

    public function login(array $data): void {

        session_start();

        $service = new GestionUsuario();
        $user = $service->login($data);

        $_SESSION["id"] = $user["id_cliente"];
        $_SESSION["rol"] = $user["tipo"];

        echo json_encode([
            "ok" => true,
            "rol" => $user["tipo"]
        ]);
    }
}
