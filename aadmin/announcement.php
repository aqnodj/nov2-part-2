<?php
session_start(); 

// Access for Admin Account only
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
                    text: 'Admin lang ang may access dito',
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcement</title> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
</head>
<body>
    
<?php include 'admin_sidenav.php'; ?> 
    <div id="content">
        <br>
        
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
            Add Announcement
        </button>

        <div class="row mt-4">
            <div class="col-lg-12">
                <h3>Announcements List</h3>
                <div class="list-group" id="announcementList">
                    <?php
                    include '../connections.php';

                    // Insert, delete, update logic
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $message = mysqli_real_escape_string($connections, $_POST['message']);
                        
                        if (isset($_POST['announcementId']) && !empty($_POST['announcementId'])) {
                            $announcementId = intval($_POST['announcementId']);
                            
                            // Fetch existing announcement
                            $existingQuery = "SELECT message FROM announcement WHERE id = $announcementId";
                            $existingResult = mysqli_query($connections, $existingQuery);
                            $existingRow = mysqli_fetch_assoc($existingResult);
                            
                            // Prepare the new message
                            $newMessage = ($existingRow['message'] === $message) ? $message : $message . "\n\n(edited)";

                            $sql = "UPDATE announcement SET message = '$newMessage', date = NOW() WHERE id = $announcementId";

                            if (mysqli_query($connections, $sql)) {
                                echo "<script>Swal.fire('Success!', 'Announcement updated successfully.', 'success');</script>";
                            } else {
                                echo "<script>Swal.fire('Error!', 'Error: " . mysqli_error($connections) . "', 'error');</script>";
                            }
                        } else {
                            $sql = "INSERT INTO announcement (message, date) VALUES ('$message', NOW())";

                            if (mysqli_query($connections, $sql)) {
                                echo "<script>Swal.fire('Success!', 'Announcement added successfully.', 'success');</script>";
                            } else {
                                echo "<script>Swal.fire('Error!', 'Error: " . mysqli_error($connections) . "', 'error');</script>";
                            }
                        }
                    }

                    // Delete Announcement Logic
                    if (isset($_GET['delete_id'])) {
                        $deleteId = intval($_GET['delete_id']);
                        $sql = "DELETE FROM announcement WHERE id = $deleteId";

                        if (mysqli_query($connections, $sql)) {
                            echo "<script>Swal.fire('Success!', 'Announcement deleted successfully.', 'success');</script>";
                        } else {
                            echo "<script>Swal.fire('Error!', 'Error: " . mysqli_error($connections) . "', 'error');</script>";
                        }
                    }

                    // Fetch and display announcements
                    $result = mysqli_query($connections, "SELECT * FROM announcement ORDER BY date DESC");
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='list-group-item d-flex justify-content-between align-items-center'>";
                        echo "<div class='me-auto'>";
                        echo nl2br(htmlspecialchars($row['message'])) . "<br>";
                        echo "<small>Posted on: " . $row['date'] . "</small>";
                        echo "</div>";
                        echo "<div class='btn-group'>";
                        echo "<button class='btn btn-warning' onclick='editAnnouncement(" . $row['id'] . ", " . json_encode($row['message']) . ")'>Update</button>";
                        echo "<span style='margin: 0 5px;'></span>"; //
                        echo "<a href='announcement.php?delete_id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirmDelete(event)'>Delete</a>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Announcement Modal -->
    <div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAnnouncementModalLabel">Add Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="modalAnnouncementForm" action="announcement.php" method="POST">
                        <input type="hidden" id="modalAnnouncementId" name="announcementId">
                        <div class="mb-3">
                            <label for="modalMessage" class="form-label">Announcement Message:</label>
                            <textarea class="form-control" id="modalMessage" name="message" rows="4" placeholder="Enter announcement message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success" id="modalSubmitBtn">Add Announcement</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editAnnouncement(id, message) {
            document.getElementById('modalAnnouncementId').value = id;
            document.getElementById('modalMessage').value = message.replace(/\n\n\(edited\)/, ''); // Remove " (edited)" for editing
            document.getElementById('addAnnouncementModalLabel').innerText = 'Update Announcement';
            document.getElementById('modalSubmitBtn').innerText = 'Update Announcement';

            var modal = new bootstrap.Modal(document.getElementById('addAnnouncementModal'));
            modal.show();
        }

        function confirmDelete(event) {
            event.preventDefault();
            const link = event.currentTarget;
            Swal.fire({
                title: 'Are you sure?',
                text: 'This announcement will be permanently deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link.href;
                }
            });
        }

        document.getElementById('modalAnnouncementForm').onsubmit = function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to save this announcement?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        };
    </script>
</body>
</html>
