<?php
require_once __DIR__ . '/../db/PostgresHelper.php';

class detalle_model
{
	private PostgresHelper $db;

	public function __construct()
	{
		$this->db = new PostgresHelper();
	}

	public function guardarLote(int $idCita, array $arrayDetalles): bool
	{
		$idCita = (int)$idCita;
		if ($idCita <= 0 || count($arrayDetalles) === 0) {
			return false;
		}

		foreach ($arrayDetalles as $fila) {
			$idDocumento = (int)($fila['iddocumento'] ?? 0);
			$entrega = (bool)($fila['entrega'] ?? false);
			$observacion = $this->escaparTexto((string)($fila['observacion'] ?? ''));

			if ($idDocumento <= 0) {
				return false;
			}

			$valorEntrega = $entrega ? 'TRUE' : 'FALSE';
			$sql = "INSERT INTO Detalle (idcita, iddocumento, entrega, observacion) VALUES ({$idCita}, {$idDocumento}, {$valorEntrega}, '{$observacion}')";

			$ok = $this->db->ejecutarDML($sql);
			if (!$ok) {
				return false;
			}
		}

		return true;
	}

	private function escaparTexto(string $texto): string
	{
		$texto = trim($texto);
		return str_replace("'", "''", $texto);
	}
}
