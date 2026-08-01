<?php

include "dp.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {

        $error = "Password and Confirm Password do not match!";

    } elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {

        $error = "Please select a valid photo!";

    } else {

        // Check if email already exists (prepared statement)
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {

            $error = "This email is already registered!";
            mysqli_stmt_close($checkStmt);

        } else {

            mysqli_stmt_close($checkStmt);

            // Validate file extension (whitelist only images)
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $originalName = $_FILES['photo']['name'];
            $tmp_name = $_FILES['photo']['tmp_name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Also verify it's really an image, not just a renamed file
            $checkImage = getimagesize($tmp_name);

            if (!in_array($ext, $allowedExt) || $checkImage === false) {

                $error = "Only image files are allowed (jpg, jpeg, png, gif, webp)!";

            } else {

                // Generate a safe, unique filename to avoid overwrite / injection via filename
                $photo = uniqid('user_', true) . '.' . $ext;

                $uploadDir = __DIR__ . "/uploads/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $destination = $uploadDir . $photo;

                if (move_uploaded_file($tmp_name, $destination)) {

                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO users (photo, name, email, password) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ssss", $photo, $name, $email, $hashedPassword);

                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Registration Successful! <a href='login.php'>Login Now</a>";
                    } else {
                        $error = "Registration Failed. Please try again.";
                    }

                    mysqli_stmt_close($stmt);

                } else {
                    $error = "Image Upload Failed!";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>User Registration</h2>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    Photo:
    <input type="file" name="photo" accept="image/*" required>
    <br><br>

    Name:
    <input type="text" name="name" required>
    <br><br>

    Email:
    <input type="email" name="email" required>
    <br><br>

    Password:
    <input type="password" name="password" required>
    <br><br>

    Confirm Password:
    <input type="password" name="confirm_password" required>
    <br><br>

    <input type="submit" name="register" value="Register">

</form>

<br>

<a href="login.php">Already have an account? Login</a>

</body>
</html>
