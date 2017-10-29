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

    <title>Kidoordinate - Requests</title>
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


    <h1 align="center"> Pending Playdate Requests </h1>
        <div class="container text-center">
<?php

if(isset($_GET['action']) && $_GET['action'] == 'reply' && isset($_GET['id'])){
    if(count(array_filter($_POST))!=count($_POST)){
        echo "Something is empty";
    }
    
    //check if this is a real request
    $stmt = $conn->prepare("SELECT status FROM playdaterequests WHERE id = ? AND toparentid = ?");
    $stmt->bind_param("ii", $_GET['id'], $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0) {
        exit('Invalid ID!');
    }
    $stmt->close();
    
    if(strcmp($_POST['response'], 'accept') == 0){
        $status = 'Approved';
    }
    elseif(strcmp($_POST['response'], 'decline') == 0){
        $status = 'Declined';
    }
    else {
        exit('Invalid response!');
    }
    
    $stmt = $conn->prepare("UPDATE playdaterequests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $_GET['id']);
    $stmt->execute();
    $stmt->close();
    echo 'Successfully sent the response!<br/><br/><a href="requests.php">Return to previous page</a>';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'send' && isset($_GET['id'])){
    
    if(count(array_filter($_POST))!=count($_POST)){
        echo "Something is empty";
    }
    
    //verify toparent
    $stmt = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0) {
        exit('Invalid ID!');
    }
    $stmt->bind_result($parentfirstname, $parentlastname);
    $stmt->fetch();
    $stmt->close();
    
    //cant sent request to self
    if($_GET['id'] == $_SESSION['userid']){
        exit('You can\'t send a request to yourself!');
    }
    
    //verify fromkidid
    $stmt = $conn->prepare("SELECT parentid FROM kids WHERE id = ?");
    $stmt->bind_param("i", $_POST['fromkid']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0) {
        exit('Invalid fromkid ID!');
    }
    $stmt->bind_result($fromkidparentid);
    $stmt->fetch();
    $stmt->close();
    
    if($fromkidparentid != $_SESSION['userid']){
        exit('This is not your kid!');
    }
    
    //verify tokidid
    $stmt = $conn->prepare("SELECT parentid FROM kids WHERE id = ?");
    $stmt->bind_param("i", $_POST['tokid']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0) {
        exit('Invalid tokid ID!');
    }
    $stmt->bind_result($tokidparentid);
    $stmt->fetch();
    $stmt->close();
    
    if($tokidparentid != $_GET['id']){
        exit('This is not their kid!');
    }
    
    //send the request
    $status = 'Pending';
    $stmt = $conn->prepare('INSERT INTO playdaterequests (fromparentid, toparentid, fromchildid, tochildid, datetime, location, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiiisss', $_SESSION['userid'], $_GET['id'], $_POST['fromkid'], $_POST['tokid'], $_POST['datetime'], $_POST['location'], $status);
    $stmt->execute();
    $stmt->close();
    
    echo 'Your request has been successfully sent!';
    
}
elseif(isset($_GET['action']) && $_GET['action'] == 'compose' && isset($_GET['id'])){
    //check if to is a real person
    $stmt = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0) {
        exit('Invalid ID!');
    }
    $stmt->bind_result($parentfirstname, $parentlastname);
    $stmt->fetch();
    $stmt->close();
    
    //cant sent request to self
    if($_GET['id'] == $_SESSION['userid']){
        exit('You can\'t send a request to yourself!');
    }
    
    echo 'Sending a playdate request to ' . $parentfirstname . ' ' . $parentlastname . ':<br/><form id="compose" method="post" action="requests.php?action=send&id=' . $_GET['id'] . '"><label for="tokid">Which of their kids?:</label><select name="tokid" id="tokid">';
    
    //get list of kids with them as parent
    $stmt = $conn->prepare("SELECT id, firstname, lastname, age FROM kids WHERE parentid = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $stmt->bind_result($tokidid, $tokidfirstname, $tokidlastname, $tokidage); 
        while ($stmt->fetch()) {
            echo '<option value="' . $tokidid . '">' . $tokidfirstname . ' ' . $tokidlastname . ' - ' . $tokidage . '</option>';
        }
    }
    else {
        //this is sloppy
        echo '</select></form>';
        exit('They have no children!');
    }
    
    echo '</select><br/><label for="kid">Which of your kids?:</label><select name="fromkid" id="fromkid">';
    //get list of kids with you as parent
    $stmt = $conn->prepare("SELECT id, firstname, lastname, age FROM kids WHERE parentid = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $stmt->bind_result($fromkidid, $fromkidfirstname, $fromkidlastname, $fromkidage); 
        while ($stmt->fetch()) {
            echo '<option value="' . $fromkidid . '">' . $fromkidfirstname . ' ' . $fromkidlastname . ' - ' . $fromkidage . '</option>';
        }
    }
    else {
        //this is sloppy
        echo '</select></form>';
        exit('You have no children!');
    }
    echo '</select><br/><label for="datetime">Date and Time (YYYY-MM-DD HH:MM:SS)</label>
    <input type="text" id="datetime" name="datetime" placeholder="YYYY-MM-DD HH:MM:SS">
      <br/><label for="location">Location</label>
    <input type="text" id="location" name="location" placeholder="Location">
      <br/><input type="submit">Submit</button>
      </form>';
}

