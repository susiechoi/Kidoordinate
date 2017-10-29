<?php
session_start();
include 'global.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link href="css/signin.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="images/icon.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="js/app.js"></script>
    <title>Kidoordinate - Login</title>
    </head>

  <body>
    <div>
        <a id="top"></a>
        <nav class="navbar navbar-toggleable-md navbar-light bg-faded">
          <a class="navbar-brand" href="./kidoordinateHome.html">
              <img src="images/logo.png" style="width:25%" >
          </a>      
          <div class="navbar-nav" id="navLinks">
              <a class="nav-item nav-link navbar-text navlink" href="./login.php">Login</a>
              <a class="nav-item nav-link navbar-text navlink" href="./register.php">Register</a>
          </div>
        </nav>
    </div>   

    <div class="jumbotron jumbtotron-fluid text-center" id="loginBack">
            <div class="container">
                <!--<form class="form-signin">
        <h2 class="form-signin-heading">Welcome back!</h2>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
        <p></p>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <p></p>
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div>

        <a href="./schedule.html" class="btn btn-lg btn-primary btn-block" role="button">Enter</a>
      </form>-->

<?php

if(isset($_SESSION['userid'])){
    exit('<h1>You\'re already logged in!</h1>');
}

if($_POST){
    //submitted login form
    
    //validate all fields filled
    if(count(array_filter($_POST))!=count($_POST)){
        echo "Something is empty";
    }
    
    //connect to db
    $conn = new mysqli($host, $user, $pass, $db);
    if($conn->connect_error){
        die('Connection failed!');
    }
    
    $stmt = $conn->prepare("SELECT id, password, firstname FROM parents WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 1) {
        $stmt->bind_result($id, $passwordhash, $firstname);
        $stmt->fetch();
        //user exists, check password
        if(password_verify($_POST['password'], $passwordhash)){
            echo 'Welcome ' . $firstname . '!';
            $_SESSION['userid'] = $id;
            $_SESSION['firstname'] = $firstname;
        }
        else {
            exit('Invalid username or password!');
        }
    }
    else {
        //invalid login
        echo 'Invalid username or password!';
    }
    $stmt->close();
}
else {
    //display login form
        echo '
  <form id="login" method="post" class="form-signin" action="">
  <h2 class="form-signin-heading">Welcome back!</h2>
  <label for="username" class="sr-only">Username</label>
    <input type="text" id="username" name="username" placeholder="Username" class="form-control" required autofocus>
      <p></p>
      <label for="password" class="sr-only">Password</label>
    <input type="password" id="password" name="password" placeholder="*******" class="form-control" required>
<p></p><div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div><input type="submit" class="btn btn-lg btn-primary btn-block"></button>
      </form>';
}

?>
                
                            </div>
        </div>
        <div class="container text-center">
        <small class="text-muted"><a href="#top">Back to Top</a></small>
        </div>
  </body>
</html>