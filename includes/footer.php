<footer class="mt-5">
    <style>
        footer {
            background: #1a1a1a;
            color: #d1d1d1;
            padding: 70px 0 30px;
            border-top-left-radius: 50px;
            border-top-right-radius: 50px;
            font-family: 'Poppins', sans-serif;
        }
        .footer-title {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
        }
        .footer-link {
            color: #b0b0b0;
            text-decoration: none;
            transition: 0.3s;
            display: block;
            margin-bottom: 12px;
        }
        .footer-link:hover {
            color: #004aad;
            transform: translateX(5px);
        }
        .contact-info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .contact-icon {
            width: 45px;
            height: 45px;
            background: rgba(0, 74, 173, 0.1);
            color: #004aad;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        .social-circle {
            width: 40px;
            height: 40px;
            background: #333;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            transition: 0.3s;
            text-decoration: none;
        }
        .social-circle:hover {
            background: #004aad;
            color: white;
            transform: translateY(-3px);
        }
        hr.light {
            border-color: rgba(255,255,255,0.1);
            margin: 40px 0 20px;
        }
    </style>

    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h4 class="footer-title">Island Aura Resort</h4>
                <p class="pe-lg-5">Escape to a world of serenity and natural beauty. Island Aura offers the perfect blend of relaxation and adventure in the heart of Mati City.</p>
                <div class="mt-4">
                    <a href="#" class="social-circle"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-circle"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-circle"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="footer-link">Home</a></li>
                    <li><a href="index.php#rates" class="footer-link">Accommodations</a></li>
                    <li><a href="index.php#gallery" class="footer-link">Gallery</a></li>
                    <li><a href="reservation.php" class="footer-link">Book Now</a></li>
                </ul>
            </div>

            <div class="col-lg-5 col-md-12 ms-lg-auto">
                <h5 class="footer-title">Get In Touch</h5>
                
                <div class="contact-info-item">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <small class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Email Us</small>
                        <span class="text-white">auraislandg5@gmail.com</span>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <small class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Call Us</small>
                        <span class="text-white">0910 145 7014</span>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <small class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Visit Us</small>
                        <span class="text-white">Mati City, Davao Oriental</span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="light">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="small mb-0 opacity-50">&copy; <?= date('Y') ?> Island Aura Beach Resort. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="small mb-0 opacity-50">Designed for Comfort & Serenity</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/js/glightbox.min.js"></script>
<script>
    // Initialize GLightbox
    const lightbox = GLightbox({ selector: '.glightbox' });
</script>