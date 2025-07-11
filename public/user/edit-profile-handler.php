<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: edit-profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$phone_number = $_POST['phone_number'];
$birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;

$province = $_POST['province_name'] ?? null;
$city = $_POST['city_name'] ?? null;
$district = $_POST['district_name'] ?? null;
$village = $_POST['village_name'] ?? null;
$street_address = $_POST['street_address'];
$address_note = $_POST['address_note'];

$sql_current_photo = "SELECT photo_profile FROM users WHERE id = ?";
$stmt_current_photo = mysqli_prepare($conn, $sql_current_photo);
mysqli_stmt_bind_param($stmt_current_photo, "i", $user_id);
mysqli_stmt_execute($stmt_current_photo);
$current_photo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_current_photo))['photo_profile'];
$photo_name = $current_photo;

if (isset($_FILES['photo_profile']) && $_FILES['photo_profile']['error'] == 0) {
    $upload_dir = '../uploads/profiles/';
    $file_name = time() . '_' . basename($_FILES['photo_profile']['name']);
    $target_file = $upload_dir . $file_name;
    if (move_uploaded_file($_FILES['photo_profile']['tmp_name'], $target_file)) {
        if ($current_photo != 'default.jpg' && file_exists($upload_dir . $current_photo)) {
            unlink($upload_dir . $current_photo);
        }
        $photo_name = $file_name;
    }
}

$sql_update = "UPDATE users SET name=?, phone_number=?, birth_date=?, province=?, city=?, district=?, village=?, street_address=?, address_note=?, photo_profile=? WHERE id=?";
$stmt_update = mysqli_prepare($conn, $sql_update);

mysqli_stmt_bind_param($stmt_update, "ssssssssssi", 
    $name, 
    $phone_number, 
    $birth_date, 
    $province, 
    $city, 
    $district, 
    $village, 
    $street_address, 
    $address_note, 
    $photo_name, 
    $user_id
);

if (mysqli_stmt_execute($stmt_update)) {
    $_SESSION['user_name'] = $name;
    $_SESSION['user_photo'] = $photo_name;
    header("Location: edit-profile.php?status=profile_updated");
} else {
    header("Location: edit-profile.php?status=update_failed");
}
exit;
?>
