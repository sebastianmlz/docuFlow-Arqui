<?php
require_once __DIR__ . '/RecepcionMemento.php';

class RolOriginator
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

    public function CreateMemento(): RecepcionMemento
    {
        return new RecepcionMemento($this->estado);
    }

    public function RestoreMemento(RecepcionMemento $memento): void
    {
        $this->estado = $memento->GetState();
    }
}