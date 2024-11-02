<?php
session_start();

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust the path if necessary
include("connections.php");

$email = "";
$emailErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = mysqli_real_escape_string($connections, $_POST["email"]); // Sanitize email input

        // Check if the email exists in the 'users' table
        $check_email = mysqli_query($connections, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $row = mysqli_fetch_assoc($check_email);
            $token = bin2hex(random_bytes(50)); // Generate a secure token
            $user_id = $row["id"];

            // Store the token in the database
            mysqli_query($connections, "UPDATE users SET reset_token='$token' WHERE id='$user_id'");

            // Prepare reset link
            $reset_link = "http://localhost/barangayweb/reset_password.php?token=$token";

            $mail = new PHPMailer(true); // Create a new PHPMailer instance

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'brgyllipa@gmail.com';
                $mail->Password = 'juut dxwd bkcr uell'; 
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('brgyllipa@gmail.com', 'Brgy. Lumang-Lipa'); // Ensure this matches the username
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "Click this link to reset your password: <a href='$reset_link'>$reset_link</a>";

                // Send the email
                $mail->send();
                echo "A reset link has been sent to your email.";
            } catch (Exception $e) {
                echo "Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $emailErr = "Email is not registered!";
        }
    }
}
?>

<!-- HTML form for email input -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error {
            color: red;
            font-size: 12px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-box">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Enter your email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <span class="error"><?php echo $emailErr; ?></span>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn">Send Reset Link</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
