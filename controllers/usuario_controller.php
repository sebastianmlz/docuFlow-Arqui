<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../models/usuario_model.php';
require_once __DIR__ . '/../models/rol_model.php';
require_once __DIR__ . '/../views/usuario_view.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['usuario_id'])) {
	header('Location: ../index.php');
	exit;
}

class usuario_controller
{
	private usuario_model $mUsuario;
	private rol_model $mRol;
	private usuario_view $vUsuario;

	private bool $mostrarDashboard;
	private bool $mostrarCrudUsuarios;
	private bool $mostrarEditarPerfil;

	private array $usuarioActual;
	private array $lista;
	private array $listaRoles;

	private bool $modoEdicion;
	private int $idSeleccionado;
	private string $nombreSeleccionado;
	private string $correoSeleccionado;
	private int $idRolSeleccionado;

	public function __construct()
	{
		$conexion = Conectar::conexion();
		$this->mUsuario = new usuario_model($conexion);
		$this->mRol = new rol_model();
		$this->vUsuario = new usuario_view();

		$this->mostrarDashboard = true;
		$this->mostrarCrudUsuarios = false;
		$this->mostrarEditarPerfil = false;

		$this->usuarioActual = array();
		$this->lista = array();
		$this->listaRoles = array();

		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->correoSeleccionado = '';
		$this->idRolSeleccionado = 0;

		$this->cargarUsuarioActual();
	}

	public function iniciar(): void
	{
		$this->mostrarDashboard = true;
		$this->mostrarCrudUsuarios = false;
		$this->mostrarEditarPerfil = false;

		$this->sincronizarVista();
		$this->vUsuario->dashboard('');
	}

	public function listar(): void
	{
		$this->lista = $this->mUsuario->listar();
		$this->listaRoles = $this->mRol->listar();
	}

	public function insertar(string $nombre, string $correo, string $pass, int $idRol): void
	{
		$nombre = trim($nombre);
		$correo = trim($correo);
		$pass = trim($pass);
		$idRol = (int)$idRol;

		if ($nombre === '' || $correo === '' || $pass === '' || $idRol <= 0) {
			$msg = 'Completa nombre, correo, contrasena y rol.';
		} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			$msg = 'El correo no tiene formato valido.';
		} elseif ($this->mUsuario->correoExiste($correo)) {
			$msg = 'Ese correo ya esta registrado.';
		} else {
			$ok = $this->mUsuario->insertar($nombre, $correo, $pass, $idRol);
			$msg = $ok ? 'Usuario registrado correctamente.' : 'No se pudo registrar el usuario.';
		}

		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->correoSeleccionado = '';
		$this->idRolSeleccionado = 0;
		$this->listar();

		$this->mostrarDashboard = false;
		$this->mostrarCrudUsuarios = true;
		$this->mostrarEditarPerfil = false;

