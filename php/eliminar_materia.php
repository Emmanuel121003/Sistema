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

    // Eliminar la materia de la base de datos
    $sql = "DELETE FROM materias WHERE id_materia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_materia);
    $stmt->execute();

    // Redirigir a la página de gestión de materias
    header('Location: gestionar_materias.php');
    exit;
} else {
    echo "ID de materia no válido.";
    exit;
}
?>
