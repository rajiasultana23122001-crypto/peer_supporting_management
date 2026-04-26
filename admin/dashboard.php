<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['name'];

function getCount($conn, $table) {
    $sql = "SELECT COUNT(*) as total FROM $table";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

$total_users = getCount($conn, 'users');
$total_students = getCount($conn, 'students');
$total_cases = getCount($conn, 'cases');
$total_referrals = getCount($conn, 'referrals');

$recent_users = mysqli_query($conn, "SELECT name, email, role, is_active FROM users ORDER BY user_id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Peer Supporting Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #4f46e5, #7c3aed);
            color: white;
            padding: 25px 15px;
        }

        .sidebar h3 {
            font-weight: 700;
            margin-bottom: 30px;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.18);
        }

        .topbar {
            background: white;
            border-radius: 18px;
            padding: 18px 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            padding: 20px;
            color: white;
            box-shadow: 0 10px 24px rgba(0,0,0,0.08);
        }

        .bg-users { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .bg-students { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-cases { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .bg-referrals { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .stat-card i {
            font-size: 30px;
            opacity: 0.9;
        }

        .stat-card h2 {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0 5px;
        }

        .content-card {
            background: white;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .table thead {
            background: #f1f5f9;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .welcome-text {
            font-size: 14px;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-3 col-lg-2 sidebar">
            <h3>Admin Panel</h3>
            <a href="dashboard.php" class="active"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-users me-2"></i> Users</a>
            <a href="assignments.php"><i class="fas fa-link me-2"></i> Assignments</a>
            <a href="students.php"><i class="fas fa-user-graduate me-2"></i> Students</a>
            <a href="cases.php"><i class="fas fa-folder-open me-2"></i> Cases</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="topbar d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="mb-1">Welcome, <?php echo htmlspecialchars($admin_name); ?> 👋</h2>
                    <div class="welcome-text">Manage users, students, cases and referrals from here.</div>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="../logout.php" class="btn btn-danger rounded-pill px-4">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card bg-users">
                        <i class="fas fa-users"></i>
                        <h2><?php echo $total_users; ?></h2>
                        <p class="mb-0">Total Users</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card bg-students">
                        <i class="fas fa-user-graduate"></i>
                        <h2><?php echo $total_students; ?></h2>
                        <p class="mb-0">Total Students</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card bg-cases">
                        <i class="fas fa-folder-open"></i>
                        <h2><?php echo $total_cases; ?></h2>
                        <p class="mb-0">Total Cases</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card bg-referrals">
                        <i class="fas fa-share-square"></i>
                        <h2><?php echo $total_referrals; ?></h2>
                        <p class="mb-0">Total Referrals</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Recent Users</h4>
                            <a href="users.php" class="btn btn-sm btn-primary rounded-pill">View All</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_users && mysqli_num_rows($recent_users) > 0): ?>
                                        <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($user['is_active'] == 1): ?>
                                                        <span class="badge-active">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge-inactive">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No users found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="content-card">
                        <h4 class="mb-3">Quick Actions</h4>
                        <div class="d-grid gap-3">
                            <a href="users.php" class="btn btn-outline-primary rounded-pill">
                                <i class="fas fa-users me-2"></i> Manage Users
                            </a>
                            <a href="students.php" class="btn btn-outline-success rounded-pill">
                                <i class="fas fa-user-graduate me-2"></i> Manage Students
                            </a>
                            <a href="cases.php" class="btn btn-outline-warning rounded-pill">
                                <i class="fas fa-folder-open me-2"></i> Manage Cases
                            </a>
                            <a href="../logout.php" class="btn btn-outline-danger rounded-pill">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>