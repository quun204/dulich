<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

/* ========== ADD ROOM ========== */
if (isset($_POST['add_room'])) {
  $features = filteration(json_decode($_POST['features']));
  $facilities = filteration(json_decode($_POST['facilities']));
  $frm_data = filteration($_POST);

  $flag = 0;

  $q1 = "INSERT INTO `rooms` (`name`, `area`, `price`, `quantity`, `adult`, `children`, `description`) 
         VALUES (?,?,?,?,?,?,?)";
  $values = [
    $frm_data['name'], 
    $frm_data['area'],     // Khu vực (chuỗi)
    $frm_data['price'], 
    $frm_data['quantity'], 
    $frm_data['adult'], 
    $frm_data['children'], 
    $frm_data['desc']
  ];

  // Sửa ở đây: dùng 'ssiiiis' thay vì 'siiiiis'
  if (insert($q1, $values, 'ssiiiis')) {
    $flag = 1;
  }

  $room_id = mysqli_insert_id($con);

  // Thêm facilities
  $q2 = "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)";
  if ($stmt = mysqli_prepare($con, $q2)) {
    foreach ($facilities as $f) {
      mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
      mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
  } else {
    $flag = 0;
    die('query cannot be prepared - facilities insert');
  }

  // Thêm features
  $q3 = "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)";
  if ($stmt = mysqli_prepare($con, $q3)) {
    foreach ($features as $f) {
      mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
      mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
  } else {
    $flag = 0;
    die('query cannot be prepared - features insert');
  }

  echo $flag ? 1 : 0;
}

/* ========== GET ALL ROOMS ========== */
if (isset($_POST['get_all_rooms'])) {
  $res = select("SELECT * FROM `rooms` WHERE `removed`=?", [0], 'i');
  $i = 1;
  $data = "";

  while ($row = mysqli_fetch_assoc($res)) {
    $status = ($row['status'] == 1)
      ? "<button onclick='toggle_status($row[id],0)' class='btn btn-dark btn-sm shadow-none'>active</button>"
      : "<button onclick='toggle_status($row[id],1)' class='btn btn-warning btn-sm shadow-none'>inactive</button>";

    $data .= "
      <tr class='align-middle'>
        <td>$i</td>
        <td>$row[name]</td>
        <td>$row[area]</td>
        <td>
          <span class='badge bg-light text-dark'>Adult: $row[adult]</span><br>
          <span class='badge bg-light text-dark'>Children: $row[children]</span>
        </td>
        <td>$row[price] VND</td>
        <td>$row[quantity]</td>
        <td>$status</td>
        <td>
          <button type='button' onclick='edit_details($row[id])' class='btn btn-primary btn-sm shadow-none' data-bs-toggle='modal' data-bs-target='#edit-room'>
            <i class='bi bi-pencil-square'></i>
          </button>
          <button type='button' onclick=\"room_images($row[id],'$row[name]')\" class='btn btn-info btn-sm shadow-none' data-bs-toggle='modal' data-bs-target='#room-images'>
            <i class='bi bi-images'></i>
          </button>
          <button type='button' onclick='remove_room($row[id])' class='btn btn-danger btn-sm shadow-none'>
            <i class='bi bi-trash'></i>
          </button>
        </td>
      </tr>
    ";
    $i++;
  }

  echo $data;
}

/* ========== GET ROOM DETAILS ========== */
if (isset($_POST['get_room'])) {
  $frm_data = filteration($_POST);
  $res1 = select("SELECT * FROM `rooms` WHERE `id`=?", [$frm_data['get_room']], 'i');
  $res2 = select("SELECT * FROM `room_features` WHERE `room_id`=?", [$frm_data['get_room']], 'i');
  $res3 = select("SELECT * FROM `room_facilities` WHERE `room_id`=?", [$frm_data['get_room']], 'i');

  $roomdata = mysqli_fetch_assoc($res1);
  $features = [];
  $facilities = [];

  while ($row = mysqli_fetch_assoc($res2)) {
    $features[] = $row['features_id'];
  }
  while ($row = mysqli_fetch_assoc($res3)) {
    $facilities[] = $row['facilities_id'];
  }

  echo json_encode(["roomdata" => $roomdata, "features" => $features, "facilities" => $facilities]);
}

/* ========== EDIT ROOM ========== */
if (isset($_POST['edit_room'])) {
  $features = filteration(json_decode($_POST['features']));
  $facilities = filteration(json_decode($_POST['facilities']));
  $frm_data = filteration($_POST);
  $flag = 0;

  $q1 = "UPDATE `rooms` SET `name`=?,`area`=?,`price`=?,`quantity`=?,`adult`=?,`children`=?,`description`=? WHERE `id`=?";
  $values = [
    $frm_data['name'], 
    $frm_data['area'], 
    $frm_data['price'], 
    $frm_data['quantity'], 
    $frm_data['adult'], 
    $frm_data['children'], 
    $frm_data['desc'], 
    $frm_data['room_id']
  ];

  if (update($q1, $values, 'ssiiiisi')) {
    $flag = 1;
  }

  delete("DELETE FROM `room_features` WHERE `room_id`=?", [$frm_data['room_id']], 'i');
  delete("DELETE FROM `room_facilities` WHERE `room_id`=?", [$frm_data['room_id']], 'i');

  $q2 = "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)";
  if ($stmt = mysqli_prepare($con, $q2)) {
    foreach ($facilities as $f) {
      mysqli_stmt_bind_param($stmt, 'ii', $frm_data['room_id'], $f);
      mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
  }

  $q3 = "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)";
  if ($stmt = mysqli_prepare($con, $q3)) {
    foreach ($features as $f) {
      mysqli_stmt_bind_param($stmt, 'ii', $frm_data['room_id'], $f);
      mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
  }

  echo $flag ? 1 : 0;
}

/* ========== TOGGLE STATUS ========== */
if (isset($_POST['toggle_status'])) {
  $frm_data = filteration($_POST);
  $q = "UPDATE `rooms` SET `status`=? WHERE `id`=?";
  echo update($q, [$frm_data['value'], $frm_data['toggle_status']], 'ii') ? 1 : 0;
}

/* ========== ADD IMAGE ========== */
if (isset($_POST['add_image'])) {
  $frm_data = filteration($_POST);
  $img_r = uploadImage($_FILES['image'], ROOMS_FOLDER);

  if (in_array($img_r, ['inv_img', 'inv_size', 'upd_failed'])) {
    echo $img_r;
  } else {
    $q = "INSERT INTO `room_images`(`room_id`, `image`) VALUES (?,?)";
    echo insert($q, [$frm_data['room_id'], $img_r], 'is');
  }
}

/* ========== GET ROOM IMAGES ========== */
if (isset($_POST['get_room_images'])) {
  $frm_data = filteration($_POST);
  $res = select("SELECT * FROM `room_images` WHERE `room_id`=?", [$frm_data['get_room_images']], 'i');
  $path = ROOMS_IMG_PATH;

  while ($row = mysqli_fetch_assoc($res)) {
    $thumb_btn = ($row['thumb'] == 1)
      ? "<i class='bi bi-check-lg text-light bg-success px-2 py-1 rounded fs-5'></i>"
      : "<button onclick='thumb_image($row[sr_no],$row[room_id])' class='btn btn-secondary shadow-none'>
           <i class='bi bi-check-lg'></i>
         </button>";

    echo "
      <tr class='align-middle'>
        <td><img src='$path$row[image]' class='img-fluid'></td>
        <td>$thumb_btn</td>
        <td>
          <button onclick='rem_image($row[sr_no],$row[room_id])' class='btn btn-danger shadow-none'>
            <i class='bi bi-trash'></i>
          </button>
        </td>
      </tr>
    ";
  }
}

/* ========== REMOVE IMAGE ========== */
if (isset($_POST['rem_image'])) {
  $frm_data = filteration($_POST);
  $values = [$frm_data['image_id'], $frm_data['room_id']];
  $img = mysqli_fetch_assoc(select("SELECT * FROM `room_images` WHERE `sr_no`=? AND `room_id`=?", $values, 'ii'));

  if (deleteImage($img['image'], ROOMS_FOLDER)) {
    echo delete("DELETE FROM `room_images` WHERE `sr_no`=? AND `room_id`=?", $values, 'ii');
  } else {
    echo 0;
  }
}

/* ========== SET THUMB IMAGE ========== */
if (isset($_POST['thumb_image'])) {
  $frm_data = filteration($_POST);
  update("UPDATE `room_images` SET `thumb`=0 WHERE `room_id`=?", [$frm_data['room_id']], 'i');
  echo update("UPDATE `room_images` SET `thumb`=1 WHERE `sr_no`=? AND `room_id`=?", [$frm_data['image_id'], $frm_data['room_id']], 'ii');
}

/* ========== REMOVE ROOM ========== */
if (isset($_POST['remove_room'])) {
  $frm_data = filteration($_POST);
  $res1 = select("SELECT * FROM `room_images` WHERE `room_id`=?", [$frm_data['room_id']], 'i');

  while ($row = mysqli_fetch_assoc($res1)) {
    deleteImage($row['image'], ROOMS_FOLDER);
  }

  delete("DELETE FROM `room_images` WHERE `room_id`=?", [$frm_data['room_id']], 'i');
  delete("DELETE FROM `room_features` WHERE `room_id`=?", [$frm_data['room_id']], 'i');
  delete("DELETE FROM `room_facilities` WHERE `room_id`=?", [$frm_data['room_id']], 'i');
  echo update("UPDATE `rooms` SET `removed`=1 WHERE `id`=?", [$frm_data['room_id']], 'i') ? 1 : 0;
}
?>