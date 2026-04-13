<?php
include('../includes/auth.php');
include('../config/db.php');

if ($_SESSION['role'] != 'admin') {
    die("Access Denied");
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
                <h2>Manage Users</h2>
                <p>View all system users in one place.</p>
            </div>
            <a href="dashboard.php" class="btn btn-primary custom-btn">Back</a>
        </div>

        <div class="activity-box">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($users)) { ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo ucfirst($row['role']); ?></td>
                            <td><?php echo $row['department']; ?></td>
                            <td>
                                <?php echo $row['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?>
                            </td>
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