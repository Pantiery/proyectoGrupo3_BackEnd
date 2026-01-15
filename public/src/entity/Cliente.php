<?php

class Cliente {

    private $id_cliente;
    private $nombre;
    private $correo;
    private $empresa;
    private $usuario;
    private $contrasena;
    private $tipo;
    private $pregunta_seguridad;
    private $respuesta_seguridad;

    public function __construct(
        $nombre,
        $correo,
        $usuario,
        $contrasena,
        $pregunta_seguridad,
        $respuesta_seguridad,
        $empresa = null,
        $tipo = 'CLIENTE'
    ) {
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->usuario = $usuario;
        $this->contrasena = $contrasena;
        $this->pregunta_seguridad = $pregunta_seguridad;
        $this->respuesta_seguridad = $respuesta_seguridad;
        $this->empresa = $empresa;
        $this->tipo = $tipo;
    }
    public function getIdCliente() {
        return $this->id_cliente;
    }
    public function getNombre() {
        return $this->nombre;
    }   
    public function getCorreo() {
        return $this->correo;
    }
    public function getEmpresa() {
        return $this->empresa;
    }
    public function getUsuario() {
        return $this->usuario;
    }
    public function getContrasena() {
        return $this->contrasena;
    }
    public function getTipo() {
        return $this->tipo;
    }
    public function getPreguntaSeguridad() {
        return $this->pregunta_seguridad;
    }
    public function getRespuestaSeguridad() {
        return $this->respuesta_seguridad;
    }
    

}
