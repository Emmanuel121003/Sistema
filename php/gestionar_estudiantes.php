<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: login.php');
    exit;
}

// Consultar todos los estudiantes
$sql = "SELECT id_estudiante, nombre FROM estudiantes";
$result = $conn->query($sql);

// Consultar todas las materias
$sql_materias = "SELECT id_materia, nombre FROM materias";
$materias_result = $conn->query($sql_materias);

// Consultar todos los grupos
$sql_grupos = "SELECT id_grupo, nombre FROM grupos";
$grupos_result = $conn->query($sql_grupos);

// Procesar la inscripción
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_estudiante = $_POST['id_estudiante'];
    $id_materia = $_POST['id_materia'];
    $id_grupo = $_POST['id_grupo'];

    // Insertar inscripción del estudiante
    $sql_inscripcion = "INSERT INTO inscripciones (id_estudiante, id_materia, id_grupo) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_inscripcion);
    $stmt->bind_param('iii', $id_estudiante, $id_materia, $id_grupo);
    $stmt->execute();

    // Redirigir después de la inscripción
    header('Location: gestionar_estudiantes.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes</title>
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
                <a class="navbar-brand" href="admin_dashboard.php"><img src="../img/logo.png" class="img-fluid" style="height: 50px;"></a>
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
        <h1>Gestión de Estudiantes</h1>
        <a href="crear_estudiante.php" class="btn btn-success mb-3">Crear Estudiante</a>

        <h2>Inscripción de Estudiantes a Materias</h2>
        <form action="gestionar_estudiantes.php" method="POST">
            <div class="mb-3">
                <label for="id_estudiante" class="form-label">Estudiante:</label>
                <select name="id_estudiante" id="id_estudiante" class="form-select" required>
                    <option value="" disabled selected>Selecciona un Estudiante</option>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?= $row['id_estudiante'] ?>"><?= $row['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_materia" class="form-label">Materia:</label>
                <select name="id_materia" id="id_materia" class="form-select" required>
                    <option value="" disabled selected>Selecciona una Materia</option>
                    <?php while ($materia = $materias_result->fetch_assoc()): ?>
                        <option value="<?= $materia['id_materia'] ?>"><?= $materia['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_grupo" class="form-label">Grupo:</label>
                <select name="id_grupo" id="id_grupo" class="form-select" required>
                    <option value="" disabled selected>Selecciona un Grupo</option>
                    <?php while ($grupo = $grupos_result->fetch_assoc()): ?>
                        <option value="<?= $grupo['id_grupo'] ?>"><?= $grupo['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Inscribir Estudiante</button>
        </form>

        <h2 class="mt-5">Lista de Estudiantes Inscritos</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Estudiante</th>
                    <th>Nombre</th>
                    <th>Materias Inscritas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consultar estudiantes inscritos en materias
                $sql_inscritos = "
                    SELECT estudiantes.id_estudiante, estudiantes.nombre, GROUP_CONCAT(materias.nombre) AS materias
                    FROM estudiantes
                    LEFT JOIN inscripciones ON estudiantes.id_estudiante = inscripciones.id_estudiante
                    LEFT JOIN materias ON inscripciones.id_materia = materias.id_materia
                    GROUP BY estudiantes.id_estudiante";
                $inscritos_result = $conn->query($sql_inscritos);

                while ($row = $inscritos_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_estudiante'] ?></td>
                        <td><?= $row['nombre'] ?></td>
                        <td><?= $row['materias'] ?></td>
                        <td>
                            <a href="eliminar_estudiante.php?id=<?= $row['id_estudiante'] ?>" class="btn btn-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-danger btn-lg">Regresar</a>
            </div>
    </div>
    </div>
</body>
</html>
