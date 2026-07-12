<?php
require_once '../clases/mod_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new mod_db();

    // Recolectar datos del formulario para mapearlos con las columnas de la tabla colaboradores
    $data = [
        'identidad' => $_POST['identidad'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'apellido' => $_POST['apellido'] ?? '',
        'edad' => $_POST['edad'] ?? '',
        'id_tiposangre' => $_POST['id_tiposangre'] ?? '',
        'id_sexo' => $_POST['id_sexo'] ?? '',
        'nacionalidad' => $_POST['nacionalidad'] ?? '',
        'id_ruta' => $_POST['id_ruta'] ?? '',
        'correo' => $_POST['correo'] ?? '',
        'celular' => $_POST['celular'] ?? ''
    ];

    // Intentar insertar los datos
    $resultado = $db->insertSeguro('colaboradores', $data);

    // Redirigir según el resultado
    if ($resultado) {
        header("Location: ../public/formulario_colaborador.php?status=success");
        exit;
    } else {
        header("Location: ../public/formulario_colaborador.php?status=error");
        exit;
    }
} else {
    // Si se accede al archivo sin método POST, redirigimos al formulario
    header("Location: ../public/formulario_colaborador.php");
    exit;
}
