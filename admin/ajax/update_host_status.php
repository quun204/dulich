<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if(isset($_POST['id']) && isset($_POST['status'])){
  $id = (int)$_POST['id'];
  $status = $_POST['status'];
  $propertyId = isset($_POST['property_id']) ? (int)$_POST['property_id'] : null;

  $allowedStatuses = ['pending', 'approved', 'rejected'];
  if(!in_array($status, $allowedStatuses, true)){
    echo 'error';
    exit;
  }

  $is_host = ($status === 'approved') ? 1 : 0;

  mysqli_begin_transaction($con);
  try {
    $updatedUser = update(
      "UPDATE user_cred SET host_status = ?, is_host = ? WHERE id = ?",
      [$status, $is_host, $id],
      'sii'
    );

    if(!$updatedUser){
      throw new Exception('user_update_failed');
    }

    if($propertyId){
      $propertyRes = select("SELECT * FROM host_properties WHERE id = ? LIMIT 1", [$propertyId], 'i');

      if(!$propertyRes || mysqli_num_rows($propertyRes) === 0){
        throw new Exception('property_not_found');
      }

      $property = mysqli_fetch_assoc($propertyRes);
      $currentStatus = $property['status'];
      $roomId = $property['room_id'] ?? null;

      if($status === 'approved'){
        if($currentStatus !== 'approved' || !$roomId){
          $roomInserted = insert(
            "INSERT INTO `rooms` (`name`, `area`, `price`, `quantity`, `adult`, `children`, `description`) VALUES (?,?,?,?,?,?,?)",
            [
              $property['property_name'],
              $property['area'],
              $property['price'],
              $property['quantity'],
              $property['adult'],
              $property['children'],
              $property['description']
            ],
            'ssiiiis'
          );

          if(!$roomInserted){
            throw new Exception('room_insert_failed');
          }

          $roomId = mysqli_insert_id($con);

          $features = json_decode($property['features'], true) ?: [];
          $facilities = json_decode($property['facilities'], true) ?: [];

          if(!empty($features)){
            $stmt = mysqli_prepare($con, "INSERT INTO room_features (room_id, features_id) VALUES (?, ?)");
            if(!$stmt){
              throw new Exception('feature_stmt_failed');
            }
            foreach($features as $featureId){
              $featureId = (int)$featureId;
              mysqli_stmt_bind_param($stmt, 'ii', $roomId, $featureId);
              mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
          }

          if(!empty($facilities)){
            $stmt = mysqli_prepare($con, "INSERT INTO room_facilities (room_id, facilities_id) VALUES (?, ?)");
            if(!$stmt){
              throw new Exception('facility_stmt_failed');
            }
            foreach($facilities as $facilityId){
              $facilityId = (int)$facilityId;
              mysqli_stmt_bind_param($stmt, 'ii', $roomId, $facilityId);
              mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
          }

          $propUpdated = update(
            "UPDATE host_properties SET status = ?, room_id = ? WHERE id = ?",
            ['approved', $roomId, $propertyId],
            'sii'
          );

          if(!$propUpdated){
            throw new Exception('property_update_failed');
          }
        } else {
          update("UPDATE host_properties SET status = 'approved' WHERE id = ?", [$propertyId], 'i');
        }
      } elseif($status === 'rejected') {
        update("UPDATE host_properties SET status = 'rejected' WHERE id = ?", [$propertyId], 'i');
      } else {
        update("UPDATE host_properties SET status = 'pending' WHERE id = ?", [$propertyId], 'i');
      }
    }

    mysqli_commit($con);
    echo 'success';
  } catch (Exception $e) {
    mysqli_rollback($con);
    echo 'error';
  }
}
?>
