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

/* Check access */
$case_query = mysqli_query($conn, "
    SELECT 
        c.case_id,
        c.status,
        s.name AS student_name,
        u.name AS peer_name
    FROM cases c
    INNER JOIN therapist_peer_assignment tpa ON c.created_by = tpa.peer_id
    LEFT JOIN students s ON c.student_id = s.student_id
    LEFT JOIN users u ON c.created_by = u.user_id
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

/*
Assuming your table columns are:
note_id, case_id, added_by, note_text, timestamp
*/
if (isset($_POST['add_note'])) {
    $note_text = mysqli_real_escape_string($conn, trim($_POST['note_text']));

    if ($note_text == "") {
        $error = "Note cannot be empty.";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO case_notes (case_id, added_by, note_text)
            VALUES ('$case_id', '$therapist_id', '$note_text')
        ");

        if ($insert) {
            $success = "Note added successfully!";
        } else {
            $error = "Failed to add note: " . mysqli_error($conn);
        }
    }
}

$notes = mysqli_query($conn, "
    SELECT 
        cn.note_id,
        cn.note_text,
        cn.timestamp,
        u.name AS added_by_name
    FROM case_notes cn
    LEFT JOIN users u ON cn.added_by = u.user_id
    WHERE cn.case_id = '$case_id'
    ORDER BY cn.note_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            font-family: 'Segoe UI', sans-serif;
        }
        .page-card {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .note-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
            border: 1px solid #e2e8f0;
        }
        textarea.form-control, .btn {
            border-radius: 12px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Case Notes</h2>
                <p class="text-muted mb-0">
                    Case #<?php echo $case['case_id']; ?> |
                    Student: <?php echo htmlspecialchars($case['student_name'] ?? 'N/A'); ?> |
                    Peer: <?php echo htmlspecialchars($case['peer_name'] ?? 'N/A'); ?>
                </p>
            </div>
            <a href="dashboard.php" class="btn btn-dark">Back</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label class="form-label">Add New Note</label>
                <textarea name="note_text" class="form-control" rows="4" placeholder="Write your note here..." required></textarea>
            </div>
            <button type="submit" name="add_note" class="btn btn-success">Save Note</button>
        </form>

        <h4 class="mb-3">Previous Notes</h4>

        <?php if ($notes && mysqli_num_rows($notes) > 0): ?>
            <?php while ($note = mysqli_fetch_assoc($notes)): ?>
                <div class="note-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong><?php echo htmlspecialchars($note['added_by_name'] ?? 'Unknown'); ?></strong>
                        <small class="text-muted"><?php echo htmlspecialchars($note['timestamp']); ?></small>
                    </div>
                    <div><?php echo nl2br(htmlspecialchars($note['note_text'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-light border">No notes added yet.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>