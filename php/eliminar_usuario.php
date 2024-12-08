<?php
session_start();
include 'db_connection.php';

// Verificar si el usuario tiene acceso
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Eliminar el usuario (esto también elimina al estudiante si aplica)
    $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();

    header('Location: gestionar_usuarios.php');
    exit;
} else {
    echo "ID de usuario no válido.";
}
?>
