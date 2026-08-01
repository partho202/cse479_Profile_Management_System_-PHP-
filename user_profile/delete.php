<?php

session_start();

include "dp.php";

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

$id = $_SESSION['user_id'];

$sql = "DELETE FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    session_destroy();

    setcookie(
        "user_email",
        "",
        time() - 3600,
        "/"
    );

    header("Location: register.php");
    exit();

} else {

    echo "Profile Delete Failed!";

}

?>
