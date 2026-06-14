<?php
require_once __DIR__ . '/EstrategiaFormatoBloque.php';

class ContextoFormatoBloque
{
	private EstrategiaFormatoBloque $strategy;

	public function __construct(EstrategiaFormatoBloque $strategy)
	{
		$this->strategy = $strategy;
	}

	public function setStrategy(EstrategiaFormatoBloque $strategy): void
	{
		$this->strategy = $strategy;
	}

	public function doSomething(array $data): string
	{
		return $this->strategy->execute($data);
	}
}
