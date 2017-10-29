<?php

include'global.php';
include'header.php';

//logged in and verified

?>
<!DOCTYPE html>
<html lang="en">
    <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="css/connect.css">
    <link rel="icon" type="image/png" href="images/icon.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">

    <title>Kidoordinate</title>
    </head>

    <body>

        <div>
            <a id="top"></a>
            <nav class="navbar navbar-toggleable-md navbar-light bg-faded">
                <a class="navbar-brand" href="./schedule.html">
                    <img src="images/logo.png" style="width:25%">
                </a>      
                <div class="navbar-nav" id="navLinks">
                     <a class="nav-item nav-link navbar-text navlink" href="./dashboard.php"><img src="http://icons.iconarchive.com/icons/paomedia/small-n-flat/1024/calendar-icon.png" width="35px" height="35px"></a>
                    <a class="nav-item nav-link navbar-text navlink" href="./connect.php"><img src="https://image.flaticon.com/icons/png/128/109/109859.png" width="35px" height="35px"></a>
                    <a class="nav-item nav-link navbar-text navlink" href="./messages.php"><img src="http://images.apusapps.com/src/icon-clear-msg-notification.png" width="35px" height="35px"></a>
                    <a class="nav-item nav-link navbar-text navlink" href="./requests.php"><img src="https://d30y9cdsu7xlg0.cloudfront.net/png/157558-200.png" width="35px" height="35px"></a>
                    <a class="nav-item nav-link navbar-text navlink" href="./account.php"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/OOjs_UI_icon_advanced.svg/2000px-OOjs_UI_icon_advanced.svg.png" width="35px" height="35px"></a>
                     <a class="nav-item nav-link navbar-text navlink" href="./index.php"><img src="https://cdn2.iconfinder.com/data/icons/large-home-icons/256/Exit_delete_close_remove_door_logout_out.png" width="35px" height="35px"></a>
                </div>
            </nav>
        </div><div class="container text-center">
<?php

if(!(isset($_GET['id']))){
    exit('<h1>No profile selected!</h1>');
}

//get users lat and long
$stmt = $conn->prepare("SELECT latitude, longitude FROM parents WHERE id = ?");
$stmt->bind_param("i", $_SESSION['userid']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($lat, $lon);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT id, firstname, lastname, latitude, longitude, bio, picformat FROM parents WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows === 1) {
    $stmt->bind_result($parentid, $parentfirstname, $parentlastname, $latitude, $longitude, $parentbio, $picformat);
    $stmt->fetch();
}
else {
    exit('Invalid ID!');
}
$stmt->close();
        
echo '<h1>Viewing Profile</h1>';

$distance = haversineGreatCircleDistance($lat, $lon, $latitude, $longitude);
echo '<img src="uploads/' . $parentid . '.' . $picformat . '"><br/>' . $parentfirstname . ' ' . $parentlastname . ' - ' . $distance . ' miles<br/>' . $parentbio . '<br/><br/><hr>Children:<br/>';

$stmt = $conn->prepare("SELECT firstname, lastname, age, bio FROM kids WHERE parentid = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
    $stmt->bind_result($kidfirstname, $kidlastname, $kidage, $kidbio); 
    while ($stmt->fetch()) {
        echo $kidfirstname . ' ' . $kidlastname . ' - ' . $kidage . '<br/>' . $kidbio . '<br/><br/>';
    }
}
else {
    echo 'This user has no kids to display!';
}
$stmt->close();

?>
        </div>        <div class="container text-center">
        <small class="text-muted"><a href="#top">Back to Top</a></small>
        </div>
  </body>
</html>