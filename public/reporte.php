<?php
require_once '../clases/mod_db.php';

$db = new mod_db();

$sql = "
    SELECT
        c.id_colaborador,
        c.identidad,
        c.nombre,
        c.apellido,
        c.edad,
        c.nacionalidad,
        c.correo,
        c.celular,
        c.empleado_activo,
        c.fecha_registro,
        ts.Nombre  AS tipo_sangre,
        sx.nombre  AS sexo,
        cr.Nombre  AS ruta,
        oc.OCUPACION   AS ocupacion,
        te.Nombre  AS tipo_empleado,
        pl.salario,
        pl.fecha_inicio,
        pl.fecha_fin,
        pl.es_activo   AS perfil_activo
    FROM colaboradores c
    LEFT JOIN perfiles_laborales pl
        ON pl.id_colaborador = c.id_colaborador AND pl.es_activo = 1
    LEFT JOIN cat_ocupaciones  oc ON oc.C_OCUP       = pl.id_ocupacion
    LEFT JOIN cat_tipoempleado te ON te.id            = pl.id_tipoempleado
    LEFT JOIN tiposangre       ts ON ts.id            = c.id_tiposangre
    LEFT JOIN cat_sexo         sx ON sx.id            = c.id_sexo
    LEFT JOIN cat_rutas        cr ON cr.id            = c.id_ruta
    ORDER BY c.id_colaborador ASC
";

$colaboradores = $db->Arreglos($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Colaboradores</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            padding-bottom: 100px;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .report-header h1 {
            color: #1e40af;
            margin: 0;
        }
        .btn-export {
            background-color: #15803d;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 0.65rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .btn-export:hover {
            background-color: #166534;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        thead tr {
            background-color: #1e40af;
            color: #ffffff;
        }
        thead th {
            padding: 0.75rem 0.9rem;
            text-align: left;
            white-space: nowrap;
        }
        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        tbody tr:hover {
            background-color: #dbeafe;
        }
        tbody td {
            padding: 0.65rem 0.9rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .resumen-cell {
            font-size: 0.8rem;
            color: #374151;
        }
        .badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1>Reporte de Colaboradores</h1>
            <a href="../controllers/exportar_excel.php" class="btn-export">⬇ Exportar a Excel</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Identidad</th>
                        <th>Nombre Completo</th>
                        <th>Datos Personales</th>
                        <th>Contacto</th>
                        <th>Perfil Activo</th>
                        <th>Estado</th>
                        <th>Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colaboradores)): ?>
                        <tr>
                            <td colspan="8" class="no-data">No hay colaboradores registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $col): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($col['id_colaborador']); ?></td>
                                <td><?php echo htmlspecialchars($col['identidad']); ?></td>
                                <td><?php echo htmlspecialchars($col['nombre'] . ' ' . $col['apellido']); ?></td>
                                <td class="resumen-cell">
                                    <?php
                                        echo implode(', ', array_filter([
                                            $col['edad'] . ' años',
                                            $col['tipo_sangre'] ?? '',
                                            $col['sexo'] ?? '',
                                            $col['nacionalidad'],
                                            $col['ruta'] ?? ''
                                        ]));
                                    ?>
                                </td>
                                <td class="resumen-cell">
                                    <?php
                                        echo implode(', ', array_filter([
                                            $col['correo'],
                                            $col['celular']
                                        ]));
                                    ?>
                                </td>
                                <td class="resumen-cell">
                                    <?php if ($col['perfil_activo']): ?>
                                        <?php
                                            echo implode(', ', array_filter([
                                                $col['ocupacion'] ?? '',
                                                $col['tipo_empleado'] ?? '',
                                                $col['salario'] ? '$ ' . number_format($col['salario'], 2) : '',
                                                $col['fecha_inicio'] ?? ''
                                            ]));
                                        ?>
                                    <?php else: ?>
                                        <span style="color:#9ca3af;">Sin perfil activo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($col['empleado_activo']): ?>
                                        <span class="badge badge-active">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($col['fecha_registro']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        © <?php echo date('Y'); ?> iTECH Contrataciones. All rights reserved.
    </footer>
</body>
</html>
