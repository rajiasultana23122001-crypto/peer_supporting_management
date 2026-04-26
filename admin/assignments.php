<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

/* Add Assignment */
if (isset($_POST['add_assignment'])) {
    $therapist_id = mysqli_real_escape_string($conn, $_POST['therapist_id']);
    $peer_id = mysqli_real_escape_string($conn, $_POST['peer_id']);

    $check = mysqli_query($conn, "
        SELECT * FROM therapist_peer_assignment 
        WHERE therapist_id='$therapist_id' AND peer_id='$peer_id'
    ");

    if ($check && mysqli_num_rows($check) > 0) {
        $error = "This therapist-peer assignment already exists!";
    } else {
        $sql = "INSERT INTO therapist_peer_assignment (therapist_id, peer_id, is_active)
                VALUES ('$therapist_id', '$peer_id', 1)";

        if (mysqli_query($conn, $sql)) {
            $success = "Therapist assigned to peer successfully!";
        } else {
            $error = "Failed to assign: " . mysqli_error($conn);
        }
    }
}

/* Toggle Active/Inactive */
if (isset($_GET['toggle'])) {
    $assignment_id = intval($_GET['toggle']);

    mysqli_query($conn, "
        UPDATE therapist_peer_assignment
        SET is_active = IF(is_active=1, 0, 1)
        WHERE assignment_id = '$assignment_id'
    ");

    header("Location: assignments.php");
    exit();
}

/* Dropdown Data */
$therapists = mysqli_query($conn, "
    SELECT user_id, name, email 
    FROM users 
    WHERE role='therapist' AND is_active=1 
    ORDER BY name ASC
");

$peers = mysqli_query($conn, "
    SELECT user_id, name, email 
    FROM users 
    WHERE role='peer' AND is_active=1 
    ORDER BY name ASC
");

/* Assignment List */
$assignments = mysqli_query($conn, "
    SELECT 
        tpa.assignment_id,
        tpa.is_active,
        tpa.assigned_at,
        therapist.name AS therapist_name,
        therapist.email AS therapist_email,
        peer.name AS peer_name,
        peer.email AS peer_email
    FROM therapist_peer_assignment tpa
    LEFT JOIN users therapist ON tpa.therapist_id = therapist.user_id
    LEFT JOIN users peer ON tpa.peer_id = peer.user_id
    ORDER BY tpa.assignment_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Therapist Peer Assignment | Admin Panel</title>
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

        .form-control,
        .form-select,
        .btn {
            border-radius: 12px;
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
            <a href="cases.php"><i class="fas fa-folder-open me-2"></i> Cases</a>
            <a href="assignments.php" class="active"><i class="fas fa-link me-2"></i> Assignments</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">

            <div class="topbar mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Therapist-Peer Assignment</h2>
                    <p class="text-muted mb-0">Assign therapists to peers so therapists can monitor peer cases.</p>
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
                        <h4 class="mb-3">Add Assignment</h4>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Select Therapist</label>
                                <select name="therapist_id" class="form-select" required>
                                    <option value="">Choose Therapist</option>
                                    <?php if ($therapists && mysqli_num_rows($therapists) > 0): ?>
                                        <?php while ($therapist = mysqli_fetch_assoc($therapists)): ?>
                                            <option value="<?php echo $therapist['user_id']; ?>">
                                                <?php echo htmlspecialchars($therapist['name']); ?> 
                                                (<?php echo htmlspecialchars($therapist['email']); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Peer</label>
                                <select name="peer_id" class="form-select" required>
                                    <option value="">Choose Peer</option>
                                    <?php if ($peers && mysqli_num_rows($peers) > 0): ?>
                                        <?php while ($peer = mysqli_fetch_assoc($peers)): ?>
                                            <option value="<?php echo $peer['user_id']; ?>">
                                                <?php echo htmlspecialchars($peer['name']); ?> 
                                                (<?php echo htmlspecialchars($peer['email']); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <button type="submit" name="add_assignment" class="btn btn-primary w-100">
                                Assign Therapist
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="content-card">
                        <h4 class="mb-3">All Assignments</h4>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Therapist</th>
                                    <th>Peer</th>
                                    <th>Status</th>
                                    <th>Assigned At</th>
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php if ($assignments && mysqli_num_rows($assignments) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($assignments)): ?>
                                        <tr>
                                            <td><?php echo $row['assignment_id']; ?></td>

                                            <td>
                                                <strong><?php echo htmlspecialchars($row['therapist_name'] ?? 'N/A'); ?></strong><br>
                                                <small><?php echo htmlspecialchars($row['therapist_email'] ?? ''); ?></small>
                                            </td>

                                            <td>
                                                <strong><?php echo htmlspecialchars($row['peer_name'] ?? 'N/A'); ?></strong><br>
                                                <small><?php echo htmlspecialchars($row['peer_email'] ?? ''); ?></small>
                                            </td>

                                            <td>
                                                <?php if ($row['is_active'] == 1): ?>
                                                    <span class="badge-active">Active</span>
                                                <?php else: ?>
                                                    <span class="badge-inactive">Inactive</span>
                                                <?php endif; ?>
                                            </td>

                                            <td><?php echo htmlspecialchars($row['assigned_at'] ?? ''); ?></td>

                                            <td>
                                                <a href="assignments.php?toggle=<?php echo $row['assignment_id']; ?>"
                                                   class="btn btn-sm btn-info"
                                                   onclick="return confirm('Change assignment status?')">
                                                    Toggle
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No assignments found.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>

                            </table>
                        </div>

                        <div class="alert alert-light border mt-3 mb-0">
                            <strong>Note:</strong> Therapists can only see cases from peers assigned to them.
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
</body>
</html>