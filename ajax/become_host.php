<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

if(session_status() !== PHP_SESSION_ACTIVE){
    session_start();
}

ensureHostSchema();

if(!isset($_SESSION['login']) || $_SESSION['login'] != true || !isset($_SESSION['uId'])){
    echo 'not_logged_in';
    exit;
}

$uId = (int)$_SESSION['uId'];

$usr_res = select("SELECT `is_host`, `host_status` FROM `user_cred` WHERE `id`=? LIMIT 1", [$uId], 'i');
if(mysqli_num_rows($usr_res) == 0){
    echo 'failed';
    exit;
}

$user = mysqli_fetch_assoc($usr_res);

if((int)$user['is_host'] === 1){
    echo 'already_host';
    exit;
}

if($user['host_status'] === 'pending'){
    echo 'already_pending';
    exit;
}

$pending_res = select("SELECT `id` FROM `host_applications` WHERE `user_id`=? AND `status`='pending' LIMIT 1", [$uId], 'i');
if(mysqli_num_rows($pending_res) > 0){
    echo 'already_pending';
    exit;
}

$required_fields = ['property_name','area','price','quantity','adult','children','description'];
foreach($required_fields as $field){
    if(!isset($_POST[$field]) || trim($_POST[$field]) === ''){
        echo 'failed';
        exit;
    }
}

$frm_data = filteration($_POST);

$features = [];
if(isset($_POST['features'])){
    $decoded_features = json_decode($_POST['features'], true);
    if(is_array($decoded_features)){
        foreach($decoded_features as $feature_id){
            if(is_numeric($feature_id)){
                $features[] = (int)$feature_id;
            }
        }
    }
}

$facilities = [];
if(isset($_POST['facilities'])){
    $decoded_facilities = json_decode($_POST['facilities'], true);
    if(is_array($decoded_facilities)){
        foreach($decoded_facilities as $facility_id){
            if(is_numeric($facility_id)){
                $facilities[] = (int)$facility_id;
            }
        }
    }
}

$features_json = json_encode($features);
$facilities_json = json_encode($facilities);

$query = "INSERT INTO `host_applications`
          (`user_id`, `property_name`, `area`, `price`, `quantity`, `adult`, `children`, `description`, `features`, `facilities`)
          VALUES (?,?,?,?,?,?,?,?,?,?)";

$values = [
    $uId,
    $frm_data['property_name'],
    $frm_data['area'],
    (int)$frm_data['price'],
    (int)$frm_data['quantity'],
    (int)$frm_data['adult'],
    (int)$frm_data['children'],
    $frm_data['description'],
    $features_json,
    $facilities_json
];

if(insert($query, $values, 'issiiiisss')){
    update("UPDATE `user_cred` SET `host_status`='pending' WHERE `id`=?", [$uId], 'i');
    echo 'request_sent';
} else {
    echo 'failed';
}
