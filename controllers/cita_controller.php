<?php
require_once __DIR__ . '/../models/cita_model.php';
require_once __DIR__ . '/../models/detalle_model.php';
require_once __DIR__ . '/../models/documento_model.php';
require_once __DIR__ . '/../models/modulo_model.php';
require_once __DIR__ . '/../models/bloqueHorario_model.php';
require_once __DIR__ . '/../views/cita_view.php';
require_once __DIR__ . '/../RecepcionCaretaker.php';
require_once __DIR__ . '/../RecepcionOriginator.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$rolSesion = strtolower((string)($_SESSION['usuario_rol'] ?? ''));
if ($rolSesion !== 'postulante' && $rolSesion !== 'ejecutivo') {
    header('Location: index.php');
    exit;
}

class cita_controller
{
    private cita_model $mCita;
    private detalle_model $mDetalle;
    private documento_model $mDocumento;
    private modulo_model $mModulo;
    private bloqueHorario_model $mBloque;
    private cita_view $vCita;
    
    private RecepcionCaretaker $caretaker;
    private RecepcionOriginator $originator;

    private bool $vistaPostulante;
    private bool $vistaEjecutivo;
    private int $idCitaActiva;
    private array $listaCitasHoy;
    private array $listaDocs;
    private array $listaModulos;
    private array $listaBloques;
    private array $citaActiva;
    private bool $recepcionAbierta;
    private int $idCitaRecepcion;
    private array $datosRestaurados;

    public function __construct()
    {
        $rol = strtolower((string)($_SESSION['usuario_rol'] ?? ''));

        $this->mCita = new cita_model();
        $this->mDetalle = new detalle_model();
        $this->mDocumento = new documento_model();
        $this->mModulo = new modulo_model();
        $this->mBloque = new bloqueHorario_model();
        $this->vCita = new cita_view();
        
        $this->caretaker = new RecepcionCaretaker();
        $this->originator = new RecepcionOriginator();

        $this->vistaPostulante = $rol === 'postulante';
        $this->vistaEjecutivo = $rol === 'ejecutivo';
        $this->idCitaActiva = 0;
        $this->listaCitasHoy = array();
        $this->listaDocs = array();
        $this->listaModulos = array();
        $this->listaBloques = array();
        $this->citaActiva = array();
        $this->recepcionAbierta = false;
        $this->idCitaRecepcion = 0;
        $this->datosRestaurados = array();
    }

    public function iniciar(): void
    {
        $this->listar();
        $this->sincronizarVista();
        $this->vCita->listar($this->listaCitasHoy);
    }

    public function listar(): void
    {
        $this->refrescarDatos();
    }

    public function reservar(int $idBloque, int $idModulo): void
    {
        $idPostulante = (int)($_SESSION['usuario_id'] ?? 0);
        $msg = '';

        if ($idPostulante <= 0 || $idBloque <= 0 || $idModulo <= 0) {
            $idCitaNueva = 0;
            $msg = 'Datos incompletos para reservar la cita.';
        } elseif ($this->mCita->obtenerActiva($idPostulante) !== null) {
            $idCitaNueva = 0;
            $msg = 'Ya tienes una cita activa.';
        } else {
            $idCitaNueva = $this->mCita->insertar($idPostulante, $idBloque, $idModulo);
            $msg = $idCitaNueva > 0 ? 'Cita reservada correctamente.' : 'No se pudo reservar la cita.';
        }

        $this->refrescarDatos();
        $this->sincronizarVista();
        $this->vCita->reservar($msg);
    }

