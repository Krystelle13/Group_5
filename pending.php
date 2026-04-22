<?php 
require_once 'db_connect.php'; 
session_start(); 

// Security Check
if (!isset($_SESSION['authenticated'])) { 
    header("Location: login.php"); 
    exit(); 
} 

use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception; 

require 'PHPMailer/Exception.php'; 
require 'PHPMailer/PHPMailer.php'; 
require 'PHPMailer/SMTP.php'; 

/**
 * Sends a styled email notification to the guest
 */
function sendStatusEmail($to, $name, $subject, $title, $msg) { 
    $mail = new PHPMailer(true); 
    try { 
        $mail->isSMTP(); 
        $mail->Host = 'smtp.gmail.com'; // Fixed Hostname
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
    } catch (Exception $e) {
        // Log error if needed: error_log($mail->ErrorInfo);
    } 
} 

// ACTION HANDLER
if (isset($_GET['action']) && $_GET['action'] == 'confirm_paid' && isset($_GET['email']) && isset($_GET['date'])) { 
    $email = $_GET['email']; 
    $date = $_GET['date']; 
    
    // Check if guest exists
    $stmt = $conn->prepare("SELECT guest_name FROM bookings WHERE guest_email = ? AND check_in_date = ? LIMIT 1"); 
    $stmt->execute([$email, $date]); 
    $guest = $stmt->fetch(); 

    if ($guest) { 
        $name = $guest['guest_name']; 
        // Update all rooms associated with this email and date
        $update = $conn->prepare("UPDATE bookings SET status='Paid' WHERE guest_email=? AND check_in_date=? AND status LIKE '%Pending%'");
        $update->execute([$email, $date]); 
        
        sendStatusEmail($email, $name, "Payment Confirmed!", "Booking Secured!", "Thank you! We've received your payment. Your reservation for $date is now confirmed."); 
    } 
    header("Location: bookings.php"); 
    exit; 
} 

// Count pending for the badge
$pCount = $conn->query("SELECT COUNT(*) FROM bookings WHERE status LIKE '%Pending%'")->fetchColumn(); 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Island Aura</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style> 
        :root { --island-blue: #004aad; --lagoon-teal: #23ced9; --sand-bg: #f8f9fa; }
        body { background-color: var(--sand-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 280px; padding: 40px; transition: 0.3s; } 
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); background: #fff; } 
        .text-island-blue { color: var(--island-blue); }
        .border-island-teal { border-left: 6px solid var(--lagoon-teal) !important; }
        .btn-island-teal { background-color: var(--lagoon-teal); color: white; border: none; transition: 0.3s; border-radius: 50px; font-weight: 600; padding: 8px 25px; }
        .btn-island-teal:hover { background-color: #1baeb8; color: white; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(35, 206, 217, 0.3); }
        .badge-notif { background: #ff4757; color: white; padding: 4px 12px; border-radius: 50px; font-size: 14px; vertical-align: middle; } 
        @media (max-width: 992px) { .main-content { margin-left: 0; } } 
    </style>
</head> 
<body> 

    <?php include 'includes/sidebar.php'; ?> 

    <div class="main-content"> 
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-island-blue m-0">
                Reservations Management 
                <?php if($pCount > 0): ?><span class="badge-notif"><?= $pCount ?></span><?php endif; ?>
            </h2>
        </div>

        <div class="card p-4 border-island-teal"> 
            <h5 class="fw-bold mb-4" style="color: #1baeb8;">
                <i class="fa-solid fa-clock-rotate-left me-2"></i>Awaiting Payment Confirmation
            </h5> 
            <div class="table-responsive"> 
                <table class="table align-middle table-hover"> 
                    <thead class="table-light">
                        <tr>
                            <th>Guest Name</th>
                            <th>Stay Date</th>
                            <th>Accommodations</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead> 
                    <tbody> 
                        <?php 
                        // Query to group multiple rooms under one row for same user/date
                        $query = "SELECT b.guest_name, b.guest_email, b.check_in_date, GROUP_CONCAT(r.room_name SEPARATOR ' & ') as all_rooms 
                                  FROM bookings b 
                                  JOIN rooms r ON b.room_id = r.room_id 
                                  WHERE b.status LIKE '%Pending%' 
                                  GROUP BY b.guest_email, b.check_in_date";
                        $q = $conn->query($query); 
                        
                        if($q->rowCount() > 0):
                            while($r = $q->fetch()): 
                        ?> 
                        <tr> 
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($r['guest_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($r['guest_email']) ?></small>
                            </td> 
                            <td><i class="fa-regular fa-calendar-days me-2 text-muted"></i><?= htmlspecialchars($r['check_in_date']) ?></td> 
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['all_rooms']) ?></span></td> 
                            <td class="text-center">
                                <a href="?action=confirm_paid&email=<?= urlencode($r['guest_email']) ?>&date=<?= urlencode($r['check_in_date']) ?>" 
                                   class="btn btn-island-teal btn-sm"
                                   onclick="return confirmAction(event, '<?= htmlspecialchars($r['guest_name'], ENT_QUOTES) ?>');">
                                   Confirm Paid
                                </a>
                            </td> 
                        </tr> 
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">No pending payments found.</td></tr>
                        <?php endif; ?>
                    </tbody> 
                </table> 
            </div> 
        </div> 
    </div> 

    <script>
    function confirmAction(e, guestName) {
        const confirmed = confirm("Are you sure you want to confirm the payment for " + guestName + "?\n\nThis will update the database and send a confirmation email automatically.");
        
        if (confirmed) {
            const btn = e.currentTarget;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            btn.classList.add('disabled');
            btn.style.pointerEvents = 'none';
            return true;
        }
        return false;
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body> 
</html>