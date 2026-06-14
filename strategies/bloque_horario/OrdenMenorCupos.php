<?php
require_once __DIR__ . '/EstrategiaOrdenBloque.php';

class OrdenMenorCupos implements EstrategiaOrdenBloque
{
	public function ordenar(array $datos): array
	{
		usort($datos, function (array $a, array $b): int {
			$cuposA = (int)($a['cantidad_cupos'] ?? 0);
			$cuposB = (int)($b['cantidad_cupos'] ?? 0);

			return $cuposA <=> $cuposB;
		});

		return $datos;
	}
}