    public function abrirRecepcion(int $idCita): void
    {
        $msg = '';
        $idCita = (int)$idCita;

        $lista = $this->mCita->listarHoy();
        $docs = $this->mDocumento->listar();

        $this->listaCitasHoy = $lista;
        $this->listaDocs = $docs;
        $this->listaModulos = array();
        $this->listaBloques = array();
        $this->citaActiva = array();
        $this->idCitaActiva = 0;
        $this->recepcionAbierta = $idCita > 0;
        $this->idCitaRecepcion = $idCita > 0 ? $idCita : 0;

        if ($idCita > 0) {
            // limpiar historial al abrir una recepcion nueva
            $this->caretaker->limpiar();
            $msg = 'Completa la recepcion documental de la cita seleccionada.';
        } else {
            $msg = 'Selecciona una cita valida para abrir recepcion.';
        }

        $this->sincronizarVista();
        $this->vCita->abrirRecepcion($msg);
    }

    public function guardarRecepcion(int $idCita, array $datos): void
    {
        $idCita = (int)$idCita;
        $arrayDetalles = array();
        $msg = '';
        $this->datosRestaurados = array();

        foreach ($this->mDocumento->listar() as $doc) {
            $idDocumento = (int)($doc['id'] ?? 0);
            if ($idDocumento <= 0) {
                continue;
            }

            $arrayDetalles[] = array(
                'iddocumento' => $idDocumento,
                'entrega' => isset($datos['entrega'][$idDocumento]),
                'observacion' => trim((string)($datos['obs'][$idDocumento] ?? '')),
            );
        }

        if ($idCita <= 0 || count($arrayDetalles) === 0) {
            $okDetalle = false;
            $okEstado = false;
            $msg = 'No hay datos validos para guardar la recepcion.';
        } else {
            $okDetalle = $this->mDetalle->guardarLote($idCita, $arrayDetalles);
            $okEstado = $okDetalle ? $this->mCita->actualizarEstado($idCita, 1) : false;
            $msg = ($okDetalle && $okEstado) ? 'Recepcion guardada correctamente.' : 'No se pudo guardar la recepcion.';
        }

        $this->refrescarDatos();
        $this->recepcionAbierta = false;
        $this->idCitaRecepcion = 0;
        $this->sincronizarVista();
        $this->vCita->guardarRecepcion($msg);
    }

    public function cancelarRecepcion(): void
    {
        $lista = $this->mCita->listarHoy();
        $docs = $this->mDocumento->listar();

        $this->caretaker->limpiar();
        
        $this->listaCitasHoy = $lista;
        $this->listaDocs = $docs;
        $this->listaModulos = array();
        $this->listaBloques = array();
        $this->citaActiva = array();
        $this->idCitaActiva = 0;
        $this->recepcionAbierta = false;
        $this->idCitaRecepcion = 0;

        $this->sincronizarVista();
        $this->vCita->guardarRecepcion('Recepcion cancelada.');
    }

    private function refrescarDatos(): void
    {
        $this->datosRestaurados = array();
        if ($this->vistaPostulante) {
            $idPostulante = (int)($_SESSION['usuario_id'] ?? 0);
            $cita = $this->mCita->obtenerActiva($idPostulante);
            $this->citaActiva = $cita ?? array();
            $this->idCitaActiva = (int)($cita['id'] ?? 0);
            $this->listaModulos = $this->mModulo->listar();
            $this->listaBloques = $this->mBloque->listar();
            $this->listaCitasHoy = array();
            $this->listaDocs = array();
            $this->recepcionAbierta = false;
            $this->idCitaRecepcion = 0;
            return;
        }

        $this->listaCitasHoy = $this->mCita->listarHoy();
        $this->listaDocs = $this->mDocumento->listar();
        $this->listaModulos = array();
        $this->listaBloques = array();
        $this->citaActiva = array();
        $this->idCitaActiva = 0;
    }

    private function sincronizarVista(): void
    {
        $this->vCita->sincronizar(array(
            'vistaPostulante' => $this->vistaPostulante,
            'vistaEjecutivo' => $this->vistaEjecutivo,
            'idCitaActiva' => $this->idCitaActiva,
            'citaActiva' => $this->citaActiva,
            'recepcionAbierta' => $this->recepcionAbierta,
            'idCitaRecepcion' => $this->idCitaRecepcion,
            'listaCitasHoy' => $this->listaCitasHoy,
            'listaDocs' => $this->listaDocs,
            'listaModulos' => $this->listaModulos,
            'listaBloques' => $this->listaBloques,
            'datosRestaurados' => $this->datosRestaurados,
        ));
    }

