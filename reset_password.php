<?php
session_start();
include("connections.php"); // Include your database connection

$token = $_GET['token'] ?? '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];

    // Validate the token
    $result = mysqli_query($connections, "SELECT * FROM users WHERE reset_token='$token'");
    if (mysqli_num_rows($result) > 0) {
        // Token is valid, update the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($connections, "UPDATE users SET password='$hashed_password', reset_token=NULL WHERE reset_token='$token'");
        $message = "Your password has been reset successfully. You can now log in.";
        header('location: login.php');
        exit();
    } else {
        $message = "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="form-container">
    <div class="form-box">
        <h2>Reset Your Password</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?token=" . htmlspecialchars($token); ?>">
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<style>
    .form-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .form-box {
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 100%;
    }
</style>

</body>
</html>
