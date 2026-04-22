<?php 
require_once 'db_connect.php'; 
session_start();
if (!isset($_SESSION['authenticated'])) { header("Location: login.php"); exit(); }

$set = $conn->query("SELECT * FROM settings LIMIT 1")->fetch();

if (isset($_POST['update_settings'])) {
    $logo = (!empty($_FILES['new_logo']['name'])) ? "logo_".time()."_".$_FILES['new_logo']['name'] : $set['hotel_logo'];
    if(!empty($_FILES['new_logo']['name'])) move_uploaded_file($_FILES['new_logo']['tmp_name'], "uploads/".$logo);
    
    $sql = "UPDATE settings SET day_entrance=?, night_entrance=?, pool_fee=?, hotel_logo=? WHERE id=?";
    $conn->prepare($sql)->execute([$_POST['day_ent'], $_POST['night_ent'], $_POST['pool'], $logo, $set['id']]);
    header("Location: settings.php?msg=success"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Settings | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main { margin-left: 260px; padding: 30px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <h2 class="fw-bold mb-4">System Settings</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card p-4 text-center">
                        <h5>Resort Logo</h5><hr>
                        <img src="uploads/<?= $set['hotel_logo'] ?>" width="100" class="mb-3 border p-2">
                        <input type="file" name="new_logo" class="form-control">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card p-4">
                        <h5 class="fw-bold text-primary">Resort Rates</h5><hr>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-bold small">Daytour Entrance (8AM-5PM)</label>
                                <input type="number" name="day_ent" class="form-control" value="<?= $set['day_entrance'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold small">Overnight Entrance (6PM-7AM)</label>
                                <input type="number" name="night_ent" class="form-control" value="<?= $set['night_entrance'] ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="fw-bold small">Pool Fee (Fixed Rate)</label>
                                <input type="number" name="pool" class="form-control" value="<?= $set['pool_fee'] ?>">
                            </div>
                        </div>
                        <button type="submit" name="update_settings" class="btn btn-primary w-100 mt-4 fw-bold">SAVE SETTINGS</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>