<?php
require_once __DIR__ . '/../models/modulo_model.php';
require_once __DIR__ . '/../views/modulo_view.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['usuario_id'])) {
	header('Location: index.php');
	exit;
}

$rolSesion = strtolower((string)($_SESSION['usuario_rol'] ?? ''));
if ($rolSesion !== 'administrador') {
	header('Location: index.php');
	exit;
}

class modulo_controller
{
	private modulo_model $mModulo;
	private modulo_view $vModulo;
	private array $lista;
	private bool $modoEdicion;
	private int $idSeleccionado;
	private string $nombreSeleccionado;

	public function __construct()
	{
		$this->mModulo = new modulo_model();
		$this->vModulo = new modulo_view();
		$this->lista = array();
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
	}

	public function iniciar(): void
	{
		$this->listar();
		$this->sincronizarVista();
		$this->vModulo->listar($this->lista);
	}

	public function listar(): void
	{
		$this->lista = $this->mModulo->listar();
	}

	public function insertar(string $nombre): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mModulo->insertar($nombre);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vModulo->insertar($ok ? 'Modulo registrado correctamente.' : 'No se pudo registrar el modulo.');
	}

	public function actualizar(int $id, string $nombre): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mModulo->actualizar($id, $nombre);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vModulo->actualizar($ok ? 'Modulo actualizado correctamente.' : 'No se pudo actualizar el modulo.');
	}

	public function eliminar(int $id): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mModulo->eliminar($id);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vModulo->eliminar($ok ? 'Modulo eliminado correctamente.' : 'No se pudo eliminar el modulo.');
	}

	public function editar(int $id, string $nombre): void
	{
		$this->modoEdicion = true;
		$this->idSeleccionado = (int)$id;
		$this->nombreSeleccionado = (string)$nombre;
		$this->iniciar();
	}

	private function sincronizarVista(): void
	{
		$this->vModulo->modoEdicion = $this->modoEdicion;
		$this->vModulo->idSeleccionado = $this->idSeleccionado;
		$this->vModulo->nombreSeleccionado = $this->nombreSeleccionado;
		$this->vModulo->lista = $this->lista;
	}
}

$controlador = new modulo_controller();
$accionModulo = trim((string)($_POST['accion_modulo'] ?? $_GET['accion_modulo'] ?? ''));

if ($accionModulo === 'volver') {
	header('Location: index.php');
	exit;
}

if ($accionModulo === 'insertar') {
	$controlador->insertar(trim((string)($_POST['nombre_modulo'] ?? '')));
	exit;
}

if ($accionModulo === 'actualizar') {
	$id = (int)($_POST['id_modulo'] ?? 0);
	$nombre = trim((string)($_POST['nombre_modulo'] ?? ''));
	$controlador->actualizar($id, $nombre);
	exit;
}

if ($accionModulo === 'eliminar') {
	$id = (int)($_POST['id_modulo'] ?? 0);
	$controlador->eliminar($id);
	exit;
}

if ($accionModulo === 'editar') {
	$id = (int)($_POST['id_modulo'] ?? 0);
	$nombre = trim((string)($_POST['nombre_modulo'] ?? ''));
	$controlador->editar($id, $nombre);
	exit;
}

$controlador->iniciar();
