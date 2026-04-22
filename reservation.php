<?php 
require_once 'db_connect.php'; 
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Kunin ang rates mula sa settings table
$set = $conn->query("SELECT * FROM settings LIMIT 1")->fetch();

// E-pass ang rates sa JavaScript para sa calculation
echo "<script>
    const rates = {
        day: " . ($set['day_entrance'] ?? 100) . ",
        night: " . ($set['night_entrance'] ?? 150) . ",
        pool: " . ($set['pool_fee'] ?? 50) . "
    };
</script>";

$today = date('Y-m-d');

if(isset($_POST['confirm_booking'])) {
    $guest_name = $_POST['name'];
    $guest_email = $_POST['email']; 
    $contact_no = $_POST['contact'];
    $check_in = $_POST['check_in'];
    $payment = $_POST['payment_method'] ?? 'Walk-in';
    $adults = intval($_POST['adults']);
    $children = intval($_POST['children']);
    $total_amount = $_POST['total_amount_val']; 
    $selected_items = $_POST['items'] ?? [];

    if(!empty($selected_items)) {
        try {
            $item_details_html = ""; 
            $total_pax = $adults + $children;
            
            foreach($selected_items as $room_id) {
                $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
                $stmt->execute([$room_id]);
                $room_info = $stmt->fetch();

                $sql = "INSERT INTO bookings (guest_name, guest_email, contact_no, check_in_date, room_id, status, payment_option, total_price, pax) 
                        VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, ?)";
                $conn->prepare($sql)->execute([$guest_name, $guest_email, $contact_no, $check_in, $room_id, $payment, $total_amount, $total_pax]);

                $item_details_html .= "
                    <div style='border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:10px;'>
                        <b>Item:</b> {$room_info['room_name']}<br>
                        <b>Price:</b> ₱".number_format($room_info['price'])."
                    </div>";
            }

           // EMAIL LOGIC
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'auraislandg5@gmail.com'; 
            $mail->Password = 'ebwt yhhz nooo vndw'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $mail->Port = 465; 
            $mail->setFrom('auraislandg5@gmail.com', 'Island Aura Resort');
            $mail->addAddress($guest_email, $guest_name);
            $mail->isHTML(true);
            $mail->Subject = 'Pending Reservation - Island Aura Resort';
            
            // DITO MO EH INSERT / PALITAN:
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; border: 1px solid #eee; padding: 20px;'>
                    <h2 style='color: #004aad;'>Hello $guest_name!</h2>
                    <p>Your reservation request for <b>$check_in</b> is now being processed.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    
                    <h4>Accommodation Details:</h4>
                    $item_details_html
                    
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    
                    <h4>Entrance & Fees:</h4>
                    <ul style='list-style: none; padding: 0;'>
                        <li><b>Adults:</b> $adults</li>
                        <li><b>Children:</b> $children</li>
                        <li><b>Pool Access:</b> " . (isset($_POST['use_pool']) ? 'Included' : 'Not Included') . "</li>
                        <li><b>Payment Method:</b> $payment</li>
                    </ul>
                    
                    <div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px;'>
                        <h3 style='margin: 0; color: #004aad;'>Total Amount: ₱" . number_format($total_amount, 2) . "</h3>
                    </div>
                    
                    <p style='font-size: 12px; color: #777; margin-top: 20px;'>
                        Please wait for our staff to call or email you for the confirmation of your booking. Thank you for choosing Island Aura!
                    </p>
                </div>";

            $mail->send();

            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                setTimeout(function() {
                    Swal.fire({ title: 'SUCCESS!', text: 'Reservation sent!', icon: 'success' }).then(() => { window.location.href='index.php'; });
                }, 100);
            </script>";
        } catch (Exception $e) { echo "Error: " . $e->getMessage(); }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Book Your Stay | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #004aad; }
        body { background: #f4f7f6; font-family: 'Poppins', sans-serif; }
        .section-header { border-left: 5px solid var(--primary); padding-left: 15px; margin: 30px 0 20px; font-weight: 700; }
        .item-card { cursor: pointer; border: none; border-radius: 15px; overflow: hidden; transition: 0.3s; height: 100%; background: white; position: relative; }
        .img-box { height: 180px; overflow: hidden; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        .item-check { display: none; }
        .item-check:checked + .item-card { outline: 3px solid var(--primary); background: #f0f7ff; }
        .total-banner { background: #222; color: #ff9f43; border-radius: 12px; padding: 15px; margin: 15px 0; }
        .view-btn { position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.8); border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; color: var(--primary); z-index: 5; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <form method="POST" id="bookingForm" onsubmit="return validateForm()">
        <div class="row g-4">
            <div class="col-lg-8">
                
                <div class="card border-0 shadow-sm p-4 rounded-4 mb-4">
                    <h5 class="fw-bold mb-3">Schedule Type</h5>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input tour-type" type="radio" name="tour_type" id="daytour" value="day" checked>
                            <label class="form-check-label fw-bold" for="daytour">Daytour Entrance</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input tour-type" type="radio" name="tour_type" id="overnight" value="night">
                            <label class="form-check-label fw-bold" for="overnight">Overnight Entrance</label>
                        </div>
                    </div>
                </div>

                <h4 class="section-header">AVAILABLE ROOMS</h4>
                <div class="row g-3 mb-5">
                    <?php 
                    $rooms = $conn->query("SELECT * FROM rooms WHERE availability='Available' AND (room_type LIKE '%Room%' OR room_type LIKE '%Suite%')");
                    while($r = $rooms->fetch()): ?>
                        <div class="col-md-6">
                            <input type="checkbox" name="items[]" value="<?= $r['room_id']; ?>" data-price="<?= $r['price']; ?>" class="item-check" id="i_<?= $r['room_id']; ?>">
                            <div class="item-card shadow-sm">
                                <a href="uploads/<?= $r['image']; ?>" class="glightbox view-btn"><i class="fa fa-expand"></i></a>
                                <label for="i_<?= $r['room_id']; ?>" class="w-100 h-100" style="cursor:pointer;">
                                    <div class="img-box"><img src="uploads/<?= $r['image']; ?>"></div>
                                    <div class="p-3">
                                        <h6 class="fw-bold mb-1"><?= $r['room_name']; ?></h6>
                                        <h5 class="text-primary fw-bold mb-0">₱<?= number_format($r['price']); ?></h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <h4 class="section-header">COTTAGES & GAZEBOS</h4>
                <div class="row g-3 mb-5">
                    <?php 
                    $cottages = $conn->query("SELECT * FROM rooms WHERE availability='Available' AND (room_type LIKE '%Cottage%' OR room_type LIKE '%Gazebo%')");
                    while($c = $cottages->fetch()): ?>
                        <div class="col-md-6">
                            <input type="checkbox" name="items[]" value="<?= $c['room_id']; ?>" data-price="<?= $c['price']; ?>" class="item-check" id="i_<?= $c['room_id']; ?>">
                            <div class="item-card shadow-sm">
                                <a href="uploads/<?= $c['image']; ?>" class="glightbox view-btn"><i class="fa fa-expand"></i></a>
                                <label for="i_<?= $c['room_id']; ?>" class="w-100 h-100" style="cursor:pointer;">
                                    <div class="img-box"><img src="uploads/<?= $c['image']; ?>"></div>
                                    <div class="p-3">
                                        <h6 class="fw-bold mb-1"><?= $c['room_name']; ?></h6>
                                        <h5 class="text-primary fw-bold mb-0">₱<?= number_format($c['price']); ?></h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h5 class="fw-bold mb-3">Entrance Fee Selection</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small fw-bold">Adults</label>
                            <input type="number" name="adults" id="adult_count" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Children</label>
                            <input type="number" name="children" id="child_count" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="use_pool" id="usePool">
                            <label class="form-check-label fw-bold" for="usePool">Include Pool Access? (+₱<?= $set['pool_fee'] ?> per head)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top:20px;">
                    <div class="card border-0 shadow-lg p-4 rounded-4">
                        <h5 class="fw-bold text-center mb-4">RESERVATION FORM</h5>
                        <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
                        <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
                        <input type="text" name="contact" class="form-control mb-3" placeholder="Contact" required>
                        <input type="date" name="check_in" class="form-control mb-4" min="<?= $today ?>" required>
                        
                        <select name="payment_method" class="form-select mb-4" required>
                            <option value="GCash">GCash</option>
                            <option value="Walk-in">Walk-in</option>
                        </select>

                        <div class="total-banner text-center">
                            <small>GRAND TOTAL</small>
                            <h2 class="fw-bold m-0" id="displayTotal">₱0.00</h2>
                            <input type="hidden" name="total_amount_val" id="total_val" value="0">
                        </div>

                        <button type="submit" name="confirm_booking" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">CONFIRM BOOKING</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
    const glightbox = GLightbox({ selector: '.glightbox' });
    const checks = document.querySelectorAll('.item-check');
    const tourRadios = document.querySelectorAll('.tour-type');
    const adultIn = document.getElementById('adult_count');
    const childIn = document.getElementById('child_count');
    const usePool = document.getElementById('usePool');

    // JS Validation para bawal mag-submit kung walang tao, pero HINDI MAGRE-REFRESH
    function validateForm() {
        const adults = parseInt(adultIn.value) || 0;
        const children = parseInt(childIn.value) || 0;
        const selected = document.querySelectorAll('.item-check:checked').length;

        if ((adults + children) <= 0) {
            Swal.fire('Wait!', 'Please set the number of Adults or Children for the Entrance Fee.', 'warning');
            return false; // I-stop ang submission, no refresh!
        }
        if (selected <= 0) {
            Swal.fire('Wait!', 'Please select at least one Room or Cottage.', 'warning');
            return false; // I-stop ang submission, no refresh!
        }
        return true; // Proceed to PHP
    }

    function calc() {
        let total = 0;
        let isNight = document.getElementById('overnight').checked;
        let entranceRate = isNight ? rates.night : rates.day;
        
        checks.forEach(c => { if(c.checked) total += parseFloat(c.getAttribute('data-price')); });
        let paxCount = (parseInt(adultIn.value) || 0) + (parseInt(childIn.value) || 0);
        total += (paxCount * entranceRate);
        if(usePool.checked) total += (paxCount * rates.pool);
        
        document.getElementById('displayTotal').innerText = '₱' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('total_val').value = total;
    }

    [adultIn, childIn, usePool].forEach(el => el.addEventListener('input', calc));
    checks.forEach(c => c.addEventListener('change', calc));
    tourRadios.forEach(r => r.addEventListener('change', calc));
    calc();
</script>
</body>
</html>