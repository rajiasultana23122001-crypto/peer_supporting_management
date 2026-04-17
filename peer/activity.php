<?php
include('../includes/auth.php');
include('../config/db.php');

if ($_SESSION['role'] != 'peer') {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT peer_service_records.*, cases.case_id
        FROM peer_service_records
        JOIN cases ON peer_service_records.case_id = cases.case_id
        WHERE peer_service_records.peer_id = '$user_id'
        ORDER BY peer_service_records.service_id DESC";

$result = mysqli_query($conn, $sql);
$total_activity = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <h3>Peer Panel</h3>
        <p>Peer Supporting Management</p>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="add_case.php">➕ Add Case</a>
        <a href="cases.php">📋 My Cases</a>
        <a href="activity.php">🕒 Activity</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="topbar d-flex justify-content-between align-items-center">
            <div>
                <h2>My Activity</h2>
                <p>Track your peer support work and service record history.</p>
            </div>
            <a href="dashboard.php" class="btn btn-primary custom-btn">Back</a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card stat-green">
                    <h5>Total Activities</h5>
                    <h3><?php echo $total_activity; ?></h3>
                    <p class="mb-0">Recorded peer service actions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-blue">
                    <h5>My Role</h5>
                    <h3>Peer</h3>
                    <p class="mb-0">Support contributor account</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-orange">
                    <h5>Status</h5>
                    <h3>Active</h3>
                    <p class="mb-0">Currently participating</p>
                </div>
            </div>
        </div>

        <div class="activity-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Activity History</h4>
                <a href="add_case.php" class="btn btn-success custom-btn">+ Add New Case</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Service ID</th>
                            <th>Case ID</th>
                            <th>Action Type</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_activity > 0) { ?>
                            <?php mysqli_data_seek($result, 0); ?>
                            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo $row['service_id']; ?></td>
                                    <td><?php echo $row['case_id']; ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $row['action_type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['timestamp']; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No activity found yet.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>