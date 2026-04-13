<?php include('../includes/auth.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peer Dashboard</title>
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
                <h2>Welcome Peer ☀️</h2>
                <p>Hello, <?php echo $_SESSION['name']; ?> — record student interactions and track your work here.</p>
            </div>
            <a href="../logout.php" class="btn btn-danger custom-btn">Logout</a>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card stat-orange">
                    <h5>My Cases</h5>
                    <h3>00</h3>
                    <p class="mb-0">Cases you created</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card stat-blue">
                    <h5>New Entries</h5>
                    <h3>00</h3>
                    <p class="mb-0">Support logs added today</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card stat-green">
                    <h5>Activities</h5>
                    <h3>00</h3>
                    <p class="mb-0">Recent peer service actions</p>
                </div>
            </div>
        </div>

        <div class="section-title">Quick Actions</div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="action-card">
                    <div class="quick-badge badge-blue">New Record</div>
                    <div class="icon">➕</div>
                    <h4>Add Case</h4>
                    <p>Create a new case whenever a student interaction needs to be documented properly.</p>
                    <a href="add_case.php" class="btn btn-primary custom-btn">Add Now</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="action-card">
                    <div class="quick-badge badge-red">Monitoring</div>
                    <div class="icon">📋</div>
                    <h4>My Cases</h4>
                    <p>Open the cases you created and keep track of progress, notes and updates.</p>
                    <a href="cases.php" class="btn btn-danger custom-btn">View Cases</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="action-card">
                    <div class="quick-badge badge-green">Progress</div>
                    <div class="icon">🕒</div>
                    <h4>Activity Log</h4>
                    <p>Track your recent support work and see how your peer service records are growing.</p>
                    <a href="activity.php" class="btn btn-success custom-btn">View Activity</a>
                </div>
            </div>
        </div>

        <div class="section-title">My Recent Summary</div>

        <div class="activity-box">
            <div class="activity-item">
                <strong>Peer Work Area Ready</strong><br>
                <span class="text-muted">You can now use this panel as the main hub for peer support tasks.</span>
            </div>
            <div class="activity-item">
                <strong>Case Entry Module Next</strong><br>
                <span class="text-muted">Add Case page can be built next to start storing real case data.</span>
            </div>
            <div class="activity-item">
                <strong>Progress Tracking</strong><br>
                <span class="text-muted">Activity and case history will make your work look organized and professional.</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>