<?php
header('Content-Type: application/json');
session_start();

// if (!isset($_SESSION['username'])) {
//     echo json_encode(["error" => "User not logged in"]);
//     exit();
// }

include_once '../dbConnect.php';

if (isset($_GET['query'])) {
    $search_query = $_GET['query'];
    
    try {
        $database = Database::getInstance();
        $conn = $database->getConnection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM movies WHERE title LIKE :search_query");
        $search_query_param = "%" . $search_query . "%";
        $stmt->bindParam(':search_query', $search_query_param);
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['movies' => $movies]);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }

    $conn = null;
}
?>
