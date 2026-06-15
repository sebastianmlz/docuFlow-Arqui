<?php
require_once __DIR__ . '/RecepcionMemento.php';

class RecepcionOriginator
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
            'id_cita' => (int)($datos['id_cita'] ?? 0),
            'entrega' => (array)($datos['entrega'] ?? array()),
            'obs'     => (array)($datos['obs'] ?? array()),
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
