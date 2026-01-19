<?php


require_once __DIR__ . '/../repository/ClienteRepository.php';
require_once __DIR__ . '/../entity/cliente.php';


class ClienteService {

    public function crearCliente(array $data): void {

        $cliente = new Cliente(
            $data["nombre"],
            $data["correo"],
            $data["usuario"],
            $data["contrasena"], // SIN hash aún
            $data["pregunta_seguridad"],
            $data["respuesta_seguridad"],
            $data["empresa"] ?? null
        );

        $repo = new ClienteRepository();
        $repo->crearCliente($cliente);
    }
}
