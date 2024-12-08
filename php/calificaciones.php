<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario es maestro
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'maestro') {
    header('Location: /index.php');
    exit;
}

// Consultar las materias asignadas al maestro
$id_maestro = $_SESSION['id_usuario'];
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
    <title>Subir Calificaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/styles2.css">
    <link rel="icon" href="../img/logo.ico" type="image/x-icon">
    <script>
        function calcularFinal() {
            let asistencia = parseFloat(document.getElementById('asistencia').value) || 0;
            let participacion = parseFloat(document.getElementById('participacion').value) || 0;
            let trabajos = parseFloat(document.getElementById('trabajos').value) || 0;
            let practicas = parseFloat(document.getElementById('practicas').value) || 0;
            let examen = parseFloat(document.getElementById('examen').value) || 0;

            // Validación básica para asegurar que las calificaciones no excedan los límites
            if (asistencia > 100 || participacion > 100 || trabajos > 100 || practicas > 100 || examen > 100) {
                alert("Las calificaciones no pueden ser mayores a 100.");
                return;
            }

            let ser = ((asistencia * participacion) /2) *0.2;
            let saberHacer = ((trabajos + practicas) / 2) * 0.50;
            let saber = ((examen + proyecto) / 2) * 0.30;

            let calificacionFinal = ser + saberHacer + saber;

            document.getElementById('resultado-final').innerText = `Calificación Final: ${calificacionFinal.toFixed(2)}%`;
        }

        function prepararExportacion() {
            let asistencia = parseFloat(document.getElementById('asistencia').value) || 0;
            let participacion = parseFloat(document.getElementById('participacion').value) || 0;
            let trabajos = parseFloat(document.getElementById('trabajos').value) || 0;
            let practicas = parseFloat(document.getElementById('practicas').value) || 0;
            let examen = parseFloat(document.getElementById('examen').value) || 0;

            // Validación antes de exportar
            if (asistencia > 100 || participacion > 100 || trabajos > 100 || practicas > 100 || examen > 100) {
                alert("Las calificaciones no pueden ser mayores a 100.");
                return;
            }

            let ser = (asistencia * participacion * 0.20) / 100;
            let saberHacer = ((trabajos + practicas) / 2) * 0.50;
            let saber = examen * 0.30;
            let calificacionFinal = ser + saberHacer + saber;

            document.getElementById('calificaciones').value = JSON.stringify({
                asistencia,
                participacion,
                trabajos,
                practicas,
                examen,
                ser: ser.toFixed(2),
                saberHacer: saberHacer.toFixed(2),
                saber: saber.toFixed(2),
                calificacionFinal: calificacionFinal.toFixed(2)
            });
        }
    </script>
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
    <h1>Subir Calificaciones</h1>
    <form action="exportar.php" method="POST">
        <label for="materia">Materia:</label>
        <select name="materia" id="materia" class="form-select" required>
            <option value="" disabled selected>Selecciona una materia</option>
            <?php while ($materia = $materias_result->fetch_assoc()): ?>
                <option value="<?= $materia['id_materia'] ?>"><?= $materia['nombre'] ?></option>
            <?php endwhile; ?>
        </select>

        <h3 class="mt-4">Ser (20%)</h3>
        Asistencia (%): <input type="number" id="asistencia" min="0" max="100" class="form-control" required>
        Participación (%): <input type="number" id="participacion" min="0" max="100" class="form-control" required>

        <h3 class="mt-4">Saber Hacer (50%)</h3>
        Total de Trabajos: <input type="number" id="trabajos" min="0" class="form-control" required>
        Total de Prácticas: <input type="number" id="practicas" min="0" class="form-control" required>

        <h3 class="mt-4">Saber (30%)</h3>
        Examen: <input type="number" id="examen" min="0" max="100" class="form-control" required>

        <button type="button" class="btn btn-primary mt-3" onclick="calcularFinal()">Calcular Calificación Final</button>
        <p id="resultado-final" class="mt-3 fw-bold">Calificación Final: 0.00%</p>

        <input type="hidden" name="calificaciones" id="calificaciones" value="">
        <button type="submit" class="btn btn-success mt-3" onclick="prepararExportacion()">Exportar Calificaciones</button>
    </form>
</div>

<div class="text-center mt-4">
    <a href="maestro_dashboard.php" class="btn btn-danger btn-lg">Regresar</a>
</div>
</body>
</html>
