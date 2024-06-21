<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include_once '../dbConnect.php';
$user =$_SESSION['username'];
$movies = [];

$database = Database::getInstance();
$conn = $database->getConnection();
// Handle remove from watched list form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove'])) {
    $movie_id = $_POST['movie_id'];
    
    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Delete the movie from the user_movie table
        $stmt = $conn->prepare("DELETE FROM user_movie WHERE username = :username AND movie_id = :movie_id");
        $stmt->bindParam(':username', $user);
        $stmt->bindParam(':movie_id', $movie_id);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    // Redirect to avoid resubmitting the form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle add to watched list form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['movie_id']) && isset($_POST['movie_title']) && !isset($_POST['remove'])) {
    $movie_id = $_POST['movie_id'];
    
    try {
        
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM user_movie WHERE username = :username AND movie_id = :movie_id");
        $checkStmt->bindParam(':username', $user);
        $checkStmt->bindParam(':movie_id', $movie_id);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();
        if ($count == 0) {
        // Insert the movie details into the user_movie table
        $stmt = $conn->prepare("INSERT INTO user_movie (username, movie_id, watched_date) VALUES (:username, :movie_id, NOW())");
        $stmt->bindParam(':username', $user);
        $stmt->bindParam(':movie_id', $movie_id);
        $stmt->execute();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    $conn = null;
    exit();

}

// Fetch the list of movies watched by the user
$watched_movies = [];

try {

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT movies.id, movies.title, movies.year, movies.poster FROM user_movie 
                            JOIN movies ON user_movie.movie_id = movies.id 
                            WHERE user_movie.username = :username");
    $stmt->bindParam(':username', $user);
    $stmt->execute();
    $watched_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">

    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Acme&family=Briem+Hand:wght@100..900&family=Caveat:wght@400..700&family=Indie+Flower&family=Permanent+Marker&family=Poetsen+One&family=Satisfy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- the script for confirming the removal of movie -->
    <script>
        function confirmRemoval(movieId) {
            var confirmation = confirm("Are you sure you want to remove this movie?");
            if (!confirmation) {
                // If user cancels, prevent form submission
                event.preventDefault(); // Prevent default form submission
            }
        }

        // Function to search movies via AJAX
        function searchMovies() {
            var searchQuery = document.getElementById('search_query').value;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../custom-api/searchMovies.php?query=' + searchQuery, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var searchResults = document.getElementById('search-results');
                    searchResults.innerHTML = '';
                    if (response.movies.length > 0) {
                        response.movies.forEach(function (movie) {
                            var movieItem = `
                            <a href="movies.php?id=${movie.id}" style="text-decoration: none; color: inherit;">
                                <li>
                                    ${movie.poster ? `<img src="${movie.poster}" alt="${movie.title}" style="width: 100px; height: 100px;">` : ''}
                                    <p>${movie.title} (${movie.year})</p>
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                                        <input type="hidden" name="movie_id" value="${movie.id}">
                                        <input type="hidden" name="movie_title" value="${movie.title}">
                                        <button type="submit">Add to Watched List</button>
                                    </form>
                                </li>
                            </a>
                                `;
                            searchResults.innerHTML += movieItem;
                        });
                    } else {
                        searchResults.innerHTML = '<p>No movies found</p>';
                    }
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
    <nav>
        <a href="/index.php"><h1>MyMovieList</h1></a>
        <div class="search-bar">
            <input type="text" id="search_query" name="search_query" placeholder="Add New Movie" required>
            <button type="button" onclick="searchMovies()">Search</button>
        </div>
        
        <input type="checkbox" id="toggle-popup" class="toggle-popup">
        <label for="toggle-popup" class="user-icon"><i class='bx bxs-user bx-md'></i></label>
        <div class="profile-popup">        
            <p><?php echo htmlspecialchars($_SESSION['username'])?></p>
            <p><?php echo htmlspecialchars($_SESSION['email']); ?> </p>
            <form action="./logout.php" method="post">
                <button>Logout</button>
            </form>
        </div>
    </nav>

    <div class="content">
        <div class="welcome">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        </div>
        
        <div class="search-content">
            <h2>Search Results</h2>
            <ul id="search-results">
                <!-- Search results will be populated here via AJAX -->
            </ul>
        </div>
    
        <div class="watched-content">
            <h2>Movies You Have Watched</h2>
            <ul>
                <?php foreach ($watched_movies as $movie) : ?>
                    <a href="movies.php?id=<?php echo $movie["id"]?>" style="text-decoration: none; color: inherit;">
                        <li>
                            <?php if (!empty($movie['poster'])) : ?>
                                <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="width: 100px; height: 100px;">
                            <?php endif; ?>
        
                            <p>
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </p>
            
                            <form id="remove-form-<?php echo htmlspecialchars($movie['id']); ?>" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                                <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($movie['id']); ?>">
                                <input type="hidden" name="remove" value="1">
                                <button type="submit" onclick="confirmRemoval('<?php echo htmlspecialchars($movie['id']); ?>')">Remove from List</button>
                            </form>
                        </li>
                    </a>
                <?php endforeach; ?>
            </ul>
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
</body>
</html>
