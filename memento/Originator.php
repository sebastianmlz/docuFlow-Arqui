<?php
require_once __DIR__ . '/Memento.php';

class Originator
{
    private array $estado;

    public function __construct()
    {
        $this->estado = array();
    }

    public function SetState(array $datos): void
    {
        $this->estado = $datos;
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
