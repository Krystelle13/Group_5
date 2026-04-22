<?php 
require_once 'db_connect.php'; 
session_start();
if (!isset($_SESSION['authenticated'])) { header("Location: login.php"); exit(); }

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php'; 
require 'PHPMailer/PHPMailer.php'; 
require 'PHPMailer/SMTP.php';

// Email Function
function sendStatusEmail($to, $name, $subject, $title, $msg) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'auraislandg5@gmail.com';
        $mail->Password = 'ebwt yhhz nooo vndw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('auraislandg5@gmail.com', 'Island Aura Beach Resort');
        $mail->addAddress($to);
        $mail->isHTML(true);

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

// COUNT ONLY CHECKED-IN GUESTS
$pCount = $conn->query("SELECT COUNT(*) FROM bookings WHERE status LIKE '%Checked-in%'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Currently Staying | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .main-content { margin-left: 280px; padding: 40px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .badge-notif { background: #17a2b8; color: white; padding: 2px 10px; border-radius: 50px; font-size: 14px; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }
    </style>
</head>

<body class="bg-light">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <h2 class="fw-bold mb-4">
        Currently Staying
        <?php if($pCount > 0): ?>
            <span class="badge-notif"><?= $pCount ?></span>
        <?php endif; ?>
    </h2>

    <!-- IN-HOUSE SECTION -->
    <div class="card p-4 border-start border-info border-5">
        <h5 class="text-info fw-bold mb-3">Guests Currently In-House</h5>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Stay Date</th>
                        <th>Accommodations</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $q = $conn->query("
                        SELECT b.*, 
                        GROUP_CONCAT(r.room_name SEPARATOR ' & ') as all_rooms 
                        FROM bookings b 
                        JOIN rooms r ON b.room_id = r.room_id 
                        WHERE b.status LIKE '%Checked-in%' 
                        GROUP BY b.guest_email, b.check_in_date
                    ");

                    while($r = $q->fetch()): ?>
                    <tr>
                        <td><b><?= $r['guest_name'] ?></b></td>
                        <td><?= $r['check_in_date'] ?></td>
                        <td><?= $r['all_rooms'] ?></td>
                        <td><span class="badge bg-info">In-House</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>