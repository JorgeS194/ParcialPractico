<?php
require_once '../clases/mod_db.php';
$db = new mod_db();

// Datos para los selects
$ocupaciones = $db->Arreglos("SELECT C_OCUP, OCUPACION FROM cat_ocupaciones WHERE Activo = 1");
$tipos_empleado = $db->Arreglos("SELECT id, Nombre FROM cat_tipoempleado");
$motivos = $db->Arreglos("SELECT C_TERMINACION, MOTIVO FROM cat_motivos_terminacion");

// Buscar colaborador por identidad
$colaborador = null;
$identidad_buscar = '';
$busqueda_realizada = false;

if (isset($_GET['identidad']) && !empty(trim($_GET['identidad']))) {
    $busqueda_realizada = true;
    $identidad_buscar = trim($_GET['identidad']);
    $conn = $db->getConexion();
    $stmt = $conn->prepare("SELECT id_colaborador, identidad, nombre, apellido FROM colaboradores WHERE identidad = :identidad");
    $stmt->execute([':identidad' => $identidad_buscar]);
    $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Mensajes de estado
$mensaje = '';
$tipo_mensaje = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            $mensaje = 'Perfil laboral guardado exitosamente.';
            $tipo_mensaje = 'success';
            break;
        case 'baja_success':
            $mensaje = 'Colaborador dado de baja exitosamente.';
            $tipo_mensaje = 'success';
            break;
        case 'error':
            $mensaje = 'Error al guardar. Verifique los datos e intente nuevamente.';
            $tipo_mensaje = 'error';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Laboral</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-section {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }
        .search-section .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        .btn-search {
            background-color: #1e40af;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 0.75rem 1.25rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            white-space: nowrap;
        }
        .btn-search:hover {
            background-color: #1e3a8a;
        }
        .colaborador-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .colaborador-info p {
            margin: 0.25rem 0;
        }
        .colaborador-info strong {
            color: #1e40af;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e40af;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #bfdbfe;
        }
        #motivo-group {
            display: none;
        }
    </style>
</head>
<body>
    <main>
        <div class="form-container">
            <h1>Perfil Laboral</h1>

            <?php if ($mensaje): ?>
                <div class="message <?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <!-- Búsqueda de colaborador -->
            <form method="GET" action="">
                <div class="search-section">
                    <div class="form-group">
                        <label for="identidad">Buscar Colaborador por Identidad</label>
                        <input type="text" id="identidad" name="identidad" value="<?php echo htmlspecialchars($identidad_buscar); ?>" required>
                    </div>
                    <button type="submit" class="btn-search">Buscar</button>
                </div>
            </form>

            <?php if ($busqueda_realizada && !$colaborador): ?>
                <div class="message error" style="margin-top: 1rem;">
                    No se encontró un colaborador con la identidad "<?php echo htmlspecialchars($identidad_buscar); ?>".
                </div>
            <?php endif; ?>

            <?php if ($colaborador): ?>
                <div class="colaborador-info">
                    <p><strong>Código:</strong> <?php echo htmlspecialchars($colaborador['id_colaborador']); ?></p>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($colaborador['nombre'] . ' ' . $colaborador['apellido']); ?></p>
                    <p><strong>Identidad:</strong> <?php echo htmlspecialchars($colaborador['identidad']); ?></p>
                </div>

                <form method="POST" action="../controllers/guardar_perfil.php">
                    <input type="hidden" name="id_colaborador" value="<?php echo htmlspecialchars($colaborador['id_colaborador']); ?>">

                    <!-- Sección: Nuevo Perfil / Promoción -->
                    <div class="section-title">Datos del Perfil</div>

                    <div class="form-group">
                        <label for="id_ocupacion">Ocupación</label>
                        <select id="id_ocupacion" name="id_ocupacion">
                            <option value="">Seleccione...</option>
                            <?php foreach ($ocupaciones as $oc): ?>
                                <option value="<?php echo htmlspecialchars($oc['C_OCUP']); ?>">
                                    <?php echo htmlspecialchars($oc['OCUPACION']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_tipoempleado">Tipo de Empleado / Planilla</label>
                        <select id="id_tipoempleado" name="id_tipoempleado">
                            <option value="">Seleccione...</option>
                            <?php foreach ($tipos_empleado as $te): ?>
                                <option value="<?php echo htmlspecialchars($te['id']); ?>">
                                    <?php echo htmlspecialchars($te['Nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="salario">Salario</label>
                        <input type="number" id="salario" name="salario" step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio">
                    </div>

                    <!-- Sección: Baja -->
                    <div class="section-title">Baja del Colaborador (opcional)</div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin">
                        <small class="form-hint">Complete este campo solo si desea dar de baja al colaborador.</small>
                    </div>

                    <div class="form-group" id="motivo-group">
                        <label for="id_motivo_terminacion">Motivo de Terminación</label>
                        <select id="id_motivo_terminacion" name="id_motivo_terminacion">
                            <option value="">Seleccione...</option>
                            <?php foreach ($motivos as $mot): ?>
                                <option value="<?php echo htmlspecialchars($mot['C_TERMINACION']); ?>">
                                    <?php echo htmlspecialchars($mot['MOTIVO']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Guardar Perfil</button>
                </form>

                <script>
                    document.getElementById('fecha_fin').addEventListener('change', function() {
                        var motivoGroup = document.getElementById('motivo-group');
                        motivoGroup.style.display = this.value ? 'block' : 'none';
                    });
                </script>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        © <?php echo date('Y'); ?> iTECH Contrataciones. All rights reserved.
    </footer>
</body>
</html>
