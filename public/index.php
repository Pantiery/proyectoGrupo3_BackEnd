<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

// Cargar conexión BD
require __DIR__ . "/../config/db.php";

// =========================
// OBTENER MÉTODO Y RUTA
// =========================
$method = $_SERVER["REQUEST_METHOD"];

// 1) Obtener path (PATH_INFO si existe, si no REQUEST_URI)
$path = $_SERVER["PATH_INFO"] ?? parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// 2) Prefijo real del proyecto
$base = "/ticket/proyectoGrupo3_BackEnd/public";

// 3) Quitar prefijo base si viene
if (str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

// 4) Quitar /index.php si viene pegado
if (str_starts_with($path, "/index.php")) {
    $path = substr($path, strlen("/index.php"));
}

// 5) Normalizar
if ($path === "") {
    $path = "/";
}

// =========================
// FUNCIÓN PARA JSON BODY
// =========================
function jsonBody(): array {
    $raw = file_get_contents("php://input");
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

try {

    // =========================
    // RUTA RAÍZ
    // =========================
    if ($method === "GET" && $path === "/") {
        echo json_encode([
            "ok" => true,
            "message" => "API TecTicket funcionando"
        ]);
        exit;
    }

    // =========================
    // HEALTHCHECK
    // =========================
    if ($method === "GET" && $path === "/health") {
        echo json_encode(["ok" => true]);
        exit;
    }

    // =========================
    // CREAR TICKET (CLIENTE)
    // POST /tickets
    // =========================
    if ($method === "POST" && $path === "/tickets") {
        $data = jsonBody();

        $titulo = trim($data["titulo"] ?? "");
        $descripcion = trim($data["descripcion"] ?? "");
        $prioridad = $data["prioridad"] ?? "Media";
        $id_cliente = (int)($data["id_cliente"] ?? 0);

        if ($titulo === "" || $descripcion === "" || $id_cliente <= 0) {
            http_response_code(400);
            echo json_encode([
                "error" => "Faltan datos: titulo, descripcion, id_cliente"
            ]);
            exit;
        }

        // Estado ABIERTO
        $st = $pdo->prepare(
            "SELECT id_estado FROM estado WHERE nombre = 'ABIERTO' LIMIT 1"
        );
        $st->execute();
        $estado = $st->fetch();

        if (!$estado) {
            http_response_code(500);
            echo json_encode(["error" => "Estado ABIERTO no existe"]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO ticket
            (titulo, descripcion, prioridad, id_cliente, id_estado)
            VALUES (:t, :d, :p, :c, :e)
        ");
        $stmt->execute([
            ":t" => $titulo,
            ":d" => $descripcion,
            ":p" => $prioridad,
            ":c" => $id_cliente,
            ":e" => (int)$estado["id_estado"]
        ]);

        echo json_encode([
            "ok" => true,
            "id_ticket" => (int)$pdo->lastInsertId()
        ]);
        exit;
    }

    // =========================
    // TICKETS SIN ASIGNAR
    // GET /tickets/sin-asignar
    // =========================
    if ($method === "GET" && $path === "/tickets/sin-asignar") {
        $stmt = $pdo->query(
            "SELECT * FROM ticket WHERE id_tecnico IS NULL"
        );
        echo json_encode([
            "ok" => true,
            "tickets" => $stmt->fetchAll()
        ]);
        exit;
    }

    // =========================
    // ASIGNAR TICKET A TECNICO
    // POST /tickets/{id}/asignar
    // =========================
    if ($method === "POST" && preg_match("#^/tickets/(\d+)/asignar$#", $path, $m)) {
        $id_ticket = (int)$m[1];
        $data = jsonBody();
        $id_tecnico = (int)($data["id_tecnico"] ?? 0);

        if ($id_tecnico <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Falta id_tecnico"]);
            exit;
        }

        // Verificar ticket
        $st = $pdo->prepare("SELECT id_tecnico FROM ticket WHERE id_ticket = :id");
        $st->execute([":id" => $id_ticket]);
        $ticket = $st->fetch();

        if (!$ticket) {
            http_response_code(404);
            echo json_encode(["error" => "Ticket no encontrado"]);
            exit;
        }

        if (!is_null($ticket["id_tecnico"])) {
            http_response_code(409);
            echo json_encode(["error" => "Ticket ya asignado"]);
            exit;
        }

        // Verificar técnico
        $st = $pdo->prepare("SELECT id_tecnico FROM tecnico WHERE id_tecnico = :t");
        $st->execute([":t" => $id_tecnico]);
        if (!$st->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Tecnico no encontrado"]);
            exit;
        }

        // Estado EN_CURSO
        $st = $pdo->prepare(
            "SELECT id_estado FROM estado WHERE nombre = 'EN_CURSO' LIMIT 1"
        );
        $st->execute();
        $estado = $st->fetch();

        $stmt = $pdo->prepare("
            UPDATE ticket
            SET id_tecnico = :tec, id_estado = :est
            WHERE id_ticket = :id
        ");
        $stmt->execute([
            ":tec" => $id_tecnico,
            ":est" => (int)$estado["id_estado"],
            ":id"  => $id_ticket
        ]);

        echo json_encode(["ok" => true]);
        exit;
    }

    
// =========================
// LOGIN
// POST /login
// Body: { "usuario": "...", "contrasena": "..." }
// =========================
if ($method === "POST" && $path === "/login") {
    $data = jsonBody();

    $usuario = trim($data["usuario"] ?? "");
    $contrasena = (string)($data["contrasena"] ?? "");

    // 1) Validación básica
    if ($usuario === "" || $contrasena === "") {
        http_response_code(400);
        echo json_encode([
            "ok" => false,
            "error" => "Faltan campos",
            "detail" => "Se requiere usuario y contrasena"
        ]);
        exit;
    }

    // 2) Buscar usuario en las 3 tablas (admin/cliente/tecnico)
    // Importante: devolvemos una respuesta genérica si no existe o contraseña incorrecta
    $found = null;

    // ADMIN
    $st = $pdo->prepare("SELECT id_admin AS id, usuario, contrasena, 'ADMIN' AS rol FROM admin WHERE usuario = :u LIMIT 1");
    $st->execute([":u" => $usuario]);
    $row = $st->fetch();
    if ($row) $found = $row;

    // CLIENTE
    if (!$found) {
        $st = $pdo->prepare("SELECT id_cliente AS id, usuario, contrasena, 'CLIENTE' AS rol FROM cliente WHERE usuario = :u LIMIT 1");
        $st->execute([":u" => $usuario]);
        $row = $st->fetch();
        if ($row) $found = $row;
    }

    // TECNICO
    if (!$found) {
        $st = $pdo->prepare("SELECT id_tecnico AS id, usuario, contrasena, 'TECNICO' AS rol FROM tecnico WHERE usuario = :u LIMIT 1");
        $st->execute([":u" => $usuario]);
        $row = $st->fetch();
        if ($row) $found = $row;
    }

    // 3) Si no existe usuario -> 401 (no autorizado)
    // (Mensaje genérico para no revelar si el usuario existe)
    if (!$found) {
        http_response_code(401);
        echo json_encode([
            "ok" => false,
            "error" => "Credenciales inválidas"
        ]);
        exit;
    }

    // 4) Verificar contraseña
    $hash = (string)$found["contrasena"];
    if (!password_verify($contrasena, $hash)) {
        http_response_code(401);
        echo json_encode([
            "ok" => false,
            "error" => "Credenciales inválidas"
        ]);
        exit;
    }

    // 5) Login OK (devuelve identidad y rol)
    echo json_encode([
        "ok" => true,
        "id" => (int)$found["id"],
        "rol" => $found["rol"],
        "usuario" => $found["usuario"]
    ]);
    exit;
}

    // =========================
    // 404
    // =========================
    http_response_code(404);
    echo json_encode([
        "error" => "Ruta no encontrada",
        "path" => $path
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error interno del servidor",
        "detail" => $e->getMessage()
    ]);
}
