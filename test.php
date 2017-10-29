<?php
session_start();
if(isset($_SESSION['userid'])){
    echo 'Welcome ' . $_SESSION['firstname'] . '!';
}
else {
    echo 'You\'re not logged in!';
}

?>