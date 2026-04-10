<?php
require_once __DIR__ . '/db.php';

class PostgresHelper
{
	private $conexion;

	public function __construct()
	{
		$this->conexion = Conectar::conexion();
	}

	public function ejecutarQuery(string $sql): array
	{
		$resultado = pg_query($this->conexion, $sql);
		if ($resultado === false) {
			return array();
		}

		$registros = array();
		while ($fila = pg_fetch_assoc($resultado)) {
			$registros[] = $fila;
		}

		return $registros;
	}

	public function ejecutarDML(string $sql): bool
	{
		$resultado = pg_query($this->conexion, $sql);
		return $resultado !== false;
	}
}

