<?php

session_start();

include "dp.php";

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

$id = $_SESSION['user_id'];
$error = "";
$success = "";

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

if (isset($_POST['update'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $photo = $user['photo']; // keep existing photo unless a new one is uploaded

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK && $_FILES['photo']['name'] != "") {

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $originalName = $_FILES['photo']['name'];
        $tmp_name = $_FILES['photo']['tmp_name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $checkImage = getimagesize($tmp_name);

        if (!in_array($ext, $allowedExt) || $checkImage === false) {

            $error = "Only image files are allowed (jpg, jpeg, png, gif, webp)!";

        } else {

            $newPhoto = uniqid('user_', true) . '.' . $ext;
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($tmp_name, $uploadDir . $newPhoto)) {
                $photo = $newPhoto;
            } else {
                $error = "Image Upload Failed!";
            }
        }
    }

    if ($error === "") {

        $sql = "UPDATE users SET name = ?, email = ?, photo = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $photo, $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Profile Updated Successfully!";
            $user['name'] = $name;
            $user['email'] = $email;
            $user['photo'] = $photo;
            header("refresh:1;url=profile.php");
        } else {
            $error = "Update Failed!";
        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
</head>

<body>

<h2>Update Profile</h2>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    Current Photo:

    <br>

    <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>"
         width="100"
         height="100">

    <br><br>

    New Photo:
    <input type="file" name="photo" accept="image/*">

    <br><br>

    Name:
    <input type="text"
           name="name"
           value="<?php echo htmlspecialchars($user['name']); ?>"
           required>

    <br><br>

    Email:
    <input type="email"
           name="email"
           value="<?php echo htmlspecialchars($user['email']); ?>"
           required>

    <br><br>

    <input type="submit"
           name="update"
           value="Update">

</form>

<br>

<a href="profile.php">Back to Profile</a>

</body>
</html>
