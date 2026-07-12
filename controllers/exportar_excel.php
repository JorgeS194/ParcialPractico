<?php
require_once '../clases/mod_db.php';

// Asegurar que exista el directorio de exportaciones
$exportDir = __DIR__ . '/../public/exportados/';
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

$outputFile = $exportDir . 'reporte_colaboradores.xlsx';

// Cargar autoloader de Composer
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

$db = new mod_db();
$conexion = $db->getConexion();

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
        CASE WHEN c.empleado_activo = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
        c.fecha_registro,
        ts.Nombre  AS tipo_sangre,
        sx.nombre  AS sexo,
        cr.Nombre  AS ruta,
        oc.OCUPACION   AS ocupacion,
        te.Nombre  AS tipo_empleado,
        pl.salario,
        pl.fecha_inicio,
        pl.fecha_fin
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

$stmt = $conexion->prepare($sql);
$stmt->execute();

// Crear el Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Colaboradores');

// Encabezados
$encabezados = [
    'ID',
    'Identidad',
    'Nombre',
    'Apellido',
    'Edad',
    'Tipo Sangre',
    'Sexo',
    'Nacionalidad',
    'Ruta',
    'Correo',
    'Celular',
    'Estado',
    'Ocupación',
    'Tipo Empleado',
    'Salario',
    'Fecha Inicio',
    'Fecha Fin',
    'Fecha Registro'
];

$sheet->fromArray($encabezados, null, 'A1');

// Estilo del encabezado
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1e40af'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
    ],
];
$lastCol = 'R'; // columna R = 18 columnas
$sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(20);

// Llenar filas con fetchObject
$fila = 2;
while ($row = $stmt->fetchObject()) {
    $sheet->fromArray([
        $row->id_colaborador,
        $row->identidad,
        $row->nombre,
        $row->apellido,
        $row->edad,
        $row->tipo_sangre ?? '',
        $row->sexo ?? '',
        $row->nacionalidad,
        $row->ruta ?? '',
        $row->correo,
        $row->celular,
        $row->estado,
        $row->ocupacion ?? '',
        $row->tipo_empleado ?? '',
        $row->salario ?? '',
        $row->fecha_inicio ?? '',
        $row->fecha_fin ?? '',
        $row->fecha_registro,
    ], null, "A{$fila}");
    $fila++;
}

// Autoajustar ancho de columnas
foreach (range('A', $lastCol) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$writer->save($outputFile);

// Enviar como descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_colaboradores.xlsx"');
header('Cache-Control: max-age=0');
readfile($outputFile);
exit;
