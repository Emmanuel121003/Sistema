<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como maestro
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'maestro') {
    header('Location: login.php');
    exit;
}

$id_maestro = $_SESSION['id_usuario'];

// Consultar materias asignadas
$sql_materias = "SELECT id_materia, nombre FROM materias WHERE id_maestro = ?";
$stmt = $conn->prepare($sql_materias);
$stmt->bind_param('i', $id_maestro);
$stmt->execute();
$materias_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maestro</title>
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
                <a class="navbar-brand" href="maestro_dashboard.php"><img src="../img/logo.png" class="img-fluid" style="height: 50px;"></a>
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
        <div>
            <h1 class="text-center">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>

            <h4 class="text-center mt-4">Tus Materias</h4>
            <?php if ($materias_result->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($materia = $materias_result->fetch_assoc()): ?>
                        <li class="list-group-item"><?= $materia['nombre'] ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-center text-warning">No tienes materias asignadas.</p>
            <?php endif; ?>

            <div class="d-flex flex-wrap justify-content-around gap-3 mt-4">
                <a href="subir_calificaciones.php" class="btn btn-primary btn-lg">Subir Calificaciones</a>
                <a href="ver_calificaciones.php" class="btn btn-secondary btn-lg">Ver Calificaciones</a>
                <a href="reportes_maestro.php" class="btn btn-success btn-lg">Generar Reportes</a>
            </div>

        </div>
    </div>
</body>
</html>
