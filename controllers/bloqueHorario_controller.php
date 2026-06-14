<?php
require_once __DIR__ . '/../models/bloqueHorario_model.php';
require_once __DIR__ . '/../models/periodo_model.php';
require_once __DIR__ . '/../views/bloqueHorario_view.php';
require_once __DIR__ . '/../strategies/bloque_horario/ContextoFormatoBloque.php';
require_once __DIR__ . '/../strategies/bloque_horario/EstrategiaTablaBloque.php';
require_once __DIR__ . '/../strategies/bloque_horario/EstrategiaTarjetasBloque.php';

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

class bloqueHorario_controller
{
	private bloqueHorario_model $mBloque;
	private periodo_model $mPeriodo;
	private bloqueHorario_view $vBloque;
	private ContextoFormatoBloque $contexto;
	private array $lista;
	private array $listaPeriodos;
	private string $htmlContenidoListado;
	private string $accionEstrategia;
	private bool $modoEdicion;
	private int $idSel;
	private string $iniSel;
	private string $finSel;
	private int $cuposSel;
	private int $idPerSel;

	public function __construct()
	{
		$this->mBloque = new bloqueHorario_model();
		$this->mPeriodo = new periodo_model();
		$this->vBloque = new bloqueHorario_view();
		$this->lista = array();
		$this->listaPeriodos = array();
		$this->htmlContenidoListado = '';
		$this->contexto = new ContextoFormatoBloque(new EstrategiaTablaBloque());
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->iniSel = '';
		$this->finSel = '';
		$this->cuposSel = 1;
		$this->idPerSel = 0;
		$this->configurarEstrategia();
	}

	public function iniciar(): void
	{
		$this->listar();
		$this->sincronizarVista();
		$this->vBloque->listar($this->lista);
	}

	public function listar(): void
	{
		$this->lista = $this->mBloque->listar();
		$this->listaPeriodos = $this->mPeriodo->listar();
	}

	public function insertar(string $ini, string $fin, int $cupos, int $idPer): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mBloque->insertar($ini, $fin, $cupos, $idPer);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->iniSel = '';
		$this->finSel = '';
		$this->cuposSel = 1;
		$this->idPerSel = 0;
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vBloque->insertar($ok ? 'Bloque de horario registrado correctamente.' : 'No se pudo registrar el bloque de horario.');
	}

	public function actualizar(int $id, string $ini, string $fin, int $cupos, int $idPer): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mBloque->actualizar($id, $ini, $fin, $cupos, $idPer);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->iniSel = '';
		$this->finSel = '';
		$this->cuposSel = 1;
		$this->idPerSel = 0;
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vBloque->actualizar($ok ? 'Bloque de horario actualizado correctamente.' : 'No se pudo actualizar el bloque de horario.');
	}

	public function eliminar(int $id): void
	{
		// Paso 1 (Modelo)
		$ok = $this->mBloque->eliminar($id);

		// Paso 2 (Logica)
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->iniSel = '';
		$this->finSel = '';
		$this->cuposSel = 1;
		$this->idPerSel = 0;
		$this->listar();

		// Paso 3 (Vista)
		$this->sincronizarVista();
		$this->vBloque->eliminar($ok ? 'Bloque de horario eliminado correctamente.' : 'No se pudo eliminar el bloque de horario.');
	}

	public function editar(int $id, string $ini, string $fin, int $cupos, int $idPer): void
	{
		$this->modoEdicion = true;
		$this->idSel = (int)$id;
		$this->iniSel = (string)$ini;
		$this->finSel = (string)$fin;
		$this->cuposSel = (int)$cupos;
		$this->idPerSel = (int)$idPer;
		$this->iniciar();
	}

	private function sincronizarVista(): void
	{
		$this->htmlContenidoListado = $this->contexto->doSomething($this->lista);

		$this->vBloque->sincronizar(
			$this->modoEdicion,
			$this->idSel,
			$this->iniSel,
			$this->finSel,
			$this->cuposSel,
			$this->idPerSel,
			$this->lista,
			$this->listaPeriodos,
			$this->htmlContenidoListado,
			$this->accionEstrategia
		);
	}

	private function configurarEstrategia(): void
	{
		$accion = trim((string)($_POST['accion_estrategia'] ?? $_GET['accion_estrategia'] ?? ($_SESSION['accion_estrategia_bloque'] ?? 'ver_tabla')));

		if ($accion !== 'ver_tarjetas') {
			$accion = 'ver_tabla';
		}

		$_SESSION['accion_estrategia_bloque'] = $accion;
		$this->accionEstrategia = $accion;
		$estrategia = $accion === 'ver_tarjetas'
			? new EstrategiaTarjetasBloque()
			: new EstrategiaTablaBloque();
		$this->contexto->setStrategy($estrategia);
	}
}

$controlador = new bloqueHorario_controller();
$accionBloque = trim((string)($_POST['accion_bloque'] ?? $_GET['accion_bloque'] ?? ''));

if ($accionBloque === 'volver') {
	header('Location: index.php');
	exit;
}

if ($accionBloque === 'insertar') {
	$ini = trim((string)($_POST['hora_inicio'] ?? ''));
	$fin = trim((string)($_POST['hora_fin'] ?? ''));
	$cupos = (int)($_POST['cantidad_cupos'] ?? 0);
	$idPer = (int)($_POST['id_periodo'] ?? 0);
	$controlador->insertar($ini, $fin, $cupos, $idPer);
	exit;
}

if ($accionBloque === 'actualizar') {
	$id = (int)($_POST['id_bloque'] ?? 0);
	$ini = trim((string)($_POST['hora_inicio'] ?? ''));
	$fin = trim((string)($_POST['hora_fin'] ?? ''));
	$cupos = (int)($_POST['cantidad_cupos'] ?? 0);
	$idPer = (int)($_POST['id_periodo'] ?? 0);
	$controlador->actualizar($id, $ini, $fin, $cupos, $idPer);
	exit;
}

if ($accionBloque === 'eliminar') {
	$id = (int)($_POST['id_bloque'] ?? 0);
	$controlador->eliminar($id);
	exit;
}

if ($accionBloque === 'editar') {
	$id = (int)($_POST['id_bloque'] ?? 0);
	$ini = trim((string)($_POST['hora_inicio'] ?? ''));
	$fin = trim((string)($_POST['hora_fin'] ?? ''));
	$cupos = (int)($_POST['cantidad_cupos'] ?? 0);
	$idPer = (int)($_POST['id_periodo'] ?? 0);
	$controlador->editar($id, $ini, $fin, $cupos, $idPer);
	exit;
}

$controlador->iniciar();
