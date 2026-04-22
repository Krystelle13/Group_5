<?php 
require_once 'db_connect.php'; 

// 1. HANDLE ADD PHOTO
if(isset($_POST['add_photo'])) {
    $caption = $_POST['caption']; 
    $image = $_FILES['image']['name'];
    $target = "uploads/gallery/" . basename($image);

    if (!file_exists('uploads/gallery/')) {
        mkdir('uploads/gallery/', 0777, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $sql = "INSERT INTO gallery (image_name, caption) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$image, $caption]);
        header("Location: gallery.php?success=1");
        exit();
    }
}

// 2. HANDLE EDIT PHOTO (BAGO ITO!)
if(isset($_POST['edit_photo'])) {
    $id = $_POST['id'];
    $caption = $_POST['caption'];
    
    // Check kung may bagong image na in-upload
    if(!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "uploads/gallery/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        
        $sql = "UPDATE gallery SET image_name = ?, caption = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$image, $caption, $id]);
    } else {
        // Caption lang ang i-update
        $sql = "UPDATE gallery SET caption = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$caption, $id]);
    }
    header("Location: gallery.php?updated=1");
    exit();
}

// 3. HANDLE DELETE
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: gallery.php?deleted=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Gallery | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main-content { 
            margin-left: 280px; 
            padding: 40px 30px; 
            min-height: 100vh;
        }
        .gallery-card { border: none; border-radius: 15px; background: #fff; height: 100%; transition: 0.3s; }
        .gallery-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .img-container { height: 180px; width: 100%; overflow: hidden; background: #f8f9fa; border-radius: 15px 15px 0 0; }
        .img-container img { width: 100%; height: 100%; object-fit: cover; }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h2 class="fw-bold m-0">Gallery Management</h2>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Add Photo
                </button>
            </div>

            <div class="row g-4">
                <?php 
                $stmt = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
                while($row = $stmt->fetch()): 
                ?>
                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="card gallery-card shadow-sm">
                            <div class="img-container">
                                <img src="uploads/gallery/<?= htmlspecialchars($row['image_name']) ?>">
                            </div>
                            <div class="card-body p-3">
                                <p class="small fw-bold text-truncate mb-3"><?= htmlspecialchars($row['caption']) ?></p>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            data-id="<?= $row['id'] ?>"
                                            data-caption="<?= htmlspecialchars($row['caption']) ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger w-100 rounded-pill" onclick="return confirm('Delete photo?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content border-0 shadow" method="POST" enctype="multipart/form-data">
                <div class="modal-header"><h5>Upload Photo</h5></div>
                <div class="modal-body">
                    <label class="small fw-bold">Caption</label>
                    <input type="text" name="caption" class="form-control mb-3" required>
                    <label class="small fw-bold">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="add_photo" class="btn btn-primary w-100">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content border-0 shadow" method="POST" enctype="multipart/form-data">
                <div class="modal-header"><h5>Edit Photo Details</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="small fw-bold">New Caption</label>
                        <input type="text" name="caption" id="edit_caption" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Change Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave blank if you don't want to change the photo.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="edit_photo" class="btn btn-success w-100">Update Photo</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // SCRIPT PARA IPASA ANG DATA SA EDIT MODAL
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const caption = button.getAttribute('data-caption');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_caption').value = caption;
        });
    </script>
</body>
</html>