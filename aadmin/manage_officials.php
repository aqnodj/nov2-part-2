<?php
session_start(); 

// Access for User Account only
if (!isset($_SESSION["user_id"]) || $_SESSION["account_type"] != "1") {
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

// Include database connection
include '../connections.php';

// Handle form submission for updating officials
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize inputs
    $oldName = $connections->real_escape_string($_POST['oldName']);
    $newName = $connections->real_escape_string($_POST['newName']);

    // Update query
    $sql = "UPDATE barangay_officials SET name='$newName' WHERE name='$oldName'";

    if ($connections->query($sql) === TRUE) {
        $_SESSION['message'] = 'Official updated successfully';
    } else {
        $_SESSION['message'] = 'Error updating record: ' . $connections->error;
    }
}

// Retrieve the list of barangay officials from the database
$result = $connections->query("SELECT name, position FROM barangay_officials");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Officials</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidenav.php'; ?> 
    <div id="content">
        <h1>Barangay Officials</h1>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td><button class="btn btn-primary" onclick="openEditModal('<?php echo htmlspecialchars($row['name']); ?>')">Edit</button></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Official</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="officialEditForm" method="POST" onsubmit="confirmUpdate(event)">
                            <input type="hidden" id="oldName" name="oldName" required>
                            <div class="mb-3">
                                <label for="newName" class="form-label">New Name:</label>
                                <input type="text" class="form-control" id="newName" name="newName" required>
                            </div>
                            <button type="submit" class="btn btn-warning">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function openEditModal(name) {
            document.getElementById('oldName').value = name;
            document.getElementById('newName').value = name; // Pre-fill with current name
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        async function confirmUpdate(event) {
            event.preventDefault(); // Prevent the form from submitting immediately
            const newName = document.getElementById('newName').value;

            const result = await Swal.fire({
                title: 'Update Official',
                text: "Are you sure you want to update this official's name to " + newName + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            });

            if (result.isConfirmed) {
                document.getElementById('officialEditForm').submit(); // Submit the form if confirmed
            }
        }

        // Show SweetAlert notifications based on session message
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['message'])): ?>
                const message = <?= json_encode($_SESSION['message']); ?>;
                let title = 'Notification';
                let icon = 'success';
                
                if (message.includes('updated')) {
                    title = 'Official updated';
                } else {
                    icon = 'error';
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: message,
                });
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