		$this->sincronizarVista();
		$this->vUsuario->insertar($msg);
	}

	public function actualizar(int $id, string $nombre, string $correo, string $pass, int $idRol): void
	{
		$id = (int)$id;
		$nombre = trim($nombre);
		$correo = trim($correo);
		$pass = trim($pass);
		$idRol = (int)$idRol;

		if ($id <= 0 || $nombre === '' || $correo === '' || $idRol <= 0) {
			$msg = 'Completa nombre, correo y rol validos.';
		} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			$msg = 'El correo no tiene formato valido.';
		} elseif ($this->mUsuario->correoExiste($correo, $id)) {
			$msg = 'Ese correo ya esta en uso por otro usuario.';
		} else {
			$ok = $this->mUsuario->actualizar($id, $nombre, $correo, $pass, $idRol);
			$msg = $ok ? 'Usuario actualizado correctamente.' : 'No se pudo actualizar el usuario.';
		}

		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->correoSeleccionado = '';
		$this->idRolSeleccionado = 0;
		$this->listar();

		$this->mostrarDashboard = false;
		$this->mostrarCrudUsuarios = true;
		$this->mostrarEditarPerfil = false;

		$this->sincronizarVista();
		$this->vUsuario->actualizar($msg);
	}

	public function eliminar(int $id): void
	{
		$id = (int)$id;

		if ($id <= 0) {
			$msg = 'Id de usuario invalido.';
		} else {
			$ok = $this->mUsuario->eliminar($id);
			$msg = $ok ? 'Usuario eliminado correctamente.' : 'No se pudo eliminar el usuario.';
		}

		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->correoSeleccionado = '';
		$this->idRolSeleccionado = 0;
		$this->listar();

		$this->mostrarDashboard = false;
		$this->mostrarCrudUsuarios = true;
		$this->mostrarEditarPerfil = false;

		$this->sincronizarVista();
		$this->vUsuario->eliminar($msg);
	}

	public function editar(int $id, string $nombre, string $correo, int $idRol): void
	{
		$this->modoEdicion = true;
		$this->idSeleccionado = (int)$id;
		$this->nombreSeleccionado = (string)$nombre;
		$this->correoSeleccionado = (string)$correo;
		$this->idRolSeleccionado = (int)$idRol;
		$this->listar();

		$this->mostrarDashboard = false;
		$this->mostrarCrudUsuarios = true;
		$this->mostrarEditarPerfil = false;

		$this->sincronizarVista();
		$this->vUsuario->listar($this->lista);
	}

	public function abrirCrudUsuarios(): void
	{
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->correoSeleccionado = '';
		$this->idRolSeleccionado = 0;
		$this->listar();

		$this->mostrarDashboard = false;
		$this->mostrarCrudUsuarios = true;
		$this->mostrarEditarPerfil = false;

		$this->sincronizarVista();
		$this->vUsuario->listar($this->lista);
	}

	public function editarPerfil(): void
	{
		$this->mostrarDashboard = false;
		$this->mostrarCrudUsuarios = false;
		$this->mostrarEditarPerfil = true;

		$this->sincronizarVista();
		$this->vUsuario->editarPerfil('');
	}

	public function guardarPerfil(string $nombre, string $correo, string $password): void
	{
		$idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
		$nombre = trim($nombre);
		$correo = trim($correo);

		if ($nombre === '' || $correo === '') {
			$msg = 'Nombre y correo son obligatorios.';
			$this->mostrarDashboard = false;
			$this->mostrarCrudUsuarios = false;
			$this->mostrarEditarPerfil = true;
		} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			$msg = 'El correo no tiene formato valido.';
			$this->mostrarDashboard = false;
			$this->mostrarCrudUsuarios = false;
			$this->mostrarEditarPerfil = true;
		} elseif ($this->mUsuario->correoExiste($correo, $idUsuario)) {
			$msg = 'El correo ya esta en uso.';
			$this->mostrarDashboard = false;
			$this->mostrarCrudUsuarios = false;
			$this->mostrarEditarPerfil = true;
		} else {
			$ok = $this->mUsuario->actualizarPerfil($idUsuario, $nombre, $correo, $password);
			if ($ok) {
				$this->cargarUsuarioActual();
				$msg = 'Perfil actualizado correctamente.';
				$this->mostrarDashboard = true;
				$this->mostrarCrudUsuarios = false;
				$this->mostrarEditarPerfil = false;
			} else {
				$msg = 'No se pudo actualizar el perfil.';
				$this->mostrarDashboard = false;
				$this->mostrarCrudUsuarios = false;
				$this->mostrarEditarPerfil = true;
			}
		}

		$this->sincronizarVista();
		if ($this->mostrarEditarPerfil) {
			$this->vUsuario->editarPerfil($msg);
			return;
		}

		$this->vUsuario->dashboard($msg);
	}

	private function cargarUsuarioActual(): void
	{
		$idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
		$usuario = $this->mUsuario->obtenerPorId($idUsuario);

		if ($usuario === null) {
			session_unset();
			session_destroy();
			header('Location: ../index.php');
			exit;
		}

		$this->usuarioActual = $usuario;
		$_SESSION['usuario_nombre'] = $usuario['nombre'];
		$_SESSION['usuario_correo'] = $usuario['correo'];
		$_SESSION['usuario_rol'] = $usuario['rol'];
	}

	private function sincronizarVista(): void
	{
		$this->vUsuario->mostrarDashboard = $this->mostrarDashboard;
		$this->vUsuario->mostrarCrudUsuarios = $this->mostrarCrudUsuarios;
		$this->vUsuario->mostrarEditarPerfil = $this->mostrarEditarPerfil;
		$this->vUsuario->usuarioActual = $this->usuarioActual;
		$this->vUsuario->lista = $this->lista;
		$this->vUsuario->listaRoles = $this->listaRoles;
		$this->vUsuario->modoEdicion = $this->modoEdicion;
		$this->vUsuario->idSeleccionado = $this->idSeleccionado;
		$this->vUsuario->nombreSeleccionado = $this->nombreSeleccionado;
		$this->vUsuario->correoSeleccionado = $this->correoSeleccionado;
		$this->vUsuario->idRolSeleccionado = $this->idRolSeleccionado;
	}
}

$controlador = new usuario_controller();
$accionUsuario = trim((string)($_POST['accion_usuario'] ?? $_GET['accion_usuario'] ?? ''));
$rolActual = strtolower((string)($_SESSION['usuario_rol'] ?? ''));

if ($accionUsuario === 'logout') {
	session_unset();
	session_destroy();
	header('Location: index.php');
	exit;
}

