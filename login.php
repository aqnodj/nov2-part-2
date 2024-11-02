<?php
session_start(); // Start the session

$email = $password = "";
$emailErr = $passwordErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Email Validation
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = $_POST["email"];
    }

    // Password Validation
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = $_POST["password"];
    }

    if ($email && $password) {
        include("connections.php");

        // Check if the email exists in the 'users' table
        $check_email = mysqli_query($connections, "SELECT * FROM users WHERE email='$email'");
        $check_email_row = mysqli_num_rows($check_email);

        if ($check_email_row > 0) {
            // If email exists in user table
            $row = mysqli_fetch_assoc($check_email);
            $user_id = $row["id"];
            $db_password = $row["password"];
            $db_account_type = $row["account_type"];

            // Password verification
            if (password_verify($password, $db_password)) {
                // Set session variable
                $_SESSION["user_id"] = $user_id;
                $_SESSION["account_type"] = $db_account_type; // Store account type in session
                
                // Redirect depending on account type
                if ($db_account_type == "1") {
                    echo "<script>window.location.href='aadmin/dashboard.php';</script>";
                } elseif ($db_account_type == "2") { // Staff
                    echo "<script>window.location.href='staff/dashboard.php';</script>";
                } else {
                    echo "<script>window.location.href='users/dashboard.php';</script>";
                }
            } else {
                $passwordErr = "Invalid password.";
            }
        } else {
            // If wala ang email sa users or staff table
            $check_staff_email = mysqli_query($connections, "SELECT * FROM staff WHERE email='$email'");
            $check_staff_row = mysqli_num_rows($check_staff_email);

            if ($check_staff_row > 0) {
                // If email exists in 'staff' table
                $row = mysqli_fetch_assoc($check_staff_email);
                $staff_id = $row["id"];
                $db_password = $row["password"];
                $db_account_type = $row["account_type"];

                // Password verification
                if (password_verify($password, $db_password)) {
                    $_SESSION["staff_id"] = $staff_id;
                    $_SESSION["account_type"] = $db_account_type; // Store account type in session
                   
                    if ($db_account_type == "1") {
                        echo "<script>window.location.href='aadmin/dashboard.php';</script>";
                    } elseif ($db_account_type == "2") { // Staff
                        echo "<script>window.location.href='staff/dashboard.php';</script>";
                    } else {
                        echo "<script>window.location.href='users/dashboard.php';</script>";
                    }
                } else {
                    $passwordErr = "Invalid password.";
                }
            } else {
                $emailErr = "Email is not registered!";
            }
        }
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .error {
        color: red;
    }

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

<div class="form-container">
    <div class="form-box">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <!-- Email Input -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <span class="error"><?php echo $emailErr; ?></span>
            </div>

            <!-- Password Input -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                <span class="error"><?php echo $passwordErr; ?></span>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>

            <!-- Forgot Password Link -->
            <div class="mt-3">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

        </form>
    </div>
</div>
