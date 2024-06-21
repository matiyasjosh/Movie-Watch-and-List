<?php
    session_start();    

    include_once './dbConnect.php';
    $watched_movies = [];
    $movie = null;

    // Check if user is logged in
    $user_logged_in = isset($_SESSION['username']);
    if ($user_logged_in) {
        $user = $_SESSION['username'];

        // Fetch the list of movies watched by the user
        try {
            $database = Database::getInstance();
            $conn = $database->getConnection();
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("SELECT movies.id, movies.title, movies.year, movies.poster FROM user_movie 
                                    JOIN movies ON user_movie.movie_id = movies.id 
                                    WHERE user_movie.username = :username");
            $stmt->bindParam(':username', $user);
            $stmt->execute();
            $watched_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    try {
        $database = Database::getInstance();
        $conn = $database->getConnection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL query to count the rows
        $sql = "SELECT COUNT(*) AS total_rows FROM movies";
        
        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);
        
        // Execute the statement
        $stmt->execute();
        
        // Fetch the result as an associative array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get the total count of rows
        $totalRows = $row["total_rows"];

        $stmt = $conn->prepare("SELECT * FROM movies WHERE id = " . rand(1, $totalRows));
        $stmt->execute();
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Output any errors
        echo "Error: " . $e->getMessage();
    }

    // Handle logout
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
        // Destroy the user's session
        session_destroy();
    
        // Redirect the user to the homepage
        header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
        exit;
    }

$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./public/css/style.css">
    <link rel="stylesheet" href="./public/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Acme&family=Briem+Hand:wght@100..900&family=Caveat:wght@400..700&family=Indie+Flower&family=Permanent+Marker&family=Poetsen+One&family=Satisfy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- boxicon -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- <script src="https://kit.fontawesome.com/d5635d350d.js" crossorigin="anonymous"></script> -->


</head>
<body>
    <nav>
        <a href="index.php"><h1>MyMovieList</h1></a>
        <div class="search-bar">
            <input type="text" placeholder="Search For any movie" id="Input">
            <!-- <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button> -->
        </div>

        <?php if (isset($_SESSION['username'])) { ?>
            <input type="checkbox" id="toggle-popup" class="toggle-popup">
            <label for="toggle-popup" class="user-icon"><i class='bx bxs-user bx-md'></i></label>
            <div class="profile-popup">
                <p><?php echo htmlspecialchars($_SESSION['username'])?></p>
                <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <button name="logout">Logout</button>
                </form> 
            </div>
        <?php } else { ?>
            <ul>
                <li><a href="/pages/login.php">Login</a></li>
                <li><a href="/pages/signup.php">Signup</a></li>
            </ul>
        <?php } ?>
    </nav>    
    <div class="fav-container">
        <!-- Items are displayed here -->
    </div>
    <div class="theme">
        <h1>A website to record a list of movies that you've watched</h1>
        <img src="../public/images/home.png">
    </div>

    <div class="image_container">
        <div class="swiper ft-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box">
                    <div class="image_slider">
                        <img src="<?php echo $movie["poster"];?>">
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image_slider">
                        <img src="<?php echo $movie["poster"];?>">
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image_slider">
                        <img src="<?php echo $movie["poster"];?>">
                    </div>
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
    
    <div class="pitch">
        <h1>About MyMovieList</h1>
        <div class="pitch-content">
            <p>platform for film enthusiasts. On the home page, you can log films to keep track of every movie you’ve ever watched or start from the day you joined.</p>
        </div>
        <div class="pitch-content">
            <p>You can also show love by liking your favorite films, lists, and reviews. If you’re feeling inspired, write reviews and share your thoughts, or follow friends to read theirs. Use the five-star rating system (with halves) to record and share your reactions to films.  </p>
        </div>
        <div class="pitch-content">
            <p>Additionally, you can keep a film-watching diary (upgrade to Pro for comprehensive stats), compile and share lists of films on any topic, and maintain a watchlist of films you want to see.</p>
        </div>
        <div class="pitch-content">
            <p>It’s a great place to connect with other film lovers and discover new movies! 🎬 You can get started for free on the Letterboxd website.</p>
        </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="./scrpt.js"></script>
</body>
</html>

