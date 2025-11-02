<?php
require('inc/essentials.php');
require('inc/db_config.php');
adminLogin();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Host</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container mt-5">
        <h4 class="mb-4">Yêu cầu trở thành Host</h4>

        <div class="card shadow border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle">
                        <thead>
                            <tr class="bg-dark text-white">
                                <th>#</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Chỗ ở</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
          $res = mysqli_query($con, "SELECT uc.*, hp.id AS property_id, hp.property_name, hp.status AS property_status FROM user_cred uc LEFT JOIN host_properties hp ON hp.id = (SELECT id FROM host_properties WHERE user_id = uc.id ORDER BY id DESC LIMIT 1) WHERE uc.host_status IS NOT NULL ORDER BY uc.datentime DESC");
          $i = 1;
          while($row = mysqli_fetch_assoc($res)){
            $status_badge = '';
            if($row['host_status'] == 'pending') $status_badge = '<span class="badge bg-warning text-dark">Đang chờ</span>';
            if($row['host_status'] == 'approved') $status_badge = '<span class="badge bg-success">Đã duyệt</span>';
            if($row['host_status'] == 'rejected') $status_badge = '<span class="badge bg-danger">Từ chối</span>';

            $propertyStatusBadge = '';
            if($row['property_status'] == 'pending') $propertyStatusBadge = '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
            if($row['property_status'] == 'approved') $propertyStatusBadge = '<span class="badge bg-success">Đã duyệt</span>';
            if($row['property_status'] == 'rejected') $propertyStatusBadge = '<span class="badge bg-danger">Từ chối</span>';

            $hostName = htmlspecialchars($row['name']);
            $hostEmail = htmlspecialchars($row['email']);
            $hostPhone = htmlspecialchars($row['phonenum']);
            $propertyName = $row['property_name'] ? htmlspecialchars($row['property_name']) : null;
            $propertyId = $row['property_id'] ? (int)$row['property_id'] : null;
            $propertyParam = $propertyId ? $propertyId : 'null';

            $propertyCell = '<span class="badge bg-secondary">Chưa có</span>';
            if($propertyId){
              $viewBtn = "<button type=\"button\" onclick=\"viewProperty($propertyId)\" class=\"btn btn-sm btn-outline-info mt-2\">Xem chi tiết</button>";
              $statusInfo = $propertyStatusBadge ? "<div class='mt-1'>$propertyStatusBadge</div>" : '';
              $propertyCell = "<div class='fw-bold'>$propertyName</div>$statusInfo$viewBtn";
            }

            echo <<<data
              <tr>
                <td>$i</td>
                <td>$hostName</td>
                <td>$hostEmail</td>
                <td>$hostPhone</td>
                <td>$propertyCell</td>
                <td>$status_badge</td>
                <td>
                  <button onclick="updateHostStatus($row[id],'approved',$propertyParam)" class="btn btn-sm btn-success me-2">Duyệt</button>
                  <button onclick="updateHostStatus($row[id],'rejected',$propertyParam)" class="btn btn-sm btn-danger">Từ chối</button>
                </td>
              </tr>
            data;
            $i++;
          }
          ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="propertyDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thông tin chỗ ở</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div><strong>Host:</strong> <span id="propertyHost"></span></div>
                            <div><strong>Tên chỗ ở:</strong> <span id="propertyName"></span></div>
                            <div><strong>Khu vực:</strong> <span id="propertyArea"></span></div>
                            <div><strong>Giá:</strong> <span id="propertyPrice"></span></div>
                            <div><strong>Số lượng phòng:</strong> <span id="propertyQuantity"></span></div>
                            <div><strong>Sức chứa:</strong> <span id="propertyGuests"></span></div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>Trạng thái:</strong> <span id="propertyStatus"></span></div>
                            <div><strong>Ngày gửi:</strong> <span id="propertyCreated"></span></div>
                        </div>
                        <div class="col-12">
                            <div class="mb-2"><strong>Mô tả:</strong></div>
                            <p id="propertyDescription" class="mb-0"></p>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2"><strong>Không gian:</strong></div>
                            <ul id="propertyFeatures" class="list-unstyled mb-0"></ul>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2"><strong>Tiện nghi:</strong></div>
                            <ul id="propertyFacilities" class="list-unstyled mb-0"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateHostStatus(id, status, propertyId) {
        if (confirm("Bạn có chắc chắn muốn cập nhật trạng thái này không?")) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/update_host_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            let params = "id=" + id + "&status=" + status;
            if (propertyId) {
                params += "&property_id=" + propertyId;
            }
            xhr.onload = function() {
                if (this.responseText == 'success') {
                    alert("Cập nhật thành công!");
                    location.reload();
                } else {
                    alert("Thao tác thất bại!");
                }
            }
            xhr.send(params);
        }
    }

    function viewProperty(propertyId) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/host_properties.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            try {
                const response = JSON.parse(this.responseText);
                if (response.status === 'success') {
                    const formatter = new Intl.NumberFormat('vi-VN');
                    document.getElementById('propertyHost').innerText = response.property.host_name;
                    document.getElementById('propertyName').innerText = response.property.name;
                    document.getElementById('propertyArea').innerText = response.property.area;
                    document.getElementById('propertyPrice').innerText = formatter.format(response.property.price) + ' VND';
                    document.getElementById('propertyQuantity').innerText = response.property.quantity;
                    document.getElementById('propertyGuests').innerText = `Người lớn: ${response.property.adult} | Trẻ em: ${response.property.children}`;
                    document.getElementById('propertyDescription').innerText = response.property.description;
                    document.getElementById('propertyCreated').innerText = response.property.created_at;

                    const statusMap = {
                        'pending': '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
                        'approved': '<span class="badge bg-success">Đã duyệt</span>',
                        'rejected': '<span class="badge bg-danger">Từ chối</span>'
                    };
                    document.getElementById('propertyStatus').innerHTML = statusMap[response.property.status] || '';

                    const featureList = document.getElementById('propertyFeatures');
                    featureList.innerHTML = '';
                    if (response.features.length) {
                        response.features.forEach((item) => {
                            const li = document.createElement('li');
                            li.textContent = item;
                            featureList.appendChild(li);
                        });
                    } else {
                        featureList.innerHTML = '<li class="text-muted">Không có</li>';
                    }

                    const facilityList = document.getElementById('propertyFacilities');
                    facilityList.innerHTML = '';
                    if (response.facilities.length) {
                        response.facilities.forEach((item) => {
                            const li = document.createElement('li');
                            li.textContent = item;
                            facilityList.appendChild(li);
                        });
                    } else {
                        facilityList.innerHTML = '<li class="text-muted">Không có</li>';
                    }

                    const modalElement = document.getElementById('propertyDetailModal');
                    if (modalElement) {
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                        modalInstance.show();
                    }
                } else {
                    alert('Không tìm thấy thông tin chỗ ở!');
                }
            } catch (error) {
                alert('Không thể tải thông tin chỗ ở!');
            }
        };
        xhr.send('get_property=' + propertyId);
    }
    </script>

    <?php require('inc/scripts.php'); ?>
</body>

</html>