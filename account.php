<?php

include'global.php';
include'header.php';
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

    <title>Kidoordinate - Account</title>
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
                     <a class="nav-item nav-link navbar-text navlink" href="./kidoordinateHome.html"><img src="https://cdn2.iconfinder.com/data/icons/large-home-icons/256/Exit_delete_close_remove_door_logout_out.png" width="35px" height="35px"></a>
                </div>
            </nav>
        </div>

<?php

//logged in and verified
if(isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['submit']) && $_GET['submit'] == 'yes'){
    //update
    
    //validate all fields filled
    if(count(array_filter($_POST))!=count($_POST)){
        echo "Something is empty";
    }
    
    //get username, email, and phone to find existing values
    $stmt = $conn->prepare("SELECT email, phone FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($email, $phone);
    $stmt->fetch();
    $stmt->close();
    
    if(!(strcmp($email, $_POST['email'] == 0 && strcmp($phone, $_POST['phone']) == 0)))
        //check if username, email, or phone already exist
        $stmt = $conn->prepare("SELECT id FROM parents WHERE username = ? OR email = ? OR phone = ?");
        $stmt->bind_param("ssi", $_POST['username'], $_POST['email'], $_POST['phone']);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows >= 1) {
            exit('Someone with that email or phone already exists!');
        }
        $stmt->close();
    
    //crpyt password
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE parents SET password = ?, firstname = ?, lastname = ?, email = ?, phone = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssssisi", $password, $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['phone'], $_POST['bio'], $_SESSION['userid']);
    $stmt->execute();
    $stmt->close();
    
    //kid1
    $stmt = $conn->prepare("UPDATE kids SET firstname = ?, lastname = ?, age = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssisi", $_POST['kidfirstname0'], $_POST['kidlastname0'], $_POST['kidage0'], $_POST['kidbio0'], $_POST['kidid0']);
    $stmt->execute();
    $stmt->close();
    
    $counter = 1;
    while(isset($_POST['kidid' . $counter])){
        echo $_POST['kidid' . $counter];
        $stmt = $conn->prepare("UPDATE kids SET firstname = ?, lastname = ?, age = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $_POST['kidfirstname' . $counter], $_POST['kidlastname' . $counter], $_POST['kidage' . $counter], $_POST['kidbio' . $counter], $_POST['kidid' . $counter]);
        $stmt->execute();
        $stmt->close();
        $counter++;
    }
    
    echo '<h1>All info has been successfully updated!</h1>';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'update'){
    //show editable fields
        $stmt = $conn->prepare("SELECT username, firstname, lastname, email, phone, complex, addressline1, addressline2, city, state, zip, bio, picformat FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $firstname, $lastname, $email, $phone, $complex, $addressline1, $addressline2, $city, $state, $zip, $bio, $picformat);
    $stmt->fetch();
    $stmt->close();

    echo '<h1 align="center"> Editing Information on File </h1>

        <div class="container">
        <form id="register" method="post" action="account.php?action=update&submit=yes">
            <input type="submit" class="btn btn-lg btn-primary btn-block"></button><br></br>
            <p></p>
  <img src="uploads/' . $_SESSION['userid'] . '.' . $picformat . '" class="rounded mx-auto d-block"><br/>
  <div class="table-responsive">
            <table class="table">
              <tbody>
                <tr>
  <td>Username (cannot change)</td><td>' . $username . '</td>
      </tr>
      <tr><td>Password</td><td><input type="password" id="password" name="password" value="*******" class="form-control" required></td>
      </tr>
      <tr><td>First Name</td><td><input type="text" id="firstname" name="firstname" value="' . $firstname . '" class="form-control" required></td>
      </tr>
      <tr><td>Last Name</td><td><input type="text" id="lastname" name="lastname" value="' . $lastname . '" class="form-control" required></td>
      </tr>
      <tr><td>Email</td><td><input type="email" id="email" name="email" value="' . $email . '" class="form-control" required></td>
      </tr>
      <tr><td>Phone number</td><td><input type="text" id="phone" name="phone" value="' . $phone . '" class="form-control" required></td>
      </tr>
          <tr><td>Apartment Complex (cannot change)</td>
    <td>' . $complex . '</td>
      </tr>
          <tr><td>Address Line 1 (cannot change)</td><td>
    ' . $addressline1 . '</td>
      </tr>
          <tr><td>Address Line 2 (cannot change)</td>
    <td>' . $addressline2 . '</td>
      </tr>
      <tr><td>City (cannot change)</td><td>' . $city . '</td>
      </tr>
      <tr><td>State (cannot change)</td><td>' . $state . '</td>
</tr>
       <tr><td>Zip Code (cannot change)</td><td>' . $zip . '</td>
      </tr>
          <tr><td>Bio</td>
<td><textarea rows="4" cols="50" id="bio" name="bio" class="form-control" required>
' . $bio . '
</textarea></td>';

    //find all kids
    $counter = 0;
    $stmt = $conn->prepare("SELECT id, firstname, lastname, age, bio FROM kids WHERE parentid = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $stmt->bind_result($kidid, $firstname, $lastname, $age, $bio); 
        while ($stmt->fetch()) {
            echo '<input type="hidden" id="kidid' . $counter . '" name="kidid' . $counter . '" value="' . $kidid . '"><tr><td>Child ' . $counter . ' First Name</td><td><input type="text" id="kidfirstname' . $counter . '" name="kidfirstname' . $counter . '" value="' . $firstname . '" class="form-control" required>
      </td></tr>
      <tr><td>Child ' . $counter . ' Last Name</td><td><input type="text" id="kidlastname' . $counter . '" name="kidlastname' . $counter . '" value="' . $lastname . '" class="form-control" required></td>
      </tr>
      <tr><td>Child ' . $counter . ' Age</td><td><input type="num" id="kidage' . $counter . '" name="kidage' . $counter . '" value="' . $age . '" class="form-control" required></td>
      </tr>
<tr><td>Child ' . $counter . ' Bio</td><td>
<textarea rows="4" cols="50" id="kidbio' . $counter . '" name="kidbio' . $counter . '" class="form-control" required>
' . $bio . '</textarea></td></tr>';
            $counter++;
        }
    }
    echo '              </tbody>
            </table>
          </div>
    </div>';
    $stmt->close();

}
else{
    $stmt = $conn->prepare("SELECT username, firstname, lastname, email, phone, complex, addressline1, addressline2, city, state, zip, bio, picformat FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $firstname, $lastname, $email, $phone, $complex, $addressline1, $addressline2, $city, $state, $zip, $bio, $picformat);
    $stmt->fetch();
    $stmt->close();

    echo '        <h1 align="center">Information on File</h1>

        <div class="container">
            <a href="./account.php?action=update"><button type="button" class="btn btn-primary" style="float: right;">Edit Information</button></a>
            <br/><br/>
          <div class="table-responsive">
            <table class="table">
  <img src="uploads/' . $_SESSION['userid'] . '.' . $picformat . '" class="rounded mx-auto d-block"><br/>
              <tbody>
                <tr>
  <td>Username</td><td>' . $username . '</td>
      </tr>
      <tr><td>Password</td><td>*******</td>
      </tr>
      <tr><td>First Name</td><td>' . $firstname . '</td>
      </tr>
      <tr><td>Last Name</td><td>' . $lastname . '</td>
      </tr>
      <tr><td>Email</td><td>' . $email . '</td>
      </tr>
      <tr><td>Phone number</td><td>' . $phone . '</td>
      </tr>
          <tr><td>Apartment Complex</td>
    <td>' . $complex . '</td>
      </tr>
          <tr><td>Address Line 1</td><td>
    ' . $addressline1 . '</td>
      </tr>
          <tr><td>Address Line 2</td>
    <td>' . $addressline2 . '</td>
      </tr>
      <tr><td>City</td><td>' . $city . '</td>
      </tr>
      <tr><td>State</td><td>' . $state . '</td>
</tr>
       <tr><td>Zip Code</td><td>' . $zip . '</td>
      </tr>
          <tr><td>Bio</td>
<td>' . $bio . '</td>';

    //find all kids
    $counter = 0;
    $stmt = $conn->prepare("SELECT id, firstname, lastname, age, bio FROM kids WHERE parentid = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $stmt->bind_result($kidid, $firstname, $lastname, $age, $bio); 
        while ($stmt->fetch()) {
            echo '<tr><td>Child ' . $counter . ' First Name</td><td>' . $firstname . '
      </td></tr>
      <tr><td>Child ' . $counter . ' Last Name</td><td>' . $lastname . '</td>
      </tr>
      <tr><td>Child ' . $counter . ' Age</td><td>' . $age . '</td>
      </tr>
<tr><td>Child ' . $counter . ' Bio</td><td>
' . $bio . '</td></tr>';
            $counter++;
        }
    }
    echo '              </tbody>
            </table>
          </div>
    </div>';

    $stmt->close();
}
?>
                <div class="container text-center">
        <small class="text-muted"><a href="#top">Back to Top</a></small>
        </div>

</body>
</html>