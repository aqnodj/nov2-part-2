<?php
session_start();

include '../connections.php'; 

// Access for Staff Account only
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
function addUser($data) {
    global $connections;
    $first_name = $data['firstname'];
    $middle_name = $data['middlename'];
    $last_name = $data['lastname'];
    $suffix = $data['suffix'];
    $purok =$data['purok'];
    $contact_number = $data['contact'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $account_type = 3;
    $created_at = date('Y-m-d H:i:s');

    $query_user = "INSERT INTO users (firstname, middlename, lastname, suffix,purok, contact, email, password, account_type, created_at) 
                   VALUES ('$first_name', '$middle_name', '$last_name', '$suffix','$purok', '$contact_number', '$email', '$password', $account_type, '$created_at')";
    
    return mysqli_query($connections, $query_user);
}

// Function to handle user update
function updateUser($data) {
    global $connections;
    $id = $data['id'];
    $first_name = $data['firstname'];
    $middle_name = $data['middlename'];
    $last_name = $data['lastname'];
    $suffix = $data['suffix'];
    $purok = $data['purok'];
    $contact_number = $data['contact'];
    $email = $data['email'];

    $query = "UPDATE users SET firstname='$first_name', middlename='$middle_name', lastname='$last_name', suffix='$suffix', purok='$purok', contact='$contact_number', email='$email' WHERE id=$id";
    
    return mysqli_query($connections, $query);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Validate required fields
        if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['purok'])  || empty($_POST['contact']) || empty($_POST['email']) || empty($_POST['password'])) {
            $_SESSION['message'] = "Please fill out all required fields.";
            header("Location: manage_residents.php");
            exit();
        }
        
        // Attempt to add user
        if (addUser($_POST)) {
            $_SESSION['message'] = "User added successfully.";
        } else {
            $_SESSION['message'] = "Error adding user.";
        }
        header("Location: manage_residents.php");
        exit();
    } elseif (isset($_POST['update'])) {
        // Validate required fields
        if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['purok']) || empty($_POST['contact']) || empty($_POST['email'])) {
            $_SESSION['message'] = "Please fill out all required fields.";
            header("Location: manage_residents.php");
            exit();
        }

        // Attempt to update user
        if (updateUser($_POST)) {
            $_SESSION['message'] = "User updated successfully.";
        } else {
            $_SESSION['message'] = "Error updating user.";
        }
        header("Location: manage_residents.php");
        exit();
    }
}

// Delete User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($connections, "DELETE FROM blotter_report WHERE user_id=$id");
    mysqli_query($connections, "DELETE FROM users WHERE id=$id");
    $_SESSION['message'] = "User deleted successfully.";
    header("Location: manage_residents.php");
    exit();
}

// Read Data
$result = mysqli_query($connections, "SELECT * FROM users WHERE account_type = 3");
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Residents</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function openUpdateModal(user) {
            document.getElementById('updateId').value = user.id;
            document.getElementById('updateFirstName').value = user.firstname;
            document.getElementById('updateMiddleName').value = user.middlename;
            document.getElementById('updateLastName').value = user.lastname;
            document.getElementById('updateSuffix').value = user.suffix;
            document.getElementById('updatePurok').value = user.purok;
            document.getElementById('updateContactNumber').value = user.contact;
            document.getElementById('updateEmail').value = user.email;

            const modal = new bootstrap.Modal(document.getElementById('updateModal'));
            modal.show();
        }

        function confirmAddUser(event) {
            event.preventDefault(); // Prevent form submission

            const form = document.getElementById('addUserForm');
            const requiredFields = ['firstname', 'lastname', 'purok','contact', 'email', 'password'];
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
                title: 'Add User',
                text: "Are you sure you want to add this user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, add user!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                }
            });
        }

        function confirmUpdateUser() {
            const form = document.getElementById('updateModal').querySelector('form');
            const requiredFields = ['firstname', 'lastname', 'purok','contact', 'email'];
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
                title: 'Update User',
                text: "Are you sure you want to update this user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update user!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                }
            });
        }

        function confirmDeleteUser(id) {
            Swal.fire({
                title: 'Delete User',
                text: "Are you sure you want to delete this user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete user!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'manage_residents.php?delete=' + id;
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
                    title = 'Resident added';
                } else if (message.includes('updated')) {
                    title = 'Resident updated';
                } else if (message.includes('deleted')) {
                    title = 'Resident deleted';
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
<?php include 'staff_sidenav.php'; ?> 

    <div id="content" class="container-fluid">
        <h1>Manage Residents</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add User</button>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Purok</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= ucfirst(strtolower($user['lastname'])) . ', ' . ucfirst(strtolower($user['firstname'])) . ' ' . ucfirst(substr(strtolower($user['middlename']), 0, 1)) . '.' ?></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= $user['purok'] ?></td>
                    <td><?= $user['contact'] ?></td>
                    <td>
                        <button class="btn btn-warning" onclick='openUpdateModal(<?= json_encode($user) ?>)'>Update</button>
                        <button class="btn btn-danger" onclick="confirmDeleteUser(<?= $user['id'] ?>)">Delete</button>
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
                <form id="addUserForm" method="POST">
                    <input type="hidden" name="create" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h7>First Name:</h7>
                        <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
                        <h7>Middle Name:</h7>
                        <input type="text" name="middlename" class="form-control" placeholder="Middle Name">
                        <h7>Last Name:</h7>
                        <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>
                        <h7>Suffix:</h7>
                        <input type="text" name="suffix" class="form-control" placeholder="Suffix (if any)">
                        <h7>Purok:</h7>
                        <input type="text" name="purok" class="form-control" placeholder="Purok" required>
                        <h7>Contact Number:</h7>
                        <input type="text" name="contact" class="form-control" placeholder="Contact Number" required>
                        <h7>Email:</h7>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <h7>Password:</h7>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="confirmAddUser(event)">Add User</button>
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
                        <h5 class="modal-title" id="updateModalLabel">Update User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h7>First Name:</h7>
                        <input type="text" name="firstname" id="updateFirstName" class="form-control" required>
                        <h7>Middle Name:</h7>
                        <input type="text" name="middlename" id="updateMiddleName" class="form-control">
                        <h7>Last Name:</h7>
                        <input type="text" name="lastname" id="updateLastName" class="form-control" required>
                        <h7>Suffix:</h7>
                        <input type="text" name="suffix" id="updateSuffix" class="form-control">
                        <h7>Purok:</h7>
                        <input type="text" name="purok" id="updatePurok" class="form-control" required>
                        <h7>Contact Number:</h7>
                        <input type="text" name="contact" id="updateContactNumber" class="form-control" required>
                        <h7>Email:</h7>
                        <input type="email" name="email" id="updateEmail" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="confirmUpdateUser()">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
