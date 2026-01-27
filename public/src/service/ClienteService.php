<?php


require_once __DIR__ . '/../repository/ClienteRepository.php';
require_once __DIR__ . '/../entity/cliente.php';


class ClienteService {



    public function crearCliente(array $data): void {

     $repo = new ClienteRepository();

    if (!isset($data["nombre"]) || trim($data["nombre"]) === '') {
        throw new ValidationException("El nombre es obligatorio", "nombre");
    }
    if (!isset($data["correo"]) || trim($data["correo"]) === '') {
        throw new ValidationException("El correo es obligatorio", "correo");
    }
    if (filter_var($data["correo"], FILTER_VALIDATE_EMAIL) === false) {
    throw new ValidationException("El formato del correo no es válido", "correo");
    }
    if (!isset($data["usuario"]) || trim($data["usuario"]) === '') {
    throw new ValidationException("El usuario es obligatorio", "usuario");
    }
    if (!isset($data["contrasena"]) || trim($data["contrasena"]) === '') {
        throw new ValidationException("La contraseña es obligatoria", "contrasena");
    }
    $minTamanyo = 6;
    $maxTamanyo = 12;
    $longitud = strlen($data["contrasena"]);

    if ($longitud < $minTamanyo) {
        throw new ValidationException("La contraseña debe tener un minimo de 6 caracteres", "contrasena");
    }
    if ($longitud > $maxTamanyo) {
        throw new ValidationException("La contraseña debe tener como máximo 12 caracteres", "contrasena");
    }
      $contHash =  password_hash($data["contrasena"], PASSWORD_DEFAULT);
    
    if (!isset($data["pregunta_seguridad"]) || trim($data["pregunta_seguridad"]) === '') {
        throw new ValidationException("La pregunta de seguridad es obligatoria", "pregunta_seguridad");
    }
    if (!isset($data["respuesta_seguridad"]) || trim($data["respuesta_seguridad"]) === '') {
        throw new ValidationException("La respuesta de seguridad es obligatoria", "respuesta_seguridad");
    }
     if ($repo->existeCorreo($data["correo"])) {
    throw new DuplicateException("El correo ya existe", "correo");
    }
    if ($repo->existeUsuario($data["usuario"])) {
    throw new DuplicateException("El usuario ya existe", "usuario");
    }


        $cliente = new Cliente(
            
            $data["nombre"],
            $data["correo"],
            $data["usuario"],
            $contHash,
            $data["pregunta_seguridad"],
            $data["respuesta_seguridad"],
            $data["empresa"] ?? null
        );

       
        $repo->crearCliente($cliente);

       
    }
}
