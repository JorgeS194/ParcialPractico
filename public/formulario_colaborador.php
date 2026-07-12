<?php
require_once '../clases/mod_db.php';
$db = new mod_db();

// Obtener datos para los selects
$tipos_sangre = $db->Arreglos("SELECT id, Nombre FROM tiposangre");
$sexos = $db->Arreglos("SELECT id, nombre FROM cat_sexo");
$rutas = $db->Arreglos("SELECT id, Nombre FROM cat_rutas");

// Manejo de mensajes (éxito o error) redirigidos desde el controlador
$mensaje = '';
$tipo_mensaje = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $mensaje = 'Colaborador guardado exitosamente.';
        $tipo_mensaje = 'success';
    } elseif ($_GET['status'] == 'error') {
        $mensaje = 'Hubo un error al intentar guardar el colaborador. Por favor, verifique los datos o intente nuevamente.';
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Colaborador</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main>
        <div class="form-container">
            <h1>Registro de Colaborador</h1>
            
            <?php if ($mensaje): ?>
                <div class="message <?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <form action="../controllers/guardar_colaborador.php" method="POST">
                <div class="form-group">
                    <label for="identidad">Identidad (Documento de Identificación)</label>
                    <input type="text" id="identidad" name="identidad" required>
                    <small class="form-hint">Ingresar sin guiones (ej: 81234567)</small>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>

                <div class="form-group">
                    <label for="edad">Edad</label>
                    <input type="number" id="edad" name="edad" required>
                </div>

                <div class="form-group">
                    <label for="id_tiposangre">Tipo de Sangre</label>
                    <select id="id_tiposangre" name="id_tiposangre" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($tipos_sangre as $sangre): ?>
                            <option value="<?php echo htmlspecialchars($sangre['id']); ?>">
                                <?php echo htmlspecialchars($sangre['Nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_sexo">Sexo</label>
                    <select id="id_sexo" name="id_sexo" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($sexos as $sexo): ?>
                            <option value="<?php echo htmlspecialchars($sexo['id']); ?>">
                                <?php echo htmlspecialchars($sexo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nacionalidad">Nacionalidad</label>
                    <input type="text" id="nacionalidad" name="nacionalidad" required>
                </div>

                <div class="form-group">
                    <label for="id_ruta">Ruta del colaborador</label>
                    <select id="id_ruta" name="id_ruta" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($rutas as $ruta): ?>
                            <option value="<?php echo htmlspecialchars($ruta['id']); ?>">
                                <?php echo htmlspecialchars($ruta['Nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" required>
                </div>

                <div class="form-group">
                    <label for="celular">Celular</label>
                    <input type="text" id="celular" name="celular" required>
                    <small class="form-hint">Solo números, sin guiones (ej: 61234567)</small>
                </div>

                <button type="submit" class="btn-submit">Guardar Colaborador</button>
            </form>
        </div>
    </main>

    <footer>
        © <?php echo date('Y'); ?> iTECH Contrataciones. All rights reserved.
    </footer>
</body>
</html>
