<?php
require_once __DIR__ . '/EstrategiaOrdenBloque.php';

class ContextoOrdenBloque
{
	private EstrategiaOrdenBloque $strategy;

	public function __construct(EstrategiaOrdenBloque $strategy)
	{
		$this->strategy = $strategy;
	}

	public function setStrategy(EstrategiaOrdenBloque $strategy): void
	{
		$this->strategy = $strategy;
	}

	public function ejecutarOrden(array $datos): array
	{
		return $this->strategy->ordenar($datos);
	}
}
