<div class="sidebar">
    <div class="p-4 text-center border-bottom border-secondary">
        <h4 class="fw-bold m-0 text-white">ISLAND AURA</h4>
        <small class="text-secondary">Admin Management</small>
    </div>
    <div class="nav flex-column mt-3">
        <a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Dashboard</a>
        <a href="bookings.php" class="nav-link"><i class="fas fa-calendar-alt me-2"></i> Bookings</a>
        <a href="accommodations.php" class="nav-link"><i class="fas fa-bed me-2"></i> Accommodations</a>
        <a href="gallery.php" class="nav-link"><i class="fas fa-image me-2"></i> Gallery</a>
        <a href="settings.php" class="nav-link"><i class="fas fa-cog me-2"></i> Settings</a>
        <a href="#" onclick="confirmLogout()" class="nav-link text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
</div>

<style>
    :root { --blue: #0d6efd; --orange: #fd7e14; }
    body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .sidebar { width: 280px; height: 100vh; position: fixed; background: #212529; color: white; }
    .nav-link { color: #adb5bd; padding: 15px 25px; border-left: 4px solid transparent; text-decoration: none; display: block; transition: 0.3s; }
    .nav-link:hover, .nav-link.active { color: white; background: #343a40; border-left: 4px solid var(--orange); }
    .main { margin-left: 280px; padding: 40px; }
    .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
</style>

<script>
    function confirmLogout() { if(confirm("Are you sure you want to logout?")) { window.location.href = "logout.php"; } }
</script>