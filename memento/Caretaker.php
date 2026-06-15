<?php
require_once __DIR__ . '/Memento.php';

class Caretaker
{
    private string $claveMementos = 'mementos_formulario';
    private string $claveIndice = 'memento_formulario_index';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[$this->claveMementos]) || !is_array($_SESSION[$this->claveMementos])) {
            $_SESSION[$this->claveMementos] = array();
        }

        if (!isset($_SESSION[$this->claveIndice]) || !is_int($_SESSION[$this->claveIndice])) {
            $_SESSION[$this->claveIndice] = -1;
        }
    }

    // Recibe el objeto Memento y lo guarda ENTERO en la sesión
    public function Add(Memento $memento): void
    {
        $mementos = &$_SESSION[$this->claveMementos];
        $index = &$_SESSION[$this->claveIndice];

        if ($index >= 0 && $index < count($mementos) - 1) {
            $mementos = array_slice($mementos, 0, $index + 1);
        }

        // Se guarda el objeto Memento completo, respetando la caja negra
        $mementos[] = $memento;
        $index = count($mementos) - 1;
    }

    public function GetUndo(): ?Memento
    {
        $mementos = &$_SESSION[$this->claveMementos];
        $index = &$_SESSION[$this->claveIndice];

        if ($index <= 0) {
            return null;
        }

        $index--;
        return $mementos[$index];
    }

    public function GetRedo(): ?Memento
    {
        $mementos = &$_SESSION[$this->claveMementos];
        $index = &$_SESSION[$this->claveIndice];

        if ($index >= count($mementos) - 1) {
            return null;
        }

        $index++;
        return $mementos[$index];
    }

    public function limpiar(): void
    {
        $_SESSION[$this->claveMementos] = array();
        $_SESSION[$this->claveIndice] = -1;
    }
}
