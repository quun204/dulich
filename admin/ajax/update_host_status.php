<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();
ensureHostSchema();

if(isset($_POST['application_id']) && isset($_POST['status'])){
  $application_id = (int)$_POST['application_id'];
  $status = filteration($_POST['status']);

  if(!in_array($status, ['approved','rejected'])){
    echo 'error';
    exit;
  }

  $application_res = select("SELECT * FROM `host_applications` WHERE `id`=? LIMIT 1", [$application_id], 'i');

  if(mysqli_num_rows($application_res) == 0){
    echo 'error';
    exit;
  }

  $application = mysqli_fetch_assoc($application_res);
  $user_id = (int)$application['user_id'];

  if($status === 'approved'){
    try {
      mysqli_begin_transaction($con);

      $features = json_decode($application['features'], true) ?: [];
      $facilities = json_decode($application['facilities'], true) ?: [];

      $room_values = [
        $user_id,
        $application['property_name'],
        $application['area'],
        (int)$application['price'],
        (int)$application['quantity'],
        (int)$application['adult'],
        (int)$application['children'],
        $application['description'],
        1,
        'approved'
      ];

      if(empty($application['room_id'])){
        $insert_room = "INSERT INTO `rooms`
          (`host_id`, `name`, `area`, `price`, `quantity`, `adult`, `children`, `description`, `status`, `approval_status`)
          VALUES (?,?,?,?,?,?,?,?,?,?)";

        insert($insert_room, $room_values, 'issiiiisis');
        $room_id = mysqli_insert_id($con);

        if(!empty($facilities)){
          $fac_stmt = mysqli_prepare($con, "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)");
          if($fac_stmt){
            foreach($facilities as $facility_id){
              $facility_id = (int)$facility_id;
              mysqli_stmt_bind_param($fac_stmt, 'ii', $room_id, $facility_id);
              mysqli_stmt_execute($fac_stmt);
            }
            mysqli_stmt_close($fac_stmt);
          }
        }

        if(!empty($features)){
          $fea_stmt = mysqli_prepare($con, "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)");
          if($fea_stmt){
            foreach($features as $feature_id){
              $feature_id = (int)$feature_id;
              mysqli_stmt_bind_param($fea_stmt, 'ii', $room_id, $feature_id);
              mysqli_stmt_execute($fea_stmt);
            }
            mysqli_stmt_close($fea_stmt);
          }
        }

        update("UPDATE `host_applications` SET `room_id`=? WHERE `id`=?", [$room_id, $application_id], 'ii');
      }

      update("UPDATE `host_applications` SET `status`='approved' WHERE `id`=?", [$application_id], 'i');
      update("UPDATE `user_cred` SET `host_status`='approved', `is_host`=1 WHERE `id`=?", [$user_id], 'i');

      mysqli_commit($con);
      echo 'success';
    } catch (Exception $e) {
      mysqli_rollback($con);
      echo 'error';
    }
  }
  else{
    if(update("UPDATE `host_applications` SET `status`='rejected' WHERE `id`=?", [$application_id], 'i')){
      update("UPDATE `user_cred` SET `host_status`='rejected', `is_host`=0 WHERE `id`=?", [$user_id], 'i');
      echo 'success';
    } else {
      echo 'error';
    }
  }
}
?>