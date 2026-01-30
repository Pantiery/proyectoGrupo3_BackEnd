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
    $base = "/proyectoGrupo3_BackEnd/public";

    // 3) Quitar prefijo base si viene
    if (str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }

    // 4) Quitar /index.php si viene pegado
    if (str_starts_with($path, "/index.php")) {
        $path = substr($path, strlen("/index.php"));
    }

    // 5) Normalizar
    $path = "/" . trim($path, "/ \n\r\t");




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

        session_start();

        if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "CLIENTE") {
            http_response_code(403);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }


        $titulo = trim($data["titulo"] ?? "");
        $descripcion = trim($data["descripcion"] ?? "");
        $prioridad = $data["prioridad"] ?? "Media";
        $id_cliente = (int)$_SESSION["id"];

        if ($titulo === "" || $descripcion === "") {
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
    // LISTAR TODOS LOS TICKETS (ADMIN)
    // GET /tickets
    // =========================
        if ($method === "GET" && $path === "/tickets") {

            session_start();

    // 🔒 Solo ADMIN
            if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "ADMIN") {
                http_response_code(403);
                echo json_encode([
                "error" => "No autorizado"
            ]);
            exit;
            }

    // Obtener todos los tickets
        $stmt = $pdo->query("
            SELECT 
                t.id_ticket,
                t.titulo,
                t.descripcion,
                t.prioridad,
                e.nombre AS estado,
                DATE(t.fecha_creacion) AS fecha_creacion
            FROM ticket t
            JOIN estado e ON t.id_estado = e.id_estado
        ");

        echo json_encode([
            "ok" => true,
            "tickets" => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
        exit;
    }


    // =========================
    // LISTAR TICKETS DEL CLIENTE
    // GET /tickets/cliente
    // =========================
        if ($method === "GET" && $path === "/tickets/cliente") {

        session_start();

        if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "CLIENTE") {
            http_response_code(403);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }

        $id_cliente = (int)$_SESSION["id"];

        $stmt = $pdo->prepare("
        SELECT 
            t.id_ticket,
            t.titulo,
            t.descripcion,
            t.prioridad,
            e.nombre AS estado,
            DATE(t.fecha_creacion) AS fecha_creacion
        FROM ticket t
        JOIN estado e ON t.id_estado = e.id_estado
        WHERE t.id_cliente = :id
        ORDER BY t.fecha_creacion DESC
        ");

        $stmt->execute([":id" => $id_cliente]);

        echo json_encode([
            "ok" => true,
            "tickets" => $stmt->fetchAll()
        ]);
        exit;
    }

    // =========================
    // LISTAR TICKETS DEL TECNICO
    // GET /tickets/tecnico
    // =========================
        if ($method === "GET" && $path === "/tickets/tecnico") {

            session_start();

        if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "TECNICO") {
            http_response_code(403);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }

        $id_tecnico = (int)$_SESSION["id"];

        $stmt = $pdo->prepare("
            SELECT 
                t.id_ticket,
                t.titulo,
                t.descripcion,
                t.prioridad,
                e.nombre AS estado,
                DATE(t.fecha_creacion) AS fecha_creacion
            FROM ticket t
            JOIN estado e ON t.id_estado = e.id_estado
            WHERE t.id_tecnico = :id
            ORDER BY t.fecha_creacion DESC
        ");

        $stmt->execute([":id" => $id_tecnico]);

        echo json_encode([
            "ok" => true,
            "tickets" => $stmt->fetchAll()
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
    // CREAR CLIENTE
    // POST /clientes
    // =========================
    if ($method === "POST" && $path === "/clientes") {
        require_once __DIR__ . '/src/Controller/ClienteController.php';
        $data = jsonBody();
        $controller = new ClienteController();
        $controller->crear($data);
        exit;
    }

    // =========================
    // CREAR TECNICO (ADMIN)
    // POST /tecnicos
    // =========================
        if ($method === "POST" && $path === "/tecnicos") {

        session_start();

        if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "ADMIN") {
            http_response_code(403);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }

        require_once __DIR__ . '/src/Controller/TecnicoController.php';

        $data = jsonBody();
        $controller = new TecnicoController();
        $controller->crear($data);
        exit;
    }


    // =========================
    // LOGIN
    // POST /login
    // =========================
    if ($method === "POST" && $path === "/login") {
        require_once __DIR__ . '/src/Controller/authController.php';
        $data = jsonBody();
        $controller = new AuthController();
        $controller->login($data);
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

    } catch (ValidationException $e) {

    http_response_code(400);
    echo json_encode([
        "error" => $e->getMessage(),
        "field" => $e->getField()
    ]);

} catch (DuplicateException $e) {

    http_response_code(409);
    echo json_encode([
        "error" => $e->getMessage(),
        "field" => $e->getField()
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error interno del servidor",
        "detail" => $e->getMessage()
    ]);
}