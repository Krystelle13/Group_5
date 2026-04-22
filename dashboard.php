<?php 
require_once 'db_connect.php'; 
session_start();
if (!isset($_SESSION['authenticated'])) { header("Location: login.php"); exit(); }

// Calculate Total Income (Example: Sum of prices of checked-out bookings)
$income_query = $conn->query("SELECT SUM(r.price) as total FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.status = 'Checked-out'");
$income = $income_query->fetch();
$total_income = $income['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Dashboard | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <h2 class="fw-bold mb-4">Resort Overview</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card p-4 bg-primary text-white">
                    <h6>Total Revenue</h6>
                    <h3>₱ <?= number_format($total_income, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card p-4">
                    <h5>Quick Navigation</h5>
                    <div class="d-flex gap-2 mt-3">
                        <a href="bookings.php" class="btn btn-outline-primary">Manage Bookings</a>
                        <a href="accommodations.php" class="btn btn-outline-primary">Update Prices</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>