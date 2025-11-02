<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if(isset($_POST['id']) && isset($_POST['status'])){
  $id = $_POST['id'];
  $status = $_POST['status'];

  $is_host = ($status == 'approved') ? 1 : 0;

  $q = "UPDATE user_cred SET host_status = ?, is_host = ? WHERE id = ?";
  $values = [$status, $is_host, $id];
  
  if(update($q, $values, 'sii')){
    echo 'success';
  } else {
    echo 'error';
  }
}
?>