<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2;url=login.php">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<div class="page-center">
    <div class="glass-card p-5 text-center logout-card">
        <h2 class="brand-title mb-3">You have been logged out</h2>
        <p class="brand-subtitle mb-4">Redirecting to login page...</p>
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-4">
            <a href="login.php" class="btn btn-primary btn-main">Go to Login Now</a>
        </div>
    </div>
</div>

</body>
</html>