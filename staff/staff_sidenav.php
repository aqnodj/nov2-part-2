<!-- sidenav.php -->
<div id="sidebar">
    <div class="logo">
        <img src="../logonav.png" alt="Logo">
    </div>
        <ul>
            <li><a href="dashboard"><i class="fas fa-user-tie"></i> Staff Dashboard</a></li>
            <li><a href="announcement"><i class="fas fa-bullhorn"></i> Manage Announcements</a></li>
            <li><a href="manage_residents"><i class="fas fa-users"></i> Manage Residents</a></li>
            <li><a href="manage_blotter_report"><i class="fas fa-file-invoice"></i> Manage Blotter Report</a></li>
            <li><a href="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
</div>
<style>
  
    
    body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    display: flex;
    overflow-x: hidden;
}

#sidebar {
    position: fixed;
    top: 0;
    left: -250px;
    width: 250px;
    height: 100vh;
    background: linear-gradient(180deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    transition: left 0.3s ease;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
}

#sidebar.active {
    left: 0;
}

#sidebar .logo {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

#sidebar .logo img {
    width: 80%;
    height: auto;
}

#sidebar ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
    flex-grow: 1;
}

#sidebar ul li {
    padding: 15px 20px;
    transition: background 0.3s, transform 0.2s;
}

#sidebar ul li:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

#sidebar ul li a {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
}

#sidebar ul li a i {
    margin-right: 10px;
    font-size: 1.2em;
}

#content {
    margin-left: 0;
    padding: 20px;
    width: 100%;
    transition: margin-left 0.3s ease;
    
}

#content.active {
    margin-left: 250px;
}

#toggle-btn {
    background-color: #6a11cb;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 18px;
    z-index: 1001;
    transition: background-color 0.3s;
}

#toggle-btn:hover {
    background-color: #2575fc;
}

#toggle-btn:focus {
    outline: none;
}
#sidebar {
            width: 250px; 
            height: 100vh; 
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6; 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 1000;
        }

        #content {
            margin-left: 250px;
        }

</style>
