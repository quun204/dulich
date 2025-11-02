<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('inc/links.php'); ?>
    <title><?php echo $settings_r['site_title'] ?> - Danh sách phòng</title>
</head>

<body class="bg-light">

    <?php 
require('inc/header.php'); 

$checkin_default = "";
$checkout_default = "";
$area_default = "";

if(isset($_GET['check_availability'])) {
  $frm_data = filteration($_GET);
  $checkin_default = $frm_data['checkin'];
  $checkout_default = $frm_data['checkout'];
  $area_default = $frm_data['area'];
}
?>

    <div class="my-5 px-4">
        <h2 class="fw-bold h-font text-center">DANH SÁCH PHÒNG</h2>
        <div class="h-line bg-dark"></div>
    </div>

    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar Filter -->
            <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 ps-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
                    <div class="container-fluid flex-lg-column align-items-stretch">
                        <h4 class="mt-2">Bộ lọc</h4>
                        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filterDropdown" aria-controls="navbarNav" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="filterDropdown">

                            <!-- Check availability -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3"
                                    style="font-size: 18px;">
                                    <span>Kiểm tra phòng trống</span>
                                    <button id="chk_avail_btn" onclick="chk_avail_clear()"
                                        class="btn shadow-none btn-sm text-secondary d-none">Làm mới</button>
                                </h5>
                                <label class="form-label">Nhận phòng</label>
                                <input type="date" class="form-control shadow-none mb-3"
                                    value="<?php echo $checkin_default ?>" id="checkin" onchange="chk_avail_filter()">
                                <label class="form-label">Trả phòng</label>
                                <input type="date" class="form-control shadow-none"
                                    value="<?php echo $checkout_default ?>" id="checkout" onchange="chk_avail_filter()">
                            </div>

                            <!-- Facilities -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3"
                                    style="font-size: 18px;">
                                    <span>Tiện ích</span>
                                    <button id="facilities_btn" onclick="facilities_clear()"
                                        class="btn shadow-none btn-sm text-secondary d-none">Làm mới</button>
                                </h5>
                                <?php 
                            $facilities_q = selectAll('facilities');
                            while($row = mysqli_fetch_assoc($facilities_q)) {
                              echo <<<facilities
                                <div class="mb-2">
                                  <input type="checkbox" onclick="fetch_rooms()" name="facilities" value="$row[id]" class="form-check-input shadow-none me-1" id="fac_$row[id]">
                                  <label class="form-check-label" for="fac_$row[id]">$row[name]</label>
                                </div>
                              facilities;
                            }
                            ?>
                            </div>

                            <!-- Area Filter -->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3"
                                    style="font-size: 18px;">
                                    <span>Khu vực</span>
                                    <button id="area_btn" onclick="area_clear()"
                                        class="btn shadow-none btn-sm text-secondary d-none">Làm mới</button>
                                </h5>
                                <?php 
                            $area_q = mysqli_query($con, "SELECT DISTINCT area FROM `rooms` WHERE area != '' ORDER BY area ASC");
                            while($row = mysqli_fetch_assoc($area_q)) {
                              $checked = ($area_default == $row['area']) ? "checked" : "";
                              echo <<<area
                                <div class="mb-2">
                                  <input type="radio" name="area" value="$row[area]" $checked class="form-check-input shadow-none me-1" id="area_$row[area]" onchange="area_filter()">
                                  <label class="form-check-label" for="area_$row[area]">$row[area]</label>
                                </div>
                              area;
                            }
                            ?>
                            </div>

                        </div>
                    </div>
                </nav>
            </div>

            <!-- Room List -->
            <div class="col-lg-9 col-md-12 px-4" id="rooms-data">
            </div>

        </div>
    </div>

    <script>
    let rooms_data = document.getElementById('rooms-data');
    let checkin = document.getElementById('checkin');
    let checkout = document.getElementById('checkout');
    let chk_avail_btn = document.getElementById('chk_avail_btn');
    let facilities_btn = document.getElementById('facilities_btn');
    let area_btn = document.getElementById('area_btn');

    function fetch_rooms() {
        let chk_avail = JSON.stringify({
            checkin: checkin.value,
            checkout: checkout.value
        });

        let facility_list = {
            "facilities": []
        };
        let get_facilities = document.querySelectorAll('[name="facilities"]:checked');
        if (get_facilities.length > 0) {
            get_facilities.forEach((facility) => {
                facility_list.facilities.push(facility.value);
            });
            facilities_btn.classList.remove('d-none');
        } else {
            facilities_btn.classList.add('d-none');
        }
        facility_list = JSON.stringify(facility_list);

        let selected_area = document.querySelector('[name="area"]:checked');
        let area = selected_area ? selected_area.value : '';

        if (area != '') {
            area_btn.classList.remove('d-none');
        } else {
            area_btn.classList.add('d-none');
        }

        let xhr = new XMLHttpRequest();
        xhr.open("GET", "ajax/rooms.php?fetch_rooms&chk_avail=" + chk_avail + "&facility_list=" + facility_list +
            "&area=" + area, true);

        xhr.onprogress = function() {
            rooms_data.innerHTML = `<div class="spinner-border text-info mb-3 d-block mx-auto" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>`;
        }

        xhr.onload = function() {
            rooms_data.innerHTML = this.responseText;
        }

        xhr.send();
    }

    function chk_avail_filter() {
        if (checkin.value != '' && checkout.value != '') {
            fetch_rooms();
            chk_avail_btn.classList.remove('d-none');
        }
    }

    function chk_avail_clear() {
        checkin.value = '';
        checkout.value = '';
        chk_avail_btn.classList.add('d-none');
        fetch_rooms();
    }

    function facilities_clear() {
        let get_facilities = document.querySelectorAll('[name="facilities"]:checked');
        get_facilities.forEach((facility) => {
            facility.checked = false;
        });
        facilities_btn.classList.add('d-none');
        fetch_rooms();
    }

    function area_filter() {
        fetch_rooms();
    }

    function area_clear() {
        let get_area = document.querySelectorAll('[name="area"]:checked');
        get_area.forEach((a) => {
            a.checked = false;
        });
        area_btn.classList.add('d-none');
        fetch_rooms();
    }

    window.onload = function() {
        fetch_rooms();
    }
    </script>

    <?php require('inc/footer.php'); ?>

</body>

</html>