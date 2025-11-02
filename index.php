<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css">
    <?php require('inc/links.php'); ?>
    <title><?php echo $settings_r['site_title'] ?> - Trang chủ</title>
    <style>
    .availability-form {
        margin-top: -50px;
        z-index: 2;
        position: relative;
    }

    @media screen and (max-width: 575px) {
        .availability-form {
            margin-top: 25px;
            padding: 0 35px;
        }
    }
    </style>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <!-- Carousel -->
    <div class="container-fluid px-lg-4 mt-4">
        <div class="swiper swiper-container">
            <div class="swiper-wrapper">
                <?php 
          $res = selectAll('carousel');
          while($row = mysqli_fetch_assoc($res))
          {
            $path = CAROUSEL_IMG_PATH;
            echo <<<data
              <div class="swiper-slide">
                <img src="$path$row[image]" class="w-100 d-block">
              </div>
            data;
          }
        ?>
            </div>
        </div>
    </div>

    <!-- Check availability form -->
    <div class="container availability-form">
        <div class="row">
            <div class="col-lg-12 bg-white shadow p-4 rounded">
                <h5 class="mb-4 fw-bold h-font">Tiến hành đặt phòng</h5>
                <form action="rooms.php" method="GET">
                    <div class="row align-items-end">

                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-semibold">Nhận phòng</label>
                            <input type="date" class="form-control shadow-none" name="checkin" required>
                        </div>

                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-semibold">Trả phòng</label>
                            <input type="date" class="form-control shadow-none" name="checkout" required>
                        </div>

                        <!-- 🔹 Lấy danh sách khu vực từ database -->
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-semibold">Khu vực</label>
                            <select name="area" class="form-select shadow-none">
                                <option value="">Tất cả</option>
                                <?php
                  $area_q = mysqli_query($con, "SELECT DISTINCT area FROM `rooms` WHERE `status`=1 AND `removed`=0 ORDER BY area ASC");
                  while($area_row = mysqli_fetch_assoc($area_q)){
                    $area = htmlspecialchars($area_row['area']);
                    echo "<option value='$area'>$area</option>";
                  }
                ?>
                            </select>
                        </div>

                        <input type="hidden" name="check_availability">

                        <div class="col-lg-3 mb-lg-3 mt-2">
                            <button type="submit" class="btn text-white shadow-none custom-bg w-100">Tìm kiếm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Our Rooms -->
    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">Danh sách phòng</h2>

    <div class="container">
        <div class="row">

            <?php 
        $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `removed`=? ORDER BY `id` DESC LIMIT 3",[1,0],'ii');

        while($room_data = mysqli_fetch_assoc($room_res))
        {
          // Features
          $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f 
            INNER JOIN `room_features` rfea ON f.id = rfea.features_id 
            WHERE rfea.room_id = '$room_data[id]'");

          $features_data = "";
          while($fea_row = mysqli_fetch_assoc($fea_q)){
            $features_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
              $fea_row[name]
            </span>";
          }

          // Facilities
          $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
            INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id 
            WHERE rfac.room_id = '$room_data[id]'");

          $facilities_data = "";
          while($fac_row = mysqli_fetch_assoc($fac_q)){
            $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
              $fac_row[name]
            </span>";
          }

          // Thumbnail
          $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
          $thumb_q = mysqli_query($con,"SELECT * FROM `room_images` 
            WHERE `room_id`='$room_data[id]' 
            AND `thumb`='1'");

          if(mysqli_num_rows($thumb_q)>0){
            $thumb_res = mysqli_fetch_assoc($thumb_q);
            $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
          }

          // Booking button
          $book_btn = "";
          if(!$settings_r['shutdown']){
            $login=0;
            if(isset($_SESSION['login']) && $_SESSION['login']==true){
              $login=1;
            }
            $book_btn = "<button onclick='checkLoginToBook($login,$room_data[id])' class='btn btn-sm text-white custom-bg shadow-none'>Đặt ngay</button>";
          }

          // Rating
          $rating_q = "SELECT AVG(rating) AS `avg_rating` FROM `rating_review`
            WHERE `room_id`='$room_data[id]' ORDER BY `sr_no` DESC LIMIT 20";
          $rating_res = mysqli_query($con,$rating_q);
          $rating_fetch = mysqli_fetch_assoc($rating_res);

          $rating_data = "";
          if($rating_fetch['avg_rating']!=NULL)
          {
            $rating_data = "<div class='rating mb-4'>
              <h6 class='mb-1'>Đánh giá</h6>
              <span class='badge rounded-pill bg-light'>";
            for($i=0; $i<$rating_fetch['avg_rating']; $i++){
              $rating_data .="<i class='bi bi-star-fill text-warning'></i> ";
            }
            $rating_data .= "</span></div>";
          }

          // Room Card
          echo <<<data
            <div class="col-lg-4 col-md-6 my-3">
              <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                <img src="$room_thumb" class="card-img-top" loading="lazy">
                <div class="card-body">
                  <h5>$room_data[name]</h5>
                  <h6 class="mb-4 text-muted">$room_data[price] VND / đêm</h6>
                  
                  <div class="features mb-4">
                    <h6 class="mb-1">Không gian</h6>
                    $features_data
                  </div>

                  <div class="facilities mb-4">
                    <h6 class="mb-1">Tiện nghi</h6>
                    $facilities_data
                  </div>

                  <div class="area mb-4">
                    <h6 class="mb-1">Khu vực</h6>
                    <span class="badge rounded-pill bg-light text-dark text-wrap">
                      $room_data[area]
                    </span>
                  </div>

                  $rating_data

                  <div class="d-flex justify-content-evenly mb-2">
                    $book_btn
                    <a href="room_details.php?id=$room_data[id]" class="btn btn-sm btn-outline-dark shadow-none">Chi tiết</a>
                  </div>
                </div>
              </div>
            </div>
          data;
        }
      ?>

            <div class="col-lg-12 text-center mt-5">
                <a href="rooms.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Xem thêm >>></a>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>
    <script>
    var swiper = new Swiper(".swiper-container", {
        spaceBetween: 30,
        effect: "fade",
        loop: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: false
        },
    });

    var swiper = new Swiper(".swiper-testimonials", {
        effect: "coverflow",
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: "auto",
        loop: true,
        coverflowEffect: {
            rotate: 50,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows: false,
        },
        pagination: {
            el: ".swiper-pagination"
        },
        breakpoints: {
            320: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 3
            },
        },
    });
    </script>
</body>

</html>