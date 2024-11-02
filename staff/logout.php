<?php
session_start(); // Start the session

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to login.php located outside the current folder
header("Location: ../login.php");
exit();
?>
