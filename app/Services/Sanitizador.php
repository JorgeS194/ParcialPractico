<?php

class Sanitizador {
    public static function limpiarTexto($valor) {
        $valor = trim($valor);
        $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
        return mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8');
    }

    public static function limpiarNumero($valor) {
        return filter_var($valor, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function limpiarEmail($valor) {
        return filter_var($valor, FILTER_SANITIZE_EMAIL);
    }
}
