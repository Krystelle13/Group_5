<?php 
require_once 'db_connect.php'; 
// Assumes db_connect.php has a valid $conn variable
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Island Aura | Welcome to Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/css/glightbox.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root { --primary-blue: #004aad; --accent-orange: #ff9f43; }
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; }

        .hero-section { 
            height: 80vh; 
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/view1.jpg'); 
            background-size: cover; 
            background-position: center; 
            display: flex; 
            align-items: center; 
            color: white; 
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
        }

        .section-title { font-weight: 800; color: var(--primary-blue); position: relative; padding-bottom: 15px; text-transform: uppercase; }
        .section-title::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 50px; height: 4px; background: var(--accent-orange); }

        .rate-card { border: none; border-radius: 20px; transition: 0.3s; overflow: hidden; background: white; }
        .rate-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .rate-card img { height: 230px; object-fit: cover; }

        .gallery-item { border-radius: 15px; overflow: hidden; height: 250px; position: relative; cursor: pointer; display: block; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .gallery-item:hover img { transform: scale(1.1); filter: brightness(70%); }
        .gallery-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; opacity: 0; transition: 0.3s; }
        .gallery-item:hover .gallery-overlay { opacity: 1; }

        /* Custom Modal Styling */
        .availability-badge { padding: 10px 20px; border-radius: 50px; font-weight: bold; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="hero-section text-center">
        <div class="container animate__animated animate__fadeIn">
            <h1 class="display-2 fw-bold mb-3">Island Aura Beach Resort</h1>
            <p class="fs-4 mb-5">Your dream getaway starts here in Mati City.</p>
            <a href="#rates" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">EXPLORE NOW</a>
        </div>
    </section>

    <section class="container py-5 mt-5" id="rates">
        <div class="text-center mb-5">
            <h2 class="section-title">Accommodations</h2>
        </div>
        <div class="row g-4">
            <?php 
            if(isset($conn)):
                // Grouping by room_name to show unique types and counting remaining available units
                $res = $conn->query("SELECT *, 
                    (SELECT COUNT(*) FROM rooms r2 WHERE r2.room_name = rooms.room_name AND r2.availability = 'Available') as qty_left 
                    FROM rooms GROUP BY room_name");
                
                while($r = $res->fetch()): 
                    $roomID = $r['room_id'];
                    $name = htmlspecialchars($r['room_name']);
                    $left = (int)$r['qty_left'];
            ?>
                    <div class="col-md-4">
                        <div class="card rate-card shadow-sm h-100">
                            <img src="uploads/<?= htmlspecialchars($r['image']) ?>" class="card-img-top">
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bold"><?= $name ?></h5>
                                <p class="text-muted small">Max <?= htmlspecialchars($r['max_pax']) ?> Pax | <?= htmlspecialchars($r['room_type']) ?></p>
                                <h3 class="text-primary fw-bold mb-4">₱<?= number_format($r['price']) ?></h3>
                                
                                <button type="button" class="btn btn-outline-primary rounded-pill w-100 fw-bold" 
                                        data-bs-toggle="modal" data-bs-target="#modal<?= $roomID ?>">
                                    Check Availability
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modal<?= $roomID ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0" style="border-radius: 25px;">
                                <div class="modal-header border-0 pb-0">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center p-5 pt-0">
                                    <div class="mb-4">
                                        <i class="fas fa-hotel fa-4x text-primary animate__animated animate__bounceIn"></i>
                                    </div>
                                    <h4 class="fw-bold mb-3"><?= $name ?></h4>
                                    
                                    <?php if($left > 0): ?>
                                        <div class="availability-badge bg-success text-white mb-4">
                                            <i class="fas fa-check-circle me-2"></i> ONLY <?= $left ?> UNIT(S) LEFT
                                        </div>
                                        <p class="text-muted small mb-4">Grab yours now before someone else does!</p>
                                        <a href="reservation.php" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow">BOOK THIS ROOM</a>
                                    <?php else: ?>
                                        <div class="availability-badge bg-danger text-white mb-4">
                                            <i class="fas fa-times-circle me-2"></i> CURRENTLY FULLY BOOKED
                                        </div>
                                        <p class="text-muted small mb-4">Check back soon or try another accommodation.</p>
                                        <button class="btn btn-secondary btn-lg rounded-pill w-100 fw-bold" data-bs-dismiss="modal">VIEW OTHERS</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; 
            endif; ?>
        </div>
    </section>

    <section class="bg-light py-5" id="gallery">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="section-title">Resort Gallery</h2>
                <p class="text-muted">Actual photos from our paradise.</p>
            </div>
            <div class="row g-3">
                <?php
                if(isset($conn)):
                    $gallery_res = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
                    if($gallery_res && $gallery_res->rowCount() > 0):
                        while($g = $gallery_res->fetch()): 
                            $imgName = $g['image_name'] ?? '';
                            $caption = $g['caption'] ?? '';
                            if($imgName):
                ?>
                            <div class="col-md-4 col-6">
                                <div class="gallery-wrapper bg-white shadow-sm rounded-4 overflow-hidden h-100">
                                    <a href="uploads/gallery/<?= htmlspecialchars($imgName) ?>" class="glightbox gallery-item">
                                        <img src="uploads/gallery/<?= htmlspecialchars($imgName) ?>" alt="Gallery Image">
                                        <div class="gallery-overlay">
                                            <i class="fas fa-expand fa-2x"></i>
                                        </div>
                                    </a>
                                    <?php if($caption): ?>
                                        <div class="p-3 text-center border-top">
                                            <p class="mb-0 small fw-bold text-dark text-truncate"><?= htmlspecialchars($caption) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                <?php 
                            endif;
                        endwhile;
                    else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted italic">No photos uploaded in the gallery yet.</p>
                        </div>
                <?php endif; 
                endif; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/js/glightbox.min.js"></script>
    <script>
        const lightbox = GLightbox({ selector: '.glightbox' });
        
        // Smooth scroll for anchors
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>