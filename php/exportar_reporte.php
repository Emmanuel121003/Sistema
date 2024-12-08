<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como estudiante
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: /login.html');
    exit;
}

$id_estudiante = $_SESSION['id_usuario'];

// Consultar datos del estudiante
$sql_reporte = "SELECT 
                    materias.nombre AS materia,
                    calificaciones.asistencia,
                    calificaciones.participacion,
                    calificaciones.trabajos,
                    calificaciones.practicas,
                    calificaciones.exposiciones,
                    calificaciones.examen,
                    calificaciones.proyecto,
                    calificaciones.calificacion_final
                FROM calificaciones
                JOIN materias ON calificaciones.id_materia = materias.id_materia
                WHERE calificaciones.id_estudiante = ?";
$stmt = $conn->prepare($sql_reporte);
$stmt->bind_param('i', $id_estudiante);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si hay datos para exportar
if ($result->num_rows > 0) {
    // Configurar encabezados para la descarga
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reporte_estudiante.csv"');

    // Abrir salida para el archivo
    $output = fopen('php://output', 'w');

    // Encabezados del archivo CSV
    fputcsv($output, [
        'Materia',
        'Asistencia',
        'Participación',
        'Trabajos',
        'Prácticas',
        'Exposiciones',
        'Examen',
        'Proyecto',
        'Calificación Final'
    ]);

    // Llenar datos
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['materia'],
            $row['asistencia'],
            $row['participacion'],
            $row['trabajos'],
            $row['practicas'],
            $row['exposiciones'],
            $row['examen'],
            $row['proyecto'],
            number_format($row['calificacion_final'], 2)
        ]);
    }

    // Cerrar la salida
    fclose($output);
    exit;
} else {
    echo "No hay datos para exportar.";
}
?>
