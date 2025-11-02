<?php 

require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

session_start();

if (isset($_GET['fetch_rooms'])) {

  // 🔹 Giải mã dữ liệu kiểm tra ngày đặt
  $chk_avail = json_decode($_GET['chk_avail'], true);

  // 🔹 Kiểm tra hợp lệ ngày checkin/checkout
  if ($chk_avail['checkin'] != '' && $chk_avail['checkout'] != '') {
    $today_date = new DateTime(date("Y-m-d"));
    $checkin_date = new DateTime($chk_avail['checkin']);
    $checkout_date = new DateTime($chk_avail['checkout']);

    if ($checkin_date == $checkout_date || $checkout_date < $checkin_date || $checkin_date < $today_date) {
      echo "<h3 class='text-center text-danger'>Ngày nhập không hợp lệ!</h3>";
      exit;
    }
  }

  // 🔹 Giải mã danh sách tiện ích
  $facility_list = json_decode($_GET['facility_list'], true);

  // 🔹 Lấy khu vực (area) được chọn
  $area = '';
  if (isset($_GET['area']) && $_GET['area'] != '') {
    $area = trim($_GET['area']);
  }

  // 🔹 Biến đếm và output
  $count_rooms = 0;
  $output = "";

  // 🔹 Kiểm tra website có đang tạm ngưng hay không
  $settings_q = "SELECT * FROM `settings` WHERE `sr_no`=1";
  $settings_r = mysqli_fetch_assoc(mysqli_query($con, $settings_q));

  // 🔹 Truy vấn danh sách phòng
  if ($area != '') {
    // Có lọc theo khu vực
    $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `removed`=? AND `approval_status`=? AND `area`=?", [1, 0, 'approved', $area], 'iiss');
  } else {
    // Không lọc khu vực
    $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `removed`=? AND `approval_status`=?", [1, 0, 'approved'], 'iis');
  }

  while ($room_data = mysqli_fetch_assoc($room_res)) {

    // 🔹 Kiểm tra phòng có trùng ngày đặt không
    if ($chk_avail['checkin'] != '' && $chk_avail['checkout'] != '') {
      $tb_query = "SELECT COUNT(*) AS `total_bookings` FROM `booking_order`
                   WHERE booking_status=? AND room_id=? 
                   AND check_out > ? AND check_in < ?";
      $values = ['booked', $room_data['id'], $chk_avail['checkin'], $chk_avail['checkout']];
      $tb_fetch = mysqli_fetch_assoc(select($tb_query, $values, 'siss'));

      if (($room_data['quantity'] - $tb_fetch['total_bookings']) == 0) {
        continue;
      }
    }

    // 🔹 Lấy danh sách tiện ích của phòng
    $fac_count = 0;
    $fac_q = mysqli_query($con, "SELECT f.name, f.id FROM `facilities` f 
                                INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
                                WHERE rfac.room_id = '$room_data[id]'");

    $facilities_data = "";
    while ($fac_row = mysqli_fetch_assoc($fac_q)) {
      if (in_array($fac_row['id'], $facility_list['facilities'])) {
        $fac_count++;
      }
      $facilities_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
        $fac_row[name]
      </span>";
    }

    if (count($facility_list['facilities']) != $fac_count) {
      continue;
    }

    // 🔹 Lấy danh sách đặc điểm (features)
    $fea_q = mysqli_query($con, "SELECT f.name FROM `features` f 
                                INNER JOIN `room_features` rfea ON f.id = rfea.features_id 
                                WHERE rfea.room_id = '$room_data[id]'");

    $features_data = "";
    while ($fea_row = mysqli_fetch_assoc($fea_q)) {
      $features_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
        $fea_row[name]
      </span>";
    }

    // 🔹 Lấy ảnh thumbnail
    $room_thumb = ROOMS_IMG_PATH . "thumbnail.jpg";
    $thumb_q = mysqli_query($con, "SELECT * FROM `room_images` 
                                  WHERE `room_id`='$room_data[id]' 
                                  AND `thumb`='1'");

    if (mysqli_num_rows($thumb_q) > 0) {
      $thumb_res = mysqli_fetch_assoc($thumb_q);
      $room_thumb = ROOMS_IMG_PATH . $thumb_res['image'];
    }

    // 🔹 Nút đặt phòng
    $book_btn = "";
    if (!$settings_r['shutdown']) {
      $login = (isset($_SESSION['login']) && $_SESSION['login'] == true) ? 1 : 0;
      $book_btn = "<button onclick='checkLoginToBook($login,$room_data[id])' 
                    class='btn btn-sm w-100 text-white custom-bg shadow-none mb-2'>
                    Đặt ngay
                  </button>";
    }

    // 🔹 Hiển thị thẻ phòng
    $output .= "
      <div class='card mb-4 border-0 shadow'>
        <div class='row g-0 p-3 align-items-center'>
          <div class='col-md-5 mb-lg-0 mb-md-0 mb-3'>
            <img src='$room_thumb' class='img-fluid rounded'>
          </div>
          <div class='col-md-5 px-lg-3 px-md-3 px-0'>
            <h5 class='mb-3'>$room_data[name]</h5>
            <div class='features mb-3'>
              <h6 class='mb-1'>Không gian</h6>
              $features_data
            </div>
            <div class='facilities mb-3'>
              <h6 class='mb-1'>Tiện ích</h6>
              $facilities_data
            </div>
            <div class='area mb-3'>
              <h6 class='mb-1'>Khu vực</h6>
              <span class='badge rounded-pill bg-light text-dark text-wrap'>
                $room_data[area]
              </span>
            </div>
          </div>
          <div class='col-md-2 mt-lg-0 mt-md-0 mt-4 text-center'>
            <h6 class='mb-4'>$room_data[price] VND / đêm</h6>
            $book_btn
            <a href='room_details.php?id=$room_data[id]' class='btn btn-sm w-100 btn-outline-dark shadow-none'>Chi tiết</a>
          </div>
        </div>
      </div>
    ";

    $count_rooms++;
  }

  // 🔹 Nếu có phòng
  if ($count_rooms > 0) {
    echo $output;
  } else {
    echo "<h3 class='text-center text-danger'>Không có phòng phù hợp!</h3>";
  }
}

?>