<?php

$route = $_GET['route'] ?? 'colaborador';

switch ($route) {
    case 'colaborador':
        require_once __DIR__ . '/../app/Views/formulario_colaborador.view.php';
        break;
    case 'perfil':
        require_once __DIR__ . '/../app/Views/formulario_perfil.view.php';
        break;
    case 'reporte':
        require_once __DIR__ . '/../app/Views/reporte.view.php';
        break;
    case 'guardar_colaborador':
        require_once __DIR__ . '/../app/Controllers/guardar_colaborador.php';
        break;
    case 'guardar_perfil':
        require_once __DIR__ . '/../app/Controllers/guardar_perfil.php';
        break;
    case 'exportar_excel':
        require_once __DIR__ . '/../app/Controllers/exportar_excel.php';
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        break;
}
