<?php
require('inc/links.php');

$hostData = hostLogin();
$hostId = $_SESSION['uId'];

$totalRooms = mysqli_fetch_assoc(select(
    "SELECT COUNT(*) AS total FROM `rooms` WHERE `host_id`=? AND `removed`=0",
    [$hostId],
    'i'
));

$activeRooms = mysqli_fetch_assoc(select(
    "SELECT COUNT(*) AS total FROM `rooms` WHERE `host_id`=? AND `removed`=0 AND `status`=1 AND `approval_status`='approved'",
    [$hostId],
    'i'
));

$totalBookings = mysqli_fetch_assoc(select(
    "SELECT COUNT(*) AS total FROM `booking_order` bo INNER JOIN `rooms` r ON bo.room_id = r.id WHERE r.host_id = ?",
    [$hostId],
    'i'
));

$pendingBookings = mysqli_fetch_assoc(select(
    "SELECT COUNT(*) AS total FROM `booking_order` bo INNER JOIN `rooms` r ON bo.room_id = r.id WHERE r.host_id = ? AND bo.booking_status = 'pending'",
    [$hostId],
    'i'
));

$confirmedBookings = mysqli_fetch_assoc(select(
    "SELECT COUNT(*) AS total FROM `booking_order` bo INNER JOIN `rooms` r ON bo.room_id = r.id WHERE r.host_id = ? AND bo.booking_status = 'booked'",
    [$hostId],
    'i'
));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang nhà cung cấp - Tổng quan</title>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">Xin chào <?php echo htmlspecialchars($hostData['name']); ?>!</h3>
                <p class="text-muted">Quản lý chỗ ở, theo dõi yêu cầu đặt phòng và cập nhật thông tin dễ dàng.</p>

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Tổng số chỗ ở</h5>
                                <p class="display-6 fw-bold"><?php echo (int)$totalRooms['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Đang mở bán</h5>
                                <p class="display-6 fw-bold text-success"><?php echo (int)$activeRooms['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Lượt đặt phòng</h5>
                                <p class="display-6 fw-bold"><?php echo (int)$totalBookings['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Đang chờ duyệt</h5>
                                <p class="display-6 fw-bold text-warning"><?php echo (int)$pendingBookings['total']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Đặt phòng đã xác nhận</h5>
                                <p class="h1 text-primary"><?php echo (int)$confirmedBookings['total']; ?></p>
                                <p class="text-muted mb-0">Bao gồm các lượt đặt phòng đã xác nhận và đang chờ khách đến.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Gợi ý hành động</h5>
                                <ul class="mb-0 small">
                                    <li>Kiểm tra các yêu cầu đặt chỗ mới để xác nhận kịp thời.</li>
                                    <li>Cập nhật mô tả và tiện ích của chỗ ở để thu hút khách.</li>
                                    <li>Theo dõi đánh giá của khách hàng trong trang lịch sử đặt phòng.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>
</body>

</html>
