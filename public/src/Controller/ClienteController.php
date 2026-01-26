<?php

require_once __DIR__ . '/../service/ClienteService.php';


class ClienteController {

    public function crear(array $data): void {
        $service = new ClienteService();
        $service->crearCliente($data);

        echo json_encode([
            "ok" => true,
            "message" => "Cliente creado correctamente"
        ]);
    }
}
