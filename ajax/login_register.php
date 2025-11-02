<?php 
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ========== USER REGISTER ========== */
if(isset($_POST['register'])) {
    $data = filteration($_POST);

    // Kiểm tra khớp mật khẩu
    if($data['pass'] != $data['cpass']) {
        echo 'pass_mismatch';
        exit;
    }

    // Kiểm tra trùng email hoặc số điện thoại
    $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1", 
                      [$data['email'], $data['phonenum']], "ss");
    if(mysqli_num_rows($u_exist) != 0) {
        $u_exist_fetch = mysqli_fetch_assoc($u_exist);
        echo ($u_exist_fetch['email'] == $data['email']) ? 'email_already' : 'phone_already';
        exit;
    }

    // Mã hóa mật khẩu bằng md5
    $hashed_pass = md5($data['pass']);

    // Thêm người dùng vào database
    $query = "INSERT INTO `user_cred` (`name`, `email`, `phonenum`, `address`, `pincode`, `dob`, `password`) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $values = [$data['name'], $data['email'], $data['phonenum'], $data['address'], $data['pincode'], $data['dob'], $hashed_pass];

    if(insert($query, $values, 'sssssss')) {
        echo 'registration_success';
    } else {
        echo 'registration_failed';
    }
    exit;
}

/* ========== USER LOGIN ========== */
if(isset($_POST['login'])) {
    $data = filteration($_POST);

    // Kiểm tra user tồn tại
    $query = "SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1";
    $values = [$data['email_mob'], $data['email_mob']];
    $res = select($query, $values, "ss");

    if(mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        $hashed_input = md5($data['pass']);

        // So khớp mật khẩu
        if($hashed_input == $row['password']) {
            session_start();
            $_SESSION['login'] = true;
            $_SESSION['uId'] = $row['id'];
            $_SESSION['uName'] = $row['name'];
            $_SESSION['uPic'] = $row['profile'];
            $_SESSION['is_host'] = isset($row['is_host']) ? (int)$row['is_host'] : 0;
            $_SESSION['host_status'] = $row['host_status'] ?? null;
            echo 'login_success';
        } else {
            echo 'invalid_password';
        }
    } else {
        echo 'invalid_email_mob';
    }
    exit;
}
?>