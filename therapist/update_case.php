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
$case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($case_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

/* Check if therapist can access this case */
$case_query = mysqli_query($conn, "
    SELECT 
        c.case_id,
        c.status,
        c.created_at,
        s.name AS student_name,
        u.name AS peer_name,
        f.level_name AS severity_name
    FROM cases c
    INNER JOIN therapist_peer_assignment tpa ON c.created_by = tpa.peer_id
    LEFT JOIN students s ON c.student_id = s.student_id
    LEFT JOIN users u ON c.created_by = u.user_id
    LEFT JOIN flagging f ON c.severity_id = f.severity_id
    WHERE c.case_id = '$case_id'
      AND tpa.therapist_id = '$therapist_id'
      AND tpa.is_active = 1
    LIMIT 1
");

if (!$case_query || mysqli_num_rows($case_query) == 0) {
    die("Unauthorized access or case not found.");
}

$case = mysqli_fetch_assoc($case_query);
$success = "";
$error = "";

if (isset($_POST['update_status'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update = mysqli_query($conn, "
        UPDATE cases
        SET status = '$status'
        WHERE case_id = '$case_id'
    ");

    if ($update) {
        $success = "Case status updated successfully!";
        $case['status'] = $status;
    } else {
        $error = "Failed to update case status: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Case Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            font-family: 'Segoe UI', sans-serif;
        }
        .page-card {
            max-width: 720px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .form-control, .form-select, .btn {
            border-radius: 12px;
        }
        .info-box {
            background: #f8fafc;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 20px;
        }
        .label {
            font-weight: 600;
            color: #334155;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Update Case Status</h2>
                <p class="text-muted mb-0">Modify the current status of this assigned case.</p>
            </div>
            <a href="dashboard.php" class="btn btn-dark">Back</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="info-box">
            <p class="mb-2"><span class="label">Case ID:</span> <?php echo $case['case_id']; ?></p>
            <p class="mb-2"><span class="label">Student:</span> <?php echo htmlspecialchars($case['student_name'] ?? 'N/A'); ?></p>
            <p class="mb-2"><span class="label">Peer:</span> <?php echo htmlspecialchars($case['peer_name'] ?? 'N/A'); ?></p>
            <p class="mb-2"><span class="label">Severity:</span> <?php echo htmlspecialchars($case['severity_name'] ?? 'N/A'); ?></p>
            <p class="mb-0"><span class="label">Current Status:</span> <?php echo ucwords(str_replace('_', ' ', $case['status'])); ?></p>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">New Status</label>
                <select name="status" class="form-select" required>
                    <option value="open" <?php if ($case['status'] == 'open') echo 'selected'; ?>>Open</option>
                    <option value="in_progress" <?php if ($case['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                    <option value="resolved" <?php if ($case['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                    <option value="closed" <?php if ($case['status'] == 'closed') echo 'selected'; ?>>Closed</option>
                    <option value="referred" <?php if ($case['status'] == 'referred') echo 'selected'; ?>>Referred</option>
                </select>
            </div>

            <button type="submit" name="update_status" class="btn btn-primary w-100">
                Update Status
            </button>
        </form>
    </div>
</div>

</body>
</html>