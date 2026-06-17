<?php
require_once __DIR__ . '/EstrategiaOrdenBloque.php';

class ContextoOrdenBloque
{
	private ?EstrategiaOrdenBloque $strategy = null;

	public function __construct(string $tipoInicial)
	{
		$this->establecerEstrategiaPorTexto($tipoInicial);
	}

	public function setStrategy(EstrategiaOrdenBloque $strategy): void
	{
		$this->strategy = $strategy;
	}

	public function establecerEstrategiaPorTexto(string $tipo): void
	{
		if ($tipo === 'mayor_cupos') {
			require_once __DIR__ . '/OrdenMayorCupos.php';
			$this->setStrategy(new OrdenMayorCupos());
		} elseif ($tipo === 'menor_cupos') {
			require_once __DIR__ . '/OrdenMenorCupos.php';
			$this->setStrategy(new OrdenMenorCupos());
		} else {
			require_once __DIR__ . '/OrdenCronologicoAsc.php';
			$this->setStrategy(new OrdenCronologicoAsc());
		}
	}

	public function ordenar(array $datos): array
	{
		if ($this->strategy === null) {
			return $datos;
		}
		return $this->strategy->ordenar($datos);
	}
}