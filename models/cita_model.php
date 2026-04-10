<?php
require_once __DIR__ . '/../db/PostgresHelper.php';

class cita_model
{
	private PostgresHelper $db;

	public function __construct()
	{
		$this->db = new PostgresHelper();
	}

	public function insertar(int $idPostulante, int $idBloque, int $idModulo): int
	{
		$idPostulante = (int)$idPostulante;
		$idBloque = (int)$idBloque;
		$idModulo = (int)$idModulo;

		if ($idPostulante <= 0 || $idBloque <= 0 || $idModulo <= 0) {
			return 0;
		}

		$sql = "INSERT INTO Cita (fecha, estado, idpostulante, idbloquehorario, idmodulo) VALUES (CURRENT_DATE, 0, {$idPostulante}, {$idBloque}, {$idModulo}) RETURNING id";
		$filas = $this->db->ejecutarQuery($sql);

		if (count($filas) === 0) {
			return 0;
		}

		return (int)($filas[0]['id'] ?? 0);
	}

	public function obtenerActiva(int $idPostulante): ?array
	{
		$idPostulante = (int)$idPostulante;
		if ($idPostulante <= 0) {
			return null;
		}

		$sql = "SELECT c.id, c.fecha, c.estado, c.idpostulante, c.idbloquehorario, c.idmodulo, m.nombre AS modulo, b.hora_inicio, b.hora_fin FROM Cita c INNER JOIN Modulo m ON c.idmodulo = m.id INNER JOIN BloqueHorario b ON c.idbloquehorario = b.id WHERE c.idpostulante = {$idPostulante} AND c.estado = 0 ORDER BY c.fecha DESC, c.id DESC LIMIT 1";
		$filas = $this->db->ejecutarQuery($sql);

		if (count($filas) === 0) {
			return null;
		}

		return $filas[0];
	}

	public function listarHoy(): array
	{
		$sql = "SELECT c.id, c.fecha, c.estado, c.idpostulante, c.idbloquehorario, c.idmodulo, u.nombre AS postulante, u.correo AS correo_postulante, m.nombre AS modulo, b.hora_inicio, b.hora_fin FROM Cita c INNER JOIN Usuario u ON c.idpostulante = u.id INNER JOIN Modulo m ON c.idmodulo = m.id INNER JOIN BloqueHorario b ON c.idbloquehorario = b.id WHERE c.fecha = CURRENT_DATE ORDER BY b.hora_inicio ASC, c.id ASC";
		return $this->db->ejecutarQuery($sql);
	}

	public function actualizarEstado(int $idCita, int $estado): bool
	{
		$idCita = (int)$idCita;
		$estado = (int)$estado;

		if ($idCita <= 0) {
			return false;
		}

		$sql = "UPDATE Cita SET estado = {$estado} WHERE id = {$idCita}";
		return $this->db->ejecutarDML($sql);
	}
}
