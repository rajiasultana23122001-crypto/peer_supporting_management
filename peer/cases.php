<?php
include('../includes/auth.php');
include('../config/db.php');

if ($_SESSION['role'] != 'peer') {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT cases.*, students.name AS student_name, students.department, flagging.level_name
        FROM cases
        JOIN students ON cases.student_id = students.student_id
        JOIN flagging ON cases.severity_id = flagging.severity_id
        WHERE cases.created_by = '$user_id'
        ORDER BY cases.case_id DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cases</title>
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
                <h2>My Cases</h2>
                <p>View all case records created by you in one organized place.</p>
            </div>
            <a href="dashboard.php" class="btn btn-primary custom-btn">Back</a>
        </div>

        <div class="activity-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Case List</h4>
                <a href="add_case.php" class="btn btn-success custom-btn">+ Add New Case</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Case ID</th>
                            <th>Student Name</th>
                            <th>Department</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Privacy</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0) { ?>
                            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo $row['case_id']; ?></td>
                                    <td><?php echo $row['student_name']; ?></td>
                                    <td><?php echo $row['department']; ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <?php echo $row['level_name']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $row['is_private'] ? '<span class="badge bg-danger">Private</span>' : '<span class="badge bg-success">Public</span>'; ?>
                                    </td>
                                    <td><?php echo $row['created_at']; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No cases found yet.</td>
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