<?php
require_once __DIR__ . '/../service/TecnicoService.php';

class TecnicoController {

    public function crear(array $data): void {

        $service = new TecnicoService();
        $service->crearTecnico($data);

        echo json_encode([
            "ok" => true,
            "message" => "Tecnico creado correctamente"
        ]);
    }
}
