<?php
require_once '../clases/mod_db.php';
require_once '../clases/Validador.php';
require_once '../clases/Sanitizador.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/formulario_perfil.php");
    exit;
}

$db = new mod_db();
$conexion = $db->getConexion();

$id_colaborador = intval($_POST['id_colaborador'] ?? 0);
$fecha_fin = trim($_POST['fecha_fin'] ?? '');
$id_motivo = trim($_POST['id_motivo_terminacion'] ?? '');

if (!$id_colaborador) {
    header("Location: ../public/formulario_perfil.php?status=error");
    exit;
}

// ============================================================
// CASO DE BAJA: fecha_fin y motivo proporcionados
// ============================================================
if (!empty($fecha_fin) && !empty($id_motivo)) {
    try {
        $conexion->beginTransaction();

        // 1. Cerrar el perfil activo actual
        $stmt = $conexion->prepare(
            "UPDATE perfiles_laborales 
             SET es_activo = 0, fecha_fin = :fecha_fin, id_motivo_terminacion = :motivo 
             WHERE id_colaborador = :id_col AND es_activo = 1"
        );
        $stmt->execute([
            ':fecha_fin' => $fecha_fin,
            ':motivo' => $id_motivo,
            ':id_col' => $id_colaborador
        ]);

        // 2. Marcar colaborador como inactivo
        $stmt2 = $conexion->prepare(
            "UPDATE colaboradores SET empleado_activo = 0 WHERE id_colaborador = :id_col"
        );
        $stmt2->execute([':id_col' => $id_colaborador]);

        $conexion->commit();
        header("Location: ../public/formulario_perfil.php?status=baja_success");
        exit;
    } catch (Exception $e) {
        $conexion->rollBack();
        header("Location: ../public/formulario_perfil.php?status=error");
        exit;
    }
}

// ============================================================
// CASO DE NUEVO PERFIL / PROMOCIÓN
// ============================================================
$id_ocupacion = intval($_POST['id_ocupacion'] ?? 0);
$id_tipoempleado = intval($_POST['id_tipoempleado'] ?? 0);
$salario = Sanitizador::limpiarNumero($_POST['salario'] ?? 0);
$fecha_inicio = trim($_POST['fecha_inicio'] ?? '');

if (!$id_ocupacion || !$id_tipoempleado || !Validador::validarNumerico($salario, 0) || !Validador::validarFecha($fecha_inicio)) {
    header("Location: ../public/formulario_perfil.php?status=error");
    exit;
}

try {
    $conexion->beginTransaction();

    // 1. Verificar si existe un perfil activo para este colaborador
    $stmt = $conexion->prepare(
        "SELECT id_perfil FROM perfiles_laborales 
         WHERE id_colaborador = :id_col AND es_activo = 1"
    );
    $stmt->execute([':id_col' => $id_colaborador]);
    $perfilActivo = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Si existe perfil activo, cerrarlo (promoción)
    if ($perfilActivo) {
        $stmtUpdate = $conexion->prepare(
            "UPDATE perfiles_laborales 
             SET es_activo = 0, fecha_fin = :fecha_fin 
             WHERE id_perfil = :id_perfil"
        );
        $stmtUpdate->execute([
            ':fecha_fin' => date('Y-m-d'),
            ':id_perfil' => $perfilActivo['id_perfil']
        ]);
    }

    // 3. Insertar el nuevo perfil con es_activo = 1
    $stmtInsert = $conexion->prepare(
        "INSERT INTO perfiles_laborales 
         (id_colaborador, id_ocupacion, id_tipoempleado, salario, fecha_inicio, es_activo) 
         VALUES (:id_colaborador, :id_ocupacion, :id_tipoempleado, :salario, :fecha_inicio, 1)"
    );
    $stmtInsert->execute([
        ':id_colaborador' => $id_colaborador,
        ':id_ocupacion' => $id_ocupacion,
        ':id_tipoempleado' => $id_tipoempleado,
        ':salario' => $salario,
        ':fecha_inicio' => $fecha_inicio
    ]);

    $conexion->commit();
    header("Location: ../public/formulario_perfil.php?status=success");
    exit;
} catch (Exception $e) {
    $conexion->rollBack();
    header("Location: ../public/formulario_perfil.php?status=error");
    exit;
}
