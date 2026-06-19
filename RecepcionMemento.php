<?php
class RecepcionMemento
{
    private array $estado; 

    public function __construct(array $estado)
    {
        $this->estado = $estado;
    }

    public function GetState(): array
    {
        return $this->estado;
    }
}