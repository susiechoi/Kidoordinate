<?php
session_start();

session_unset(); 
session_destroy();

?>

<!DOCTYPE html>

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
    <title>Kidoordinate - Logout</title>
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
                <h1>You have been logged out!</h1>
            </div>
        </div>

        <div class="container text-center">
        <small class="text-muted"><a href="#top">Back to Top</a></small>
        </div>
  </body>
</html>