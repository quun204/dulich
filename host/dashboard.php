<?php
require('inc/essentials.php');
require('../admin/inc/db_config.php');
hostLogin();

$hostId = $_SESSION['uId'];

$total_rooms = 0;
$active_rooms = 0;
$pending_requests = 0;

$room_query = select("SELECT COUNT(*) AS total, SUM(status = 1) AS active FROM rooms WHERE removed = 0 AND host_id = ?", [$hostId], 'i');
if ($room_query) {
    $room_stats = mysqli_fetch_assoc($room_query);
    $total_rooms = (int)($room_stats['total'] ?? 0);
    $active_rooms = (int)($room_stats['active'] ?? 0);
}

$request_query = select("SELECT COUNT(*) AS pending FROM bookings WHERE status = 'pending' AND room_id IN (SELECT id FROM rooms WHERE host_id = ?)", [$hostId], 'i');
if ($request_query) {
    $row = mysqli_fetch_assoc($request_query);
    $pending_requests = (int)($row['pending'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Khu vực Host - Tổng quan</title>
  <?php require('inc/links.php'); ?>
</head>
<body>
  <?php require('inc/header.php'); ?>
  <main class="container my-5">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Tổng số chỗ ở</h5>
            <p class="display-6 fw-bold text-primary mb-0"><?php echo $total_rooms; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Đang hiển thị</h5>
            <p class="display-6 fw-bold text-success mb-0"><?php echo $active_rooms; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Yêu cầu đặt chỗ chờ duyệt</h5>
            <p class="display-6 fw-bold text-warning mb-0"><?php echo $pending_requests; ?></p>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
      <div class="card-body">
        <h5 class="card-title">Chào mừng bạn trở lại, <?php echo $_SESSION['uName']; ?>!</h5>
        <p class="card-text mb-0">Bắt đầu bằng việc thêm chỗ ở mới hoặc quản lý các chỗ ở hiện tại của bạn trong mục "Chỗ ở của tôi".</p>
      </div>
    </div>
  </main>
  <?php require('inc/footer.php'); ?>
  <?php require('inc/scripts.php'); ?>
</body>
</html>
