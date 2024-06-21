<?php
include_once '../dbConnect.php';
include "../custom-api/send-email.php";

// Get the token from the URL
$token = $_GET["token"];

// Get a database connection
$database = Database::getInstance();
$conx = $database->getConnection();

// Prepare the SQL statement to select the user by token
$sql = "SELECT * FROM users WHERE token = :token";
$stmt = $conx->prepare($sql);
$stmt->bindParam(":token", $token);

// Execute the statement
$stmt->execute();

// Fetch the user
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user === false) {
    die("token not found");
}

// Prepare the SQL statement to update the user's token to NULL
$sql = "UPDATE users SET token = NULL WHERE id = :id";
$stmt = $conx->prepare($sql);
$stmt->bindParam(":id", $user["id"], PDO::PARAM_INT);

// Execute the statement
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Activated</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>

    <h1>Account Activated</h1>

    <p>Account activated successfully. You can now
       <a href="../pages/login.php">log in</a>.</p>

</body>
</html>
