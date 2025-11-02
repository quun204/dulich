<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
session_start();

if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){
  echo 'not_logged_in';
  exit;
}

$uId = $_SESSION['uId'];

$hostRes = select("SELECT host_status, is_host FROM user_cred WHERE id = ? LIMIT 1", [$uId], 'i');
if(!$hostRes || mysqli_num_rows($hostRes) === 0){
  echo 'failed';
  exit;
}

$hostRow = mysqli_fetch_assoc($hostRes);
if((int)$hostRow['is_host'] === 1){
  echo 'already_host';
  exit;
}
if($hostRow['host_status'] === 'pending'){
  echo 'already_requested';
  exit;
}

$propertyName = isset($_POST['property_name']) ? filteration($_POST['property_name']) : '';
$area = isset($_POST['area']) ? filteration($_POST['area']) : '';
$price = isset($_POST['price']) ? (int)$_POST['price'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$adult = isset($_POST['adult']) ? (int)$_POST['adult'] : 0;
$children = isset($_POST['children']) ? (int)$_POST['children'] : 0;
$description = isset($_POST['description']) ? filteration($_POST['description']) : '';

$featuresRaw = $_POST['features'] ?? '[]';
$facilitiesRaw = $_POST['facilities'] ?? '[]';

$features = json_decode($featuresRaw, true);
$facilities = json_decode($facilitiesRaw, true);

if(!is_array($features)) $features = [];
if(!is_array($facilities)) $facilities = [];

$features = array_values(array_unique(array_filter($features, 'is_numeric')));
$facilities = array_values(array_unique(array_filter($facilities, 'is_numeric')));

if($propertyName === '' || $area === '' || $description === '' || $price < 1 || $quantity < 1 || $adult < 1 || $children < 0){
  echo 'invalid_input';
  exit;
}

$pendingProperty = select("SELECT id FROM host_properties WHERE user_id = ? AND status = 'pending' LIMIT 1", [$uId], 'i');
if($pendingProperty && mysqli_num_rows($pendingProperty) > 0){
  echo 'already_requested';
  exit;
}

$featuresJson = json_encode(array_map('intval', $features));
$facilitiesJson = json_encode(array_map('intval', $facilities));

mysqli_begin_transaction($con);
try {
  $inserted = insert(
    "INSERT INTO host_properties (user_id, property_name, area, price, quantity, adult, children, description, features, facilities, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
    [$uId, $propertyName, $area, $price, $quantity, $adult, $children, $description, $featuresJson, $facilitiesJson, 'pending'],
    'issiiiissss'
  );

  if(!$inserted){
    throw new Exception('property_insert_failed');
  }

  $updated = update(
    "UPDATE user_cred SET host_status = ?, is_host = 0 WHERE id = ?",
    ['pending', $uId],
    'si'
  );

  if(!$updated){
    throw new Exception('status_update_failed');
  }

  mysqli_commit($con);
  $_SESSION['hostStatus'] = 'pending';
  $_SESSION['isHost'] = 0;
  echo 'request_sent';
} catch (Exception $e) {
  mysqli_rollback($con);
  echo 'failed';
}
