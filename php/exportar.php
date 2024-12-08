<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calificaciones = json_decode($_POST['calificaciones'], true);

    header("Content-Disposition: attachment; filename=calificaciones.xls");
    header("Content-Type: application/vnd.ms-excel");

    echo "Categoría\tCalificación\n";
    echo "Asistencia\t{$calificaciones['asistencia']}%\n";
    echo "Participación\t{$calificaciones['participacion']}%\n";
    echo "Trabajos\t{$calificaciones['trabajos']}\n";
    echo "Prácticas\t{$calificaciones['practicas']}\n";
    echo "Examen\t{$calificaciones['examen']}%\n";
    echo "Ser\t{$calificaciones['ser']}%\n";
    echo "Saber Hacer\t{$calificaciones['saberHacer']}%\n";
    echo "Saber\t{$calificaciones['saber']}%\n";
    echo "Calificación Final\t{$calificaciones['calificacionFinal']}%\n";
}
?>
