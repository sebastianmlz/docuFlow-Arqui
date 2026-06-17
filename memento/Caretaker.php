<?php
require_once __DIR__ . '/Memento.php';

class Caretaker
{
    private string $mementos = 'mementos_modulo';
    private string $memento_index = 'memento_index_modulo';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[$this->mementos]) || !is_array($_SESSION[$this->mementos])) {
            $_SESSION[$this->mementos] = array();
        }

        if (!isset($_SESSION[$this->memento_index]) || !is_int($_SESSION[$this->memento_index])) {
            $_SESSION[$this->memento_index] = -1;
        }
    }

    // Recibe objeto Memento y lo guarda 
    public function Add(Memento $memento): void
    {
        $mementos = &$_SESSION[$this->mementos];
        $index = &$_SESSION[$this->memento_index];

        if ($index >= 0 && $index < count($mementos) - 1) {
            $mementos = array_slice($mementos, 0, $index + 1);
        }

        $mementos[] = $memento;
        $index = count($mementos) - 1;
    }

    public function GetUndo(): ?Memento
    {
        $mementos = &$_SESSION[$this->mementos];
        $index = &$_SESSION[$this->memento_index];

        if ($index <= 0) {
            return null;
        }

        $index--;
        return $mementos[$index];
    }

    public function GetRedo(): ?Memento
    {
        $mementos = &$_SESSION[$this->mementos];
        $index = &$_SESSION[$this->memento_index];

        if ($index >= count($mementos) - 1) {
            return null;
        }

        $index++;
        return $mementos[$index];
    }

    public function limpiar(): void
    {
        $_SESSION[$this->mementos] = array();
        $_SESSION[$this->memento_index] = -1;
    }
}
