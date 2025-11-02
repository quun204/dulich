<?php
require('../../admin/inc/db_config.php');
require('../inc/essentials.php');
hostLoginAjax();

$hostId = $_SESSION['uId'];

function decode_input_array($raw)
{
    if ($raw === null) {
        return [];
    }
    if (is_array($raw)) {
        return filteration($raw);
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }
    return filteration($decoded);
}

if (isset($_POST['add_room'])) {
    $features = decode_input_array($_POST['features'] ?? []);
    $facilities = decode_input_array($_POST['facilities'] ?? []);
    $frm_data = filteration($_POST);

    $flag = 0;

    $q1 = "INSERT INTO `rooms` (`name`, `area`, `price`, `quantity`, `adult`, `children`, `description`, `host_id`, `status`, `removed`)
            VALUES (?,?,?,?,?,?,?,?,1,0)";
    $values = [
        $frm_data['name'],
        $frm_data['area'],
        $frm_data['price'],
        $frm_data['quantity'],
        $frm_data['adult'],
        $frm_data['children'],
        $frm_data['desc'],
        $hostId
    ];

    if (insert($q1, $values, 'ssiiiisi')) {
        $flag = 1;
    }

    $room_id = mysqli_insert_id($con);

    $q2 = "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)";
    if ($stmt = mysqli_prepare($con, $q2)) {
        foreach ($facilities as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
    }

    $q3 = "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)";
    if ($stmt = mysqli_prepare($con, $q3)) {
        foreach ($features as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
    }

    if ($flag) {
        update("UPDATE user_cred SET is_host = 1, host_status = 'approved' WHERE id = ?", [$hostId], 'i');
        $_SESSION['isHost'] = 1;
        $_SESSION['hostStatus'] = 'approved';
    }

    echo $flag ? 1 : 0;
    exit;
}

if (isset($_POST['get_all_rooms'])) {
    $res = select("SELECT * FROM `rooms` WHERE `removed` = 0 AND `host_id` = ?", [$hostId], 'i');
    $i = 1;
    $data = '';

    while ($row = mysqli_fetch_assoc($res)) {
        $status_btn = ($row['status'] == 1)
            ? "<button onclick='toggle_room_status($row[id],0)' class='btn btn-success btn-sm'>Đang hiển thị</button>"
            : "<button onclick='toggle_room_status($row[id],1)' class='btn btn-warning btn-sm'>Tạm ẩn</button>";

        $data .= "
            <tr>
              <td>$i</td>
              <td>{$row['name']}</td>
              <td>{$row['area']}</td>
              <td>
                <span class='badge bg-light text-dark'>Người lớn: {$row['adult']}</span><br>
                <span class='badge bg-light text-dark'>Trẻ em: {$row['children']}</span>
              </td>
              <td>" . number_format($row['price']) . " VND</td>
              <td>{$row['quantity']}</td>
              <td>$status_btn</td>
              <td>
                <button onclick='edit_room($row[id])' class='btn btn-primary btn-sm me-1'>Sửa</button>
                <button onclick='remove_room($row[id])' class='btn btn-danger btn-sm'>Xoá</button>
              </td>
            </tr>
        ";
        $i++;
    }

    echo $data ?: '<tr><td colspan="8" class="text-muted">Bạn chưa có chỗ ở nào. Hãy đăng chỗ ở đầu tiên ngay!</td></tr>';
    exit;
}

if (isset($_POST['get_room'])) {
    $frm_data = filteration($_POST);
    $room_id = (int)$frm_data['get_room'];

    $res1 = select("SELECT * FROM `rooms` WHERE `id` = ? AND `host_id` = ?", [$room_id, $hostId], 'ii');
    if (!$res1 || mysqli_num_rows($res1) === 0) {
        echo json_encode(['error' => 'not_found']);
        exit;
    }

    $res2 = select("SELECT * FROM `room_features` WHERE `room_id` = ?", [$room_id], 'i');
    $res3 = select("SELECT * FROM `room_facilities` WHERE `room_id` = ?", [$room_id], 'i');

    $roomdata = mysqli_fetch_assoc($res1);
    $features = [];
    $facilities = [];

    while ($row = mysqli_fetch_assoc($res2)) {
        $features[] = (int)$row['features_id'];
    }
    while ($row = mysqli_fetch_assoc($res3)) {
        $facilities[] = (int)$row['facilities_id'];
    }

    echo json_encode(['roomdata' => $roomdata, 'features' => $features, 'facilities' => $facilities]);
    exit;
}

if (isset($_POST['edit_room'])) {
    $features = decode_input_array($_POST['features'] ?? []);
    $facilities = decode_input_array($_POST['facilities'] ?? []);
    $frm_data = filteration($_POST);
    $room_id = (int)$frm_data['room_id'];

    $room_exists = select("SELECT id FROM rooms WHERE id = ? AND host_id = ?", [$room_id, $hostId], 'ii');
    if (!$room_exists || mysqli_num_rows($room_exists) === 0) {
        echo 0;
        exit;
    }

    $q1 = "UPDATE `rooms` SET `name`=?, `area`=?, `price`=?, `quantity`=?, `adult`=?, `children`=?, `description`=? WHERE `id`=? AND `host_id`=?";
    $values = [
        $frm_data['name'],
        $frm_data['area'],
        $frm_data['price'],
        $frm_data['quantity'],
        $frm_data['adult'],
        $frm_data['children'],
        $frm_data['desc'],
        $room_id,
        $hostId
    ];

    $flag = update($q1, $values, 'ssiiiisii');

    delete("DELETE FROM `room_features` WHERE `room_id`=?", [$room_id], 'i');
    delete("DELETE FROM `room_facilities` WHERE `room_id`=?", [$room_id], 'i');

    $q2 = "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)";
    if ($stmt = mysqli_prepare($con, $q2)) {
        foreach ($facilities as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    }

    $q3 = "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)";
    if ($stmt = mysqli_prepare($con, $q3)) {
        foreach ($features as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    }

    echo $flag ? 1 : 0;
    exit;
}

if (isset($_POST['toggle_status'])) {
    $frm_data = filteration($_POST);
    $room_id = (int)$frm_data['toggle_status'];

    $room_exists = select("SELECT id FROM rooms WHERE id = ? AND host_id = ?", [$room_id, $hostId], 'ii');
    if (!$room_exists || mysqli_num_rows($room_exists) === 0) {
        echo 0;
        exit;
    }

    echo update("UPDATE `rooms` SET `status` = ? WHERE `id` = ?", [$frm_data['value'], $room_id], 'ii') ? 1 : 0;
    exit;
}

if (isset($_POST['remove_room'])) {
    $frm_data = filteration($_POST);
    $room_id = (int)$frm_data['remove_room'];

    $room_exists = select("SELECT id FROM rooms WHERE id = ? AND host_id = ?", [$room_id, $hostId], 'ii');
    if (!$room_exists || mysqli_num_rows($room_exists) === 0) {
        echo 0;
        exit;
    }

    $res1 = select("SELECT * FROM `room_images` WHERE `room_id`=?", [$room_id], 'i');
    while ($row = mysqli_fetch_assoc($res1)) {
        deleteImage($row['image'], ROOMS_FOLDER);
    }

    delete("DELETE FROM `room_images` WHERE `room_id`=?", [$room_id], 'i');
    delete("DELETE FROM `room_features` WHERE `room_id`=?", [$room_id], 'i');
    delete("DELETE FROM `room_facilities` WHERE `room_id`=?", [$room_id], 'i');

    echo update("UPDATE `rooms` SET `removed` = 1 WHERE `id` = ?", [$room_id], 'i') ? 1 : 0;
    exit;
}

?>
