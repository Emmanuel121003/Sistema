<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id_materia = $_GET['id'];

    // Consultar la materia a editar
    $sql = "SELECT * FROM materias WHERE id_materia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_materia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $materia = $result->fetch_assoc();
    } else {
        echo "Materia no encontrada.";
        exit;
    }

    // Procesar la edición
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $id_maestro = $_POST['id_maestro'];

        // Actualizar la materia
        $sql_update = "UPDATE materias SET nombre = ?, id_maestro = ? WHERE id_materia = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('sii', $nombre, $id_maestro, $id_materia);
        $stmt_update->execute();

        header('Location: gestionar_materias.php');
        exit;
    }
} else {
    echo "ID de materia no válido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Materia</title>
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
        <h1>Editar Materia</h1>
        <form action="editar_materia.php?id=<?= $id_materia ?>" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Materia:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= $materia['nombre'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="id_maestro" class="form-label">Maestro Asignado:</label>
                <select name="id_maestro" id="id_maestro" class="form-select" required>
                    <option value="" disabled selected>Selecciona un maestro</option>
                    <?php
                    // Consultar maestros disponibles
                    $sql_maestros = "SELECT id_usuario, nombre FROM usuarios WHERE rol = 'maestro'";
                    $maestros_result = $conn->query($sql_maestros);
                    while ($maestro = $maestros_result->fetch_assoc()):
                    ?>
                        <option value="<?= $maestro['id_usuario'] ?>" <?= $materia['id_maestro'] === $maestro['id_usuario'] ? 'selected' : '' ?>>
                            <?= $maestro['nombre'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Materia</button>
        </form>
    </div>
</body>
</html>