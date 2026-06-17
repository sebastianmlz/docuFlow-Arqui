<?php
require_once __DIR__ . '/EstrategiaOrdenBloque.php';

class ContextoOrdenBloque
{
	private ?EstrategiaOrdenBloque $strategy = null;

	public function __construct(string $tipoInicial)
	{
		// El constructor recibe el texto inicial y usa el traductor interno
		$this->cambiarEstrategiaPorTexto($tipoInicial);
	}

	// 1. CUMPLE AL 100% CON LA FIRMA DEL DOCENTE: Recibe el objeto abstracto general
	public function setStrategy(EstrategiaOrdenBloque $strategy): void
	{
		$this->strategy = $strategy;
	}

	// 2. MÉTODO AUXILIAR: Centraliza los "new" aquí para no ensuciar el controlador
	// ni romper la firma orientada a objetos de setStrategy
	public function cambiarEstrategiaPorTexto(string $tipo): void
	{
		if ($tipo === 'mayor_cupos') {
			require_once __DIR__ . '/OrdenMayorCupos.php';
			$this->setStrategy(new OrdenMayorCupos()); // Llama al setStrategy OO
		} elseif ($tipo === 'menor_cupos') {
			require_once __DIR__ . '/OrdenMenorCupos.php';
			$this->setStrategy(new OrdenMenorCupos());
		} else {
			require_once __DIR__ . '/OrdenCronologicoAsc.php';
			$this->setStrategy(new OrdenCronologicoAsc());
		}
	}

	public function ejecutarOrden(array $datos): array
	{
		if ($this->strategy === null) {
			return $datos;
		}
		// Ejecución polimórfica pura
		return $this->strategy->ordenar($datos);
	}
}