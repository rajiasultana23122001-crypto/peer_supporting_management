<?php include('../includes/auth.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome Admin: <?php echo $_SESSION['name']; ?></h2>
    <a href="../logout.php">Logout</a>
</body>
</html>