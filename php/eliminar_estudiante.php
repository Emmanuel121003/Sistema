<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso como administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id_estudiante = $_GET['id'];

    // Eliminar al estudiante de las inscripciones
    $sql = "DELETE FROM inscripciones WHERE id_estudiante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_estudiante);
    $stmt->execute();

    // Eliminar al estudiante de la tabla 'estudiantes'
    $sql_delete = "DELETE FROM estudiantes WHERE id_estudiante = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $id_estudiante);
    $stmt_delete->execute();

    // Redirigir después de la eliminación
    header('Location: gestionar_estudiantes.php');
    exit;
} else {
    echo "ID de estudiante no válido.";
    exit;
}
?>
