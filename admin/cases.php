<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

/* Add Case */
if (isset($_POST['add_case'])) {
    $student_id  = mysqli_real_escape_string($conn, $_POST['student_id']);
    $created_by  = mysqli_real_escape_string($conn, $_POST['created_by']);
    $severity_id = mysqli_real_escape_string($conn, $_POST['severity_id']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);
    $is_private  = isset($_POST['is_private']) ? 1 : 0;

    $sql = "INSERT INTO cases (student_id, created_by, severity_id, status, is_private)
            VALUES ('$student_id', '$created_by', '$severity_id', '$status', '$is_private')";

    if (mysqli_query($conn, $sql)) {
        $success = "Case added successfully!";
    } else {
        $error = "Failed to add case: " . mysqli_error($conn);
    }
}

/* Load Edit Data */
$edit_case = null;
if (isset($_GET['edit'])) {
    $case_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM cases WHERE case_id = '$case_id'");

    if ($result && mysqli_num_rows($result) > 0) {
        $edit_case = mysqli_fetch_assoc($result);
    }
}

/* Update Case */
if (isset($_POST['update_case'])) {
    $case_id     = intval($_POST['case_id']);
    $student_id  = mysqli_real_escape_string($conn, $_POST['student_id']);
    $created_by  = mysqli_real_escape_string($conn, $_POST['created_by']);
    $severity_id = mysqli_real_escape_string($conn, $_POST['severity_id']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);
    $is_private  = isset($_POST['is_private']) ? 1 : 0;

    $sql = "UPDATE cases
            SET student_id='$student_id',
                created_by='$created_by',
                severity_id='$severity_id',
                status='$status',
                is_private='$is_private'
            WHERE case_id='$case_id'";

    if (mysqli_query($conn, $sql)) {
        $success = "Case updated successfully!";
        $edit_case = null;
    } else {
        $error = "Failed to update case: " . mysqli_error($conn);
    }
}

/* Dropdown Data */
$students = mysqli_query($conn, "SELECT student_id, name FROM students ORDER BY student_id DESC");
$peers    = mysqli_query($conn, "SELECT user_id, name FROM users WHERE role='peer' AND is_active=1 ORDER BY name ASC");
$severity = mysqli_query($conn, "SELECT severity_id, level_name FROM flagging ORDER BY severity_id ASC");

