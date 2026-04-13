<?php
include('../includes/auth.php');
include('../config/db.php');

if ($_SESSION['role'] != 'admin') {
    die("Access Denied");
}

$sql = "SELECT cases.*, 
               students.name AS student_name, 
               students.department,
               users.name AS created_by_name,
               flagging.level_name
        FROM cases
        JOIN students ON cases.student_id = students.student_id
        JOIN users ON cases.created_by = users.user_id
        JOIN flagging ON cases.severity_id = flagging.severity_id
        ORDER BY cases.case_id DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Cases</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <p>Peer Supporting Management</p>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="users.php">👤 Manage Users</a>
        <a href="students.php">🎓 Students</a>
        <a href="cases.php">📁 Cases</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="topbar d-flex justify-content-between align-items-center">
            <div>
                <h2>All Cases</h2>
                <p>Monitor all student support case records from one place.</p>
            </div>
            <a href="dashboard.php" class="btn btn-primary custom-btn">Back</a>
        </div>

        <div class="activity-box">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Case ID</th>
                            <th>Student</th>
                            <th>Department</th>
                            <th>Created By</th>
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
                                    <td><?php echo $row['created_by_name']; ?></td>
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
                                <td colspan="8" class="text-center text-muted">No cases found yet.</td>
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