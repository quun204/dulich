<?php
require('inc/essentials.php');
require('inc/db_config.php');
adminLogin();
ensureHostSchema();

$features_map = [];
$facilities_map = [];

$feature_res = selectAll('features');
while($feature_row = mysqli_fetch_assoc($feature_res)){
    $features_map[$feature_row['id']] = $feature_row['name'];
}

$facility_res = selectAll('facilities');
while($facility_row = mysqli_fetch_assoc($facility_res)){
    $facilities_map[$facility_row['id']] = $facility_row['name'];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý nhà cung cấp</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container mt-5">
        <h4 class="mb-4">Yêu cầu trở thành nhà cung cấp</h4>

        <div class="card shadow border-0 mb-5">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Người gửi</th>
                                <th>Liên hệ</th>
                                <th>Chỗ ở</th>
                                <th>Khu vực</th>
                                <th>Giá / đêm</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
          $query = "SELECT ha.*, uc.name, uc.email, uc.phonenum FROM `host_applications` ha
                    INNER JOIN `user_cred` uc ON ha.user_id = uc.id
                    ORDER BY ha.created_at DESC";
          $res = mysqli_query($con, $query);
          $i = 1;

          while($row = mysqli_fetch_assoc($res)){
            $status_badge = '<span class="badge bg-secondary">Không xác định</span>';
            if($row['status'] === 'pending'){
              $status_badge = '<span class="badge bg-warning text-dark">Đang chờ</span>';
            }
            elseif($row['status'] === 'approved'){
              $status_badge = '<span class="badge bg-success">Đã duyệt</span>';
            }
            elseif($row['status'] === 'rejected'){
              $status_badge = '<span class="badge bg-danger">Từ chối</span>';
            }

            $feature_names = [];
            $facility_names = [];

            $feature_ids = json_decode($row['features'], true) ?? [];
            foreach($feature_ids as $fid){
              if(isset($features_map[$fid])){
                $feature_names[] = $features_map[$fid];
              }
            }

            $facility_ids = json_decode($row['facilities'], true) ?? [];
            foreach($facility_ids as $faid){
              if(isset($facilities_map[$faid])){
                $facility_names[] = $facilities_map[$faid];
              }
            }

            $features_text = htmlspecialchars(implode(', ', $feature_names));
            $facilities_text = htmlspecialchars(implode(', ', $facility_names));
            $description_text = htmlspecialchars($row['description']);

            $user_name = htmlspecialchars($row['name']);
            $user_email = htmlspecialchars($row['email']);
            $user_phone = htmlspecialchars($row['phonenum']);
            $property_name = htmlspecialchars($row['property_name']);
            $area = htmlspecialchars($row['area']);
            $price_display = htmlspecialchars((string)$row['price']);

            $actions = '';
            if($row['status'] === 'pending'){
              $actions = "
                <button onclick=\"updateHostStatus($row[id],'approved')\" class='btn btn-sm btn-success me-2'>Duyệt</button>
                <button onclick=\"updateHostStatus($row[id],'rejected')\" class='btn btn-sm btn-danger'>Từ chối</button>
              ";
            }

            echo <<<HTML
              <tr>
                <td>{$i}</td>
                <td>
                  <div class="fw-bold">{$user_name}</div>
                  <span class="badge bg-light text-dark">Mã user: {$row['user_id']}</span>
                </td>
                <td>
                  <div>{$user_email}</div>
                  <div>{$user_phone}</div>
                </td>
                <td class="text-start">
                  <div class="fw-bold">{$property_name}</div>
                  <div class="small text-muted">{$description_text}</div>
                  <button type="button" class="btn btn-link btn-sm p-0 mt-1" data-bs-toggle="modal" data-bs-target="#hostApplicationDetails" data-property="{$property_name}" data-description="{$description_text}" data-features="{$features_text}" data-facilities="{$facilities_text}">
                    Xem chi tiết
                  </button>
                </td>
                <td>{$area}</td>
                <td>{$price_display} VND</td>
                <td>{$status_badge}</td>
                <td>$actions</td>
              </tr>
            HTML;

            $i++;
          }
          ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <h4 class="mb-3">Nhà cung cấp đang hoạt động</h4>
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Số lượng chỗ ở</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
          $host_query = "SELECT uc.id, uc.name, uc.email, uc.phonenum, COUNT(r.id) AS total_rooms
                          FROM `user_cred` uc
                          LEFT JOIN `rooms` r ON r.host_id = uc.id AND r.removed = 0
                          WHERE uc.is_host = 1
                          GROUP BY uc.id, uc.name, uc.email, uc.phonenum";
          $host_res = mysqli_query($con, $host_query);
          $counter = 1;
          while($host = mysqli_fetch_assoc($host_res)){
            $host_name = htmlspecialchars($host['name']);
            $host_email = htmlspecialchars($host['email']);
            $host_phone = htmlspecialchars($host['phonenum']);
            $host_rooms = htmlspecialchars((string)$host['total_rooms']);
            echo <<<ROW
              <tr>
                <td>{$counter}</td>
                <td>{$host_name}</td>
                <td>{$host_email}</td>
                <td>{$host_phone}</td>
                <td>{$host_rooms}</td>
              </tr>
            ROW;
            $counter++;
          }
          ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hostApplicationDetails" tabindex="-1" aria-labelledby="hostApplicationDetailsLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hostApplicationDetailsLabel">Thông tin chỗ ở</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Tên chỗ ở:</strong> <span id="modalPropertyName"></span></p>
                    <p><strong>Mô tả:</strong> <span id="modalPropertyDescription"></span></p>
                    <p><strong>Không gian nổi bật:</strong></p>
                    <p id="modalPropertyFeatures" class="text-muted"></p>
                    <p><strong>Tiện ích:</strong></p>
                    <p id="modalPropertyFacilities" class="text-muted"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    const hostDetailsModal = document.getElementById('hostApplicationDetails');
    if (hostDetailsModal) {
        hostDetailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;

            document.getElementById('modalPropertyName').innerText = button.getAttribute('data-property') || '';
            document.getElementById('modalPropertyDescription').innerText = button.getAttribute('data-description') || '';
            document.getElementById('modalPropertyFeatures').innerText = button.getAttribute('data-features') || 'Không có';
            document.getElementById('modalPropertyFacilities').innerText = button.getAttribute('data-facilities') || 'Không có';
        });
    }

    function updateHostStatus(applicationId, status) {
        if (!confirm('Bạn có chắc chắn muốn cập nhật trạng thái này không?')) {
            return;
        }

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax/update_host_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (this.responseText === 'success') {
                alert('success', 'Cập nhật thành công!');
                setTimeout(function(){ location.reload(); }, 1000);
            } else {
                alert('error', 'Thao tác thất bại!');
            }
        };

        xhr.send('application_id=' + applicationId + '&status=' + status);
    }
    </script>

    <?php require('inc/scripts.php'); ?>
</body>

</html>