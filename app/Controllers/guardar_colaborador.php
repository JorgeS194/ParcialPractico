<?php
require_once __DIR__ . '/../Models/mod_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new mod_db();

    require_once __DIR__ . '/../Services/Sanitizador.php';
    require_once __DIR__ . '/../Services/Validador.php';

    $nombre = Sanitizador::limpiarTexto($_POST['nombre'] ?? '');
    $apellido = Sanitizador::limpiarTexto($_POST['apellido'] ?? '');
    $nacionalidad = Sanitizador::limpiarTexto($_POST['nacionalidad'] ?? '');
    $correo = Sanitizador::limpiarEmail($_POST['correo'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $edad = trim($_POST['edad'] ?? '');

    if (!Validador::validarTexto($nombre) || !Validador::validarTexto($apellido) || !Validador::validarTexto($nacionalidad) || !Validador::validarEmail($correo) || !Validador::validarTelefono($celular) || !Validador::validarNumerico($edad, 1)) {
        header("Location: index.php?route=colaborador&status=error");
        exit;
    }

    // Recolectar datos del formulario para mapearlos con las columnas de la tabla colaboradores
    $data = [
        'identidad' => $_POST['identidad'] ?? '',
        'nombre' => $nombre,
        'apellido' => $apellido,
        'edad' => $edad,
        'id_tiposangre' => $_POST['id_tiposangre'] ?? '',
        'id_sexo' => $_POST['id_sexo'] ?? '',
        'nacionalidad' => $nacionalidad,
        'id_ruta' => $_POST['id_ruta'] ?? '',
        'correo' => $correo,
        'celular' => $celular
    ];

    // Intentar insertar los datos
    $resultado = $db->insertSeguro('colaboradores', $data);

    // Redirigir según el resultado
    if ($resultado) {
        header("Location: index.php?route=colaborador&status=success");
        exit;
    } else {
        header("Location: index.php?route=colaborador&status=error");
        exit;
    }
} else {
    // Si se accede al archivo sin método POST, redirigimos al formulario
    header("Location: index.php?route=colaborador");
    exit;
}
