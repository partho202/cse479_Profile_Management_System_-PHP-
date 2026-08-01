<?php

$conn = mysqli_connect("localhost", "root", "", "user_profile");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>
