<?php


class ValidationException extends DomainException
{

private string $field;

    public function __construct( string $message, string $field) {
        parent::__construct($message);
        $this->field = $field;
    }

    public function getField() {
        return $this->field;
    }
}