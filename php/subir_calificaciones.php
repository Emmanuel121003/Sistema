<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como maestro
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'maestro') {
    header('Location: login.php');
    exit;
}

$id_maestro = $_SESSION['id_usuario'];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_estudiante = $_POST['id_estudiante'];
    $id_materia = $_POST['id_materia'];
    $asistencia = $_POST['asistencia'];
    $participacion = $_POST['participacion'];
    $trabajos = $_POST['trabajos'];
    $practicas = $_POST['practicas'];
    $exposiciones = $_POST['exposiciones'];
    $examen = $_POST['examen'];
    $proyecto = $_POST['proyecto'];

    // Calcular la calificación final
    $ser = ($asistencia + $participacion) / 2 * .20;
    $saber_hacer = ($trabajos + $practicas + $exposiciones) / 3 * .50;
    $saber = ($examen + $proyecto) / 2 * .30;
    $calificacion_final = $ser + $saber_hacer + $saber;

    // Insertar o actualizar las calificaciones
    $sql = "INSERT INTO calificaciones (id_estudiante, id_materia, asistencia, participacion, trabajos, practicas, exposiciones, examen, proyecto, calificacion_final)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            asistencia = VALUES(asistencia),
            participacion = VALUES(participacion),
            trabajos = VALUES(trabajos),
            practicas = VALUES(practicas),
            exposiciones = VALUES(exposiciones),
            examen = VALUES(examen),
            proyecto = VALUES(proyecto),
            calificacion_final = VALUES(calificacion_final)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iidddddddd', $id_estudiante, $id_materia, $asistencia, $participacion, $trabajos, $practicas, $exposiciones, $examen, $proyecto, $calificacion_final);
    $stmt->execute();

    // Notificación al estudiante
    $mensaje = "Se han actualizado tus calificaciones en la materia $id_materia.";
    $sql_notificacion = "INSERT INTO notificaciones (id_estudiante, mensaje) VALUES (?, ?)";
    $stmt_notificacion = $conn->prepare($sql_notificacion);
    $stmt_notificacion->bind_param('is', $id_estudiante, $mensaje);
    $stmt_notificacion->execute();
}

// Obtener las materias asignadas al maestro
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
    <title>Subir Calificaciones</title>
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
            <h1 class="text-center">Subir Calificaciones</h1>
            <form action="subir_calificaciones.php" method="POST" id="form_calificaciones">
                <!-- Materias -->
                <div class="mb-3">
                    <label for="id_materia" class="form-label">Materia:</label>
                    <select name="id_materia" id="id_materia" class="form-select" required>
                        <option value="" disabled selected>Selecciona una Materia</option>
                        <?php while ($materia = $materias_result->fetch_assoc()): ?>
                            <option value="<?= $materia['id_materia'] ?>">
                                <?= $materia['nombre'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Grupos -->
                <div class="mb-3" id="grupo_div" style="display: none;">
                    <label for="id_grupo" class="form-label">Grupo:</label>
                    <select id="id_grupo" name="id_grupo" class="form-select" required>
                        <option value="" disabled selected>Selecciona un grupo</option>
                    </select>
                </div>

                <!-- Estudiantes -->
                <div class="mb-3" id="estudiantes_div" style="display: none;">
                    <label for="id_estudiante" class="form-label">Estudiante:</label>
                    <select id="id_estudiante" name="id_estudiante" class="form-select" required>
                        <option value="" disabled selected>Selecciona un estudiante</option>
                    </select>
                </div>

                <!-- Calificaciones -->
                <div class="mb-3">
                    <label for="asistencia" class="form-label">Asistencia:</label>
                    <input type="number" id="asistencia" name="asistencia" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="participacion" class="form-label">Participación:</label>
                    <input type="number" id="participacion" name="participacion" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="trabajos" class="form-label">Trabajos:</label>
                    <input type="number" id="trabajos" name="trabajos" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="practicas" class="form-label">Prácticas:</label>
                    <input type="number" id="practicas" name="practicas" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="exposiciones" class="form-label">Exposiciones:</label>
                    <input type="number" id="exposiciones" name="exposiciones" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="examen" class="form-label">Examen:</label>
                    <input type="number" id="examen" name="examen" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="proyecto" class="form-label">Proyecto:</label>
                    <input type="number" id="proyecto" name="proyecto" class="form-control" required>
                </div>

                <!-- Mostrar Calificación Final -->
                <div class="mb-3">
                    <label for="calificacion_final" class="form-label">Calificación Final:</label>
                    <span id="calificacion_final" class="form-control-plaintext">0</span>
                </div>

                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
        <div class="mt-3">
            <a href="maestro_dashboard.php" class="btn btn-danger btn-lg">Regresar</a>
        </div>
    </div>

    <script>
        // Función para obtener los grupos relacionados con la materia seleccionada
        document.getElementById('id_materia').addEventListener('change', function() {
            let materiaId = this.value;
            if (materiaId) {
                fetch('get_grupos.php?materia_id=' + materiaId)
                    .then(response => response.json())
                    .then(data => {
                        let grupoSelect = document.getElementById('id_grupo');
                        grupoSelect.innerHTML = '<option value="" disabled selected>Selecciona un grupo</option>';
                        data.grupos.forEach(grupo => {
                            let option = document.createElement('option');
                            option.value = grupo.id_grupo;
                            option.textContent = grupo.nombre;
                            grupoSelect.appendChild(option);
                        });
                        document.getElementById('grupo_div').style.display = 'block';
                    });
            }
        });

        // Función para obtener los estudiantes relacionados con el grupo seleccionado
        document.getElementById('id_grupo').addEventListener('change', function() {
            let grupoId = this.value;
            let materiaId = document.getElementById('id_materia').value;
            if (grupoId) {
                fetch('get_estudiantes.php?grupo_id=' + grupoId + '&materia_id=' + materiaId)
                    .then(response => response.json())
                    .then(data => {
                        let estudianteSelect = document.getElementById('id_estudiante');
                        estudianteSelect.innerHTML = '<option value="" disabled selected>Selecciona un estudiante</option>';
                        data.estudiantes.forEach(estudiante => {
                            let option = document.createElement('option');
                            option.value = estudiante.id_estudiante;
                            option.textContent = estudiante.nombre;
                            estudianteSelect.appendChild(option);
                        });
                        document.getElementById('estudiantes_div').style.display = 'block';
                    });
            }
        });

        // Calcular la calificación final
        function calcularCalificacion() {
            const asistencia = parseFloat(document.getElementById('asistencia').value) || 0;
            const participacion = parseFloat(document.getElementById('participacion').value) || 0;
            const trabajos = parseFloat(document.getElementById('trabajos').value) || 0;
            const practicas = parseFloat(document.getElementById('practicas').value) || 0;
            const exposiciones = parseFloat(document.getElementById('exposiciones').value) || 0;
            const examen = parseFloat(document.getElementById('examen').value) || 0;
            const proyecto = parseFloat(document.getElementById('proyecto').value) || 0;

            const ser = (asistencia + participacion) / 2 * .20;
            const saberHacer = (trabajos + practicas + exposiciones) / 3 * .50;
            const saber = (examen + proyecto) / 2 * .30;

            const calificacionFinal = ser + saberHacer + saber;
            document.getElementById('calificacion_final').textContent = calificacionFinal.toFixed(2);
        }

        // Llamar a la función de cálculo cada vez que se modifiquen los campos
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', calcularCalificacion);
        });
    </script>
</body>

</html>
