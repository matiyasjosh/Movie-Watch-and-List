<?php
  include_once '../dbConnect.php';
  include "../custom-api/send-email.php";

  $database = Database::getInstance();
  $conx = $database->getConnection();

  $signup_success = false;

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    // hashing passwd is mandatory for logging in latter
    $hashed_password = password_hash($_POST['passwd'], PASSWORD_DEFAULT);

    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);

    
    $stmt = $conx->prepare("INSERT INTO users (username, email, password, token) VALUES (:username, :email, :password, :token)");

    $stmt->bindParam(':username', $_POST['username']);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':token', $token_hash);

    $stmt->execute();
    $signup_success = True;
    
    sendEmail($_POST["email"], $_POST["username"], $token_hash);
    // $conx = null;
  }
  
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>

    <link rel="stylesheet" href="../public/css/login.css">
    <link rel="stylesheet" href="../public/css/style.css">

    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Acme&family=Briem+Hand:wght@100..900&family=Caveat:wght@400..700&family=Indie+Flower&family=Permanent+Marker&family=Poetsen+One&family=Satisfy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- boxicon -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <nav>
        <a href="../index.php"><h1>MyMovieList</h1></a>
        <ul>
            <li><a href="login.php">Login</a></li>
            <li><a href="#">Signup</a></li>
        </ul>
    </nav>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class = "login-container">
        <div class='login-info'>
          <?php if (!$signup_success) : ?>
            <h1>Sign-up</h1>
            <div class="form__group field">
              <input
                type="text"
                class="form__field"
                placeholder="Name"
                name="username"
                id="username"
                required
              />
              <label for="name" class="form__label">
                User Name
              </label>
              </div>
      
              <div class="form__group field">
              <input
                type="email"
                class="form__field"
                placeholder="Email"
                name="email"
                id="email"
                required
                />
              <label for="name" class="form__label">
                Email
              </label>
              </div>
              
              <div class="form__group field">
              <input
                type="password"
                class="form__field"
                placeholder="Password"
                name="passwd"
                id="passwd"
                required
                />
              <label for="name" class="form__label">
                Password
              </label>
              </div>
      
              <!-- <div class="form__group field">
              <input
                type="password"
                class="form__field"
                placeholder="Password"
                name="passwd-confirm"
                id="passwd-confirm"
                required
                />
              <label for="name" class="form__label">
                Confirm Password
              </label>
              </div> -->
      
                <button>Signup</button>
                <?php else : ?>
                  <h1>You Have Signed UP Successfully, However You Need To Verify Your email First to Log In So Check Your Email!</h1>
                <?php endif; ?> 
            </div>
          <div class="login-img">
            <img src="../public/images/movie_watch.jpg"/>
          </div>
      </div>
    </form>

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