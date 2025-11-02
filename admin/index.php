<?php
require('inc/essentials.php');
require('inc/db_config.php');

session_start();
if ((isset($_SESSION['adminLogin']) && $_SESSION['adminLogin'] === true) ||
    (isset($_SESSION['hostLogin']) && $_SESSION['hostLogin'] === true)) {
  redirect('dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Panel</title>
    <?php require('inc/links.php'); ?>
    <style>
    div.login-form {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 400px;
    }
    </style>
</head>

<body class="bg-light">

    <div class="login-form text-center rounded bg-white shadow overflow-hidden">
        <form method="POST">
            <h4 class="bg-dark text-white py-3">Trang Quản Lý</h4>
            <div class="p-4">
                <div class="mb-3">
                    <input name="admin_name" required type="text" class="form-control shadow-none text-center"
                        placeholder="Tên quản trị viên / Email Host">
                </div>
                <div class="mb-4">
                    <input name="admin_pass" required type="password" class="form-control shadow-none text-center"
                        placeholder="Mật khẩu">
                </div>
                <button name="login" type="submit" class="btn text-white custom-bg shadow-none">Đăng Nhập</button>
            </div>
        </form>
    </div>

    <?php 
  if (isset($_POST['login'])) {
    $frm_data = filteration($_POST);
    
    // Băm mật khẩu nhập vào
    $hashed_pass = md5($frm_data['admin_pass']);

    $query = "SELECT * FROM `admin_cred` WHERE `admin_name` = ? AND `admin_pass` = ?";
    $values = [$frm_data['admin_name'], $hashed_pass];
    $res = select($query, $values, "ss");

    if ($res && $res->num_rows == 1) {
      $row = mysqli_fetch_assoc($res);
      $_SESSION['adminLogin'] = true;
      $_SESSION['adminId'] = $row['sr_no'];
      redirect('dashboard.php');
    } else {
      $hostRes = select(
        "SELECT * FROM `user_cred` WHERE (`email` = ? OR `phonenum` = ?) LIMIT 1",
        [$frm_data['admin_name'], $frm_data['admin_name']],
        'ss'
      );

      if ($hostRes && $hostRes->num_rows === 1) {
        $hostRow = mysqli_fetch_assoc($hostRes);
        if ($hostRow['password'] === $hashed_pass && (int)$hostRow['is_host'] === 1 && $hostRow['host_status'] === 'approved') {
          $_SESSION['hostLogin'] = true;
          $_SESSION['hostId'] = $hostRow['id'];
          $_SESSION['hostName'] = $hostRow['name'];
          redirect('dashboard.php');
        }
      }

      alert('error', 'Đăng nhập thất bại! Sai tên hoặc mật khẩu.');
    }
  }
  ?>

    <?php require('inc/scripts.php') ?>
</body>

</html>