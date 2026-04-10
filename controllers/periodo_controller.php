<?php
require_once __DIR__ . '/../models/periodo_model.php';
require_once __DIR__ . '/../views/periodo_view.php';

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

class periodo_controller
{
	private periodo_model $mPeriodo;
	private periodo_view $vPeriodo;
	private array $lista;
	private bool $modoEdicion;
	private int $idSeleccionado;
	private string $gestionSeleccionada;
	private string $semestreSeleccionado;
	private int $estadoSeleccionado;

	public function __construct()
	{
		$this->mPeriodo = new periodo_model();
		$this->vPeriodo = new periodo_view();
		$this->lista = array();
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->gestionSeleccionada = '';
		$this->semestreSeleccionado = '';
		$this->estadoSeleccionado = 1;
	}

	public function iniciar(): void
	{
		$this->listar();
		$this->sincronizarVista();
		$this->vPeriodo->listar($this->lista);
	}

	public function listar(): void
	{
		$this->lista = $this->mPeriodo->listar();
	}

	public function insertar(string $gestion, string $semestre, int $estado): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mPeriodo->insertar($gestion, $semestre, $estado);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->gestionSeleccionada = '';
		$this->semestreSeleccionado = '';
		$this->estadoSeleccionado = 1;
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vPeriodo->insertar($ok ? 'Periodo registrado correctamente.' : 'No se pudo registrar el periodo.');
	}

	public function actualizar(int $id, string $gestion, string $semestre, int $estado): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mPeriodo->actualizar($id, $gestion, $semestre, $estado);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->gestionSeleccionada = '';
		$this->semestreSeleccionado = '';
		$this->estadoSeleccionado = 1;
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vPeriodo->actualizar($ok ? 'Periodo actualizado correctamente.' : 'No se pudo actualizar el periodo.');
	}

	public function eliminar(int $id): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mPeriodo->eliminar($id);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->gestionSeleccionada = '';
		$this->semestreSeleccionado = '';
		$this->estadoSeleccionado = 1;
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vPeriodo->eliminar($ok ? 'Periodo eliminado correctamente.' : 'No se pudo eliminar el periodo.');
	}

	public function editar(int $id, string $gestion, string $semestre, int $estado): void
	{
		$this->modoEdicion = true;
		$this->idSeleccionado = (int)$id;
		$this->gestionSeleccionada = (string)$gestion;
		$this->semestreSeleccionado = (string)$semestre;
		$this->estadoSeleccionado = (int)$estado === 0 ? 0 : 1;
		$this->iniciar();
	}

	private function sincronizarVista(): void
	{
		$this->vPeriodo->sincronizar(
			$this->modoEdicion,
			$this->idSeleccionado,
			$this->gestionSeleccionada,
			$this->semestreSeleccionado,
			$this->estadoSeleccionado,
			$this->lista
		);
	}
}

$controlador = new periodo_controller();
$accionPeriodo = trim((string)($_POST['accion_periodo'] ?? $_GET['accion_periodo'] ?? ''));

if ($accionPeriodo === 'volver') {
	header('Location: index.php');
	exit;
}

if ($accionPeriodo === 'insertar') {
	$gestion = trim((string)($_POST['gestion_periodo'] ?? ''));
	$semestre = trim((string)($_POST['semestre_periodo'] ?? ''));
	$estado = (int)($_POST['estado_periodo'] ?? 1);
	$controlador->insertar($gestion, $semestre, $estado);
	exit;
}

if ($accionPeriodo === 'actualizar') {
	$id = (int)($_POST['id_periodo'] ?? 0);
	$gestion = trim((string)($_POST['gestion_periodo'] ?? ''));
	$semestre = trim((string)($_POST['semestre_periodo'] ?? ''));
	$estado = (int)($_POST['estado_periodo'] ?? 1);
	$controlador->actualizar($id, $gestion, $semestre, $estado);
	exit;
}

if ($accionPeriodo === 'eliminar') {
	$id = (int)($_POST['id_periodo'] ?? 0);
	$controlador->eliminar($id);
	exit;
}

if ($accionPeriodo === 'editar') {
	$id = (int)($_POST['id_periodo'] ?? 0);
	$gestion = trim((string)($_POST['gestion_periodo'] ?? ''));
	$semestre = trim((string)($_POST['semestre_periodo'] ?? ''));
	$estado = (int)($_POST['estado_periodo'] ?? 1);
	$controlador->editar($id, $gestion, $semestre, $estado);
	exit;
}

$controlador->iniciar();
