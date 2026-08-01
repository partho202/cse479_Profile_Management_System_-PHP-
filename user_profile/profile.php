<?php

session_start();

include "dp.php";

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

$id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    
    session_destroy();
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
</head>

<body>

<h2>My Profile</h2>

<img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>"
     width="150"
     height="150">

<br><br>

Name:
<?php echo htmlspecialchars($user['name']); ?>

<br><br>

Email:
<?php echo htmlspecialchars($user['email']); ?>

<br><br>

<a href="update.php">Update Profile</a>

<br><br>

<a href="delete.php"
   onclick="return confirm('Are you sure you want to delete your profile?');">
   Delete Profile
</a>

<br><br>

<a href="logout.php">Logout</a>

</body>
</html>
