<?php
require_once __DIR__ . '/../models/rol_model.php';
require_once __DIR__ . '/../views/rol_view.php';


if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['usuario_id'])) {
	header('Location: index.php');
	exit;
}

$rolSesion = strtolower((string) ($_SESSION['usuario_rol'] ?? ''));
if ($rolSesion !== 'administrador') {
	header('Location: index.php');
	exit;
}

class rol_controller
{
	private rol_model $mRol;
	private rol_view $vRol;
	private array $lista;
	private bool $modoEdicion;
	private int $idSeleccionado;
	private string $nombreSeleccionado;

	public function __construct()
	{
		$this->mRol = new rol_model();
		$this->vRol = new rol_view();
		$this->lista = array();
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
	}

	public function iniciar(): void
	{
		$this->listar();
		$this->sincronizarVista();
		$this->vRol->listar($this->lista);
	}

	public function listar(): void
	{
		$this->lista = $this->mRol->listar();
	}

	public function insertar(string $nombre): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mRol->insertar($nombre);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vRol->insertar($ok ? 'Rol registrado correctamente.' : 'No se pudo registrar el rol.');
	}

	public function actualizar(int $id, string $nombre): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mRol->actualizar($id, $nombre);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vRol->actualizar($ok ? 'Rol actualizado correctamente.' : 'No se pudo actualizar el rol.');
	}

	public function eliminar(int $id): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mRol->eliminar($id);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vRol->eliminar($ok ? 'Rol eliminado correctamente.' : 'No se pudo eliminar el rol.');
	}

	public function editar(int $id, string $nombre): void
	{
		$this->modoEdicion = true;
		$this->idSeleccionado = (int) $id;
		$this->nombreSeleccionado = (string) $nombre;
		$this->iniciar();
	}

	private function sincronizarVista(): void
	{
		$this->vRol->modoEdicion = $this->modoEdicion;
		$this->vRol->idSeleccionado = $this->idSeleccionado;
		$this->vRol->nombreSeleccionado = $this->nombreSeleccionado;
		$this->vRol->lista = $this->lista;
	}

}

$controlador = new rol_controller();
$accionRol = trim((string) ($_POST['accion_rol'] ?? $_GET['accion_rol'] ?? ''));


if ($accionRol === 'volver') {
	header('Location: index.php');
	exit;
}

if ($accionRol === 'insertar') {
	$controlador->insertar(trim((string) ($_POST['nombre_rol'] ?? '')));
	exit;
}

if ($accionRol === 'actualizar') {
	$id = (int) ($_POST['id_rol'] ?? 0);
	$nombre = trim((string) ($_POST['nombre_rol'] ?? ''));
	$controlador->actualizar($id, $nombre);
	exit;
}

if ($accionRol === 'eliminar') {
	$id = (int) ($_POST['id_rol'] ?? 0);
	$controlador->eliminar($id);
	exit;
}

if ($accionRol === 'editar') {
	$id = (int) ($_POST['id_rol'] ?? 0);
	$nombre = trim((string) ($_POST['nombre_rol'] ?? ''));
	$controlador->editar($id, $nombre);
	exit;
}

$controlador->iniciar();

