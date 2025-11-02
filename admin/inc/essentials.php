<?php

  //frontend purpose data
  define('SITE_URL', 'http://localhost/dulich/');
  define('ABOUT_IMG_PATH',SITE_URL.'images/about/');
  define('CAROUSEL_IMG_PATH',SITE_URL.'images/carousel/');
  define('FACILITIES_IMG_PATH',SITE_URL.'images/facilities/');
  define('ROOMS_IMG_PATH',SITE_URL.'images/rooms/');
  define('USERS_IMG_PATH',SITE_URL.'images/users/');

  //backend upload process needs this data

  define('UPLOAD_IMAGE_PATH',$_SERVER['DOCUMENT_ROOT'].'/dulich/images/');
  define('ABOUT_FOLDER','about/');
  define('CAROUSEL_FOLDER','carousel/');
  define('FACILITIES_FOLDER','facilities/');
  define('ROOMS_FOLDER','rooms/');
  define('USERS_FOLDER','users/');

        function adminLogin() {
                if(session_status() !== PHP_SESSION_ACTIVE){
                        session_start();
                }
                if(!(isset($_SESSION['adminLogin']) && $_SESSION['adminLogin'] == true)){
                        echo"<script>window.location.href='index.php'</script>";
                        exit;
                }
        }

        function hostLogin($redirectOnFail = true) {
                if(session_status() !== PHP_SESSION_ACTIVE){
                        session_start();
                }

                if(!(isset($_SESSION['login']) && $_SESSION['login'] == true && isset($_SESSION['uId']))){
                        if($redirectOnFail){
                                redirect('../index.php');
                        }
                        return false;
                }

                global $con;

                $res = select("SELECT id, is_host, host_status, name FROM `user_cred` WHERE `id`=?", [$_SESSION['uId']], 'i');

                if(mysqli_num_rows($res) == 0){
                        if($redirectOnFail){
                                redirect('../index.php');
                        }
                        return false;
                }

                $row = mysqli_fetch_assoc($res);
                $_SESSION['is_host'] = (int)$row['is_host'];
                $_SESSION['host_status'] = $row['host_status'];

                if((int)$row['is_host'] !== 1){
                        if($redirectOnFail){
                                redirect('../profile.php');
                        }
                        return false;
                }

                return $row;
        }

	function redirect($url) {
		echo "<script>window.location.href='$url'</script>";
		exit;
	}

	function alert($type, $msg) {

		$bs_class = ($type == 'success') ? 'alert-success' : 'alert-danger';

		echo <<<alert
			<div class="alert $bs_class alert-dismissible fade show custom-alert" role="alert">
				<strong class="me-3">$msg</strong>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		alert;
	}

  function uploadImage($image, $folder)
  {
    $valid_mime = ['image/jpeg', 'image/png', 'image/webp'];
    $img_mime = $image['type'];

    if (!in_array($img_mime, $valid_mime)) {
      return 'Không hỗ trợ định dạng này!';
    } else if (($image['size'] / (1024 * 1024)) > 2) {
      return 'Vui lòng chọn hình ảnh dưới 2MB!';
    } else {
      $img_path = UPLOAD_IMAGE_PATH . $folder . basename($image['name']);
      if (move_uploaded_file($image['tmp_name'], $img_path)) {
        return basename($image['name']);
      } else {
        return 'Tải lên hình ảnh thất bại!';
      }
    }
  }

  function deleteImage($image, $folder)
  {
    if(unlink(UPLOAD_IMAGE_PATH.$folder.$image)){
      return true;
    }
    else{
      return false;
    }
  }

  function uploadSVGImage($image,$folder)
  {
    $valid_mime = ['image/svg+xml'];
    $img_mime = $image['type'];

    if(!in_array($img_mime,$valid_mime)){
      return 'Không hỗ trợ định dạng này!'; //invalid image mime or format
    }
    else if(($image['size']/(1024*1024))>1){
      return 'Vui lòng chọn hình ảnh dưới 2MB!'; //invalid size greater than 1mb
    }
    else{
      $ext = pathinfo($image['name'],PATHINFO_EXTENSION);
      $rname = 'IMG_'.random_int(11111,99999).".$ext";

      $img_path = UPLOAD_IMAGE_PATH.$folder.$rname;
      if(move_uploaded_file($image['tmp_name'],$img_path)){
        return $rname;
      }
      else{
        return 'Tải lên hình ảnh thất bại!';
      }
    }
  }

	function uploadUserImage($image)
  {
    $valid_mime = ['image/jpeg','image/png','image/webp'];
    $img_mime = $image['type'];

    if(!in_array($img_mime,$valid_mime)){
      return 'inv_img'; //invalid image mime or format
    }
    else
    {
      $ext = pathinfo($image['name'],PATHINFO_EXTENSION);
      $rname = 'IMG_'.random_int(11111,99999).".jpeg";

      $img_path = UPLOAD_IMAGE_PATH.USERS_FOLDER.$rname;

      if($ext == 'png' || $ext == 'PNG') {
        $img = imagecreatefrompng($image['tmp_name']);
      }
      else if($ext == 'webp' || $ext == 'WEBP') {
        $img = imagecreatefromwebp($image['tmp_name']);
      }
      else{
        $img = imagecreatefromjpeg($image['tmp_name']);
      }


      if(imagejpeg($img,$img_path,75)){
        return $rname;
      }
      else{
        return 'upd_failed';
      }
    }
  }

  function ensureHostSchema()
  {
    global $con;

    static $ensured = false;
    if($ensured){
      return;
    }

    $applications_table = "CREATE TABLE IF NOT EXISTS `host_applications` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `property_name` VARCHAR(255) NOT NULL,
      `area` VARCHAR(255) DEFAULT NULL,
      `price` INT DEFAULT 0,
      `quantity` INT DEFAULT 1,
      `adult` INT DEFAULT 1,
      `children` INT DEFAULT 0,
      `description` TEXT,
      `features` TEXT,
      `facilities` TEXT,
      `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
      `room_id` INT DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($con, $applications_table);

    $check_host_id = mysqli_query($con, "SHOW COLUMNS FROM `rooms` LIKE 'host_id'");
    if(mysqli_num_rows($check_host_id) == 0){
      mysqli_query($con, "ALTER TABLE `rooms` ADD `host_id` INT NULL AFTER `id`");
    }

    $check_approval = mysqli_query($con, "SHOW COLUMNS FROM `rooms` LIKE 'approval_status'");
    if(mysqli_num_rows($check_approval) == 0){
      mysqli_query($con, "ALTER TABLE `rooms` ADD `approval_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER `status`");
    }

    $ensured = true;
  }
?>