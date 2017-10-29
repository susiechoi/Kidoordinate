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
    <script src="js/app.js"></script>
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
        </div>


    <h1 align="center"> Find Your Child's Perfect Playdate!</h1><br/><h5 align="center">Showing results within 100 miles of your location.</h5>

<?php

//get users lat and long
$stmt = $conn->prepare("SELECT latitude, longitude FROM parents WHERE id = ?");
$stmt->bind_param("i", $_SESSION['userid']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($lat, $lon);
$stmt->fetch();
$stmt->close();

$sf = 3.14159 / 180; // scaling factor
$er = 6350; // earth radius in miles, approximate
$mr = 100; // max radius

$stmt = $conn->prepare("SELECT id, firstname, lastname, latitude, longitude, bio, lastonline, picformat FROM parents WHERE $mr >= $er * ACOS(SIN(latitude*$sf)*SIN($lat*$sf) + COS(latitude*$sf)*COS($lat*$sf)*COS((longitude-$lon)*$sf)) AND activated = 1 ORDER BY ACOS(SIN(latitude*$sf)*SIN($lat*$sf) + COS(latitude*$sf)*COS($lat*$sf)*COS((longitude-$lon)*$sf))");
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
    $stmt->bind_result($id, $firstname, $lastname, $latitude, $longitude, $bio, $lastonline, $picformat); 
    echo '    <div class="container">
            <a id="portfolio"></a>
            <div class="card-columns">';
    while ($stmt->fetch()) {
        $date1 = new DateTime(date("Y-m-d H:i:s"));
        $date2 = new DateTime($lastonline);
        $interval = date_diff($date1, $date2);
        $days = $interval->format('%a');
        $distance = haversineGreatCircleDistance($lat, $lon, $latitude, $longitude);
        if($days <= 7 && $id != $_SESSION['userid']){
            //online recently
            echo '<div class="card">
                  <img class="card-img-top thumbnail" src="uploads/' . $id . '.' . $picformat . '">
                    <div class="card-body">
                        <h4 class="card-title">' . $firstname . ' ' . $lastname . '<small class="text-muted"><i>  (' . $distance . ' mi away)</i></small></h4>
                        <p class="card-text"><i>' . $bio . '</i><br/></p>
                    </div>
                        <div class="card-footer" align="center"><a href="profile.php?id=' . $id . '" class="btn btn-primary">
  View Profile
</a>
<a href="messages.php?action=compose&id=' . $id . '" class="btn btn-primary">
  Send Message
</a><a href="requests.php?action=compose&id=' . $id . '" class="btn btn-primary">
  Send Playdate Request
</a>

                    </div>
                </div>';
            
        }
    }
    echo '            </div>
        </div>';
}
else {
    echo '<h1>There are no parents within 100 miles of you!</h1>';
}
$stmt->close();

?>
        <div class="container text-center">
        <small class="text-muted"><a href="#top">Back to Top</a></small>
        </div>
  </body>
</html>