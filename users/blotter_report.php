<?php
session_start();
include '../connections.php';

// Check the connection
if (!$connections) {
    die("Connection failed: " . mysqli_connect_error());
}

// Access for Admin Account only
if (!isset($_SESSION["user_id"]) || $_SESSION["account_type"] != "3") {
    // Output the SweetAlert warning and redirect back to the previous page
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Access Denied</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Access Denied',
                    text: 'Normal Acc lang ang may access dito',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.history.back(); // Redirects back to the previous page
                    }
                });
            });
        </script>
    </head>
    <body>
    </body>
    </html>";
    exit();
}

// Check if the form to submit a blotter report is submitted
if (isset($_POST['submit_report'])) {
    var_dump($_POST); // Debug: Check if the form data is being submitted
    var_dump($_SESSION['user_id']); // Debug: Check if user_id is set

    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
    $report_content = $_POST['report_content'];
    $reason = $_POST['reason'];

    // Sanitize user input to prevent SQL injection
    $user_id = mysqli_real_escape_string($connections, $user_id);
    $report_content = mysqli_real_escape_string($connections, $report_content);
    $reason = mysqli_real_escape_string($connections, $reason);

    // Insert the blotter report into the database
    $query = "INSERT INTO blotter_report (user_id, report_content, reason, status) VALUES ('$user_id', '$report_content', '$reason', 'pending')";

    if (mysqli_query($connections, $query)) {
        // Redirect to refresh the page with a success message
        header("Location: blotter_report.php?message=1");
        exit();
    } else {
        echo "Error: " . mysqli_error($connections);
    }
}

// Fetch the user's blotter report
$user_id = $_SESSION['user_id']; 
$reports_query = "SELECT br.* FROM blotter_report br WHERE br.user_id = '$user_id' ORDER BY br.created_at DESC";
$reports_result = mysqli_query($connections, $reports_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Include SweetAlert -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
</head>
<body>
<?php include 'user_sidenav.php'; ?> 
    <div id="content">
        <div class="container">
            <h2 class="my-4">Report a Blotter</h2>

            <!-- Blotter report sub form-->
            <form id="blotterForm" method="POST" action="blotter_report.php" class="mb-4">
                <input type="hidden" name="submit_report" value="1"> <!-- Hidden input to trigger form submission -->
                <div class="form-group">
                    <label for="report_content">Salaysay ng Pangyayari:</label>
                    <textarea name="report_content" id="report_content" rows="4" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label for="reason">Bakit Magpapablotter:</label>
                    <textarea name="reason" id="reason" rows="2" class="form-control" required></textarea>
                </div>
                <p><strong>Note:</strong> You need to pay 200 pesos for the blotter report.</p>
                <button type="button" id="submitReportBtn" class="btn btn-primary">Submit Report</button>
            </form>

            <script>
                // Check for success message
                <?php if (isset($_GET['message'])): ?>
                    let message = <?= json_encode($_GET['message']) ?>;
                    if (message == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Blotter report submitted successfully!',
                        });
                    }
                <?php endif; ?>

                document.getElementById('submitReportBtn').addEventListener('click', function() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You are about to submit your blotter report.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, submit it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('blotterForm').submit(); // Submit the form if confirmed
                        }
                    });
                });
            </script>

            <h2 class="my-4">Your Blotter Reports</h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Date Reported</th>
                            <th>Report</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Meeting Date</th>
                            <th>Meeting Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($reports_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td><?= htmlspecialchars($row['report_content']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                            <td><?= $row['meeting_date'] ?: 'N/A' ?></td>
                            <td><?= $row['meeting_time'] ? date('h:i A', strtotime($row['meeting_time'])) : 'N/A' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