if ($accionUsuario === 'ir_gestion' && $rolActual === 'administrador') {
	$destino = trim((string)($_POST['destino'] ?? ''));

	if ($destino === 'usuario') {
		header('Location: index.php?accion_usuario=gestion_usuario');
		exit;
	}

	$mapa = array(
		'rol' => 'index.php?accion_usuario=gestion_rol',
		'periodo' => 'index.php?accion_usuario=gestion_periodo',
		'modulo' => 'index.php?accion_usuario=gestion_modulo',
		'bloquehorario' => 'index.php?accion_usuario=gestion_bloquehorario',
		'documento' => 'index.php?accion_usuario=gestion_documento',
	);

	if (isset($mapa[$destino])) {
		header('Location: ' . $mapa[$destino]);
		exit;
	}
}

if ($accionUsuario === 'gestion_rol' && $rolActual === 'administrador') {
	require_once __DIR__ . '/rol_controller.php';
	exit;
}

if ($accionUsuario === 'gestion_modulo' && $rolActual === 'administrador') {
	require_once __DIR__ . '/modulo_controller.php';
	exit;
}

if ($accionUsuario === 'gestion_periodo' && $rolActual === 'administrador') {
	require_once __DIR__ . '/periodo_controller.php';
	exit;
}

if ($accionUsuario === 'gestion_bloquehorario' && $rolActual === 'administrador') {
	require_once __DIR__ . '/bloqueHorario_controller.php';
	exit;
}

if ($accionUsuario === 'gestion_documento' && $rolActual === 'administrador') {
	require_once __DIR__ . '/documento_controller.php';
	exit;
}

if ($accionUsuario === 'gestion_cita' && $rolActual === 'ejecutivo') {
	require_once __DIR__ . '/cita_controller.php';
	exit;
}

if ($accionUsuario === 'mis_citas' && $rolActual === 'postulante') {
	require_once __DIR__ . '/cita_controller.php';
	exit;
}

if ($accionUsuario === 'gestion_usuario' && $rolActual === 'administrador') {
	$controlador->abrirCrudUsuarios();
	exit;
}

if ($accionUsuario === 'abrir_crud_usuarios' && $rolActual === 'administrador') {
	$controlador->abrirCrudUsuarios();
	exit;
}

if ($accionUsuario === 'insertar_usuario' && $rolActual === 'administrador') {
	$nombre = trim((string)($_POST['nombre_usuario'] ?? ''));
	$correo = trim((string)($_POST['correo_usuario'] ?? ''));
	$pass = (string)($_POST['pass_usuario'] ?? '');
	$idRol = (int)($_POST['idrol_usuario'] ?? 0);
	$controlador->insertar($nombre, $correo, $pass, $idRol);
	exit;
}

if ($accionUsuario === 'actualizar_usuario' && $rolActual === 'administrador') {
	$id = (int)($_POST['id_usuario'] ?? 0);
	$nombre = trim((string)($_POST['nombre_usuario'] ?? ''));
	$correo = trim((string)($_POST['correo_usuario'] ?? ''));
	$pass = (string)($_POST['pass_usuario'] ?? '');
	$idRol = (int)($_POST['idrol_usuario'] ?? 0);
	$controlador->actualizar($id, $nombre, $correo, $pass, $idRol);
	exit;
}

if ($accionUsuario === 'eliminar_usuario' && $rolActual === 'administrador') {
	$id = (int)($_POST['id_usuario'] ?? 0);
	$controlador->eliminar($id);
	exit;
}

if ($accionUsuario === 'editar_usuario' && $rolActual === 'administrador') {
	$id = (int)($_POST['id_usuario'] ?? 0);
	$nombre = trim((string)($_POST['nombre_usuario'] ?? ''));
	$correo = trim((string)($_POST['correo_usuario'] ?? ''));
	$idRol = (int)($_POST['idrol_usuario'] ?? 0);
	$controlador->editar($id, $nombre, $correo, $idRol);
	exit;
}

if ($accionUsuario === 'editar_perfil') {
	$controlador->editarPerfil();
	exit;
}

if ($accionUsuario === 'volver_dashboard') {
	$controlador->iniciar();
	exit;
}

if ($accionUsuario === 'guardar_perfil') {
	$nombre = trim((string)($_POST['perfil_nombre'] ?? ''));
	$correo = trim((string)($_POST['perfil_correo'] ?? ''));
	$password = (string)($_POST['perfil_password'] ?? '');
	$controlador->guardarPerfil($nombre, $correo, $password);
	exit;
}

if ($accionUsuario === 'ver_citas' && $rolActual === 'ejecutivo') {
	header('Location: index.php?accion_usuario=gestion_cita');
	exit;
}

if ($accionUsuario === 'ver_mis_citas' && $rolActual === 'postulante') {
	header('Location: index.php?accion_usuario=mis_citas');
	exit;
}

$controlador->iniciar();
