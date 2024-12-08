<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como maestro
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'maestro') {
    header('Location: login.php');
    exit;
}

$id_maestro = $_SESSION['id_usuario'];

// Procesar formulario para exportar datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar'])) {
    $id_materia = $_POST['id_materia'];
    $id_grupo = $_POST['id_grupo'];

    // Consultar calificaciones para exportar
    $sql = "
        SELECT e.nombre AS estudiante, c.asistencia, c.participacion, c.trabajos, c.practicas, 
               c.exposiciones, c.examen, c.proyecto, c.calificacion_final
        FROM calificaciones c
        JOIN estudiantes e ON c.id_estudiante = e.id_estudiante
        WHERE c.id_materia = ? AND EXISTS (
            SELECT 1 
            FROM inscripciones i 
            WHERE i.id_estudiante = c.id_estudiante AND i.id_grupo = ?
        )
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id_materia, $id_grupo);
    $stmt->execute();
    $result = $stmt->get_result();

    // Generar CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reporte_calificaciones.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Estudiante', 'Asistencia', 'Participación', 'Trabajos', 'Prácticas', 'Exposiciones', 'Examen', 'Proyecto', 'Calificación Final']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Consultar materias impartidas por el maestro
$sql_materias = "SELECT id_materia, nombre FROM materias WHERE id_maestro = ?";
$stmt_materias = $conn->prepare($sql_materias);
$stmt_materias->bind_param('i', $id_maestro);
$stmt_materias->execute();
$materias_result = $stmt_materias->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Calificaciones</title>
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
        <h1 class="text-center">Reportes de Calificaciones</h1>

        <!-- Selección de Materia -->
        <form action="reportes_maestro.php" method="POST">
            <div class="mb-3">
                <label for="id_materia" class="form-label">Materia:</label>
                <select name="id_materia" id="id_materia" class="form-select" required>
                    <option value="" disabled selected>Seleccione una materia</option>
                    <?php while ($materia = $materias_result->fetch_assoc()): ?>
                        <option value="<?= $materia['id_materia'] ?>"><?= $materia['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Selección de Grupo -->
            <div class="mb-3" id="grupo_div" style="display: none;">
                <label for="id_grupo" class="form-label">Grupo:</label>
                <select name="id_grupo" id="id_grupo" class="form-select" required>
                    <option value="" disabled selected>Seleccione un grupo</option>
                </select>
            </div>

            <button type="submit" name="exportar" class="btn btn-success w-100 mt-3">Exportar Reporte</button>
        </form>
    </div>

    <div class="text-center mt-4">
        <a href="maestro_dashboard.php" class="btn btn-danger btn-lg">Regresar</a>
    </div>

    <script>
        // Cargar grupos dinámicamente según la materia seleccionada
        document.getElementById('id_materia').addEventListener('change', function() {
            const materiaId = this.value;

            if (materiaId) {
                fetch('get_grupos.php?materia_id=' + materiaId)
                    .then(response => response.json())
                    .then(data => {
                        const grupoSelect = document.getElementById('id_grupo');
                        grupoSelect.innerHTML = '<option value="" disabled selected>Seleccione un grupo</option>';
                        data.grupos.forEach(grupo => {
                            const option = document.createElement('option');
                            option.value = grupo.id_grupo;
                            option.textContent = grupo.nombre;
                            grupoSelect.appendChild(option);
                        });
                        document.getElementById('grupo_div').style.display = 'block';
                    })
                    .catch(error => console.error('Error al cargar los grupos:', error));
            }
        });
    </script>
</body>

</html>
