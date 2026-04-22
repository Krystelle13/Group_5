<?php 
require_once 'db_connect.php'; 
session_start(); 

// 1. AUTHENTICATION & INITIALIZATION
if (!isset($_SESSION['authenticated'])) { header("Location: login.php"); exit(); }

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// 2. CORE FUNCTIONS
function sendIslandMail($to, $name, $subject, $title, $msg) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = '://gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'auraislandg5@gmail.com'; 
        $mail->Password = 'ebwt yhhz nooo vndw'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom('auraislandg5@gmail.com', 'Island Aura Beach Resort');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<div style='font-family:Arial;border:1px solid #eee;padding:25px;border-radius:15px;'>
                        <h2 style='color:#004aad;text-align:center;'>$title</h2><p>Warm Greetings <b>$name</b>,</p><p>$msg</p></div>";
        $mail->send();
    } catch (Exception $e) { error_log($mail->ErrorInfo); }
}

// 3. ACTION CONTROLLER (Handles the "Move" logic)
if (isset($_GET['action']) && isset($_GET['email']) && isset($_GET['date'])) {
    $act = $_GET['action'];
    $em  = $_GET['email'];
    $dt  = $_GET['date'];
    $nm  = $_GET['name'] ?? 'Guest';

    if ($act == 'confirm_paid') {
        $upd = $conn->prepare("UPDATE bookings SET status='Paid' WHERE guest_email=? AND check_in_date=? AND status='Pending'");
        if($upd->execute([$em, $dt])) {
            sendIslandMail($em, $nm, "Payment Confirmed", "Booking Secured!", "Your payment for $dt is confirmed. We look forward to seeing you!");
            $_SESSION['alert'] = "Payment confirmed for $nm. Guest moved to Confirmed Arrivals.";
        }
    } elseif ($act == 'check_in') {
        $upd = $conn->prepare("UPDATE bookings SET status='Checked-in' WHERE guest_email=? AND check_in_date=? AND status='Paid'");
        if($upd->execute([$em, $dt])) {
            $_SESSION['alert'] = "$nm has been checked in.";
        }
    } elseif ($act == 'check_out') {
        $upd = $conn->prepare("UPDATE bookings SET status='Completed' WHERE guest_email=? AND check_in_date=? AND status='Checked-in'");
        if($upd->execute([$em, $dt])) {
            sendIslandMail($em, $nm, "Thank You", "Safe Travels!", "Thank you for staying at Island Aura Resort.");
            $_SESSION['alert'] = "Check-out complete for $nm.";
        }
    }
    // CLEAN REDIRECT: Redirects to the base page to prevent re-submission on refresh
    header("Location: process_bookings.php");
    exit();
}

// 4. COUNTER LOGIC
$c_pend = $conn->query("SELECT COUNT(*) FROM (SELECT 1 FROM bookings WHERE status='Pending' GROUP BY guest_email, check_in_date) as t")->fetchColumn();
$c_paid = $conn->query("SELECT COUNT(*) FROM (SELECT 1 FROM bookings WHERE status='Paid' GROUP BY guest_email, check_in_date) as t")->fetchColumn();
$c_stay = $conn->query("SELECT COUNT(*) FROM (SELECT 1 FROM bookings WHERE status='Checked-in' GROUP BY guest_email, check_in_date) as t")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Management | Island Aura</title>
    <link href="https://jsdelivr.net" rel="stylesheet">
    <link rel="stylesheet" href="https://cloudflare.com">
    <style>
        :root { --ia-blue: #004aad; }
        .main-content { margin-left: 280px; padding: 40px; transition: 0.3s; }
        .nav-pills .nav-link { color: #555; font-weight: 600; border-radius: 10px; margin-right: 10px; }
        .nav-pills .nav-link.active { background-color: var(--ia-blue); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .badge-notif { font-size: 0.7em; vertical-align: top; margin-left: 5px; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary m-0">Front Desk Operations</h2>
            <?php if(isset($_SESSION['alert'])): ?>
                <div class="alert alert-success py-2 px-3 m-0 shadow-sm rounded-pill" style="font-size: 0.9em;">
                    <i class="fa-solid fa-check-circle me-1"></i> <?= $_SESSION['alert']; unset($_SESSION['alert']); ?>
                </div>
            <?php endif; ?>
        </div>

        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#p1">Awaiting Payment <span class="badge bg-danger badge-notif"><?= $c_pend ?></span></button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#p2">Confirmed Arrivals <span class="badge bg-success badge-notif"><?= $c_paid ?></span></button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#p3">In-House Guests <span class="badge bg-info badge-notif"><?= $c_stay ?></span></button>
            </li>
        </ul>

        <div class="tab-content card p-4">
            <div class="tab-pane fade show active" id="p1"><?php masterTable($conn, 'Pending', 'confirm_paid', 'Verify Payment', 'btn-warning'); ?></div>
            <div class="tab-pane fade" id="p2"><?php masterTable($conn, 'Paid', 'check_in', 'Check-In Guest', 'btn-primary'); ?></div>
            <div class="tab-pane fade" id="p3"><?php masterTable($conn, 'Checked-in', 'check_out', 'Check-Out', 'btn-danger'); ?></div>
        </div>
    </div>

<?php 
function masterTable($conn, $status, $action, $btnLabel, $btnClass) {
    $stmt = $conn->prepare("SELECT b.guest_name, b.guest_email, b.check_in_date, GROUP_CONCAT(r.room_name SEPARATOR ' & ') as rooms 
                            FROM bookings b JOIN rooms r ON b.room_id = r.room_id 
                            WHERE b.status = ? GROUP BY b.guest_email, b.check_in_date");
    $stmt->execute([$status]);
    echo '<div class="table-responsive"><table class="table align-middle">
            <thead class="table-light"><tr><th>Guest</th><th>Stay Date</th><th>Accommodations</th><th class="text-end">Action</th></tr></thead><tbody>';
    if($stmt->rowCount() > 0) {
        while($r = $stmt->fetch()) {
            $link = "?action=$action&email=".urlencode($r['guest_email'])."&date=".urlencode($r['check_in_date'])."&name=".urlencode($r['guest_name']);
            echo "<tr>
                    <td><b>{$r['guest_name']}</b><br><small class='text-muted'>{$r['guest_email']}</small></td>
                    <td>{$r['check_in_date']}</td>
                    <td><span class='badge bg-light text-dark border'>{$r['rooms']}</span></td>
                    <td class='text-end'><a href='$link' class='btn $btnClass btn-sm fw-bold px-3 rounded-pill'>$btnLabel</a></td>
                  </tr>";
        }
    } else {
        echo '<tr><td colspan="4" class="text-center py-5 text-muted">No active records in this category.</td></tr>';
    }
    echo '</tbody></table></div>';
}
?>
    <script src="https://jsdelivr.net"></script>
</body>
</html>
