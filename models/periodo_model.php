<?php
require_once __DIR__ . '/../db/PostgresHelper.php';

class periodo_model
{
	private PostgresHelper $db;

	public function __construct()
	{
		$this->db = new PostgresHelper();
	}

	public function listar(): array
	{
		$sql = 'SELECT id, gestion, semestre, estado FROM Periodo ORDER BY id DESC';
		return $this->db->ejecutarQuery($sql);
	}

	public function insertar(string $gestion, string $semestre, int $estado): bool
	{
		$gestion = $this->escaparTexto($gestion);
		$semestre = $this->escaparTexto($semestre);
		$estado = (int)$estado;

		if ($gestion === '' || $semestre === '' || ($estado !== 0 && $estado !== 1)) {
			return false;
		}

		$sql = "INSERT INTO Periodo (gestion, semestre, estado) VALUES ('{$gestion}', '{$semestre}', {$estado})";
		return $this->db->ejecutarDML($sql);
	}

	public function actualizar(int $id, string $gestion, string $semestre, int $estado): bool
	{
		$gestion = $this->escaparTexto($gestion);
		$semestre = $this->escaparTexto($semestre);
		$estado = (int)$estado;

		if ($id <= 0 || $gestion === '' || $semestre === '' || ($estado !== 0 && $estado !== 1)) {
			return false;
		}

		$sql = "UPDATE Periodo SET gestion = '{$gestion}', semestre = '{$semestre}', estado = {$estado} WHERE id = {$id}";
		return $this->db->ejecutarDML($sql);
	}

	public function eliminar(int $id): bool
	{
		if ($id <= 0) {
			return false;
		}

		$sql = "DELETE FROM Periodo WHERE id = {$id}";
		return $this->db->ejecutarDML($sql);
	}

	private function escaparTexto(string $texto): string
	{
		$texto = trim($texto);
		return str_replace("'", "''", $texto);
	}
}
