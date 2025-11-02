<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
session_start();

if(!isset($_SESSION['login']) || $_SESSION['login'] != true){
  echo 'not_logged_in';
  exit;
}

$uId = $_SESSION['uId'];

// Kiểm tra xem đã gửi yêu cầu chưa
$check_q = mysqli_query($con, "SELECT host_status FROM user_cred WHERE id = '$uId'");
$row = mysqli_fetch_assoc($check_q);

if($row['host_status'] != NULL){
  echo 'already_requested';
  exit;
}

// Cập nhật trạng thái yêu cầu
$q = "UPDATE user_cred SET host_status = 'pending' WHERE id = ?";
$values = [$uId];
if(update($q, $values, 'i')){
  echo 'request_sent';
} else {
  echo 'failed';
}
?>