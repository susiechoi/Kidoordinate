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
    <link href="css/register.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="images/icon.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="js/app.js"></script>
    <title>Kidoordinate - Register</title>
    </head>

  <body>

    <div>
        <a id="top"></a>
        <nav class="navbar navbar-toggleable-md navbar-light bg-faded">
          <a class="navbar-brand" href="./index.php">
              <img src="images/logo.png" style="width:25%" >
          </a>      
          <div class="navbar-nav" id="navLinks">
              <a class="nav-item nav-link navbar-text navlink" href="./login.php">Login</a>
              <a class="nav-item nav-link navbar-text navlink" href="./register.php">Register</a>
          </div>
        </nav>
    </div>   
    <div class="jumbotron jumbtotron-fluid text-center" id="regBack">
            <div class="container">
<?php
if(isset($_SESSION['userid'])){
    exit('<h1>You\'re already registered!</h1>');
}

if($_POST){
    //there is submitted form data
    
    //validate all fields filled
    if(count(array_filter($_POST))!=count($_POST)){
        echo "<h1>Something is empty</h1>";
    }
    
    //connect to db
    $conn = new mysqli($host, $user, $pass, $db);
    if($conn->connect_error){
        die('<h1>Connection failed!</h1>');
    }
    
    //find the date and time
    $datetime = date("Y-m-d H:i:s");
    
    //set zero
    $zero = 0;
    
    //crpyt password
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    //ensure this is a unique user
    $stmt = $conn->prepare("SELECT id FROM parents WHERE username = ? OR email = ? OR phone = ?");
    $stmt->bind_param("ssi", $_POST['username'], $_POST['email'], $_POST['phone']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows >= 1) {
        exit('<h1>Someone with that username, email, or phone already exists!</h1>');
    }
    $stmt->close();
    
    $filetype = pathinfo($_FILES["addressverification"]["name"],PATHINFO_EXTENSION);
    
    //find latitude and longitude
    $apiaddress = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($_POST['addressline1']) . ',' . urlencode($_POST['city']) . ',' . urlencode($_POST['state']) . '&key=' . $gmapskey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiaddress);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $geoloc = json_decode(curl_exec($ch), true);
    
    if(strcmp($geoloc['status'], 'ZERO_RESULTS')==0){
        exit('<h1>Invalid address!</h1>');
    }
    $formattedaddress = explode(",", $geoloc['results'][0]['formatted_address']);
    $latitude = $geoloc['results'][0]['geometry']['location']['lat'];
    $longitude = $geoloc['results'][0]['geometry']['location']['lng'];
    $addressline1 = trim($formattedaddress[0]);
    $city = trim($formattedaddress[1]);
    $stateandzip = explode(" ", trim($formattedaddress[2]));
    
    //prepared statements to enter parent data
    $stmt = $conn->prepare('INSERT INTO parents (username, password, firstname, lastname, email, phone, complex, addressline1, addressline2, city, state, zip, latitude, longitude, bio, lastonline, activated, picformat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssisssssiddssis', $_POST['username'], $password, $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['phone'], $_POST['aptcomplex'], $addressline1, $_POST['addressline2'], $city, $stateandzip[0], $stateandzip[1], $latitude, $longitude, $_POST['bio'], $datetime, $zero, $filetype);
    $stmt->execute();
    $parentid = $conn->insert_id;
    $stmt->close();
    
    //prepare statements to enter kid data
    $stmt = $conn->prepare('INSERT INTO kids (firstname, lastname, age, bio, parentid) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssisi', $_POST['kidfirstname'], $_POST['kidlastname'], $_POST['kidage'], $_POST['kidbio'], $parentid);
    $stmt->execute();
    $kidid = $conn->insert_id;
    $stmt->close();
    
    //associate parent with kid
    $stmt = $conn->prepare('INSERT INTO parentkidrelation (parentid, kidid) VALUES (?, ?)');
    $stmt->bind_param('ii', $parentid, $kidid);
    $stmt->execute();
    $stmt->close();
    
    //now handle file upload
    $directory = "uploads/";
    $uploadstatus = 1;
    $filename = $directory.$parentid.'.'.$filetype;
    
    //file verification
    $check = getimagesize($_FILES["addressverification"]["tmp_name"]);
    if($check == false) {
        echo "<h1>File is not an image.</h1>";
        $uploadstatus = 0;
    }
    
    //file size verification
    if ($_FILES["addressverification"]["size"] > 500000000) {
        echo "<h1>Sorry, your file is too large.</h1>";
        $uploadstatus = 0;
    }
    
    //extension verification
    if($filetype != "jpg" && $filetype != "png" && $filetype != "jpeg" && $filetype != "JPG" && $filetype != "PNG" && $filetype != "JPEG") {
        echo "<h1>Sorry, only JPG, JPEG, PNG files are allowed.</h1>";
        $uploadstatus = 0;
    }
    
    if($uploadstatus == 1){
        move_uploaded_file($_FILES["addressverification"]["tmp_name"], $filename);
    }
    else {
        exit('<h1>Please try again.</h1>')
    }
    
    echo '<h1>You have been registered! Please wait to be activated.</h1>';    
}
else {
    //display registration form
    echo '<form id="register" method="post" action="" class="form-signin" enctype="multipart/form-data">
    <h2 class="form-signin-heading">Join the family.</h2>
  <label for="username" class="sr-only">Username</label>
    <input type="text" id="username" name="username" placeholder="Username" class="form-control" required>
      <p></p>
      <label for="password" class="sr-only">Password</label>
    <input type="password" id="password" name="password" class="form-control" placeholder="********" required>
      <p></p>
      <label for="firstname" class="sr-only">First Name</label>
    <input type="text" id="firstname" name="firstname" placeholder="First name" class="form-control" required>
      <p></p>
      <label for="lastname" class="sr-only">Last Name</label>
    <input type="text" id="lastname" name="lastname" placeholder="Last name" class="form-control" required>
      <p></p>
      <label for="email" class="sr-only">Email</label>
    <input type="email" id="email" name="email" placeholder="Email" class="form-control" required>
      <p></p>
      <label for="phone" class="sr-only">Phone number</label>
    <input type="text" id="phone" name="phone" placeholder="Phone" class="form-control" required>
      <p></p>
          <label for="aptcomplex" class="sr-only">Apartment Complex</label>
    <input type="text" id="aptcomplex" name="aptcomplex" placeholder="Apartment complex" class="form-control" required>
      <p></p>
          <label for="addressline1" class="sr-only">Address Line 1</label>
    <input type="text" id="addressline1" name="addressline1" placeholder="Address Line 1" class="form-control" required>
      <p></p>
          <label for="addressline2" class="sr-only">Address Line 2</label>
    <input type="text" id="addressline2" name="addressline2" placeholder="Address Line 2" class="form-control">
      <p></p>
       <label for="addressline2" class="sr-only">City</label>
    <input type="text" id="city" name="city" placeholder="City" class="form-control" required>
      <p></p>
      <label for="state" class="sr-only">State</label>
      <select name="state" id="state" class="form-control" required>
	<option value="AL">Alabama</option>
	<option value="AK">Alaska</option>
	<option value="AZ">Arizona</option>
	<option value="AR">Arkansas</option>
	<option value="CA">California</option>
	<option value="CO">Colorado</option>
	<option value="CT">Connecticut</option>
	<option value="DE">Delaware</option>
	<option value="DC">District Of Columbia</option>
	<option value="FL">Florida</option>
	<option value="GA">Georgia</option>
	<option value="HI">Hawaii</option>
	<option value="ID">Idaho</option>
	<option value="IL">Illinois</option>
	<option value="IN">Indiana</option>
	<option value="IA">Iowa</option>
	<option value="KS">Kansas</option>
	<option value="KY">Kentucky</option>
	<option value="LA">Louisiana</option>
	<option value="ME">Maine</option>
	<option value="MD">Maryland</option>
	<option value="MA">Massachusetts</option>
	<option value="MI">Michigan</option>
	<option value="MN">Minnesota</option>
	<option value="MS">Mississippi</option>
	<option value="MO">Missouri</option>
	<option value="MT">Montana</option>
	<option value="NE">Nebraska</option>
	<option value="NV">Nevada</option>
	<option value="NH">New Hampshire</option>
	<option value="NJ">New Jersey</option>
	<option value="NM">New Mexico</option>
	<option value="NY">New York</option>
	<option value="NC">North Carolina</option>
	<option value="ND">North Dakota</option>
	<option value="OH">Ohio</option>
	<option value="OK">Oklahoma</option>
	<option value="OR">Oregon</option>
	<option value="PA">Pennsylvania</option>
	<option value="RI">Rhode Island</option>
	<option value="SC">South Carolina</option>
	<option value="SD">South Dakota</option>
	<option value="TN">Tennessee</option>
	<option value="TX">Texas</option>
	<option value="UT">Utah</option>
	<option value="VT">Vermont</option>
	<option value="VA">Virginia</option>
	<option value="WA">Washington</option>
	<option value="WV">West Virginia</option>
	<option value="WI">Wisconsin</option>
	<option value="WY">Wyoming</option>
</select><p></p>
       <label for="addressline2" class="sr-only">Zip Code</label>
    <input type="text" id="zip" name="zip" placeholder="Zip Code" class="form-control" required>
      <p></p>
      
          <label for="bio" class="sr-only">Bio</label>
          <textarea rows="4" cols="50" id="bio" name="bio" placeholder="Bio" required>
</textarea>
          <p></p>
          <label for="addressverification" class="sr-only">Upload address verification</label>
          <input type="file" id="addressverification" name="addressverification" class="form-control" required>
          <p></p><p></p><hr><br/>More children can be added later<p></p>
          <label for="kidfirstname" class="sr-only">Kid First Name</label>
    <input type="text" id="kidfirstname" name="kidfirstname" placeholder="Kid First name" class="form-control" required>
      <p></p>
      <label for="kidlastname" class="sr-only">Kid Last Name</label>
    <input type="text" id="kidlastname" name="kidlastname" placeholder="Kid Last name" class="form-control" required>
      <p></p>
      <label for="kidage" class="sr-only">Kid age</label>
    <input type="num" id="kidage" name="kidage" placeholder="Kid age" class="form-control" required>
      <p></p>
          <label for="kidbio" class="sr-only">Kid Bio</label>
          <textarea rows="4" cols="50" id="kidbio" name="kidbio" class="form-control" required>
Bio
</textarea><p></p>
<div class="checkbox">
          <label>
            <input type="checkbox" value="confirm"> I certify that I am a legal guardian, and that the above information is correct.
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="create">Register</button>
      </form>';
}

?>