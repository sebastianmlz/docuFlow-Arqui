<?php
require_once __DIR__ . '/Memento.php';

class Originator
{
    private array $estado;

    public function __construct()
    {
        $this->estado = array();
    }

    // Actualizar el estado del Originator con nuevos datos
    public function SetState(array $datos): void
    {
        $this->estado = array(
            'id_rol' => (int) ($datos['id_rol'] ?? 0),
            'nombre_rol' => (string) ($datos['nombre_rol'] ?? ''),
        );
    }

    public function GetState(): array
    {
        return $this->estado;
    }

    public function CreateMemento(): Memento
    {
        return new Memento($this->estado);
    }

    public function RestoreMemento(Memento $memento): void
    {
        $this->estado = $memento->GetState();
    }
}