    private function prepararRecepcion(int $idCita): void
    {
        $this->listaCitasHoy = $this->mCita->listarHoy();
        $this->listaDocs = $this->mDocumento->listar();
        $this->listaModulos = array();
        $this->listaBloques = array();
        $this->citaActiva = array();
        $this->idCitaActiva = 0;
        $this->recepcionAbierta = $idCita > 0;
        $this->idCitaRecepcion = $idCita > 0 ? $idCita : 0;
    }

    public function guardarEstadoTemporal(array $datos): void
    {
        $this->originator->SetState($datos);
        $memento = $this->originator->CreateMemento();
        $this->caretaker->Add($memento);
        // Actualizar la vista con los datos actuales
        $this->datosRestaurados = $this->originator->GetState();
        $idCita = (int)($this->datosRestaurados['id_cita'] ?? 0);
        $this->prepararRecepcion($idCita);

        $this->sincronizarVista();
        $this->vCita->abrirRecepcion('Borrador guardado.');
    }

    public function deshacerEstado(array $datos): void
    {
        $memento = $this->caretaker->GetUndo();
        
        if ($memento !== null) {
            $this->originator->RestoreMemento($memento);
            $msg = 'Estado anterior restaurado.';
        } else {
            $this->originator->SetState($datos); 
            $msg = 'No hay estados para deshacer.';
        }
        
        $this->datosRestaurados = $this->originator->GetState();
        $idCita = (int)($this->datosRestaurados['id_cita'] ?? ($datos['id_cita'] ?? 0));
        $this->prepararRecepcion($idCita);

        $this->sincronizarVista();
        $this->vCita->abrirRecepcion($msg);
    }

    public function rehacerEstado(array $datos): void
    {
        $memento = $this->caretaker->GetRedo();
        
        if ($memento !== null) {
            $this->originator->RestoreMemento($memento);
            $msg = 'Estado futuro restaurado.';
        } else {
            $this->originator->SetState($datos); 
            $msg = 'No hay estados para rehacer.';
        }
        
        $this->datosRestaurados = $this->originator->GetState();
        $idCita = (int)($this->datosRestaurados['id_cita'] ?? ($datos['id_cita'] ?? 0));
        $this->prepararRecepcion($idCita);

        $this->sincronizarVista();
        $this->vCita->abrirRecepcion($msg);
    }
}

$controlador = new cita_controller();
$accion = trim((string)($_POST['accion_cita'] ?? $_GET['accion_cita'] ?? $_POST['action'] ?? $_GET['action'] ?? ''));

if ($accion === 'guardar_estado') {
    $controlador->guardarEstadoTemporal($_POST);
    exit;
}

if ($accion === 'deshacer') {
    $controlador->deshacerEstado($_POST);
    exit;
}

if ($accion === 'rehacer') {
    $controlador->rehacerEstado($_POST);
    exit;
}

if ($accion === 'reservar') {
    $idBloque = (int)($_POST['id_bloque'] ?? 0);
    $idModulo = (int)($_POST['id_modulo'] ?? 0);
    $controlador->reservar($idBloque, $idModulo);
    exit;
}

if ($accion === 'abrir_recepcion') {
    $idCita = (int)($_POST['id_cita'] ?? 0);
    $controlador->abrirRecepcion($idCita);
    exit;
}

if ($accion === 'guardar_recepcion') {
    $idCita = (int)($_POST['id_cita'] ?? 0);
    $controlador->guardarRecepcion($idCita, array(
        'entrega' => (array)($_POST['entrega'] ?? array()),
        'obs' => (array)($_POST['obs'] ?? array()),
    ));
    exit;
}

if ($accion === 'cancelar_recepcion') {
    $controlador->cancelarRecepcion();
    exit;
}

$controlador->iniciar();