<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como estudiante
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: login.php');
    exit;
}

$id_estudiante = $_SESSION['id_usuario'];

// Consultar materias inscritas y calificaciones
$sql_materias = "SELECT 
    estudiantes.nombre AS estudiante,
    estudiantes.grupo,
    materias.nombre AS materia,
    calificaciones.asistencia,
    calificaciones.participacion,
    calificaciones.trabajos,
    calificaciones.practicas,
    calificaciones.exposiciones,
    calificaciones.examen,
    calificaciones.proyecto,
    calificaciones.calificacion_final
FROM usuarios
JOIN estudiantes ON usuarios.id_usuario = estudiantes.id_usuario
JOIN calificaciones ON estudiantes.id_estudiante = calificaciones.id_estudiante
JOIN materias ON calificaciones.id_materia = materias.id_materia
WHERE usuarios.id_usuario = ?";
$stmt = $conn->prepare($sql_materias);
$stmt->bind_param('i', $id_estudiante);
$stmt->execute();
$result = $stmt->get_result();

// Consultar notificaciones
$sql_notificaciones = "SELECT mensaje, fecha FROM notificaciones WHERE id_estudiante = ? AND leido = FALSE";
$stmt_notificaciones = $conn->prepare($sql_notificaciones);
$nombre_estudiante = $_SESSION['nombre'] ?? 'Usuario';
$stmt_notificaciones->bind_param('i', $id_estudiante);
$stmt_notificaciones->execute();
$result_notificaciones = $stmt_notificaciones->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiante</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/styles2.css">
    <link rel="icon" href="../img/logo.ico" type="image/x-icon">
</head>
<body>
<header>
        <nav class="navbar navbar-expand-lg navbar-light bg-ligh">
            <div class="container-fluid">
                <a class="navbar-brand" href="estudiante_dashboard.php"><img src="../img/logo.png" class="img-fluid" style="height: 50px;"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <div >
        <h1 class="text-center">Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h1>

            <h4 class="text-center">Tus Materias y Calificaciones</h4>
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-striped table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Asistencia</th>
                            <th>Participación</th>
                            <th>Trabajos</th>
                            <th>Prácticas</th>
                            <th>Exposiciones</th>
                            <th>Examen</th>
                            <th>Proyecto</th>
                            <th>Calificación Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['materia'] ?></td>
                                <td><?= $row['asistencia'] ?></td>
                                <td><?= $row['participacion'] ?></td>
                                <td><?= $row['trabajos'] ?></td>
                                <td><?= $row['practicas'] ?></td>
                                <td><?= $row['exposiciones'] ?></td>
                                <td><?= $row['examen'] ?></td>
                                <td><?= $row['proyecto'] ?></td>
                                <td><?= number_format($row['calificacion_final'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-warning">No tienes calificaciones registradas.</p>
            <?php endif; ?>

            <div class="text-center mt-4">
            <a href="exportar_reporte.php" class="btn btn-success">Descargar Reporte</a>
                
            </div>
        </div>
    </div>
</body>
</html>