else {

$status = 'Pending';
//find pending requests
$stmt = $conn->prepare("SELECT id, fromparentid, fromchildid, tochildid, datetime, location FROM playdaterequests WHERE toparentid = ? AND status = ?");
$stmt->bind_param("is", $_SESSION['userid'], $status);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
    echo 'You have ' . $stmt->num_rows . ' playdate request(s)!<br/><br/>';
    $stmt->bind_result($playdateid, $fromparentid, $fromchildid, $tochildid, $datetime, $location); 
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
        
        echo $fromchildfirstname . ' ' . $fromchildlastname . ' (parent: ' . $fromparentfirstname . ' ' . $fromparentlastname . ') requested a playdate with ' . $tochildfirstname . ' ' . $tochildlastname . ' on ' . $datetime . ' at ' . $location .'.<br/>
    <form id="reply" method="post" action="requests.php?action=reply&id=' . $playdateid . '">
    
    <div class="btn-group" data-toggle="buttons">
  <label class="btn btn-success active">
    <input type="radio" name="response" value="accept" autocomplete="off" checked> Accept
  </label>
  <label class="btn btn-danger">
    <input type="radio" name="response" value="decline" autocomplete="off"> Decline
  </label>
</div>
<br/>
<br/><input type="submit"  class="btn btn-lg btn-primary btn-block"></button>
      </form><br/><br/>';
    }
}
else {
    echo 'You have no pending playdate requests!<br/><br/>';
}
$stmt->close();
    
    //see sent requests
    echo '<h1>Sent Requests</h1>';
    $stmt = $conn->prepare("SELECT toparentid, fromchildid, tochildid, datetime, location, status FROM playdaterequests WHERE fromparentid = ?");
$stmt->bind_param("i", $_SESSION['userid']);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
        $stmt->bind_result($toparentid, $fromchildid, $tochildid, $datetime, $location, $status); 
    while ($stmt->fetch()) {
        //get to parent name
        $stmt2 = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
        $stmt2->bind_param("i", $toparentid);
        $stmt2->execute();
        $stmt2->store_result();
        if($stmt2->num_rows === 1) {
            $stmt2->bind_result($toparentfirstname, $toparentlastname);
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
        echo 'You requested a playdate for ' . $fromchildfirstname . ' ' . $fromchildlastname . ' with ' . $tochildfirstname . ' ' . $tochildlastname . ' (parent: ' . $toparentfirstname . ' ' . $toparentlastname . ') on ' . $datetime . ' at ' . $location .'. The request is ' . strtolower($status) . '.<br/>';
}
}
    else {
        echo 'You have no sent requests!';
    }
}

            ?></div>
<div class="container text-center">
        <small class="text-muted"><a href="#top">Back to Top</a></small>
        </div>

</body>
</html>