<?php
require('inc/links.php');

$hostData = hostLogin();
$hostId = $_SESSION['uId'];
$message = '';
$message_type = 'success';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'])){
    $frm = filteration($_POST);
    $roomId = (int)$frm['room_id'];
    $desiredStatus = isset($frm['status']) ? (int)$frm['status'] : 0;

    $update = update(
        "UPDATE `rooms` SET `status`=? WHERE `id`=? AND `host_id`=?",
        [$desiredStatus, $roomId, $hostId],
        'iii'
    );

    if($update){
        $message = 'Đã cập nhật trạng thái phòng thành công!';
    } else {
        $message = 'Cập nhật trạng thái thất bại!';
        $message_type = 'error';
    }
}

$rooms = select(
    "SELECT * FROM `rooms` WHERE `host_id`=? AND `removed`=0 ORDER BY `id` DESC",
    [$hostId],
    'i'
);

$features_map = [];
$facilities_map = [];

$feature_res = selectAll('features');
while($feature = mysqli_fetch_assoc($feature_res)){
    $features_map[$feature['id']] = $feature['name'];
}

$facility_res = selectAll('facilities');
while($facility = mysqli_fetch_assoc($facility_res)){
    $facilities_map[$facility['id']] = $facility['name'];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang nhà cung cấp - Chỗ ở</title>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Chỗ ở của tôi</h3>
                </div>

                <?php if($message !== ''): ?>
                    <div class="alert <?php echo ($message_type === 'success') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                        <strong><?php echo htmlspecialchars($message); ?></strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php
      if(mysqli_num_rows($rooms) === 0){
        echo "<div class='alert alert-info'>Bạn chưa có chỗ ở nào được duyệt. Vui lòng chờ quản trị viên xác nhận yêu cầu hoặc liên hệ để được hỗ trợ.</div>";
      }
      else{
        echo "<div class='row g-3'>";
        while($room = mysqli_fetch_assoc($rooms)){
          $roomName = htmlspecialchars($room['name']);
          $roomArea = htmlspecialchars($room['area']);
          $roomDesc = htmlspecialchars($room['description']);
          $roomPrice = number_format($room['price']);
          $approval = htmlspecialchars($room['approval_status']);
          $status = (int)$room['status'];
          $badge = '<span class="badge bg-secondary">Không xác định</span>';

          if($approval === 'approved'){
            $badge = '<span class="badge bg-success">Đã duyệt</span>';
          }
          elseif($approval === 'pending'){
            $badge = '<span class="badge bg-warning text-dark">Đang chờ</span>';
          }
          elseif($approval === 'rejected'){
            $badge = '<span class="badge bg-danger">Bị từ chối</span>';
          }

          $features = [];
          $feature_res = select("SELECT features_id FROM `room_features` WHERE `room_id`=?", [$room['id']], 'i');
          while($f = mysqli_fetch_assoc($feature_res)){
            $features[] = $features_map[$f['features_id']] ?? '';
          }
          $features = array_filter($features);

          $facilities = [];
          $facility_res = select("SELECT facilities_id FROM `room_facilities` WHERE `room_id`=?", [$room['id']], 'i');
          while($f = mysqli_fetch_assoc($facility_res)){
            $facilities[] = $facilities_map[$f['facilities_id']] ?? '';
          }
          $facilities = array_filter($facilities);

          echo "<div class='col-md-6'>";
          echo "  <div class='card border-0 shadow-sm h-100'>";
          echo "    <div class='card-body d-flex flex-column'>";
          echo "      <div class='d-flex justify-content-between align-items-start mb-2'>";
          echo "        <div>";
          echo "          <h5 class='card-title mb-1'>{$roomName}</h5>";
          echo "          <span class='badge bg-light text-dark me-1'>Khu vực: {$roomArea}</span>";
          echo "          <span class='badge bg-light text-dark'>Giá: {$roomPrice} VND</span>";
          echo "        </div>";
          echo "        {$badge}";
          echo "      </div>";
          echo "      <p class='text-muted small flex-grow-1'>{$roomDesc}</p>";

          if(!empty($features)){
            echo "      <div class='mb-2'><h6 class='mb-1'>Không gian</h6>";
            foreach($features as $feat){
              $feat = htmlspecialchars($feat);
              echo "<span class='badge bg-light text-dark me-1'>{$feat}</span>";
            }
            echo "</div>";
          }

          if(!empty($facilities)){
            echo "      <div class='mb-3'><h6 class='mb-1'>Tiện ích</h6>";
            foreach($facilities as $fac){
              $fac = htmlspecialchars($fac);
              echo "<span class='badge bg-secondary bg-opacity-25 text-dark me-1'>{$fac}</span>";
            }
            echo "</div>";
          }

          echo "      <div class='mt-auto'>";
          if($approval !== 'approved'){
            echo "        <span class='text-muted small'>Chờ quản trị viên duyệt trước khi bật hiển thị.</span>";
          } else {
            echo "        <form method='POST' class='d-flex align-items-center gap-2'>";
            echo "          <input type='hidden' name='room_id' value='{$room['id']}'>";
            if($status === 1){
              echo "          <input type='hidden' name='status' value='0'>";
              echo "          <button type='submit' class='btn btn-outline-danger btn-sm'>Tắt hiển thị</button>";
            }
            else{
              echo "          <input type='hidden' name='status' value='1'>";
              echo "          <button type='submit' class='btn btn-success btn-sm'>Bật hiển thị</button>";
            }
            echo "        </form>";
          }
          echo "      </div>";
          echo "    </div>";
          echo "  </div>";
          echo "</div>";
        }
        echo "</div>";
      }
      ?>
            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>
</body>

</html>
