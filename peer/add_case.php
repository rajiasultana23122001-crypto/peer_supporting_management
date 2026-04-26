<?php
include('../includes/auth.php');
include('../config/db.php');

if ($_SESSION['role'] != 'peer') {
    die("Access Denied");
}

$students = mysqli_query($conn, "SELECT * FROM students ORDER BY name ASC");
$severity = mysqli_query($conn, "SELECT * FROM flagging ORDER BY severity_id ASC");

$success = "";
$error = "";

if (isset($_POST['add_case'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $severity_id = mysqli_real_escape_string($conn, $_POST['severity_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $is_private = mysqli_real_escape_string($conn, $_POST['is_private']);
    $created_by = $_SESSION['user_id'];

    $sql = "INSERT INTO cases (student_id, created_by, severity_id, status, is_private)
            VALUES ('$student_id', '$created_by', '$severity_id', '$status', '$is_private')";

    if (mysqli_query($conn, $sql)) {
       $case_id = mysqli_insert_id($conn);

mysqli_query($conn, "
    INSERT INTO peer_service_records (peer_id, case_id, action_type)
    VALUES ('$created_by', '$case_id', 'Case Created')
");

$therapist_query = mysqli_query($conn, "
    SELECT therapist_id 
    FROM therapist_peer_assignment 
    WHERE peer_id = '$created_by' AND is_active = 1
");

while ($therapist = mysqli_fetch_assoc($therapist_query)) {
    $therapist_id = $therapist['therapist_id'];

    mysqli_query($conn, "
        INSERT INTO notifications (user_id, case_id, message)
        VALUES ('$therapist_id', '$case_id', 'New case created by assigned peer')
    ");
}

        while ($therapist = mysqli_fetch_assoc($therapist_query)) {
            $therapist_id = $therapist['therapist_id'];
            mysqli_query($conn, "
                INSERT INTO notifications (user_id, case_id, message)
                VALUES ('$therapist_id', '$case_id', 'New case created by assigned peer')
            ");
        }

        $success = "Case added successfully and therapist notified!";
    } else {
        $error = "Something went wrong: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Case</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
                <h2>Add New Case</h2>
                <p>Create a student support case record.</p>
            </div>
            <a href="dashboard.php" class="btn btn-primary custom-btn">Back</a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="activity-box">
                    <h4 class="mb-4">Case Entry Form</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Choose Student</option>
                                <?php while($row = mysqli_fetch_assoc($students)): ?>
                                    <option value="<?php echo $row['student_id']; ?>">
                                        <?php echo htmlspecialchars($row['name']); ?> - <?php echo htmlspecialchars($row['department']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Severity Level</label>
                            <select name="severity_id" class="form-select" required>
                                <option value="">Choose Severity</option>
                                <?php while($sev = mysqli_fetch_assoc($severity)): ?>
                                    <option value="<?php echo $sev['severity_id']; ?>">
                                        <?php echo htmlspecialchars($sev['level_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Case Status</label>
                            <select name="status" class="form-select" required>
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                                <option value="referred">Referred</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Privacy</label>
                            <select name="is_private" class="form-select" required>
                                <option value="1">Private</option>
                                <option value="0">Public</option>
                            </select>
                        </div>

                        <button type="submit" name="add_case" class="btn btn-success custom-btn">
                            Save Case
                        </button>

                        <a href="activity.php" class="btn btn-outline-primary custom-btn ms-2">
                            View Activity
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>