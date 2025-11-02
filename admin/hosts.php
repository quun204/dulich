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
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
          $res = mysqli_query($con, "SELECT * FROM user_cred WHERE host_status IS NOT NULL");
          $i = 1;
          while($row = mysqli_fetch_assoc($res)){
            $status_badge = '';
            if($row['host_status'] == 'pending') $status_badge = '<span class="badge bg-warning text-dark">Đang chờ</span>';
            if($row['host_status'] == 'approved') $status_badge = '<span class="badge bg-success">Đã duyệt</span>';
            if($row['host_status'] == 'rejected') $status_badge = '<span class="badge bg-danger">Từ chối</span>';

            echo <<<data
              <tr>
                <td>$i</td>
                <td>$row[name]</td>
                <td>$row[email]</td>
                <td>$row[phonenum]</td>
                <td>$status_badge</td>
                <td>
                  <button onclick="updateHostStatus($row[id],'approved')" class="btn btn-sm btn-success me-2">Duyệt</button>
                  <button onclick="updateHostStatus($row[id],'rejected')" class="btn btn-sm btn-danger">Từ chối</button>
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

    <script>
    function updateHostStatus(id, status) {
        if (confirm("Bạn có chắc chắn muốn cập nhật trạng thái này không?")) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/update_host_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (this.responseText == 'success') {
                    alert("Cập nhật thành công!");
                    location.reload();
                } else {
                    alert("Thao tác thất bại!");
                }
            }
            xhr.send("id=" + id + "&status=" + status);
        }
    }
    </script>

    <?php require('inc/scripts.php'); ?>
</body>

</html>