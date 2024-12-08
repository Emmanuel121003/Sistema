<?php
include 'db_connection.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="calificaciones_finales.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Estudiante', 'Materia', 'Calificacion Final']);

$sql = "SELECT estudiantes.nombre, materias.nombre AS materia, 
       (calificaciones.ser * 0.2 + calificaciones.saber_hacer * 0.5 + calificaciones.saber * 0.3) AS final 
       FROM calificaciones 
       JOIN estudiantes ON calificaciones.id_estudiante = estudiantes.id_estudiante 
       JOIN materias ON calificaciones.id_materia = materias.id_materia";
$result = $conn->query($sql);

while ($data = $result->fetch_assoc()) {
    fputcsv($output, [$data['nombre'], $data['materia'], number_format($data['final'], 2)]);
}

fclose($output);
?>