<?php
    require '../dbConnect.php';

    $database = Database::getInstance();
    $conn = $database->getConnection();

    session_start();

    if (!isset($_SESSION['admin_name'])) {
        header("Location: ../pages/admin.php");
        exit();
    }           
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <link rel="stylesheet" href="../public/css/upload.css">

    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Acme&family=Briem+Hand:wght@100..900&family=Caveat:wght@400..700&family=Indie+Flower&family=Permanent+Marker&family=Poetsen+One&family=Satisfy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <nav>
        <a href="../index.php"><h1>MyMovieList</h1></a>
              
        <input type="checkbox" id="toggle-popup" class="toggle-popup">
        <label for="toggle-popup" class="user-icon"><i class='bx bxs-user bx-md'></i></label>
        <div class="profile-popup">
            <form action="./logout.php" method="post">
                <p><?php echo htmlspecialchars($_SESSION['admin_name'])?></p>
                <button>Logout</button>
            </form>
        </div>
    </nav>

    <div class="content">
        <h1>Upload Movie</h1>
        <div class="error-content">
            <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $errors = array();
                    $title = $_POST['title'];
                    $year = $_POST['year'];
                    $poster = $_POST['poster'];
                    $file_name = $_FILES['video']['name'];
                    $file_size = $_FILES['video']['size'];
                    $file_tmp = $_FILES['video']['tmp_name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    $extensions = array("mp4", "avi", "mov", "mpeg");

                    if (!in_array($file_ext, $extensions)) {
                        $errors[] = "Extension not allowed, please choose a MP4, AVI, MOV, or MPEG file.";
                    }
                    
                    if ($file_size > 209715200) { // 200MB limit
                        $errors[] = 'File size must be less than 200 MB';
                    }
                    
                    if (empty($errors)) {
                        $upload_directory = 'uploads/';
                        if (!file_exists($upload_directory)) {
                            mkdir($upload_directory, 0777, true);
                        }
                        $file_path = $upload_directory . $file_name;
                        if (move_uploaded_file($file_tmp, $file_path)) {
                            $stmt = $conn->prepare("INSERT INTO movies (title, year, poster, video_path) VALUES (?, ?, ?, ?)");
                            
                            // Execute the statement with the file information
                            if ($stmt->execute([$title, $year, $poster, "/custom-api/" . $file_path])) {
                                echo "The file ". htmlspecialchars(basename($file_name)). " has been uploaded.";
                            } else {
                                echo "Error: Could not execute the query.";
                                print_r($stmt->errorInfo());
                            }
                        } else {
                            echo "Failed to move the uploaded file.";
                        }
                    } else {
                        foreach ($errors as $error) {
                            echo $error . "<br>";
                        }
                    }
                }
            ?>

        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required><br><br>
            
            <label for="year">Year:</label>
            <input type="text" id="year" name="year" required><br><br>

            <label for="poster">Poster URL:</label>
            <input type="text" id="poster" name="poster"><br><br>
            
            <label for="video">Video File:</label>
            <input type="file" id="video" name="video" accept="video/*" required><br><br>
            
            <button type="submit">Upload</button>
        </form>
    </div>

    <footer>
        <div class="footer-container">
            <h1>MyMovieList</h1>
            <ul>
                <li>Watch</li>
                <li>Record</li>
                <li>Aware</li>
                <li>Of what you watched</li>
            </ul>
            <div class="social-media">
                <i class='bx bxl-instagram bx-md'></i>
                <i class='bx bxl-telegram bx-md' ></i>
                <i class='bx bxl-tiktok bx-md' ></i>
            </div>
            
        </div>
    </footer>
</body>
</html>

