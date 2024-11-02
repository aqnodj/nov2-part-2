<?php
include '../connections.php';
session_start();

// Access for Admin Account only
if (!isset($_SESSION["staff_id"]) || $_SESSION["account_type"] != "2") {
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
                    text: 'Staff lang ang may access dito',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.history.back();
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

// Check if the request is an AJAX request for assigning a meeting
if (isset($_POST['assign_meeting'])) {
    $blotter_id = $_POST['blotter_id'];
    $meeting_date = $_POST['meeting_date'];
    $meeting_time = $_POST['meeting_time'];

    // Update blotter report
    $query = "UPDATE blotter_report SET status='assigned', meeting_date='$meeting_date', meeting_time='$meeting_time' WHERE blotter_id='$blotter_id'";
    if (mysqli_query($connections, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Check if the request is an AJAX request for canceling a meeting
if (isset($_POST['cancel_meeting'])) {
    $blotter_id = $_POST['blotter_id'];

    // If canceled, remove the date and time
    $query = "UPDATE blotter_report SET status='canceled', meeting_date=NULL, meeting_time=NULL WHERE blotter_id='$blotter_id'";
    if (mysqli_query($connections, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Check if the request is an AJAX request for marking a meeting as completed
if (isset($_POST['complete_meeting'])) {
    $blotter_id = $_POST['blotter_id'];

    // Update the status to 'finished'
    $query = "UPDATE blotter_report SET status='finished' WHERE blotter_id='$blotter_id'";
    if (mysqli_query($connections, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Fetch blotter report in descending order of created_at for latest reports first
$blotter_query = "
    SELECT br.*, u.firstname, u.lastname 
    FROM blotter_report br 
    JOIN users u ON br.user_id = u.id 
    ORDER BY br.created_at DESC
";
$blotter_result = mysqli_query($connections, $blotter_query);

// Function to format dates
function formatDate($date) {
    return date('F j, Y', strtotime($date)); // e.g., August 24, 2024
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blotter Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<?php include 'staff_sidenav.php'; ?> 
    <div id="content">
        <h2 class="mb-4">Manage Blotter Reports</h2>

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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($blotter_result)): ?>
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
                        <td>
                            <div class="d-flex">
                                <form id="assignForm_<?= $row['blotter_id'] ?>" class="me-2">
                                    <input type="hidden" name="blotter_id" value="<?= $row['blotter_id'] ?>">
                                    <input type="date" name="meeting_date" required class="form-control d-inline" style="width: auto;">
                                    <input type="time" name="meeting_time" required class="form-control d-inline" style="width: auto;">
                                    <button type="button" class="btn btn-primary ms-2" onclick="confirmAssign(<?= $row['blotter_id'] ?>)">Assign</button>
                                </form>
                                <form id="cancelForm_<?= $row['blotter_id'] ?>" class="me-2">
                                    <input type="hidden" name="blotter_id" value="<?= $row['blotter_id'] ?>">
                                    <button type="button" class="btn btn-danger" onclick="confirmCancel(<?= $row['blotter_id'] ?>)">Cancel</button>
                                </form>
                                <button type="button" class="btn btn-success" onclick="confirmComplete(<?= $row['blotter_id'] ?>)">Finish Meeting</button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function confirmAssign(blotterId) {
        const form = $('#assignForm_' + blotterId);
        const meetingDate = form.find('input[name="meeting_date"]').val();
        const meetingTime = form.find('input[name="meeting_time"]').val();

        if (!meetingDate || !meetingTime) {
            Swal.fire({
                icon: 'warning',
                title: 'Input Required',
                text: 'Please fill in both meeting date and time.',
                confirmButtonText: 'OK'
            });
            return; 
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to assign this meeting!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, assign it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'manage_blotter_report.php',
                    data: {
                        assign_meeting: true,
                        blotter_id: blotterId,
                        meeting_date: meetingDate,
                        meeting_time: meetingTime
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.success) {
                            Swal.fire('Success!', 'Meeting assigned successfully!', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to assign meeting.', 'error');
                        }
                    }
                });
            }
        });
    }

    function confirmCancel(blotterId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to cancel this meeting!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, keep it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'manage_blotter_report.php',
                    data: {
                        cancel_meeting: true,
                        blotter_id: blotterId
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.success) {
                            Swal.fire('Success!', 'Meeting canceled successfully!', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to cancel meeting.', 'error');
                        }
                    }
                });
            }
        });
    }

    function confirmComplete(blotterId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to mark this meeting as completed!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, finish it!',
            cancelButtonText: 'No, keep it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'manage_blotter_report.php',
                    data: {
                        complete_meeting: true,
                        blotter_id: blotterId
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.success) {
                            Swal.fire('Success!', 'Meeting marked as finished!', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to mark meeting as finished.', 'error');
                        }
                    }
                });
            }
        });
    }
    </script>
</body>
</html>
