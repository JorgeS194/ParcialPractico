<?php

class Validador {
    public static function validarTexto($valor) {
        $valor = trim($valor);
        if (empty($valor)) return false;
        if (preg_match('/\d/', $valor)) return false;
        return true;
    }

    public static function validarEmail($valor) {
        return filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validarTelefono($valor) {
        return preg_match('/^\d{7,15}$/', $valor) === 1;
    }

    public static function validarNumerico($valor, $min = null, $max = null) {
        if (!is_numeric($valor)) return false;
        if ($min !== null && $valor < $min) return false;
        if ($max !== null && $valor > $max) return false;
        return true;
    }

    public static function validarFecha($valor) {
        $d = DateTime::createFromFormat('Y-m-d', $valor);
        return $d && $d->format('Y-m-d') === $valor;
    }
}
