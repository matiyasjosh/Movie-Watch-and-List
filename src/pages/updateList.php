<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

include_once '../dbConnect.php';

$database = Database::getInstance();

if (isset($_POST['movieId']) && isset($_POST['action'])) {
    $movieId = intval($_POST['movieId']);
    $action = $_POST['action'];
    $username = $_SESSION['username'];
    $currentDate = date('Y-m-d');

    try {
        $conn = $database->getConnection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action == 'watched') {
            $stmt = $conn->prepare("INSERT INTO user_movie (username, movie_id, watched_date) VALUES (:username, :movie_id, :watched_date) ON DUPLICATE KEY UPDATE watched_date = :watched_date");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':movie_id', $movieId);
            $stmt->bindParam(':watched_date', $currentDate);
            $stmt->execute();
            echo json_encode(["success" => "Movie added to watched list"]);
        } elseif ($action == 'dropped') {
            $stmt = $conn->prepare("DELETE FROM user_movie WHERE username = :username AND movie_id = :movie_id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':movie_id', $movieId);
            $stmt->execute();
            echo json_encode(["success" => "Movie removed from list"]);
        } else {
            echo json_encode(["error" => "Invalid action"]);
        }

    } catch(PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }

    $conn = null;
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
