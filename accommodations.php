<?php 
require_once 'db_connect.php'; 
session_start();
if (!isset($_SESSION['authenticated'])) { header("Location: login.php"); exit(); }

// --- DATABASE QUERIES (Anti-Error) ---
// POST NEW CONTENT
if (isset($_POST['post_room'])) {
    $img = time() . '_' . $_FILES['image']['name'];
    if(move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img)) {
        $conn->prepare("INSERT INTO rooms (room_name, room_type, price, max_pax, image) VALUES (?, ?, ?, ?, ?)")
             ->execute([$_POST['name'], $_POST['type'], $_POST['price'], $_POST['pax'], $img]);
    }
    header("Location: accommodations.php"); exit;
}

// UPDATE CONTENT
if (isset($_POST['update_room'])) {
    $id = $_POST['room_id'];
    if (!empty($_FILES['image']['name'])) {
        $img = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
        $conn->prepare("UPDATE rooms SET room_name=?, room_type=?, price=?, max_pax=?, image=? WHERE room_id=?")
             ->execute([$_POST['name'], $_POST['type'], $_POST['price'], $_POST['pax'], $img, $id]);
    } else {
        $conn->prepare("UPDATE rooms SET room_name=?, room_type=?, price=?, max_pax=? WHERE room_id=?")
             ->execute([$_POST['name'], $_POST['type'], $_POST['price'], $_POST['pax'], $id]);
    }
    header("Location: accommodations.php"); exit;
}

if (isset($_GET['del_room'])) {
    $conn->prepare("DELETE FROM rooms WHERE room_id=?")->execute([$_GET['del_room']]);
    header("Location: accommodations.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Accommodations | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main { margin-left: 280px; padding: 40px; }
        .room-card { transition: 0.3s; border: none; border-radius: 15px; overflow: hidden; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .room-card:hover { transform: translateY(-10px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .img-container { height: 200px; overflow: hidden; position: relative; }
        .img-container img { width: 100%; height: 100%; object-fit: cover; }
        .price-tag { position: absolute; top: 10px; right: 10px; background: rgba(13, 110, 253, 0.9); color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold m-0">Accommodations</h2>
                <p class="text-muted">Manage your resort's rooms and cottages</p>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="fas fa-plus me-2"></i> Add New Content
            </button>
        </div>

        <div class="row g-4">
            <?php $rooms = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC");
            while($rm = $rooms->fetch()): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="room-card">
                        <div class="img-container">
                            <img src="uploads/<?= $rm['image'] ?>">
                            <div class="price-tag">₱<?= number_format($rm['price']) ?></div>
                        </div>
                        <div class="p-3">
                            <h5 class="fw-bold mb-1"><?= $rm['room_name'] ?></h5>
                            <p class="text-muted small mb-3"><i class="fas fa-users me-1"></i> Max <?= $rm['max_pax'] ?> persons</p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-sm flex-grow-1 border" 
                                    onclick='editRoom(<?= json_encode($rm) ?>)'>
                                    <i class="fas fa-edit me-1 text-primary"></i> Edit
                                </button>
                                <a href="accommodations.php?del_room=<?= $rm['room_id'] ?>" 
                                   class="btn btn-light btn-sm border text-danger" 
                                   onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="modal fade" id="addRoomModal" tabindex="-1"><div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header border-0"><h5>Post New Accommodation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="small fw-bold">Name</label><input type="text" name="name" class="form-control mb-3" placeholder="e.g. Deluxe Room" required>
                <label class="small fw-bold">Category</label><select name="type" class="form-select mb-3"><option>Room</option><option>Cottage</option></select>
                <div class="row">
                    <div class="col"><label class="small fw-bold">Price</label><input type="number" name="price" class="form-control mb-3" required></div>
                    <div class="col"><label class="small fw-bold">Max Pax</label><input type="number" name="pax" class="form-control mb-3" required></div>
                </div>
                <label class="small fw-bold">Image</label><input type="file" name="image" class="form-control" required>
            </div>
            <div class="modal-footer border-0"><button type="submit" name="post_room" class="btn btn-primary w-100 py-2">Upload Content</button></div>
        </form>
    </div></div>

    <div class="modal fade" id="editRoomModal" tabindex="-1"><div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="room_id" id="edit_id">
            <div class="modal-header border-0"><h5>Edit Accommodation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" name="name" id="edit_name" class="form-control mb-3" required>
                <select name="type" id="edit_type" class="form-select mb-3"><option>Room</option><option>Cottage</option></select>
                <div class="row">
                    <div class="col"><input type="number" name="price" id="edit_price" class="form-control mb-3" required></div>
                    <div class="col"><input type="number" name="pax" id="edit_pax" class="form-control mb-3" required></div>
                </div>
                <label class="small text-primary">Leave empty if you don't want to change the image</label>
                <input type="file" name="image" class="form-control">
            </div>
            <div class="modal-footer border-0"><button type="submit" name="update_room" class="btn btn-success w-100 py-2">Update Changes</button></div>
        </form>
    </div></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRoom(data) {
            document.getElementById('edit_id').value = data.room_id;
            document.getElementById('edit_name').value = data.room_name;
            document.getElementById('edit_type').value = data.room_type;
            document.getElementById('edit_price').value = data.price;
            document.getElementById('edit_pax').value = data.max_pax;
            new bootstrap.Modal(document.getElementById('editRoomModal')).show();
        }
    </script>
</body>
</html>