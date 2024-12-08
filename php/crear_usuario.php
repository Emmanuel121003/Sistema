<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];
    $grupo = $_POST['grupo'] ?? null;

    // Crear usuario
    $sql_usuario = "INSERT INTO usuarios (nombre, usuario, contraseña, rol) VALUES (?, ?, ?, ?)";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param('ssss', $nombre, $usuario, $contraseña, $rol);
    $stmt_usuario->execute();

    // Si es estudiante, crear el registro asociado en `estudiantes`
    if ($rol === 'estudiante') {
        $id_usuario = $conn->insert_id;
        $sql_estudiante = "INSERT INTO estudiantes (nombre, grupo, id_usuario) VALUES (?, ?, ?)";
        $stmt_estudiante = $conn->prepare($sql_estudiante);
        $stmt_estudiante->bind_param('ssi', $nombre, $grupo, $id_usuario);
        $stmt_estudiante->execute();
    }

    header('Location: gestionar_usuarios.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
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
        <h1>Crear Usuario</h1>
        <form action="crear_usuario.php" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre Completo:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario:</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña:</label>
                <input type="password" name="contraseña" id="contraseña" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol:</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="maestro">Maestro</option>
                    <option value="administrador">Administrador</option>
                    <option value="estudiante">Estudiante</option>
                </select>
            </div>
            <div class="mb-3" id="grupoField" style="display: none;">
                <label for="grupo" class="form-label">Grupo (solo para estudiantes):</label>
                <input type="text" name="grupo" id="grupo" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </form>
        <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-danger btn-lg">Regresar</a>
            </div>
    </div>
    <script>
        const rolSelect = document.getElementById('rol');
        const grupoField = document.getElementById('grupoField');
        rolSelect.addEventListener('change', function () {
            grupoField.style.display = this.value === 'estudiante' ? 'block' : 'none';
        });
    </script>

</body>
</html>
