<?php
  
  include_once '../dbConnect.php';

  $database = Database::getInstance();
  $conx = $database->getConnection();

  session_start();  
  $display_error = False;

  if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $input_username = $_POST['username'];
    $input_password = $_POST['passwd'];
    
    $stmt = $conx->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $input_username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

      // passowrd verification
    if ($user && password_verify($input_password, $user['password'])&& $user["token"]==null) {
      $_SESSION['username'] = $user['username'];
      $_SESSION['email'] = $user['email'];
      header("Location: ./dashboard.php");
      exit();
    } else {
      $error_message = "Invalid usr or passwd input!";
      $display_error = True;
    }
    $conx = null;
  }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="/public/css/login.css">

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
            <li><a href="./login.php">Login</a></li>
            <li><a href="./signup.php">Signup</a></li>
        </ul>
    </nav>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <div class = "login-container">
          <div class="login-info">
          <h1>Login</h1>
          <?php if($display_error) : ?>
            <div class="error-content">
              <?php if(isset($error_message)) { echo "<p>$error_message</p>"; } ?>              
            </div>
          <?php endif; ?> 
          
          <div class="form__group field">
            <input
              type="text"
              class="form__field"
              placeholder="Name"
              name="username"
              id="username"
              required
            />
            <label for="username" class="form__label">
              User Name
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
            <label for="passwd" class="form__label">
              Password
            </label>
            
              <button>Login</button>
            </div>
          </div>
    
          <div class="login-img">
            <img src="../public/images/login.png" style="width: 25rem;"/>
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
