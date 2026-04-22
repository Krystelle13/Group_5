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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* In-enhance na Logout Container */
    .logout-container {
        padding: 15px;
        margin: 20px 15px;
        border-radius: 12px;
        background: rgba(255, 71, 87, 0.05); /* Very light red tint */
        border: 1px solid rgba(255, 71, 87, 0.2);
        transition: all 0.3s ease;
    }

    .logout-container:hover {
        background: rgba(255, 71, 87, 0.1);
        border-color: rgba(255, 71, 87, 0.4);
    }

    .btn-logout {
        color: #ff4757;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
        cursor: pointer;
        background: none;
        border: none;
        width: 100%;
        gap: 10px;
    }

    .btn-logout i {
        font-size: 18px;
    }

    .btn-logout:hover {
        color: #eb4b4b;
        letter-spacing: 0.5px;
    }

    /* Custom SweetAlert Design para bumagay sa Island Aura */
    .aura-popup {
        border-radius: 20px !important;
    }
    .aura-confirm {
        padding: 10px 25px !important;
    }
    .aura-cancel {
        padding: 10px 25px !important;
    }
</style>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Sign Out?',
        text: "Are you sure you want to leave Island Aura Admin Management?",
        icon: 'warning',
        iconColor: '#ff4757',
        showCancelButton: true,
        confirmButtonColor: '#004aad', // Resort Primary Blue
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Sign Out',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        background: '#ffffff',
        backdrop: `rgba(0, 0, 0, 0.4)`,
        customClass: {
            popup: 'aura-popup shadow-lg',
            title: 'fw-bold',
            confirmButton: 'aura-confirm rounded-pill',
            cancelButton: 'aura-cancel rounded-pill'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Loading effect bago mag-redirect
            Swal.fire({
                title: 'Signing out...',
                timer: 800,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading() }
            }).then(() => {
                window.location.href = 'logout.php';
            });
        }
    })
}
</script>