<?php
mysqli_report(MYSQLI_REPORT_OFF);

session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

/* Add User */
if (isset($_POST['add_user'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if ($check && mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (name, email, password_hash, role, is_active)
                VALUES ('$name', '$email', '$hashed_password', '$role', '$is_active')";

        if (mysqli_query($conn, $sql)) {
            $success = "User added successfully with secure password!";
        } else {
            $error = "Failed to add user: " . mysqli_error($conn);
        }
    }
}

/* Delete User */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $case_check = mysqli_query($conn, "SELECT case_id FROM cases WHERE created_by = $id LIMIT 1");

    if ($case_check && mysqli_num_rows($case_check) > 0) {
        $error = "This user has linked cases, so delete is disabled. Please deactivate the user instead.";
    } else {
        $delete = mysqli_query($conn, "DELETE FROM users WHERE user_id = $id");

        if ($delete) {
            header("Location: users.php");
            exit();
        } else {
            $error = "Failed to delete user: " . mysqli_error($conn);
        }
    }
}

/* Toggle Status */
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);

    mysqli_query($conn, "UPDATE users 
                         SET is_active = IF(is_active=1, 0, 1) 
                         WHERE user_id = $id");

    header("Location: users.php");
    exit();
}

/* Update User */
if (isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND user_id != '$user_id'");

    if ($check && mysqli_num_rows($check) > 0) {
        $error = "Another user already uses this email!";
    } else {
        $sql = "UPDATE users 
                SET name='$name', email='$email', role='$role', is_active='$is_active'
                WHERE user_id='$user_id'";

        if (mysqli_query($conn, $sql)) {
            $success = "User updated successfully!";
        } else {
            $error = "Failed to update user: " . mysqli_error($conn);
        }
    }
}

$edit_user = null;

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $id");

    if ($result && mysqli_num_rows($result) > 0) {
        $edit_user = mysqli_fetch_assoc($result);
    }
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin Panel</title>
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

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px;
        }

        .btn {
            border-radius: 12px;
        }

        .lock-btn {
            cursor: not-allowed;
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 col-lg-2 sidebar">
            <h3>Admin Panel</h3>
            <a href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="users.php" class="active"><i class="fas fa-users me-2"></i> Users</a>
            <a href="students.php"><i class="fas fa-user-graduate me-2"></i> Students</a>
            <a href="cases.php"><i class="fas fa-folder-open me-2"></i> Cases</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">

            <div class="topbar mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Manage Users</h2>
                    <p class="text-muted mb-0">Add, edit, remove, and manage system users.</p>
                </div>
                <a href="dashboard.php" class="btn btn-dark">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
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
                        <h4 class="mb-3">
                            <?php echo $edit_user ? 'Edit User' : 'Add New User'; ?>
                        </h4>

                        <form method="POST">
                            <?php if ($edit_user): ?>
                                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control"
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['name']) : ''; ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>"
                                       required>
                            </div>

                            <?php if (!$edit_user): ?>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="">Select Role</option>
                                    <option value="admin" <?php if ($edit_user && $edit_user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                    <option value="peer" <?php if ($edit_user && $edit_user['role'] == 'peer') echo 'selected'; ?>>Peer</option>
                                    <option value="therapist" <?php if ($edit_user && $edit_user['role'] == 'therapist') echo 'selected'; ?>>Therapist</option>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                    <?php if (($edit_user && $edit_user['is_active'] == 1) || !$edit_user) echo 'checked'; ?>>
                                <label class="form-check-label" for="is_active">Active User</label>
                            </div>

                            <?php if ($edit_user): ?>
                                <button type="submit" name="update_user" class="btn btn-warning w-100">
                                    Update User
                                </button>
                                <a href="users.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_user" class="btn btn-primary w-100">
                                    Add User
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="content-card">
                        <h4 class="mb-3">All Users</h4>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th width="230">Actions</th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php if ($users && mysqli_num_rows($users) > 0): ?>
                                    <?php while ($user = mysqli_fetch_assoc($users)): ?>

                                        <?php
                                        $uid = intval($user['user_id']);

                                        $case_check = mysqli_query($conn, "SELECT case_id FROM cases WHERE created_by = $uid LIMIT 1");
                                        $has_case = $case_check && mysqli_num_rows($case_check) > 0;

                                        $note_check = mysqli_query($conn, "SELECT note_id FROM case_notes WHERE added_by = $uid LIMIT 1");
                                        $has_note = $note_check && mysqli_num_rows($note_check) > 0;

                                        $referral_check = mysqli_query($conn, "SELECT referral_id FROM referrals WHERE referred_by = $uid OR referred_to = $uid LIMIT 1");
                                        $has_referral = $referral_check && mysqli_num_rows($referral_check) > 0;

                                        $assignment_check = mysqli_query($conn, "SELECT assignment_id FROM therapist_peer_assignment WHERE therapist_id = $uid OR peer_id = $uid LIMIT 1");
                                        $has_assignment = $assignment_check && mysqli_num_rows($assignment_check) > 0;

                                        $can_delete = !$has_case && !$has_note && !$has_referral && !$has_assignment;
                                        ?>

                                        <tr>
                                            <td><?php echo $user['user_id']; ?></td>
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
                                            <td>
                                                <a href="users.php?edit=<?php echo $user['user_id']; ?>"
                                                   class="btn btn-sm btn-warning"
                                                   title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <a href="users.php?toggle=<?php echo $user['user_id']; ?>"
                                                   class="btn btn-sm btn-info"
                                                   onclick="return confirm('Change user status?')"
                                                   title="Active / Inactive">
                                                    <i class="fas fa-power-off"></i>
                                                </a>

                                                <?php if ($can_delete): ?>
                                                    <a href="users.php?delete=<?php echo $user['user_id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this user?')"
                                                       title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary lock-btn"
                                                            disabled
                                                            title="This user has linked records. Deactivate instead.">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No users found.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-light border mt-3 mb-0">
                            <strong>Note:</strong> Users with linked cases, notes, referrals, or assignments cannot be deleted.
                            Use Active/Inactive instead to preserve database records.
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
</body>
</html>