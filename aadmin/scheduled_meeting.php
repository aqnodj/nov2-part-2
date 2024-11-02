<?php
include '../connections.php';
session_start();

// Access for Admin Account only
if (!isset($_SESSION["user_id"]) || $_SESSION["account_type"] != "1") {
    header("Location: access_denied.php");
    exit();
}

// Fetch all meetings, including canceled, ordered by created_at for latest reports first
$scheduled_query = "
    SELECT br.*, u.firstname, u.lastname 
    FROM blotter_report br 
    JOIN users u ON br.user_id = u.id 
    ORDER BY br.created_at DESC
";
$scheduled_result = mysqli_query($connections, $scheduled_query);

// Function to format the date
function formatDate($date) {
    return date('F j, Y', strtotime($date)); // e.g., August 21, 2024
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Meetings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        #content {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }
        h2 {
            margin-bottom: 20px;
            color: #343a40;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        .table th {
            background-color: #343a40;
            color: #fff;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
<?php include 'admin_sidenav.php'; ?>
    <div id="content">
        <h2 class="mb-4">Scheduled Meetings</h2>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Firstname</th>
                        <th>Lastname</th>
                        <th>Report</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Meeting Date</th>
                        <th>Meeting Time</th>
                        <th>Date Reported</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($scheduled_result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['firstname']) ?></td>
                        <td><?= htmlspecialchars($row['lastname']) ?></td>
                        <td><?= htmlspecialchars($row['report_content']) ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                        <td><?= $row['meeting_date'] ? formatDate($row['meeting_date']) : 'N/A' ?></td>
                        <td><?= $row['meeting_time'] ? date('h:i A', strtotime($row['meeting_time'])) : 'N/A' ?></td>
                        <td>
                            <?= formatDate($row['created_at']) ?><br>
                            <?= date('h:i A', strtotime($row['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
