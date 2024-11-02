<?php
session_start();
include '../connections.php'; 

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

// Function to handle user addition
function addStaff($data) {
    global $connections;

    $last_name = $data['last_name'];
    $first_name = $data['first_name'];
    $middle_name = $data['middle_name'];
    $contact_number = $data['contact_number'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $position = $data['position'];
    $sex = $data['sex'];
    $account_type = 2;

    $query_staff = "INSERT INTO staff (last_name, first_name, middle_name, contact_number, email, password, position, sex, account_type) 
                   VALUES ('$last_name', '$first_name', '$middle_name', '$contact_number', '$email', '$password', '$position', '$sex', '$account_type')";
    
    return mysqli_query($connections, $query_staff);
}

// Function to handle user update
function updateStaff($data) {
    global $connections;
    $staff_id = $data['id'];
    $last_name = $data['last_name'];
    $first_name = $data['first_name'];
    $middle_name = $data['middle_name'];
    $contact_number = $data['contact_number'];
    $email = $data['email'];

    // Corrected query
    $query = "UPDATE staff SET last_name='$last_name', first_name='$first_name', middle_name='$middle_name', contact_number='$contact_number', email='$email' WHERE id=$staff_id";
    
    return mysqli_query($connections, $query);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Validate required fields
        if (empty($_POST['last_name']) || empty($_POST['first_name']) || empty($_POST['contact_number']) || empty($_POST['email']) || empty($_POST['password'])) {
            $_SESSION['message'] = "Please fill out all required fields.";
            header("Location: manage_staff.php");
            exit();
        }
        
        // Attempt to add user
        if (addStaff($_POST)) {
            $_SESSION['message'] = "Staff added successfully.";
        } else {
            $_SESSION['message'] = "Error adding Staff.";
        }
        header("Location: manage_staff.php");
        exit();

    } elseif (isset($_POST['update'])) {
        // Validate required fields
        if (empty($_POST['last_name']) || empty($_POST['first_name']) || empty($_POST['contact_number']) || empty($_POST['email'])) {
            $_SESSION['message'] = "Please fill out all required fields.";
            header("Location: manage_staff.php");
            exit();
        }

        // Attempt to update user
        if (updateStaff($_POST)) {
            $_SESSION['message'] = "Staff updated successfully.";
        } else {
            $_SESSION['message'] = "Error updating Staff.";
        }
        header("Location: manage_staff.php");
        exit();
    }
}

// Delete User
if (isset($_GET['delete'])) {
    $staff_id = $_GET['delete'];
    // mysqli_query($connections, "DELETE FROM blotter_report WHERE user_id=$id");
    mysqli_query($connections, "DELETE FROM staff WHERE id=$staff_id");
    $_SESSION['message'] = "Staff deleted successfully.";
    header("Location: manage_staff.php");
    exit();
}

// Read Data
$result = mysqli_query($connections, "SELECT * FROM staff WHERE account_type = 2");
$staff = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script>
        function openUpdateModal(staff) {
            document.getElementById('updateId').value = staff.id;
            document.getElementById('updateLastName').value = staff.last_name;
            document.getElementById('updateFirstName').value = staff.first_name;
            document.getElementById('updateMiddleName').value = staff.middle_name;
            document.getElementById('updateContactNumber').value = staff.contact_number;
            document.getElementById('updateEmail').value = staff.email;
            document.getElementById('updatePosition').value = staff.position;
            const modal = new bootstrap.Modal(document.getElementById('updateModal'));
            modal.show();
        }

        function confirmAddStaff(event) {
            event.preventDefault(); // Prevent form submission

            const form = document.getElementById('addStaffForm');
            const requiredFields = ['last_name', 'first_name', 'contact_number', 'email', 'password'];
            let isValid = true;

            requiredFields.forEach(field => {
                if (!form[field].value) {
                    isValid = false;
                }
            });

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Input',
                    text: 'Please fill out all required fields.',
                });
                return;
            }

            Swal.fire({
                title: 'Add Staff',
                text: "Are you sure you want to add this staff?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, add staff!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                }
            });
        }

        function confirmUpdateStaff() {
            const form = document.getElementById('updateModal').querySelector('form');
            const requiredFields = ['last_name', 'first_name', 'contact_number', 'email'];
            let isValid = true;

            requiredFields.forEach(field => {
                if (!form[field].value) {
                    isValid = false;
                }
            });

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Input',
                    text: 'Please fill out all required fields.',
                });
                return;
            }

            Swal.fire({
                title: 'Update Staff',
                text: "Are you sure you want to update this Staff?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update Staff!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                }
            });
        }

        function confirmDeleteStaff(id) {
            Swal.fire({
                title: 'Delete Staff',
                text: "Are you sure you want to delete this Staff?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete staff!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'manage_staff.php?delete=' + id;
                }
            });
        }

        // Show SweetAlert notifications based on session message
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['message'])): ?>
                const message = <?= json_encode($_SESSION['message']); ?>;
                let title = 'Notification';
                let icon = 'success';
                
                if (message.includes('added')) {
                    title = 'Staff added';
                } else if (message.includes('updated')) {
                    title = 'Staff updated';
                } else if (message.includes('deleted')) {
                    title = 'Staff deleted';
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
</head>
<body>
<?php include 'admin_sidenav.php'; ?> 

    <div id="content" class="container-fluid">
        <h1>Manage Staff</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Staff</button>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $staff): ?>
                <tr>
                    <td><?= ucfirst(strtolower($staff['last_name'])) . ', ' . ucfirst(strtolower($staff['first_name'])) . ' ' . ucfirst(substr(strtolower($staff['middle_name']), 0, 1)) . '.' ?></td>
                    <td><?= $staff['email'] ?></td>
                    <td><?= $staff['contact_number'] ?></td>
                    <td>
                        <button class="btn btn-warning" onclick='openUpdateModal(<?= json_encode($staff) ?>)'>Update</button>
                        <button class="btn btn-danger" onclick="confirmDeleteStaff(<?= $staff['id'] ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addStaffForm" method="POST">
                    <input type="hidden" name="create" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Staff</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h7>Last Name:</h7>
                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                        <h7>First Name:</h7>
                        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                        <h7>Middle Name:</h7>
                        <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
                        <h7>Contact Number:</h7>
                        <input type="text" name="contact_number" class="form-control" placeholder="Contact Number" required>
                        <h7>Email:</h7>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <h7>Password:</h7>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <h7>Position:</h7>
                        <input type="text" name="position" class="form-control" placeholder="Position" required>
                        <h7>Sex:</h7>
                        <select name="sex" class="form-control" required>
                            <option value="" disabled selected>Position</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="confirmAddStaff(event)">Add Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update User Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="update" value="1">
                    <input type="hidden" name="id" id="updateId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update Staff</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h7>Last Name:</h7>
                        <input type="text" name="last_name" id="updateLastName" class="form-control" required>
                        <h7>First Name:</h7>
                        <input type="text" name="first_name" id="updateFirstName" class="form-control">
                        <h7>Middle Name:</h7>
                        <input type="text" name="middle_name" id="updateMiddleName" class="form-control" required>
                        <h7>Contact Number:</h7>
                        <input type="text" name="contact_number" id="updateContactNumber" class="form-control">
                        <h7>Email:</h7>
                        <input type="email" name="email" id="updateEmail" class="form-control" required>
                        <h7>Position:</h7>
                        <input type="text" name="position" id="updatePosition" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="confirmUpdateStaff()">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
