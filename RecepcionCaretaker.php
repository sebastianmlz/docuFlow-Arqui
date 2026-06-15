<?php
require_once __DIR__ . '/RecepcionMemento.php';

class RecepcionCaretaker
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['mementos']) || !is_array($_SESSION['mementos'])) {
            $_SESSION['mementos'] = array();
        }

        if (!isset($_SESSION['memento_index']) || !is_int($_SESSION['memento_index'])) {
            $_SESSION['memento_index'] = -1;
        }
    }

    public function Add(RecepcionMemento $memento): void
    {
        $mementos = &$_SESSION['mementos'];
        $index = &$_SESSION['memento_index'];

        if ($index >= 0 && $index < count($mementos) - 1) {
            $mementos = array_slice($mementos, 0, $index + 1);
        }

        $mementos[] = $memento->GetState();
        $index = count($mementos) - 1;
    }

    // deshacer
    public function GetUndo(): ?RecepcionMemento
    {
        $mementos = &$_SESSION['mementos'];
        $index = &$_SESSION['memento_index'];

        if ($index <= 0) {
            return null;
        }

        $index--;
        $estado = $mementos[$index];
        if (!is_array($estado)) {
            $this->limpiar();
            return null;
        }
        return new RecepcionMemento($estado);
    }

    // rehacer
    public function GetRedo(): ?RecepcionMemento
    {
        $mementos = &$_SESSION['mementos'];
        $index = &$_SESSION['memento_index'];

        if ($index >= count($mementos) - 1) {
            return null;
        }

        $index++;
        $estado = $mementos[$index];
        if (!is_array($estado)) {
            $this->limpiar();
            return null;
        }
        return new RecepcionMemento($estado);
    }

    public function limpiar(): void
    {
        $_SESSION['mementos'] = array();
        $_SESSION['memento_index'] = -1;
    }
}