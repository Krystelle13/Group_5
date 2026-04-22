<?php 
require_once 'db_connect.php'; 
session_start();

// Security: Redirect if not authenticated
if (!isset($_SESSION['authenticated'])) { 
    header("Location: login.php"); 
    exit(); 
}

/** * FETCH TOTAL REVENUE */
try {
    $income_query = $conn->query("
        SELECT SUM(r.price) as total 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.room_id 
        WHERE b.status = 'Checked-out'
    ");
    
    $income = $income_query->fetch(PDO::FETCH_ASSOC);
    $total_income = $income['total'] ?? 0;
} catch (PDOException $e) {
    $total_income = 0; // Fallback
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f7f6; }
        .main { padding: 30px; margin-left: 250px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .bg-income { background: linear-gradient(45deg, #0d6efd, #0dcaf0); color: white; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <h2 class="fw-bold mb-4">Resort Overview</h2>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card p-4 bg-income">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase fw-bold opacity-75 small">Total Revenue</h6>
                            <h3 class="mb-0">₱ <?= number_format($total_income, 2) ?></h3>
                        </div>
                        <div class="icon">
                            <i class="fa-solid fa-coins fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="alert alert-info border-0 shadow-sm">
                <i class="fa-solid fa-circle-info me-2"></i>
                Revenue is calculated based on bookings with a <strong>'Checked-out'</strong> status.
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>