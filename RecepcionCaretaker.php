<?php
require_once __DIR__ . '/RecepcionMemento.php';

class RecepcionCaretaker
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['undo_stack']) || !is_array($_SESSION['undo_stack'])) {
            $_SESSION['undo_stack'] = array();
        }

        if (!isset($_SESSION['redo_stack']) || !is_array($_SESSION['redo_stack'])) {
            $_SESSION['redo_stack'] = array();
        }
    }

    public function Add(RecepcionMemento $memento): void
    {
        $_SESSION['undo_stack'][] = $memento->GetState();
        $_SESSION['redo_stack'] = array();
    }

    // obtener paso anterior
    public function GetUndo(): ?RecepcionMemento
    {
        $undoStack = &$_SESSION['undo_stack'];
        $redoStack = &$_SESSION['redo_stack'];

        if (count($undoStack) <= 1) {
            return null;
        }

        $ultimo = array_pop($undoStack);
        $redoStack[] = $ultimo;

        $estado = $undoStack[count($undoStack) - 1];
        if (!is_array($estado)) {
            $this->limpiar();
            return null;
        }
        return new RecepcionMemento($estado);
    }

    // obtener paso siguiente
    public function GetRedo(): ?RecepcionMemento
    {
        $redoStack = &$_SESSION['redo_stack'];

        if (count($redoStack) === 0) {
            return null;
        }

        $estado = array_pop($redoStack);
        if (!is_array($estado)) {
            $this->limpiar();
            return null;
        }
        $_SESSION['undo_stack'][] = $estado;

        return new RecepcionMemento($estado);
    }

    public function limpiar(): void
    {
        $_SESSION['undo_stack'] = array();
        $_SESSION['redo_stack'] = array();
    }
}