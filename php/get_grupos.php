<?php
include 'db_connection.php';

// Verificar si se pasó el ID de la materia
if (isset($_GET['materia_id'])) {
    $materia_id = $_GET['materia_id'];

    // Consultar los grupos asociados a la materia
    $sql = "SELECT g.id_grupo, g.nombre 
            FROM grupos g
            JOIN materias_grupos mg ON g.id_grupo = mg.id_grupo
            WHERE mg.id_materia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $materia_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $grupos = [];
    while ($row = $result->fetch_assoc()) {
        $grupos[] = $row;
    }

    // Devolver los grupos en formato JSON
    header('Content-Type: application/json');
    echo json_encode(['grupos' => $grupos]);
} else {
    echo json_encode(['grupos' => []]);
}
?>