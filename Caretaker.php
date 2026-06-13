<?php
require_once __DIR__ . '/Memento.php';

class Caretaker
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

    // Recibe el objeto Memento y lo guarda ENTERO en la sesión
    public function Add(Memento $memento): void
    {
        $mementos = &$_SESSION['mementos'];
        $index = &$_SESSION['memento_index'];

        if ($index >= 0 && $index < count($mementos) - 1) {
            $mementos = array_slice($mementos, 0, $index + 1);
        }

        // Se guarda el objeto Memento completo, respetando la caja negra
        $mementos[] = $memento;
        $index = count($mementos) - 1;
    }

    public function GetUndo(): ?Memento
    {
        $mementos = &$_SESSION['mementos'];
        $index = &$_SESSION['memento_index'];

        if ($index <= 0) {
            return null;
        }

        $index--;
        return $mementos[$index];
    }

    public function GetRedo(): ?Memento
    {
        $mementos = &$_SESSION['mementos'];
        $index = &$_SESSION['memento_index'];

        if ($index >= count($mementos) - 1) {
            return null;
        }

        $index++;
        return $mementos[$index];
    }

    public function limpiar(): void
    {
        $_SESSION['mementos'] = array();
        $_SESSION['memento_index'] = -1;
    }
}