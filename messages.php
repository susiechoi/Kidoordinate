<?php

include'global.php';
include'header.php';

//logged in and verified

echo 'Welcome back, ' . $_SESSION['firstname'] . '!<br/><br/>';

if(isset($_GET['action']) && $_GET['action'] == 'send' && isset($_GET['id'])){
    if(count(array_filter($_POST))!=count($_POST)){
        echo "Something is empty";
    }
    $stmt = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows === 1) {
        $stmt->bind_result($firstname, $lastname);
        $stmt->fetch();
    }
    else {
        exit('Invalid ID!');
    }
    $stmt->close();
    
    if($_GET['id'] == $_SESSION['userid']){
        exit('You can\'t send a message to yourself!');
    }
    $zero = 0;
    $stmt = $conn->prepare('INSERT INTO messages (fromid, toid, content, isread) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iisi', $_SESSION['userid'], $_GET['id'], $_POST['message'], $zero);
    $stmt->execute();
    $stmt->close();
    
    echo 'Your message has been successfully sent!';
}

elseif(isset($_GET['action']) && $_GET['action'] == 'compose' && isset($_GET['id'])){
    //check if parent id is a real id
    $stmt = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows === 1) {
        $stmt->bind_result($firstname, $lastname);
        $stmt->fetch();
    }
    else {
        exit('Invalid ID!');
    }
    $stmt->close();
    
    if($_GET['id'] == $_SESSION['userid']){
        exit('You can\'t send a message to yourself!');
    }
    
    echo 'Composing a message to: ' . $firstname . ' ' . $lastname . '<br/>
    <form id="compose" method="post" action="messages.php?action=send&id=' . $_GET['id'] . '">
    <label for="message">Message</label>
          <textarea rows="4" cols="50" id="message" name="message" required>
Message
</textarea><br/><input type="submit">Submit</button>
      </form>';
    
}
else {
    $read = 0;
    echo 'Unread messages:<br/><hr>';
    //then find unread messages
    $stmt = $conn->prepare("SELECT fromid, content FROM messages WHERE toid = ? AND isread = ?");
    $stmt->bind_param("ii", $_SESSION['userid'], $read);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $stmt->bind_result($fromparentid, $content); 
        while ($stmt->fetch()) {
            //find from parent name
            $stmt2 = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
            $stmt2->bind_param("i", $fromparentid);
            $stmt2->execute();
            $stmt2->store_result();
            if($stmt2->num_rows === 1) {
                $stmt2->bind_result($fromparentfirstname, $fromparentlastname);
                $stmt2->fetch();
            }
            $stmt2->close();

            echo 'From: ' . $fromparentfirstname . ' ' . $fromparentlastname . '<br/>Message:<br/>' . $content . '<br/><br/><a href="messages.php?action=compose&id=' . $fromparentid. '">Reply</a><hr>';
        }
    }
    else {
        echo 'You have no unread messages!';
    }
    $stmt->close();
    echo '<br/><br/>';
    //find read messages
    echo 'Messages:<br/><hr>';
    $read = 1;
    $stmt = $conn->prepare("SELECT fromid, content FROM messages WHERE toid = ? AND isread = ?");
    $stmt->bind_param("ii", $_SESSION['userid'], $read);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $stmt->bind_result($fromparentid, $content); 
        while ($stmt->fetch()) {
            //find from parent name
            $stmt2 = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
            $stmt2->bind_param("i", $fromparentid);
            $stmt2->execute();
            $stmt2->store_result();
            if($stmt2->num_rows === 1) {
                $stmt2->bind_result($fromparentfirstname, $fromparentlastname);
                $stmt2->fetch();
            }
            $stmt2->close();

            echo 'From: ' . $fromparentfirstname . ' ' . $fromparentlastname . '<br/>Message:<br/>' . $content . '<br/><br/><a href="messages.php?action=compose&id=' . $fromparentid. '">Reply</a><hr>';
        }
    }
    else {
        echo 'You have no messages!';
    }
    $stmt->close();
    echo '<br/><br/>';
    //find sent messages
    echo 'Sent Messages:<br/><hr>';
    $stmt = $conn->prepare("SELECT toid, content FROM messages WHERE fromid = ?");
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $stmt->bind_result($toparentid, $content); 
        while ($stmt->fetch()) {
            //find to parent name
            $stmt2 = $conn->prepare("SELECT firstname, lastname FROM parents WHERE id = ?");
            $stmt2->bind_param("i", $toparentid);
            $stmt2->execute();
            $stmt2->store_result();
            if($stmt2->num_rows === 1) {
                $stmt2->bind_result($toparentfirstname, $toparentlastname);
                $stmt2->fetch();
            }
            $stmt2->close();

            echo 'To: ' . $toparentfirstname . ' ' . $toparentlastname . '<br/>Message:<br/>' . $content . '<br/><hr>';
        }
    }
    else {
        echo 'You have no sent messages!';
    }
    $stmt->close();
}
?>