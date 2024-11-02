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

// Queries
$total_residents_query = "SELECT COUNT(id) as total FROM users WHERE account_type != '1'"; // Exclude admin
$total_residents_result = $connections->query($total_residents_query);
$total_residents = $total_residents_result->fetch_assoc()['total'];

$pending_reports_query = "SELECT COUNT(*) as pending FROM blotter_report WHERE status = 'pending'";
$pending_reports_result = $connections->query($pending_reports_query);
$pending_reports = $pending_reports_result->fetch_assoc()['pending'];

$scheduled_meetings_query = "SELECT COUNT(*) as finished FROM blotter_report WHERE status = 'finished'";
$scheduled_meetings_result = $connections->query($scheduled_meetings_query);
$scheduled_meetings = $scheduled_meetings_result->fetch_assoc()['finished'];

// Fetch data for the pie chart
$purok_counts_query = "SELECT purok, COUNT(*) as count FROM users WHERE account_type != '1' GROUP BY purok ORDER BY purok ASC";
$purok_counts_result = $connections->query($purok_counts_query);

$purok_data = [];
$total_count = 0;

// Initialize counts for purok 1 to 7
for ($i = 1; $i <= 7; $i++) {
    $purok_data[$i] = 0;
}

while ($row = $purok_counts_result->fetch_assoc()) {
    $purok = (int)$row['purok'];
    $purok_data[$purok] = (int)$row['count'];
    $total_count += $row['count'];
}

$connections->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metric-container {
            border: 1px solid #ccc; 
            border-radius: 5px; 
            padding: 15px; 
            margin-bottom: 15px; 
            background-color: #f9f9f9; 
            width: calc(30% - 20px); 
        }

        .dashboard-metrics {
            display: flex; 
            flex-wrap: wrap; 
            gap: 20px; 
        }

        #content {
            padding: 20px; 
        }

        #pie-chart {
            max-width: 600px; 
            margin: 0 auto; 
        }

        .dashboard-metrics {
            display: flex;
            justify-content: center; 
            text-align: center; 
        }
    </style>
</head>
<body>
<?php include 'admin_sidenav.php'; ?> 
    <div id="content">
        <center><h1>Dashboard</h1></center>

        <hr>
        
        <!-- Dashboard content -->
        <div class="dashboard-metrics">
            <div class="metric-container">
                <h2>Total Residents</h2>
                <p><span id="total-residents"><?php echo $total_residents; ?></span></p>
            </div>
            <div class="metric-container">
                <h2>Pending Blotter Reports</h2>
                <p><span id="pending-reports"><?php echo $pending_reports; ?></span></p>
            </div>
            <div class="metric-container">
                <h2>Scheduled Meetings</h2>
                <p><span id="scheduled-meetings"><?php echo $scheduled_meetings; ?></span></p>
            </div>
        </div>

        <!-- Pie Chart -->
        <div id="pie-chart">
            <canvas id="purokPieChart"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('purokPieChart').getContext('2d');
            const totalResidents = <?php echo $total_count; ?>;
            const purokCounts = [<?php echo implode(',', $purok_data); ?>];
            const purokLabels = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Purok 7'];

            const pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: purokLabels,
                    datasets: [{
                        data: purokCounts,
                        backgroundColor: [
                            '#B0C4DE', // Light Steel Blue
                            '#ADD8E6', // Light Blue
                            '#87CEFA', // Light Sky Blue
                            '#4682B4', // Steel Blue
                            '#6495ED', // Cornflower Blue
                            '#5F9EA0', // Cadet Blue
                            '#6A5ACD'  // Slate Blue
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    const count = tooltipItem.raw;
                                    const percentage = totalResidents ? ((count / totalResidents) * 100).toFixed(2) : 0;
                                    return `${tooltipItem.label}: ${count} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        </script>
    </div>
</body>
</html>
