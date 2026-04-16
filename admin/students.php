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

/* Add Student */
if (isset($_POST['add_student'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $contact_info = mysqli_real_escape_string($conn, $_POST['contact_info']);
    $consent_given = isset($_POST['consent_given']) ? 1 : 0;

    $check = mysqli_query($conn, "SELECT * FROM students WHERE student_id='$student_id'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Student ID already exists!";
    } else {
        $sql = "INSERT INTO students (student_id, name, department, academic_year, contact_info, consent_given)
                VALUES ('$student_id', '$name', '$department', '$academic_year', '$contact_info', '$consent_given')";

        if (mysqli_query($conn, $sql)) {
            $success = "Student added successfully!";
        } else {
            $error = "Failed to add student: " . mysqli_error($conn);
        }
    }
}

/* Delete Student */
if (isset($_GET['delete'])) {
    $student_id = intval($_GET['delete']);
    $delete = mysqli_query($conn, "DELETE FROM students WHERE student_id = '$student_id'");

    if ($delete) {
        header("Location: students.php");
        exit();
    } else {
        $error = "Failed to delete student: " . mysqli_error($conn);
    }
}

/* Get Student For Edit */
$edit_student = null;
if (isset($_GET['edit'])) {
    $student_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM students WHERE student_id = '$student_id'");
    if ($result && mysqli_num_rows($result) > 0) {
        $edit_student = mysqli_fetch_assoc($result);
    }
}

/* Update Student */
if (isset($_POST['update_student'])) {
    $old_student_id = intval($_POST['old_student_id']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $contact_info = mysqli_real_escape_string($conn, $_POST['contact_info']);
    $consent_given = isset($_POST['consent_given']) ? 1 : 0;

    $check = mysqli_query($conn, "SELECT * FROM students WHERE student_id='$student_id' AND student_id != '$old_student_id'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Another student already uses this Student ID!";
    } else {
        $sql = "UPDATE students 
                SET student_id='$student_id',
                    name='$name',
                    department='$department',
                    academic_year='$academic_year',
                    contact_info='$contact_info',
                    consent_given='$consent_given'
                WHERE student_id='$old_student_id'";

        if (mysqli_query($conn, $sql)) {
            $success = "Student updated successfully!";
            $edit_student = null;
        } else {
            $error = "Failed to update student: " . mysqli_error($conn);
        }
    }
}

$students = mysqli_query($conn, "SELECT * FROM students ORDER BY student_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | Admin Panel</title>
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
        .topbar, .content-card {
            background: white;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        .table thead {
            background: #f1f5f9;
        }
        .form-control, .btn {
            border-radius: 12px;
        }
        .badge-yes {
            background: #dcfce7;
            color: #166534;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .badge-no {
            background: #fee2e2;
            color: #991b1b;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
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
            <a href="students.php" class="active"><i class="fas fa-user-graduate me-2"></i> Students</a>
            <a href="cases.php"><i class="fas fa-folder-open me-2"></i> Cases</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="topbar mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Manage Students</h2>
                    <p class="text-muted mb-0">Add, edit and remove student records.</p>
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
                        <h4 class="mb-3"><?php echo $edit_student ? 'Edit Student' : 'Add Student'; ?></h4>

                        <form method="POST">
                            <?php if ($edit_student): ?>
                                <input type="hidden" name="old_student_id" value="<?php echo $edit_student['student_id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Student ID</label>
                                <input type="number" name="student_id" class="form-control"
                                       value="<?php echo $edit_student ? htmlspecialchars($edit_student['student_id']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Student Name</label>
                                <input type="text" name="name" class="form-control"
                                       value="<?php echo $edit_student ? htmlspecialchars($edit_student['name']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control"
                                       value="<?php echo $edit_student ? htmlspecialchars($edit_student['department']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" name="academic_year" class="form-control"
                                       value="<?php echo $edit_student ? htmlspecialchars($edit_student['academic_year']) : ''; ?>"
                                       placeholder="e.g. 1st Year, 2nd Year">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contact Info</label>
                                <input type="text" name="contact_info" class="form-control"
                                       value="<?php echo $edit_student ? htmlspecialchars($edit_student['contact_info']) : ''; ?>"
                                       placeholder="Email | Phone">
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="consent_given" id="consent_given"
                                    <?php if (($edit_student && $edit_student['consent_given'] == 1) || !$edit_student) echo 'checked'; ?>>
                                <label class="form-check-label" for="consent_given">Consent Given</label>
                            </div>

                            <?php if ($edit_student): ?>
                                <button type="submit" name="update_student" class="btn btn-warning w-100">Update Student</button>
                                <a href="students.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_student" class="btn btn-primary w-100">Add Student</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="content-card">
                        <h4 class="mb-3">All Students</h4>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Academic Year</th>
                                        <th>Contact Info</th>
                                        <th>Consent</th>
                                        <th width="140">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($students && mysqli_num_rows($students) > 0): ?>
                                    <?php while ($student = mysqli_fetch_assoc($students)): ?>
                                        <tr>
                                            <td><?php echo $student['student_id']; ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['department']); ?></td>
                                            <td><?php echo htmlspecialchars($student['academic_year']); ?></td>
                                            <td><?php echo htmlspecialchars($student['contact_info']); ?></td>
                                            <td>
                                                <?php if ($student['consent_given'] == 1): ?>
                                                    <span class="badge-yes">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge-no">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="students.php?edit=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="students.php?delete=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Delete this student?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No students found.</td>
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