/* Case List */
$cases = mysqli_query($conn, "
    SELECT 
        c.case_id,
        c.student_id,
        c.created_by,
        c.severity_id,
        c.status,
        c.is_private,
        c.created_at,
        s.name AS student_name,
        u.name AS peer_name,
        f.level_name AS severity_name
    FROM cases c
    LEFT JOIN students s ON c.student_id = s.student_id
    LEFT JOIN users u ON c.created_by = u.user_id
    LEFT JOIN flagging f ON c.severity_id = f.severity_id
    ORDER BY c.case_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Cases | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            background: rgba(255,255,255,0.18);
        }

        .topbar,
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .table thead {
            background: #f1f5f9;
        }

        .form-control,
        .form-select,
        .btn {
            border-radius: 12px;
        }

        .status-badge,
        .privacy-badge,
        .severity-badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }

        .status-open {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-in_progress {
            background: #fef3c7;
            color: #b45309;
        }

        .status-resolved {
            background: #dcfce7;
            color: #166534;
        }

        .status-closed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-referred {
            background: #ede9fe;
            color: #6d28d9;
        }

        .privacy-yes {
            background: #fee2e2;
            color: #991b1b;
        }

        .privacy-no {
            background: #dcfce7;
            color: #166534;
        }

        .sev-low {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .sev-medium {
            background: #fef3c7;
            color: #b45309;
        }

        .sev-high {
            background: #fed7aa;
            color: #c2410c;
        }

        .sev-critical {
            background: #fecaca;
            color: #b91c1c;
        }

        .sev-default {
            background: #e5e7eb;
            color: #374151;
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 col-lg-2 sidebar">
            <h3>Admin Panel</h3>
            <a href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-users me-2"></i> Users</a>
            <a href="students.php"><i class="fas fa-user-graduate me-2"></i> Students</a>
            <a href="assignments.php"><i class="fas fa-link me-2"></i> Assignments</a>
            <a href="cases.php" class="active"><i class="fas fa-folder-open me-2"></i> Cases</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">

            <div class="topbar mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Manage Cases</h2>
                    <p class="text-muted mb-0">Add, edit and monitor case records.</p>
                </div>
                <a href="dashboard.php" class="btn btn-dark">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row g-4">

                <div class="col-lg-4">
                    <div class="content-card">
                        <h4 class="mb-3"><?php echo $edit_case ? 'Edit Case' : 'Add Case'; ?></h4>

                        <form method="POST">
                            <?php if ($edit_case): ?>
                                <input type="hidden" name="case_id" value="<?php echo $edit_case['case_id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Student</label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">Select Student</option>
                                    <?php
                                    mysqli_data_seek($students, 0);
                                    while ($student = mysqli_fetch_assoc($students)):
                                    ?>
                                        <option value="<?php echo $student['student_id']; ?>"
                                            <?php if ($edit_case && $edit_case['student_id'] == $student['student_id']) echo 'selected'; ?>>
                                            <?php echo $student['student_id'] . ' - ' . htmlspecialchars($student['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Created By (Peer)</label>
                                <select name="created_by" class="form-select" required>
                                    <option value="">Select Peer</option>
                                    <?php
                                    mysqli_data_seek($peers, 0);
                                    while ($peer = mysqli_fetch_assoc($peers)):
                                    ?>
                                        <option value="<?php echo $peer['user_id']; ?>"
                                            <?php if ($edit_case && $edit_case['created_by'] == $peer['user_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($peer['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Severity</label>
                                <select name="severity_id" class="form-select" required>
                                    <option value="">Select Severity</option>
                                    <?php
                                    mysqli_data_seek($severity, 0);
                                    while ($sev = mysqli_fetch_assoc($severity)):
                                    ?>
                                        <option value="<?php echo $sev['severity_id']; ?>"
                                            <?php if ($edit_case && $edit_case['severity_id'] == $sev['severity_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($sev['level_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <?php
                                    $statuses = ['open', 'in_progress', 'resolved', 'closed', 'referred'];
                                    foreach ($statuses as $st):
                                    ?>
                                        <option value="<?php echo $st; ?>"
                                            <?php if ($edit_case && $edit_case['status'] == $st) echo 'selected'; ?>>
                                            <?php echo ucwords(str_replace('_', ' ', $st)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="is_private" id="is_private"
                                    <?php if (($edit_case && $edit_case['is_private'] == 1) || !$edit_case) echo 'checked'; ?>>
                                <label class="form-check-label" for="is_private">Private Case</label>
                            </div>

                            <?php if ($edit_case): ?>
                                <button type="submit" name="update_case" class="btn btn-warning w-100">
                                    Update Case
                                </button>
                                <a href="cases.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_case" class="btn btn-primary w-100">
                                    Add Case
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="content-card">
                        <h4 class="mb-3">All Cases</h4>

                        <div class="alert alert-light border">
                            <strong>Note:</strong> Case delete option is disabled to protect case history, notes, activities and notifications.
                            Use status update such as Resolved or Closed instead.
                        </div>

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
                                    <th>Actions</th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php if ($cases && mysqli_num_rows($cases) > 0): ?>
                                    <?php while ($case = mysqli_fetch_assoc($cases)): ?>
                                        <?php
                                            $statusClass = 'status-open';
                                            if ($case['status'] == 'in_progress') $statusClass = 'status-in_progress';
                                            if ($case['status'] == 'resolved') $statusClass = 'status-resolved';
                                            if ($case['status'] == 'closed') $statusClass = 'status-closed';
                                            if ($case['status'] == 'referred') $statusClass = 'status-referred';

                                            $sevClass = 'sev-default';
                                            $sevNameLower = strtolower(trim($case['severity_name'] ?? ''));
                                            if ($sevNameLower == 'low') $sevClass = 'sev-low';
                                            if ($sevNameLower == 'medium') $sevClass = 'sev-medium';
                                            if ($sevNameLower == 'high') $sevClass = 'sev-high';
                                            if ($sevNameLower == 'critical') $sevClass = 'sev-critical';
                                        ?>

                                        <tr>
                                            <td><?php echo $case['case_id']; ?></td>
                                            <td><?php echo htmlspecialchars($case['student_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($case['peer_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="severity-badge <?php echo $sevClass; ?>">
                                                    <?php echo htmlspecialchars($case['severity_name'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $statusClass; ?>">
                                                    <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($case['status']))); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($case['is_private'] == 1): ?>
                                                    <span class="privacy-badge privacy-yes">Private</span>
                                                <?php else: ?>
                                                    <span class="privacy-badge privacy-no">Public</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="cases.php?edit=<?php echo $case['case_id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No cases found.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
</body>
</html>