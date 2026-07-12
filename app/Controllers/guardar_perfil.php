<?php
require_once __DIR__ . '/../Models/mod_db.php';
require_once __DIR__ . '/../Services/Validador.php';
require_once __DIR__ . '/../Services/Sanitizador.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?route=perfil");
    exit;
}

$db = new mod_db();
$conexion = $db->getConexion();

$id_colaborador = intval($_POST['id_colaborador'] ?? 0);
$fecha_fin = trim($_POST['fecha_fin'] ?? '');
$id_motivo = trim($_POST['id_motivo_terminacion'] ?? '');

if (!$id_colaborador) {
    header("Location: index.php?route=perfil&status=error");
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

        // Firmar el perfil que se acaba de cerrar (baja)
        $stmtPerfil = $conexion->prepare(
            "SELECT id_perfil, id_colaborador, id_tipoempleado, id_ocupacion, salario, fecha_inicio
             FROM perfiles_laborales
             WHERE id_colaborador = :id_col AND fecha_fin = :fecha_fin AND es_activo = 0
             ORDER BY id_perfil DESC LIMIT 1"
        );
        $stmtPerfil->execute([':id_col' => $id_colaborador, ':fecha_fin' => $fecha_fin]);
        $perfilBaja = $stmtPerfil->fetch(PDO::FETCH_ASSOC);

        if ($perfilBaja) {
            $dataFirma = $perfilBaja['id_colaborador'] . '|' . $perfilBaja['id_tipoempleado'] . '|' . $perfilBaja['id_ocupacion'] . '|' . $perfilBaja['salario'] . '|' . $perfilBaja['fecha_inicio'];
            $privateKey = file_get_contents(__DIR__ . '/../../storage/keys/private_key.pem');
            openssl_sign($dataFirma, $firma, $privateKey, OPENSSL_ALGO_SHA256);
            $firmaBase64 = base64_encode($firma);

            $stmtFirma = $conexion->prepare(
                "UPDATE perfiles_laborales SET firma_integridad = :firma WHERE id_perfil = :id_perfil"
            );
            $stmtFirma->execute([':firma' => $firmaBase64, ':id_perfil' => $perfilBaja['id_perfil']]);
        }

        $conexion->commit();
        header("Location: index.php?route=perfil&status=baja_success");
        exit;
    } catch (Exception $e) {
        $conexion->rollBack();
        header("Location: index.php?route=perfil&status=error");
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
    header("Location: index.php?route=perfil&status=error");
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

    // 4. Firmar el nuevo perfil insertado
    $newPerfilId = $conexion->lastInsertId();
    $dataFirma = $id_colaborador . '|' . $id_tipoempleado . '|' . $id_ocupacion . '|' . $salario . '|' . $fecha_inicio;
    $privateKey = file_get_contents(__DIR__ . '/../../storage/keys/private_key.pem');
    openssl_sign($dataFirma, $firma, $privateKey, OPENSSL_ALGO_SHA256);
    $firmaBase64 = base64_encode($firma);

    $stmtFirma = $conexion->prepare(
        "UPDATE perfiles_laborales SET firma_integridad = :firma WHERE id_perfil = :id_perfil"
    );
    $stmtFirma->execute([':firma' => $firmaBase64, ':id_perfil' => $newPerfilId]);

    $conexion->commit();
    header("Location: index.php?route=perfil&status=success");
    exit;
} catch (Exception $e) {
    $conexion->rollBack();
    header("Location: index.php?route=perfil&status=error");
    exit;
}
