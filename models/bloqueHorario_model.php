<?php
require_once __DIR__ . '/../db/PostgresHelper.php';

class bloqueHorario_model
{
	private PostgresHelper $db;

	public function __construct()
	{
		$this->db = new PostgresHelper();
	}

	public function listar(): array
	{
		$sql = 'SELECT b.id, b.hora_inicio, b.hora_fin, b.cantidad_cupos, b.idperiodo, p.gestion, p.semestre FROM BloqueHorario b INNER JOIN Periodo p ON b.idperiodo = p.id ORDER BY b.id ASC';
		return $this->db->ejecutarQuery($sql);
	}

	public function insertar(string $ini, string $fin, int $cupos, int $idPer): bool
	{
		$ini = $this->escaparTexto($ini);
		$fin = $this->escaparTexto($fin);
		$cupos = (int)$cupos;
		$idPer = (int)$idPer;

		if ($ini === '' || $fin === '' || $cupos <= 0 || $idPer <= 0) {
			return false;
		}

		$sql = "INSERT INTO BloqueHorario (hora_inicio, hora_fin, cantidad_cupos, idperiodo) VALUES ('{$ini}', '{$fin}', {$cupos}, {$idPer})";
		return $this->db->ejecutarDML($sql);
	}

	public function actualizar(int $id, string $ini, string $fin, int $cupos, int $idPer): bool
	{
		$ini = $this->escaparTexto($ini);
		$fin = $this->escaparTexto($fin);
		$cupos = (int)$cupos;
		$idPer = (int)$idPer;

		if ($id <= 0 || $ini === '' || $fin === '' || $cupos <= 0 || $idPer <= 0) {
			return false;
		}

		$sql = "UPDATE BloqueHorario SET hora_inicio = '{$ini}', hora_fin = '{$fin}', cantidad_cupos = {$cupos}, idperiodo = {$idPer} WHERE id = {$id}";
		return $this->db->ejecutarDML($sql);
	}

	public function eliminar(int $id): bool
	{
		if ($id <= 0) {
			return false;
		}

		$sql = "DELETE FROM BloqueHorario WHERE id = {$id}";
		return $this->db->ejecutarDML($sql);
	}

	private function escaparTexto(string $texto): string
	{
		$texto = trim($texto);
		return str_replace("'", "''", $texto);
	}
}
