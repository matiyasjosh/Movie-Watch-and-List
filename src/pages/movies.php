<?php
session_start();

// if (!isset($_SESSION['username'])) {
//     echo json_encode(["error" => "User not logged in"]);
//     exit();
// }

include_once '../dbConnect.php';


$database = Database::getInstance();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $conn = $database->getConnection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM movies WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }

    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/movies.css">
    <link rel="stylesheet" href="/public/css/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Acme&family=Briem+Hand:wght@100..900&family=Caveat:wght@400..700&family=Indie+Flower&family=Permanent+Marker&family=Poetsen+One&family=Satisfy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        function handleSelection(event) {
            const movieId = event.target.dataset.movieId;
            const action = event.target.value;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "./updateList.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log(xhr.responseText);
                    // You can add code here to update the UI based on the response if needed
                }
            };
            xhr.send("movieId=" + movieId + "&action=" + action);
        }

        document.addEventListener("DOMContentLoaded", function () {
            const selectElement = document.getElementById("add-to-list");
            selectElement.addEventListener("change", handleSelection);
        });
    </script>
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
                    if (response.movies && response.movies.length > 0) {
                        response.movies.forEach(function (movie) {
                            var movieItem = `
                            <a href="movies.php?id=${movie.id}" style="text-decoration: none; color: inherit;">
                                <li>
                                    ${movie.poster ? `<img src="${movie.poster}" alt="${movie.title}" style="width: 100px; height: 100px;">` : ''}
                                    <p>${movie.title} (${movie.year})</p>
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                                        <input type="hidden" name="movie_id" value="${movie.id}">
                                        <input type="hidden" name="movie_title" value="${movie.title}">
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
<body onload="singleMovie()">
    <nav>
        <a href="/index.php"><h1>MyMovieList</h1></a>
        <div class="search-bar">
            <input type="text" id="search_query" name="search_query" placeholder="Add New Movie" required>
            <button type="button" onclick="searchMovies()">Search</button>
        </div>
        <?php if (isset($_SESSION['username'])) { ?>
            <input type="checkbox" id="toggle-popup" class="toggle-popup">
            <label for="toggle-popup" class="user-icon"><i class='bx bxs-user bx-md'></i></label>
            <div class="profile-popup">
                <p><?php echo htmlspecialchars($_SESSION['username'])?></p>
                <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                <form action="./logout.php" method="post">
                    <button>Logout</button>
                </form>
            </div>
        <?php } else { ?>
            <ul>
                <li><a href="./login.php">Login</a></li>
                <li><a href="#">Signup</a></li>
            </ul>
        <?php } ?>
    </nav>    

    <div id="search-results" style="margin: 3rem 15rem;text-align: center;"></div> <!-- Container to display search results -->

    <div class="movie-container">
        <div class="video-container">
            <video src="<?php echo $movie["video_path"]?>" controls poster="<?php echo $movie["poster"]; ?>"></video>
        </div>

        <hr>
        
        <div class="movie-spec">
            <div class="movie-poster">
                <img src="<?php echo $movie["poster"]; ?>" alt="Movie Poster">
            </div>
            
            <div class="detail-div">
                <h1><?php echo $movie["title"]; ?></h1>
                
                <select class="valuing" name="add-to-list" id="add-to-list" data-movie-id="<?php echo $movie['id']; ?>">
                    <option value="" disabled selected>List</option>
                    <option value="watched">Watched</option>
                    <option value="dropped">Remove</option>
                </select>
                    
                <p>
                    <span class="italics-text"><i><?php echo $movie["year"];?></span>/10 </i></span>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloremque rem saepe at officiis magni? In, fuga sapiente? Saepe laborum accusamus, animi,
                    deserunt eveniet est ea sed eius libero, optio harum.
                </p>
            </div>
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
