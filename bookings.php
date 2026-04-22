<?php 
require_once 'db_connect.php'; 
session_start();
if (!isset($_SESSION['authenticated'])) { header("Location: login.php"); exit(); }

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php'; require 'PHPMailer/PHPMailer.php'; require 'PHPMailer/SMTP.php';

// Approchable Email Function
function sendStatusEmail($to, $name, $subject, $title, $msg) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
        $mail->Username = 'auraislandg5@gmail.com'; $mail->Password = 'ebwt yhhz nooo vndw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; $mail->Port = 465;
        $mail->setFrom('auraislandg5@gmail.com', 'Island Aura Beach Resort');
        $mail->addAddress($to); $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; border: 1px solid #eee; padding: 25px; border-radius: 15px; max-width: 600px; margin: auto;'>
                <h2 style='color: #004aad; text-align: center;'>$title</h2>
                <p>Warm greetings, <b>$name</b>!</p>
                <p style='line-height: 1.6; color: #444;'>$msg</p>
                <hr style='border:0; border-top:1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 11px; color: #999; text-align: center;'>Island Aura Beach Resort Notification</p>
            </div>";
        $mail->send();
    } catch (Exception $e) { }
}

// ACTION HANDLER: Force update status
if (isset($_GET['action']) && isset($_GET['email']) && isset($_GET['date'])) {
    $email = $_GET['email']; 
    $action = $_GET['action'];
    $date = $_GET['date'];

    $g = $conn->prepare("SELECT guest_name FROM bookings WHERE guest_email = ? AND check_in_date = ? LIMIT 1");
    $g->execute([$email, $date]); 
    $guest = $g->fetch();

    if ($guest) {
        $name = $guest['guest_name'];
        if ($action == 'confirm_paid') {
            $conn->prepare("UPDATE bookings SET status='Paid' WHERE guest_email=? AND check_in_date=?")->execute([$email, $date]);
            sendStatusEmail($email, $name, "Payment Confirmed!", "Booking Secured!", "Thank you! We've received your payment. Your reservation for $date is now confirmed.");
        } elseif ($action == 'checkin') {
            $conn->prepare("UPDATE bookings SET status='Checked-in' WHERE guest_email=? AND check_in_date=?")->execute([$email, $date]);
            sendStatusEmail($email, $name, "Welcome!", "Checked-in!", "You're now checked-in. Enjoy your stay!");
        } elseif ($action == 'checkout') {
            $conn->prepare("UPDATE bookings SET status='Checked-out' WHERE guest_email=? AND check_in_date=?")->execute([$email, $date]);
            sendStatusEmail($email, $name, "Thank You!", "Safe Travels!", "Thank you for staying with us!");
        }
    }
    header("Location: bookings.php"); exit;
}

$pCount = $conn->query("SELECT COUNT(*) FROM bookings WHERE status LIKE '%Pending%'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Bookings | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main-content { margin-left: 280px; padding: 40px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .badge-notif { background: #ff4757; color: white; padding: 2px 10px; border-radius: 50px; font-size: 14px; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <h2 class="fw-bold mb-4">Reservations Management <?php if($pCount > 0): ?><span class="badge-notif"><?= $pCount ?></span><?php endif; ?></h2>

        <div class="card p-4 border-start border-warning border-5">
            <h5 class="text-warning fw-bold mb-3">Awaiting Payment Confirmation</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Guest</th><th>Stay Date</th><th>Accommodations</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $q = $conn->query("SELECT b.*, GROUP_CONCAT(r.room_name SEPARATOR ' & ') as all_rooms FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.status LIKE '%Pending%' GROUP BY b.guest_email, b.check_in_date");
                        while($r = $q->fetch()): ?>
                        <tr>
                            <td><b><?= $r['guest_name'] ?></b></td>
                            <td><?= $r['check_in_date'] ?></td>
                            <td><?= $r['all_rooms'] ?></td>
                            <td><a href="?action=confirm_paid&email=<?= $r['guest_email'] ?>&date=<?= $r['check_in_date'] ?>" class="btn btn-warning btn-sm fw-bold">Confirm Paid</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 border-start border-primary border-5">
            <h5 class="text-primary fw-bold mb-3">Confirmed & Paid</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Guest</th><th>Stay Date</th><th>Accommodations</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $q = $conn->query("SELECT b.*, GROUP_CONCAT(r.room_name SEPARATOR ' & ') as all_rooms FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.status LIKE '%Paid%' GROUP BY b.guest_email, b.check_in_date");
                        while($r = $q->fetch()): ?>
                        <tr>
                            <td><b><?= $r['guest_name'] ?></b></td>
                            <td><?= $r['check_in_date'] ?></td>
                            <td><?= $r['all_rooms'] ?></td>
                            <td><a href="?action=checkin&email=<?= $r['guest_email'] ?>&date=<?= $r['check_in_date'] ?>" class="btn btn-success btn-sm">Check-in Guest</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 border-start border-success border-5">
            <h5 class="text-success fw-bold mb-3">Currently Staying (In-House)</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Guest</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $q = $conn->query("SELECT b.*, GROUP_CONCAT(r.room_name SEPARATOR ' & ') as all_rooms FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.status LIKE '%Checked-in%' GROUP BY b.guest_email, b.check_in_date");
                        while($r = $q->fetch()): ?>
                        <tr>
                            <td><b><?= $r['guest_name'] ?></b><br><small><?= $r['all_rooms'] ?></small></td>
                            <td><span class="badge bg-success">In-House</span></td>
                            <td><a href="?action=checkout&email=<?= $r['guest_email'] ?>&date=<?= $r['check_in_date'] ?>" class="btn btn-outline-danger btn-sm">Check-out</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>