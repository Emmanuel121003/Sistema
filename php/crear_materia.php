<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $id_maestro = $_POST['id_maestro'];
    $grupos_seleccionados = $_POST['grupos']; // Array de grupos seleccionados

    // Insertar la nueva materia
    $sql = "INSERT INTO materias (nombre, id_maestro) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $nombre, $id_maestro);
    $stmt->execute();
    
    // Obtener el ID de la materia recién creada
    $id_materia = $conn->insert_id;

    // Insertar las relaciones entre materia y grupos
    foreach ($grupos_seleccionados as $id_grupo) {
        $sql_grupos = "INSERT INTO materias_grupos (id_materia, id_grupo) VALUES (?, ?)";
        $stmt_grupos = $conn->prepare($sql_grupos);
        $stmt_grupos->bind_param('ii', $id_materia, $id_grupo);
        $stmt_grupos->execute();
    }

    header('Location: gestionar_materias.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Materia</title>
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
        <h1>Crear Materia</h1>
        <form action="crear_materia.php" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Materia:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="id_maestro" class="form-label">ID Maestro Asignado:</label>
                <input type="number" name="id_maestro" id="id_maestro" class="form-control" required>
            </div>

            <!-- Selección de Grupos -->
            <div class="mb-3">
                <label for="grupos" class="form-label">Grupos Asignados:</label>
                <select name="grupos[]" id="grupos" class="form-select" multiple required>
                    <?php
                    // Obtener todos los grupos disponibles
                    $sql_grupos = "SELECT id_grupo, nombre FROM grupos";
                    $result_grupos = $conn->query($sql_grupos);

                    // Mostrar los grupos como opciones
                    while ($grupo = $result_grupos->fetch_assoc()) {
                        echo "<option value='{$grupo['id_grupo']}'>{$grupo['nombre']}</option>";
                    }
                    ?>
                </select>
                <small class="form-text text-muted">Seleccione los grupos en los que se impartirá esta materia.</small>
            </div>

            <button type="submit" class="btn btn-primary">Crear Materia</button>
        </form>
    </div>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-danger btn-lg">Regresar</a>
    </div>
</body>

</html>
