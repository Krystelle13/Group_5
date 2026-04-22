<?php 
require_once 'db_connect.php'; 
session_start(); 
use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception; 
require 'PHPMailer/Exception.php'; 
require 'PHPMailer/PHPMailer.php'; 
require 'PHPMailer/SMTP.php'; 

// Fetch settings for different rates
$set = $conn->query("SELECT * FROM settings LIMIT 1")->fetch(); 

// Pass separate rates to JavaScript
echo "<script> 
    const rates = { 
        day_adult: " . ($set['day_adult_entrance'] ?? 100) . ", 
        day_child: " . ($set['day_child_entrance'] ?? 50) . ", 
        night_adult: " . ($set['night_adult_entrance'] ?? 150) . ", 
        night_child: " . ($set['night_child_entrance'] ?? 75) . " 
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
                $sql = "INSERT INTO bookings (guest_name, guest_email, contact_no, check_in_date, room_id, status, payment_option, total_price, pax) VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, ?)"; 
                $conn->prepare($sql)->execute([$guest_name, $guest_email, $contact_no, $check_in, $room_id, $payment, $total_amount, $total_pax]); 
                $item_details_html .= " <div style='border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:10px;'> <b>Item:</b> {$room_info['room_name']}<br> <b>Price:</b> ₱".number_format($room_info['price'])." </div>"; 
            } 

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
            $mail->Body = " <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; border: 1px solid #eee; padding: 20px;'> <h2 style='color: #004aad;'>Hello $guest_name!</h2> <p>Your reservation request for <b>$check_in</b> is now being processed.</p> <hr style='border: 0; border-top: 1px solid #eee;'> <h4>Entrance & Fees:</h4> <ul style='list-style: none; padding: 0;'> <li><b>Adults:</b> $adults</li> <li><b>Children:</b> $children</li> <li><b>Payment Method:</b> $payment</li> </ul> <div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px;'> <h3 style='margin: 0; color: #004aad;'>Total Amount: ₱" . number_format($total_amount, 2) . "</h3> </div> </div>"; 
            $mail->send(); 

            echo "<script> Swal.fire({ title: 'SUCCESS!', text: 'Reservation sent!', icon: 'success' }).then(() => { window.location.href='index.php'; }); </script>"; 
        } catch (Exception $e) { echo "Error: " . $e->getMessage(); } 
    } 
} 
?> 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8">
    <title>Book Your Stay | Island Aura</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <style> 
        :root { --primary: #004aad; --accent: #ff9f43; --bg: #f8fafd; } 
        body, html { height: 100%; margin: 0; overflow: hidden; background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #2d3436; } 
        .booking-wrapper { height: calc(100vh - 80px); overflow: hidden; }
        .scroll-container { height: 100%; overflow-y: auto; padding-bottom: 80px; scrollbar-width: none; }
        .scroll-container::-webkit-scrollbar { display: none; }
        .section-header { position: relative; font-size: 1.25rem; letter-spacing: 1px; color: var(--primary); margin: 40px 0 25px; padding-left: 15px; }
        .section-header::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px; background: var(--primary); border-radius: 4px; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,74,173,0.05); }
        .item-card { cursor: pointer; border-radius: 18px; overflow: hidden; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); background: white; border: 2px solid transparent; height: 100%; }
        .item-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-color: rgba(0,74,173,0.1); }
        .img-box { height: 200px; overflow: hidden; position: relative; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s; }
        .item-check { display: none; }
        .item-check:checked + .item-card { border-color: var(--primary); background: #f0f7ff; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #e1e8ef; transition: 0.3s; background: #fcfdfe; }
        .total-banner { background: linear-gradient(135deg, #2d3436 0%, #000 100%); color: var(--accent); border-radius: 15px; padding: 25px; margin: 20px 0; position: relative; overflow: hidden; }
        .view-btn { position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.9); border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; color: var(--primary); z-index: 5; }
    </style> 
</head> 
<body> 
<?php include 'includes/header.php'; ?> 

<div class="container-fluid booking-wrapper"> 
    <form method="POST" id="bookingForm" onsubmit="return validateForm()" class="h-100"> 
        <div class="row h-100 g-0"> 
            
            <div class="col-lg-8 scroll-container px-lg-5 py-4"> 
                <div class="glass-card p-4 mb-4"> 
                    <h5 class="fw-bold mb-3"><i class="fa-regular fa-clock me-2 text-primary"></i>Schedule Type</h5> 
                    <div class="d-flex gap-4"> 
                        <div class="form-check custom-radio"> 
                            <input class="form-check-input tour-type" type="radio" name="tour_type" id="daytour" value="day" checked> 
                            <label class="form-check-label fw-600" for="daytour">Daytour Entrance</label> 
                        </div> 
                        <div class="form-check custom-radio"> 
                            <input class="form-check-input tour-type" type="radio" name="tour_type" id="overnight" value="night"> 
                            <label class="form-check-label fw-600" for="overnight">Overnight Entrance</label> 
                        </div> 
                    </div> 
                </div> 

                <h4 class="section-header fw-bold">ACCOMMODATIONS</h4> 
                <div class="row g-4 mb-5"> 
                    <?php $rooms = $conn->query("SELECT * FROM rooms WHERE availability='Available' AND (room_type LIKE '%Room%' OR room_type LIKE '%Suite%')"); 
                    while($r = $rooms->fetch()): ?> 
                    <div class="col-md-6 col-xl-4"> 
                        <input type="checkbox" name="items[]" value="<?= $r['room_id']; ?>" data-name="<?= $r['room_name']; ?>" data-price="<?= $r['price']; ?>" class="item-check" id="i_<?= $r['room_id']; ?>"> 
                        <div class="item-card shadow-sm"> 
                            <a href="uploads/<?= $r['image']; ?>" class="glightbox view-btn"><i class="fa fa-expand"></i></a> 
                            <label for="i_<?= $r['room_id']; ?>" class="w-100 h-100" style="cursor:pointer;"> 
                                <div class="img-box"><img src="uploads/<?= $r['image']; ?>"></div> 
                                <div class="p-4"> 
                                    <h6 class="fw-bold text-dark mb-2"><?= $r['room_name']; ?></h6> 
                                    <h5 class="text-primary fw-bold mb-0">₱<?= number_format($r['price']); ?></h5> 
                                </div> 
                            </label> 
                        </div> 
                    </div> 
                    <?php endwhile; ?> 
                </div> 

                <h4 class="section-header fw-bold">COTTAGES & GAZEBOS</h4> 
                <div class="row g-4 mb-5"> 
                    <?php $cottages = $conn->query("SELECT * FROM rooms WHERE availability='Available' AND (room_type LIKE '%Cottage%' OR room_type LIKE '%Gazebo%')"); 
                    while($c = $cottages->fetch()): ?> 
                    <div class="col-md-6 col-xl-4"> 
                        <input type="checkbox" name="items[]" value="<?= $c['room_id']; ?>" data-name="<?= $c['room_name']; ?>" data-price="<?= $c['price']; ?>" class="item-check" id="i_<?= $c['room_id']; ?>"> 
                        <div class="item-card shadow-sm"> 
                            <a href="uploads/<?= $c['image']; ?>" class="glightbox view-btn"><i class="fa fa-expand"></i></a> 
                            <label for="i_<?= $c['room_id']; ?>" class="w-100 h-100" style="cursor:pointer;"> 
                                <div class="img-box"><img src="uploads/<?= $c['image']; ?>"></div> 
                                <div class="p-4"> 
                                    <h6 class="fw-bold text-dark mb-2"><?= $c['room_name']; ?></h6> 
                                    <h5 class="text-primary fw-bold mb-0">₱<?= number_format($c['price']); ?></h5> 
                                </div> 
                            </label> 
                        </div> 
                    </div> 
                    <?php endwhile; ?> 
                </div> 

                <div class="glass-card p-4 mb-5"> 
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-users me-2 text-primary"></i>Guest Count & Entrance</h5> 
                    <div class="row g-3"> 
                        <div class="col-md-6"> 
                            <label class="small fw-bold text-muted mb-2 text-uppercase">Adults</label> 
                            <input type="number" name="adults" id="adult_count" class="form-control" value="0" min="0"> 
                            <p class="mt-2 small text-primary fw-bold">Rate: ₱<span id="adult_rate_display">0</span></p>
                        </div> 
                        <div class="col-md-6"> 
                            <label class="small fw-bold text-muted mb-2 text-uppercase">Children</label> 
                            <input type="number" name="children" id="child_count" class="form-control" value="0" min="0"> 
                            <p class="mt-2 small text-primary fw-bold">Rate: ₱<span id="child_rate_display">0</span></p>
                        </div> 
                    </div> 
                </div> 
            </div> 

            <div class="col-lg-4 scroll-container bg-white border-start px-4 py-4"> 
                <div class="p-2"> 
                    <h4 class="fw-bold mb-4 text-center">Booking Summary</h4>
                    <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
                    <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email Address" required></div>
                    <div class="mb-3"><input type="text" name="contact" class="form-control" placeholder="Contact Number" required></div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Target Check-in</label>
                        <input type="date" name="check_in" class="form-control" min="<?= $today ?>" required> 
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-1">Payment via</label>
                        <select name="payment_method" class="form-select" required> 
                            <option value="GCash">GCash</option> 
                            <option value="Walk-in">Walk-in</option> 
                        </select> 
                    </div>

                    <div id="selection-summary" class="mb-3 small text-muted border-top pt-3"></div>

                    <div class="total-banner shadow-sm"> 
                        <p class="text-uppercase mb-1 small fw-bold" style="letter-spacing: 2px; opacity: 0.8;">Grand Total</p> 
                        <h2 class="fw-bold m-0" id="displayTotal">₱0.00</h2> 
                        <input type="hidden" name="total_amount_val" id="total_val" value="0"> 
                    </div> 
                    
                    <button type="submit" name="confirm_booking" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                        CONFIRM RESERVATION <i class="fa fa-arrow-right ms-2"></i>
                    </button> 
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
    const summaryDiv = document.getElementById('selection-summary');

    function validateForm() { 
        const adults = parseInt(adultIn.value) || 0; 
        const children = parseInt(childIn.value) || 0; 
        if ((adults + children) <= 0) { 
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please add at least one guest.' }); 
            return false; 
        } 
        return true; 
    } 

    function calc() { 
        let total = 0; 
        let summaryHtml = '<p class="fw-bold mb-2 text-dark">Billing Breakdown:</p>';
        
        let isNight = document.getElementById('overnight').checked; 
        let currentAdultRate = isNight ? rates.night_adult : rates.day_adult; 
        let currentChildRate = isNight ? rates.night_child : rates.day_child; 

        // Update visual labels in the guest section
        document.getElementById('adult_rate_display').innerText = currentAdultRate.toLocaleString();
        document.getElementById('child_rate_display').innerText = currentChildRate.toLocaleString();
        
        // 1. Items (Accommodations & Cottages)
        checks.forEach(c => { 
            if(c.checked) {
                let price = parseFloat(c.getAttribute('data-price'));
                total += price; 
                summaryHtml += `<div class="d-flex justify-content-between"><span>${c.getAttribute('data-name')}</span><span>₱${price.toLocaleString()}</span></div>`;
            }
        }); 

        // 2. Adult Calculation
        let adultQty = parseInt(adultIn.value) || 0;
        if(adultQty > 0) {
            let adultTotal = adultQty * currentAdultRate;
            total += adultTotal;
            summaryHtml += `<div class="d-flex justify-content-between"><span>Adult Entrance (x${adultQty})</span><span>₱${adultTotal.toLocaleString()}</span></div>`;
        }

        // 3. Child Calculation
        let childQty = parseInt(childIn.value) || 0;
        if(childQty > 0) {
            let childTotal = childQty * currentChildRate;
            total += childTotal;
            summaryHtml += `<div class="d-flex justify-content-between"><span>Child Entrance (x${childQty})</span><span>₱${childTotal.toLocaleString()}</span></div>`;
        }

        summaryDiv.innerHTML = total > 0 ? summaryHtml : "";
        document.getElementById('displayTotal').innerText = '₱' + total.toLocaleString(undefined, {minimumFractionDigits: 2}); 
        document.getElementById('total_val').value = total; 
    } 

    [adultIn, childIn].forEach(el => el.addEventListener('input', calc)); 
    checks.forEach(c => c.addEventListener('change', calc)); 
    tourRadios.forEach(r => r.addEventListener('change', calc)); 
    calc(); 
</script> 
</body> 
</html>