<?php
require_once __DIR__ . '/EstrategiaOrdenBloque.php';

class OrdenCronologicoAsc implements EstrategiaOrdenBloque
{
	public function ordenar(array $datos): array
	{
		usort($datos, function (array $a, array $b): int {
			$horaA = (string)($a['hora_inicio'] ?? '');
			$horaB = (string)($b['hora_inicio'] ?? '');

			return strcmp($horaA, $horaB);
		});

		return $datos;
	}
}
