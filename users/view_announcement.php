<?php
session_start();

// Access for User Account only
if (!isset($_SESSION["user_id"]) || $_SESSION["account_type"] != "3") {

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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>

    <style>
        
        #announcementList {
            max-width: 100%;
            word-wrap: break-word; /* Ensure long words wrap properly */
            overflow-wrap: break-word;
        }

        .announcement-item {
            max-width: 100%; /* Limit each announcement to not exceed the container */
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

       
        .list-group-item {
            padding: 15px;
            border-radius: 8px;
        }

        .list-group-item h5 {
            font-weight: bold; /* Make titles bold */
        }
        
   
    </style>
</head>
<body>
<?php include 'user_sidenav.php'; ?> 
    
    <div id="content" class="container" style="margin-left: 250px;">
       

        <!-- Announcement List (Read Only) -->
        <div class="mt-4">
            <h3>Current Announcements</h3>
            <div class="list-group" id="announcementList">
                <?php
                // Include database connection
                include '../connections.php';

                // Fetch and display announcements (read-only)
                $result = mysqli_query($connections, "SELECT * FROM announcement ORDER BY date DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='list-group-item announcement-item mb-3'>";
                   
                    echo "<p>" . nl2br(htmlspecialchars($row['message'])) . "</p>"; // Line breaks for the message
                    echo "<small class='text-muted'>Posted on: " . $row['date'] . "</small>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
