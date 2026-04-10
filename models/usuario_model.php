<?php
class usuario_model
{
	private $conexion;

	public function __construct($conexion)
	{
		$this->conexion = $conexion;
	}

	public function listar(): array
	{
		$usuarios = array();
		$sql = "
			SELECT u.id, u.nombre, u.correo, u.idrol, r.nombre AS rol
			FROM Usuario u
			INNER JOIN Rol r ON r.id = u.idrol
			ORDER BY u.id ASC
		";
		$res = pg_query($this->conexion, $sql);

		if ($res) {
			while ($fila = pg_fetch_assoc($res)) {
				$usuarios[] = $fila;
			}
		}

		return $usuarios;
	}

	public function insertar(string $nombre, string $correo, string $password, int $idrol): bool
	{
		$nombre = trim($nombre);
		$correo = trim($correo);
		$password = trim($password);
		$idrol = (int)$idrol;

		if ($nombre === '' || $correo === '' || $password === '' || $idrol <= 0) {
			return false;
		}

		$hash = password_hash($password, PASSWORD_DEFAULT);
		$res = pg_query_params(
			$this->conexion,
			"INSERT INTO Usuario (nombre, correo, password, idrol) VALUES ($1, $2, $3, $4)",
			array($nombre, $correo, $hash, $idrol)
		);

		return $res !== false;
	}

	public function actualizar(int $id, string $nombre, string $correo, string $password, int $idrol): bool
	{
		$id = (int)$id;
		$nombre = trim($nombre);
		$correo = trim($correo);
		$password = trim($password);
		$idrol = (int)$idrol;

		if ($id <= 0 || $nombre === '' || $correo === '' || $idrol <= 0) {
			return false;
		}

		if ($password !== '') {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			$res = pg_query_params(
				$this->conexion,
				"UPDATE Usuario SET nombre = $1, correo = $2, password = $3, idrol = $4 WHERE id = $5",
				array($nombre, $correo, $hash, $idrol, $id)
			);
			return $res !== false;
		}

		$res = pg_query_params(
			$this->conexion,
			"UPDATE Usuario SET nombre = $1, correo = $2, idrol = $3 WHERE id = $4",
			array($nombre, $correo, $idrol, $id)
		);

		return $res !== false;
	}

	public function eliminar(int $id): bool
	{
		$id = (int)$id;
		if ($id <= 0) {
			return false;
		}

		$res = pg_query_params(
			$this->conexion,
			"DELETE FROM Usuario WHERE id = $1",
			array($id)
		);

		return $res !== false;
	}

	public function obtenerPorId($idUsuario)
	{
		$sql = "
			SELECT u.id, u.nombre, u.correo, u.idrol, r.nombre AS rol
			FROM Usuario u
			INNER JOIN Rol r ON r.id = u.idrol
			WHERE u.id = $1
			LIMIT 1
		";
		$res = pg_query_params($this->conexion, $sql, array((int)$idUsuario));

		if ($res && pg_num_rows($res) === 1) {
			return pg_fetch_assoc($res);
		}

		return null;
	}

	public function correoExiste($correo, $idExcluir = null)
	{
		if ($idExcluir === null) {
			$res = pg_query_params(
				$this->conexion,
				"SELECT id FROM Usuario WHERE LOWER(correo) = LOWER($1) LIMIT 1",
				array($correo)
			);
		} else {
			$res = pg_query_params(
				$this->conexion,
				"SELECT id FROM Usuario WHERE LOWER(correo) = LOWER($1) AND id <> $2 LIMIT 1",
				array($correo, (int)$idExcluir)
			);
		}

		return $res && pg_num_rows($res) > 0;
	}

	public function actualizarPerfil($idUsuario, $nombre, $correo, $password = '')
	{
		if ($password !== '') {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			return pg_query_params(
				$this->conexion,
				"UPDATE Usuario SET nombre = $1, correo = $2, password = $3 WHERE id = $4",
				array($nombre, $correo, $hash, (int)$idUsuario)
			);
		}

		return pg_query_params(
			$this->conexion,
			"UPDATE Usuario SET nombre = $1, correo = $2 WHERE id = $3",
			array($nombre, $correo, (int)$idUsuario)
		);
	}
}
