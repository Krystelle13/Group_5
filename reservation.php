<?php 
require_once 'db_connect.php'; 
session_start(); 
use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception; 
require 'PHPMailer/Exception.php'; 
require 'PHPMailer/PHPMailer.php'; 
require 'PHPMailer/SMTP.php'; 

$set = $conn->query("SELECT * FROM settings LIMIT 1")->fetch(); 

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
            foreach($selected_items as $room_id) { 
                $sql = "INSERT INTO bookings (guest_name, guest_email, contact_no, check_in_date, room_id, status, payment_option, total_price, pax) VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, ?)"; 
                $conn->prepare($sql)->execute([$guest_name, $guest_email, $contact_no, $check_in, $room_id, $payment, $total_amount, ($adults + $children)]); 
            } 

            echo "<script> 
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ 
                        title: 'Reservation Sent!', 
                        text: 'Admin will contact you for confirmation.', 
                        icon: 'success',
                        showConfirmButton: false, 
                        allowOutsideClick: true,
                        backdrop: `rgba(0,0,123,0.4)`
                    }).then((result) => {
                        window.location.href='index.php';
                    });
                });
            </script>"; 
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <style> 
        :root { --primary: #004aad; --accent: #ff9f43; --bg: #f8fafd; } 
        body, html { height: 100%; margin: 0; overflow: hidden; background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #2d3436; } 
        .booking-wrapper { height: calc(100vh - 80px); overflow: hidden; }
        .scroll-container { height: 100%; overflow-y: auto; padding-bottom: 80px; scrollbar-width: none; }
        .scroll-container::-webkit-scrollbar { display: none; }
        .section-header { position: relative; font-size: 1.25rem; color: var(--primary); margin: 40px 0 25px; padding-left: 15px; }
        .section-header::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px; background: var(--primary); border-radius: 4px; }
        .glass-card { background: rgba(255, 255, 255, 0.9); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,74,173,0.05); }
        
        /* Item Card Styles */
        .item-card { border-radius: 18px; overflow: hidden; background: white; border: 2px solid transparent; height: 100%; transition: 0.3s; position: relative; }
        .img-box { height: 200px; overflow: hidden; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        .item-check { display: none; }
        .item-check:checked + .item-card { border-color: var(--primary); background: #f0f7ff; }
        
        /* RESTRICTED / FULLY BOOKED STYLING */
        .item-card.restricted { opacity: 0.6; filter: grayscale(0.8); cursor: not-allowed; pointer-events: none; }
        .booked-label { position: absolute; top: 10px; right: 10px; background: #eb4d4b; color: white; padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: bold; z-index: 10; }

        .total-banner { background: linear-gradient(135deg, #2d3436 0%, #000 100%); color: var(--accent); border-radius: 15px; padding: 25px; margin: 20px 0; }
    </style> 
</head> 
<body> 
<?php include 'includes/header.php'; ?> 

<div class="container-fluid booking-wrapper"> 
    <form method="POST" id="bookingForm" onsubmit="return validateForm()" class="h-100"> 
        <div class="row h-100 g-0"> 
            
            <div class="col-lg-8 scroll-container px-lg-5 py-4"> 
                <div class="glass-card p-4 mb-4"> 
                    <h5 class="fw-bold mb-3">Schedule Type</h5> 
                    <div class="d-flex gap-4"> 
                        <div class="form-check"> 
                            <input class="form-check-input tour-type" type="radio" name="tour_type" id="daytour" value="day" checked> 
                            <label class="form-check-label" for="daytour">Daytour Entrance</label> 
                        </div> 
                        <div class="form-check"> 
                            <input class="form-check-input tour-type" type="radio" name="tour_type" id="overnight" value="night"> 
                            <label class="form-check-label" for="overnight">Overnight Entrance</label> 
                        </div> 
                    </div> 
                </div> 

                <h4 class="section-header fw-bold">ACCOMMODATIONS</h4> 
                <div class="row g-4 mb-5"> 
                    <?php 
                    $rooms = $conn->query("SELECT * FROM rooms WHERE (room_type LIKE '%Room%' OR room_type LIKE '%Suite%')"); 
                    while($r = $rooms->fetch()): 
                        // Logic for restriction
                        $isRestricted = (strpos($r['room_name'], 'Kubo') !== false || $r['availability'] != 'Available');
                    ?> 
                    <div class="col-md-6 col-xl-4"> 
                        <input type="checkbox" name="items[]" value="<?= $r['room_id']; ?>" 
                               data-name="<?= $r['room_name']; ?>" data-price="<?= $r['price']; ?>" 
                               class="item-check" id="i_<?= $r['room_id']; ?>" 
                               <?= $isRestricted ? 'disabled' : '' ?>> 
                        
                        <div class="item-card shadow-sm <?= $isRestricted ? 'restricted' : '' ?>"> 
                            <?php if($isRestricted): ?>
                                <div class="booked-label">FULLY BOOKED</div>
                            <?php endif; ?>
                            <label for="i_<?= $r['room_id']; ?>" class="w-100 h-100" style="cursor:<?= $isRestricted ? 'default' : 'pointer' ?>;"> 
                                <div class="img-box"><img src="uploads/<?= $r['image']; ?>"></div> 
                                <div class="p-4"> 
                                    <h6 class="fw-bold mb-2"><?= $r['room_name']; ?></h6> 
                                    <h5 class="text-primary fw-bold">₱<?= number_format($r['price']); ?></h5> 
                                </div> 
                            </label> 
                        </div> 
                    </div> 
                    <?php endwhile; ?> 
                </div> 

                <h4 class="section-header fw-bold">COTTAGES & GAZEBOS</h4> 
                <div class="row g-4 mb-5"> 
                    <?php 
                    $cottages = $conn->query("SELECT * FROM rooms WHERE (room_type LIKE '%Cottage%' OR room_type LIKE '%Gazebo%')"); 
                    while($c = $cottages->fetch()): 
                        // Restriction: Only Mushroom Cottage is clickable. The rest are restricted.
                        $isRestricted = (strpos($c['room_name'], 'Mushroom') === false || $c['availability'] != 'Available');
                    ?> 
                    <div class="col-md-6 col-xl-4"> 
                        <input type="checkbox" name="items[]" value="<?= $c['room_id']; ?>" 
                               data-name="<?= $c['room_name']; ?>" data-price="<?= $c['price']; ?>" 
                               class="item-check" id="i_<?= $c['room_id']; ?>"
                               <?= $isRestricted ? 'disabled' : '' ?>> 
                        
                        <div class="item-card shadow-sm <?= $isRestricted ? 'restricted' : '' ?>"> 
                            <?php if($isRestricted): ?>
                                <div class="booked-label">UNAVAILABLE</div>
                            <?php endif; ?>
                            <label for="i_<?= $c['room_id']; ?>" class="w-100 h-100" style="cursor:<?= $isRestricted ? 'default' : 'pointer' ?>;"> 
                                <div class="img-box"><img src="uploads/<?= $c['image']; ?>"></div> 
                                <div class="p-4"> 
                                    <h6 class="fw-bold mb-2"><?= $c['room_name']; ?></h6> 
                                    <h5 class="text-primary fw-bold">₱<?= number_format($c['price']); ?></h5> 
                                </div> 
                            </label> 
                        </div> 
                    </div> 
                    <?php endwhile; ?> 
                </div> 

                <div class="glass-card p-4 mb-5"> 
                    <h5 class="fw-bold mb-4">Guest Count & Entrance</h5> 
                    <div class="row g-3"> 
                        <div class="col-md-6"> 
                            <label class="small fw-bold">ADULTS</label> 
                            <input type="number" name="adults" id="adult_count" class="form-control" value="0" min="0"> 
                            <p class="mt-2 small text-primary fw-bold">Rate: ₱<span id="adult_rate_display">0</span></p>
                        </div> 
                        <div class="col-md-6"> 
                            <label class="small fw-bold">CHILDREN</label> 
                            <input type="number" name="children" id="child_count" class="form-control" value="0" min="0"> 
                            <p class="mt-2 small text-primary fw-bold">Rate: ₱<span id="child_rate_display">0</span></p>
                        </div> 
                    </div> 
                </div> 
            </div> 

            <div class="col-lg-4 scroll-container bg-white border-start px-4 py-4"> 
                <h4 class="fw-bold mb-4 text-center">Booking Summary</h4>
                <div class="mb-3"><input type="text" name="name" id="name" class="form-control" placeholder="Full Name"></div>
                <div class="mb-3"><input type="email" name="email" id="email" class="form-control" placeholder="Email Address"></div>
                <div class="mb-3"><input type="text" name="contact" id="contact" class="form-control" placeholder="Contact Number"></div>
                <div class="mb-3">
                    <label class="small fw-bold">Target Check-in</label>
                    <input type="date" name="check_in" id="check_in" class="form-control" min="<?= $today ?>"> 
                </div>
                <div class="mb-4">
                    <select name="payment_method" class="form-select"> 
                        <option value="GCash">GCash</option> 
                        <option value="Walk-in">Walk-in</option> 
                    </select> 
                </div>

                <div class="total-banner"> 
                    <p class="text-uppercase mb-1 small fw-bold">Grand Total</p> 
                    <h2 class="fw-bold m-0" id="displayTotal">₱0.00</h2> 
                    <input type="hidden" name="total_amount_val" id="total_val" value="0"> 
                </div> 
                
                <button type="submit" name="confirm_booking" id="confirmBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">
                    CONFIRM RESERVATION
                </button> 
            </div> 
        </div> 
    </form> 
</div> 

<script> 
    const adultIn = document.getElementById('adult_count'); 
    const childIn = document.getElementById('child_count'); 

    function validateForm() { 
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const contact = document.getElementById('contact').value;
        const date = document.getElementById('check_in').value;
        const adults = parseInt(adultIn.value) || 0;
        const children = parseInt(childIn.value) || 0;

        if(!name || !email || !contact || !date) {
            Swal.fire({ icon: 'warning', title: 'Incomplete Form', text: 'Please fill out all personal details.' });
            return false;
        }

        let anyChecked = false;
        document.querySelectorAll('.item-check:checked').forEach(c => { anyChecked = true; });
        if(!anyChecked) {
            Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select an available accommodation.' });
            return false;
        }

        if((adults + children) <= 0) {
            Swal.fire({ icon: 'warning', title: 'Guest Count', text: 'Please enter number of guests.' });
            return false;
        }

        document.getElementById('confirmBtn').innerText = 'Please wait...';
        return true; 
    } 

    function calc() { 
        let total = 0; 
        let isNight = document.getElementById('overnight').checked; 
        let ar = isNight ? rates.night_adult : rates.day_adult; 
        let cr = isNight ? rates.night_child : rates.day_child; 

        document.getElementById('adult_rate_display').innerText = ar.toLocaleString();
        document.getElementById('child_rate_display').innerText = cr.toLocaleString();
        
        document.querySelectorAll('.item-check:checked').forEach(c => { 
            total += parseFloat(c.getAttribute('data-price')); 
        }); 

        total += (parseInt(adultIn.value) || 0) * ar;
        total += (parseInt(childIn.value) || 0) * cr;

        document.getElementById('displayTotal').innerText = '₱' + total.toLocaleString(); 
        document.getElementById('total_val').value = total; 
    } 

    [adultIn, childIn].forEach(el => el.addEventListener('input', calc)); 
    document.querySelectorAll('.item-check').forEach(c => c.addEventListener('change', calc)); 
    document.querySelectorAll('.tour-type').forEach(r => r.addEventListener('change', calc)); 
    calc(); 
</script> 
</body> 
</html>