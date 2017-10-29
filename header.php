<?php
session_start();

if(isset($_SESSION['userid'])){
    //logged in
    //connect to db
    $conn = new mysqli($host, $user, $pass, $db);
    if($conn->connect_error){
        die('Connection failed!');
    }
    
    $stmt = $conn->prepare("SELECT activated FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($activated);
    $stmt->fetch();
    if($activated == 0){
        exit('Your account has not yet been activated!');
    }
    $stmt->close();
    
    //find the date and time
    $datetime = date("Y-m-d H:i:s");
    
    $stmt = $conn->prepare("UPDATE parents SET lastonline = ? WHERE id = ?");
    $stmt->bind_param("si", $datetime, $_SESSION['id']);
    $stmt->execute();
    $stmt->close();
}
else {
    //not logged in
    header("Location: login.php");
    die();
}
