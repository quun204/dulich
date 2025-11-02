<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
session_start();

if(!isset($_SESSION['login']) || $_SESSION['login'] != true){
  echo 'not_logged_in';
  exit;
}

$uId = $_SESSION['uId'];

$res = select("SELECT host_status, is_host FROM user_cred WHERE id = ? LIMIT 1", [$uId], 'i');
if(!$res || mysqli_num_rows($res) === 0){
  echo 'failed';
  exit;
}

$row = mysqli_fetch_assoc($res);

if((int)$row['is_host'] === 1){
  echo 'already_host';
  exit;
}

if($row['host_status'] === 'pending'){
  echo 'already_requested';
  exit;
}

$status = 'pending';
if($row['host_status'] === 'rejected'){
  $status = 'pending';
}

if(update("UPDATE user_cred SET host_status = ? WHERE id = ?", [$status, $uId], 'si')){
  $_SESSION['hostStatus'] = $status;
  echo ($row['host_status'] === 'rejected') ? 'request_resent' : 'request_sent';
} else {
  echo 'failed';
}
?>