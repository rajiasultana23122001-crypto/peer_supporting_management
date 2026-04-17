<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'therapist') {
    header("Location: ../login.php");
    exit();
}

$therapist_id = $_SESSION['user_id'];
$therapist_name = $_SESSION['name'];

/* Total assigned peers */
$assigned_peers_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total_assigned_peers
    FROM therapist_peer_assignment
    WHERE therapist_id = '$therapist_id' AND is_active = 1
");
$assigned_peers = mysqli_fetch_assoc($assigned_peers_result)['total_assigned_peers'] ?? 0;

/* Total visible cases from assigned peers */
$visible_cases_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total_visible_cases
    FROM cases c
    INNER JOIN therapist_peer_assignment tpa ON c.created_by = tpa.peer_id
    WHERE tpa.therapist_id = '$therapist_id' AND tpa.is_active = 1
");
$total_visible_cases = mysqli_fetch_assoc($visible_cases_result)['total_visible_cases'] ?? 0;

/* Open cases */
$open_cases_result = mysqli_query($conn, "
    SELECT COUNT(*) AS total_open_cases
    FROM cases c
    INNER JOIN therapist_peer_assignment tpa ON c.created_by = tpa.peer_id
    WHERE tpa.therapist_id = '$therapist_id'
      AND tpa.is_active = 1
      AND c.status = 'open'
");
$total_open_cases = mysqli_fetch_assoc($open_cases_result)['total_open_cases'] ?? 0;

/* Recent visible cases */
$recent_cases = mysqli_query($conn, "
    SELECT 
        c.case_id,
        c.status,
        c.is_private,
        c.created_at,
        s.name AS student_name,
        u.name AS peer_name,
        f.level_name AS severity_name
    FROM cases c
    INNER JOIN therapist_peer_assignment tpa ON c.created_by = tpa.peer_id
    LEFT JOIN students s ON c.student_id = s.student_id
    LEFT JOIN users u ON c.created_by = u.user_id
    LEFT JOIN flagging f ON c.severity_id = f.severity_id
    WHERE tpa.therapist_id = '$therapist_id' AND tpa.is_active = 1
    ORDER BY c.case_id DESC
    LIMIT 8
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0f172a, #1e293b);
            color: white;
            padding: 25px 15px;
        }
        .sidebar h3 { font-weight: 700; margin-bottom: 30px; }
        .sidebar a {
            color: #fff; text-decoration: none; display: block;
            padding: 12px 15px; border-radius: 12px; margin-bottom: 10px; transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.14); }
        .topbar, .content-card, .stat-card {
            background: white; border-radius: 20px; padding: 22px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        .stat-card h2 { font-size: 34px; font-weight: 700; margin: 10px 0 0; }
        .icon-box {
            width: 56px; height: 56px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: white;
        }
        .bg1 { background: #3b82f6; }
        .bg2 { background: #10b981; }
        .bg3 { background: #f59e0b; }
        .table thead { background: #f1f5f9; }
        .badge-status, .badge-privacy {
            padding: 6px 10px; border-radius: 20px; font-size: 12px;
        }
        .status-open { background: #dbeafe; color: #1d4ed8; }
        .status-in_progress { background: #fef3c7; color: #b45309; }
        .status-resolved { background: #dcfce7; color: #166534; }
        .status-closed { background: #fee2e2; color: #991b1b; }
        .status-referred { background: #ede9fe; color: #6d28d9; }
        .privacy-private { background: #fee2e2; color: #991b1b; }
        .privacy-public { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <h3>Therapist Panel</h3>
            <a href="dashboard.php" class="active"><i class="fas fa-house me-2"></i> Dashboard</a>
            <a href="../logout.php"><i class="fas fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="topbar mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Welcome, <?php echo htmlspecialchars($therapist_name); ?></h2>
                    <p class="text-muted mb-0">Monitor assigned peers and their case activities.</p>
                </div>
                <a href="../logout.php" class="btn btn-danger rounded-pill px-4">
                    <i class="fas fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="icon-box bg1"><i class="fas fa-user-group"></i></div>
                        <h2><?php echo $assigned_peers; ?></h2>
                        <p class="text-muted mb-0">Assigned Peers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="icon-box bg2"><i class="fas fa-folder-open"></i></div>
                        <h2><?php echo $total_visible_cases; ?></h2>
                        <p class="text-muted mb-0">Visible Cases</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="icon-box bg3"><i class="fas fa-triangle-exclamation"></i></div>
                        <h2><?php echo $total_open_cases; ?></h2>
                        <p class="text-muted mb-0">Open Cases</p>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <h4 class="mb-3">Recent Assigned Cases</h4>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Peer</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Privacy</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($recent_cases && mysqli_num_rows($recent_cases) > 0): ?>
                            <?php while ($case = mysqli_fetch_assoc($recent_cases)): ?>
                                <?php
                                    $statusClass = 'status-open';
                                    if ($case['status'] == 'in_progress') $statusClass = 'status-in_progress';
                                    if ($case['status'] == 'resolved') $statusClass = 'status-resolved';
                                    if ($case['status'] == 'closed') $statusClass = 'status-closed';
                                    if ($case['status'] == 'referred') $statusClass = 'status-referred';
                                ?>
                                <tr>
                                    <td><?php echo $case['case_id']; ?></td>
                                    <td><?php echo htmlspecialchars($case['student_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($case['peer_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($case['severity_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge-status <?php echo $statusClass; ?>"><?php echo ucwords(str_replace('_', ' ', $case['status'])); ?></span></td>
                                    <td>
                                        <?php if ($case['is_private'] == 1): ?>
                                            <span class="badge-privacy privacy-private">Private</span>
                                        <?php else: ?>
                                            <span class="badge-privacy privacy-public">Public</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($case['created_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted">No assigned cases found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>