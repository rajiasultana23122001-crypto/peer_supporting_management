<?php
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
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $case_title = mysqli_real_escape_string($conn, $_POST['case_title']);
    $case_type = mysqli_real_escape_string($conn, $_POST['case_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO cases (student_id, case_title, case_type, description, status)
            VALUES ('$student_id', '$case_title', '$case_type', '$description', '$status')";

    if (mysqli_query($conn, $sql)) {
        $success = "Case added successfully!";
    } else {
        $error = "Failed to add case: " . mysqli_error($conn);
    }
}

/* Delete Case */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM cases WHERE case_id = $id");
    header("Location: cases.php");
    exit();
}

/* Update Case */
if (isset($_POST['update_case'])) {
    $case_id = intval($_POST['case_id']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $case_title = mysqli_real_escape_string($conn, $_POST['case_title']);
    $case_type = mysqli_real_escape_string($conn, $_POST['case_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE cases
            SET student_id='$student_id', case_title='$case_title', case_type='$case_type',
                description='$description', status='$status'
            WHERE case_id='$case_id'";

    if (mysqli_query($conn, $sql)) {
        $success = "Case updated successfully!";
    } else {
        $error = "Failed to update case: " . mysqli_error($conn);
    }
}

$edit_case = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM cases WHERE case_id = $id");
    $edit_case = mysqli_fetch_assoc($result);
}

$students = mysqli_query($conn, "SELECT id, full_name FROM students ORDER BY full_name ASC");

$cases = mysqli_query($conn, "
    SELECT cases.*, students.full_name 
    FROM cases 
    LEFT JOIN students ON cases.student_id = students.id
    ORDER BY cases.case_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cases | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #4f46e5, #7c3aed);
            color: white; padding: 25px 15px;
        }
        .sidebar h3 { font-weight: 700; margin-bottom: 30px; }
        .sidebar a {
            color: #fff; text-decoration: none; display: block; padding: 12px 15px;
            border-radius: 12px; margin-bottom: 10px; transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.18); }
        .topbar, .content-card {
            background: white; border-radius: 20px; padding: 22px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        .table thead { background: #f1f5f9; }
        .form-control, .form-select, .btn { border-radius: 12px; }
        .badge-open { background: #dbeafe; color: #1d4ed8; }
        .badge-closed { background: #fee2e2; color: #b91c1c; }
        .badge-progress { background: #fef3c7; color: #b45309; }
        .status-badge { padding: 6px 10px; border-radius: 20px; font-size: 12px; }
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
            <a href="cases.php" class="active"><i class="fas fa-folder-open me-2"></i> Cases</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="topbar mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Manage Cases</h2>
                    <p class="text-muted mb-0">Create, update and delete support cases.</p>
                </div>
                <a href="dashboard.php" class="btn btn-dark">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

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
                                        <option value="<?php echo $student['id']; ?>"
                                            <?php if ($edit_case && $edit_case['student_id'] == $student['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($student['full_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Case Title</label>
                                <input type="text" name="case_title" class="form-control"
                                       value="<?php echo $edit_case ? htmlspecialchars($edit_case['case_title']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Case Type</label>
                                <input type="text" name="case_type" class="form-control"
                                       value="<?php echo $edit_case ? htmlspecialchars($edit_case['case_type']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo $edit_case ? htmlspecialchars($edit_case['description']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="open" <?php if ($edit_case && $edit_case['status'] == 'open') echo 'selected'; ?>>Open</option>
                                    <option value="in progress" <?php if ($edit_case && $edit_case['status'] == 'in progress') echo 'selected'; ?>>In Progress</option>
                                    <option value="closed" <?php if ($edit_case && $edit_case['status'] == 'closed') echo 'selected'; ?>>Closed</option>
                                </select>
                            </div>

                            <?php if ($edit_case): ?>
                                <button type="submit" name="update_case" class="btn btn-warning w-100">Update Case</button>
                                <a href="cases.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_case" class="btn btn-primary w-100">Add Case</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="content-card">
                        <h4 class="mb-3">All Cases</h4>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($cases && mysqli_num_rows($cases) > 0): ?>
                                    <?php while ($case = mysqli_fetch_assoc($cases)): ?>
                                        <tr>
                                            <td><?php echo $case['case_id']; ?></td>
                                            <td><?php echo htmlspecialchars($case['full_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                                            <td><?php echo htmlspecialchars($case['case_type']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'badge-open';
                                                if ($case['status'] == 'closed') $statusClass = 'badge-closed';
                                                if ($case['status'] == 'in progress') $statusClass = 'badge-progress';
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($case['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="cases.php?edit=<?php echo $case['case_id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="cases.php?delete=<?php echo $case['case_id']; ?>" class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Delete this case?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No cases found.</td></tr>
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