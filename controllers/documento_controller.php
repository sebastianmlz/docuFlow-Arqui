<?php
require_once __DIR__ . '/../models/documento_model.php';
require_once __DIR__ . '/../views/documento_view.php';

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

class documento_controller
{
	private documento_model $mDocumento;
	private documento_view $vDocumento;
	private array $lista;
	private bool $modoEdicion;
	private int $idSel;
	private string $nombreSel;

	public function __construct()
	{
		$this->mDocumento = new documento_model();
		$this->vDocumento = new documento_view();
		$this->lista = array();
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->nombreSel = '';
	}

	public function iniciar(): void
	{
		$this->listar();
		$this->sincronizarVista();
		$this->vDocumento->listar($this->lista);
	}

	public function listar(): void
	{
		$this->lista = $this->mDocumento->listar();
	}

	public function insertar(string $nombre): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mDocumento->insertar($nombre);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->nombreSel = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vDocumento->insertar($ok ? 'Documento registrado correctamente.' : 'No se pudo registrar el documento.');
	}

	public function actualizar(int $id, string $nombre): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mDocumento->actualizar($id, $nombre);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->nombreSel = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vDocumento->actualizar($ok ? 'Documento actualizado correctamente.' : 'No se pudo actualizar el documento.');
	}

	public function eliminar(int $id): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mDocumento->eliminar($id);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->nombreSel = '';
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vDocumento->eliminar($ok ? 'Documento eliminado correctamente.' : 'No se pudo eliminar el documento.');
	}

	public function editar(int $id, string $nombre): void
	{
		$this->modoEdicion = true;
		$this->idSel = (int)$id;
		$this->nombreSel = (string)$nombre;
		$this->iniciar();
	}

	private function sincronizarVista(): void
	{
		$this->vDocumento->modoEdicion = $this->modoEdicion;
		$this->vDocumento->idSel = $this->idSel;
		$this->vDocumento->nombreSel = $this->nombreSel;
		$this->vDocumento->lista = $this->lista;
	}
}

$controlador = new documento_controller();
$accionDocumento = trim((string)($_POST['accion_documento'] ?? $_GET['accion_documento'] ?? ''));

if ($accionDocumento === 'volver') {
	header('Location: index.php');
	exit;
}

if ($accionDocumento === 'insertar') {
	$controlador->insertar(trim((string)($_POST['nombre_documento'] ?? '')));
	exit;
}

if ($accionDocumento === 'actualizar') {
	$id = (int)($_POST['id_documento'] ?? 0);
	$nombre = trim((string)($_POST['nombre_documento'] ?? ''));
	$controlador->actualizar($id, $nombre);
	exit;
}

if ($accionDocumento === 'eliminar') {
	$id = (int)($_POST['id_documento'] ?? 0);
	$controlador->eliminar($id);
	exit;
}

if ($accionDocumento === 'editar') {
	$id = (int)($_POST['id_documento'] ?? 0);
	$nombre = trim((string)($_POST['nombre_documento'] ?? ''));
	$controlador->editar($id, $nombre);
	exit;
}

$controlador->iniciar();
