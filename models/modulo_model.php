<?php
require_once __DIR__ . '/../db/PostgresHelper.php';

class modulo_model
{
	private PostgresHelper $db;

	public function __construct()
	{
		$this->db = new PostgresHelper();
	}

	public function listar(): array
	{
		$sql = 'SELECT id, nombre FROM Modulo ORDER BY id ASC';
		return $this->db->ejecutarQuery($sql);
	}

	public function insertar(string $nombre): bool
	{
		$nombre = $this->escaparTexto($nombre);
		if ($nombre === '') {
			return false;
		}

		$sql = "INSERT INTO Modulo (nombre) VALUES ('{$nombre}')";
		return $this->db->ejecutarDML($sql);
	}

	public function actualizar(int $id, string $nombre): bool
	{
		$nombre = $this->escaparTexto($nombre);

		if ($id <= 0 || $nombre === '') {
			return false;
		}

		$sql = "UPDATE Modulo SET nombre = '{$nombre}' WHERE id = {$id}";
		return $this->db->ejecutarDML($sql);
	}

	public function eliminar(int $id): bool
	{
		if ($id <= 0) {
			return false;
		}

		$sql = "DELETE FROM Modulo WHERE id = {$id}";
		return $this->db->ejecutarDML($sql);
	}

	private function escaparTexto(string $texto): string
	{
		$texto = trim($texto);
		return str_replace("'", "''", $texto);
	}
}
