<?php
require('inc/links.php');

$hostData = hostLogin();
$hostId = $_SESSION['uId'];
$message = '';
$message_type = 'success';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])){
    $frm = filteration($_POST);
    $bookingId = (int)$frm['booking_id'];
    $action = $frm['action'] ?? '';

    if($action === 'confirm'){
        $room_no = trim($frm['room_no'] ?? '');
        if($room_no === ''){
            $message = 'Vui lòng nhập số phòng trước khi xác nhận.';
            $message_type = 'error';
        } else {
            $update = update(
                "UPDATE `booking_order` bo
                 INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
                 INNER JOIN `rooms` r ON bo.room_id = r.id
                 SET bo.arrival = 1, bo.rate_review = 0, bd.room_no = ?, bo.booking_status = 'booked'
                 WHERE bo.booking_id = ? AND r.host_id = ?",
                [$room_no, $bookingId, $hostId],
                'sii'
            );
            if($update){
                $message = 'Đã xác nhận đặt phòng thành công!';
            } else {
                $message = 'Không thể cập nhật đặt phòng. Vui lòng thử lại!';
                $message_type = 'error';
            }
        }
    }
    elseif($action === 'cancel'){
        $update = update(
            "UPDATE `booking_order` bo
             INNER JOIN `rooms` r ON bo.room_id = r.id
             SET bo.booking_status = 'cancelled', bo.refund = 0
             WHERE bo.booking_id = ? AND r.host_id = ?",
            [$bookingId, $hostId],
            'ii'
        );
        if($update){
            $message = 'Đã huỷ yêu cầu đặt phòng!';
        } else {
            $message = 'Huỷ đặt phòng thất bại!';
            $message_type = 'error';
        }
    }
}

$bookings = select(
    "SELECT bo.*, bd.*, r.name AS room_name
     FROM `booking_order` bo
     INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
     INNER JOIN `rooms` r ON bo.room_id = r.id
     WHERE r.host_id = ?
     ORDER BY bo.datentime DESC",
    [$hostId],
    'i'
);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang nhà cung cấp - Đặt phòng</title>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Yêu cầu đặt chỗ</h3>
                </div>

                <?php if($message !== ''): ?>
                    <div class="alert <?php echo ($message_type === 'success') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                        <strong><?php echo htmlspecialchars($message); ?></strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Khách hàng</th>
                                        <th>Thông tin đặt</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                  $index = 1;
                  while($row = mysqli_fetch_assoc($bookings)){
                    $orderId = htmlspecialchars($row['order_id']);
                    $guest = htmlspecialchars($row['user_name']);
                    $phone = htmlspecialchars($row['phonenum']);
                    $email = htmlspecialchars($row['email']);
                    $roomName = htmlspecialchars($row['room_name']);
                    $checkin = date('d/m/Y', strtotime($row['check_in']));
                    $checkout = date('d/m/Y', strtotime($row['check_out']));
                    $status = htmlspecialchars($row['booking_status']);
                    $created = date('d/m/Y H:i', strtotime($row['datentime']));
                    $roomNo = htmlspecialchars($row['room_no'] ?? '');

                    $statusBadge = '<span class="badge bg-secondary">'.$status.'</span>';
                    if($row['booking_status'] === 'pending'){
                      $statusBadge = '<span class="badge bg-warning text-dark">Đang chờ</span>';
                    }
                    elseif($row['booking_status'] === 'booked'){
                      $statusBadge = '<span class="badge bg-success">Đã xác nhận</span>';
                    }
                    elseif($row['booking_status'] === 'cancelled'){
                      $statusBadge = '<span class="badge bg-danger">Đã huỷ</span>';
                    }

                    echo "<tr>";
                    echo "<td>{$index}</td>";
                    echo "<td><div class='fw-bold'>Khách: {$guest}</div><div class='small text-muted'>SĐT: {$phone}</div><div class='small text-muted'>Email: {$email}</div></td>";
                    echo "<td>";
                    echo "<div class='fw-bold'>Phòng: {$roomName}</div>";
                    echo "<div class='small text-muted'>Mã đơn: {$orderId}</div>";
                    echo "<div class='small'>Nhận: {$checkin} - Trả: {$checkout}</div>";
                    echo "<div class='small text-muted'>Tạo lúc: {$created}</div>";
                    if($roomNo){
                      echo "<div class='small text-muted'>Phòng số: {$roomNo}</div>";
                    }
                    echo "</td>";
                    echo "<td>{$statusBadge}</td>";
                    echo "<td>";
                    if($row['booking_status'] === 'pending'){
                      echo "<form method='POST' class='d-flex flex-column gap-2'>";
                      echo "<input type='hidden' name='booking_id' value='{$row['booking_id']}'>";
                      echo "<input type='hidden' name='action' value='confirm'>";
                      echo "<input type='text' name='room_no' class='form-control form-control-sm' placeholder='Số phòng' required>";
                      echo "<button type='submit' class='btn btn-sm btn-success'>Xác nhận</button>";
                      echo "</form>";
                      echo "<form method='POST' class='mt-2'>";
                      echo "<input type='hidden' name='booking_id' value='{$row['booking_id']}'>";
                      echo "<input type='hidden' name='action' value='cancel'>";
                      echo "<button type='submit' class='btn btn-sm btn-outline-danger'>Huỷ yêu cầu</button>";
                      echo "</form>";
                    } else {
                      echo "<span class='text-muted small'>Không có thao tác</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                    $index++;
                  }

                  if($index === 1){
                    echo "<tr><td colspan='5' class='text-center text-muted py-4'>Chưa có yêu cầu đặt chỗ nào.</td></tr>";
                  }
                  ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>
</body>

</html>
