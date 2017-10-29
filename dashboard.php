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
        </div>

<div class="jumbotron jumbtotron-fluid text-center" id="schedBack">
            <div class="container">
        <h1 align="center"> We're glad you're back! </h1>
                <h5>You have <b><i>
<?php

//find messages, playdate requests, upcoming playdates, and sent playdate requests

$status = 'Pending';
$read = 0;

//first find pending requests
$stmt = $conn->prepare("SELECT id FROM playdaterequests WHERE toparentid = ? AND status = ?");
$stmt->bind_param("is", $_SESSION['userid'], $status);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
    echo $stmt->num_rows;
}
else {
    echo '0';
}                    
$stmt->close();

echo ' pending playdate request(s)</i></b> and <b><i>';

//then find messages
$stmt = $conn->prepare("SELECT id FROM messages WHERE toid = ? AND isread = ?");
$stmt->bind_param("ii", $_SESSION['userid'], $read);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
    echo $stmt->num_rows; 
}
                    else {
                        echo '0';
                    }
$stmt->close();
                    
echo ' unread message(s)</i></b>!</h5><div class="container">
    <div class="card-columns">';

$status = 'Approved';

//then find upcoming playdates
$stmt = $conn->prepare("SELECT fromparentid, fromchildid, tochildid, datetime, location FROM playdaterequests WHERE toparentid = ? OR fromparentid = ? AND status = ?");
$stmt->bind_param("iis", $_SESSION['userid'], $_SESSION['userid'], $status);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
    $stmt->bind_result($fromparentid, $fromchildid, $tochildid, $datetime, $location); 
    while ($stmt->fetch()) {
        //get from parent name
        $stmt2 = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
        $stmt2->bind_param("i", $fromparentid);
        $stmt2->execute();
        $stmt2->store_result();
        if($stmt2->num_rows === 1) {
            $stmt2->bind_result($fromparentfirstname, $fromparentlastname);
            $stmt2->fetch();
        }
        $stmt2->close();
        
        //get from child name
        $stmt3 = $conn->prepare("SELECT firstname, lastname FROM kids WHERE id = ?");
        $stmt3->bind_param("i", $fromchildid);
        $stmt3->execute();
        $stmt3->store_result();
        if($stmt3->num_rows === 1) {
            $stmt3->bind_result($fromchildfirstname, $fromchildlastname);
            $stmt3->fetch();
        }
        $stmt3->close();
        
        //get to child name
        $stmt4 = $conn->prepare("SELECT firstname, lastname FROM kids WHERE id = ?");
        $stmt4->bind_param("i", $tochildid);
        $stmt4->execute();
        $stmt4->store_result();
        if($stmt4->num_rows === 1) {
            $stmt4->bind_result($tochildfirstname, $tochildlastname);
            $stmt4->fetch();
        }
        $stmt4->close();
        echo '<div class="card">
                    <div class="card-body">
                        <h4 class="card-title">' . $fromchildfirstname . ' ' . $fromchildlastname . ' (parent: ' . $fromparentfirstname . ' ' . $fromparentlastname . '</h4>
                        <p class="card-text"><i>With ' . $tochildfirstname . ' ' . $tochildlastname . ' on ' . $datetime . ' at ' . $location .'</i><br/></p>
                    </div>
                        <div class="card-footer" align="center"><a href="./schedule.html">
<button type="button" class="btn btn-primary">
Add to Google Calendar</button></a>
                    </div>
                </div>';
    }
}
else {
    echo '<h1>You have no upcoming playdates!</h1>';
}
$stmt->close();
?>
                    </div>
</div>
</div>

        <div class="container text-center">
        <small class="text-muted"><a href="#top">Back to Top</a></small>
        </div>
  </body>
</html>