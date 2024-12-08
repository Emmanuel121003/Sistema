<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Consultar el usuario a editar
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.usuario, usuarios.rol, estudiantes.grupo
            FROM usuarios
            LEFT JOIN estudiantes ON usuarios.id_usuario = estudiantes.id_usuario
            WHERE usuarios.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
    } else {
        echo "Usuario no encontrado.";
        exit;
    }

    // Procesar la edición
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $usuario = $_POST['usuario'];
        $contraseña = !empty($_POST['contraseña']) ? password_hash($_POST['contraseña'], PASSWORD_BCRYPT) : $usuario['contraseña'];
        $rol = $_POST['rol'];
        $grupo = $_POST['grupo'] ?? null;

        // Actualizar la información del usuario
        $sql_update = "UPDATE usuarios SET nombre = ?, usuario = ?, contraseña = ?, rol = ? WHERE id_usuario = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ssssi', $nombre, $usuario, $contraseña, $rol, $id_usuario);
        $stmt_update->execute();

        // Si es estudiante, actualizar también el grupo
        if ($rol === 'estudiante') {
            $sql_estudiante = "INSERT INTO estudiantes (nombre, grupo, id_usuario) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE grupo = ?";
            $stmt_estudiante = $conn->prepare($sql_estudiante);
            $stmt_estudiante->bind_param('sssi', $nombre, $grupo, $id_usuario, $grupo);
            $stmt_estudiante->execute();
        }

        header('Location: gestionar_usuarios.php');
        exit;
    }
} else {
    echo "ID de usuario no válido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
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
        <h1>Editar Usuario</h1>
        <form action="editar_usuario.php?id=<?= $id_usuario ?>" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre Completo:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= $usuario['nombre'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario:</label>
                <input type="text" name="usuario" id="usuario" class="form-control" value="<?= $usuario['usuario'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña (dejar en blanco para no cambiar):</label>
                <input type="password" name="contraseña" id="contraseña" class="form-control">
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol:</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="maestro" <?= $usuario['rol'] === 'maestro' ? 'selected' : '' ?>>Maestro</option>
                    <option value="administrador" <?= $usuario['rol'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                    <option value="estudiante" <?= $usuario['rol'] === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                </select>
            </div>
            <div class="mb-3" id="grupoField" style="display: <?= $usuario['rol'] === 'estudiante' ? 'block' : 'none' ?>;">
                <label for="grupo" class="form-label">Grupo:</label>
                <input type="text" name="grupo" id="grupo" class="form-control" value="<?= $usuario['grupo'] ?>">
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        </form>
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
