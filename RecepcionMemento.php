<?php

class RecepcionMemento
{
	private array $estadoFormulario;

	public function __construct(array $estadoFormulario)
	{
		$this->estadoFormulario = $estadoFormulario;
	}

	public function obtenerEstado(): array
	{
		return $this->estadoFormulario;
	}
}
