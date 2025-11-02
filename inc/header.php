<nav id="nav-bar" class="navbar navbar-expand-lg navbar-light bg-white px-lg-3 py-lg-2 shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand me-5 fw-bold fs-3 h-font" href="index.php"><?php echo $settings_r['site_title'] ?></a>
        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link me-2" href="index.php">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="rooms.php">Danh sách phòng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="facilities.php">Tiện ích</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="contact.php">Liên hệ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">Về chúng tôi</a>
                </li>
            </ul>
            <div class="d-flex">
                <?php
          if(isset($_SESSION['login']) && $_SESSION['login']==true)
          {
            ensureHostSchema();
            $path = USERS_IMG_PATH;
            $host_menu = '';

            $host_res = select("SELECT is_host, host_status FROM `user_cred` WHERE `id`=?", [$_SESSION['uId']], 'i');
            if(mysqli_num_rows($host_res) == 1){
              $host_row = mysqli_fetch_assoc($host_res);
              $_SESSION['is_host'] = (int)$host_row['is_host'];
              $_SESSION['host_status'] = $host_row['host_status'];

              if((int)$host_row['is_host'] === 1){
                $host_menu = "<li><a class='dropdown-item' href='host/dashboard.php'>Trang nhà cung cấp</a></li>";
              }
              else if($host_row['host_status'] === 'pending'){
                $host_menu = "<li><span class='dropdown-item-text text-muted small'>Yêu cầu trở thành nhà cung cấp đang chờ duyệt</span></li>";
              }
              else{
                $host_menu = "<li><button type='button' class='dropdown-item text-primary' data-bs-toggle='modal' data-bs-target='#hostRequestModal'>Trở thành nhà cung cấp</button></li>";
              }
            }

            echo<<<data
              <div class="btn-group">
                <button type="button" class="btn btn-outline-dark shadow-none dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                  <img src="$path$_SESSION[uPic]" style="width: 25px; height: 25px;" class="me-1 rounded-circle">
                  $_SESSION[uName]
                </button>
                <ul class="dropdown-menu dropdown-menu-lg-end">
                  $host_menu
                  <li><a class="dropdown-item" href="profile.php">Hồ sơ cá nhân</a></li>
                  <li><a class="dropdown-item" href="bookings.php">Lịch sử đặt phòng</a></li>
                  <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                </ul>
              </div>
            data;
          }
          else
          {
            echo<<<data
              <button type="button" class="btn btn-outline-dark shadow-none me-lg-3 me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                Đăng nhập
              </button>
              <button type="button" class="btn btn-outline-dark shadow-none" data-bs-toggle="modal" data-bs-target="#registerModal">
                Đăng ký
              </button>
            data;
          }
        ?>
            </div>
        </div>
    </div>
</nav>

<div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="login-form">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-circle fs-3 me-2"></i> Đăng nhập
                    </h5>
                    <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email / Số điện thoại</label>
                        <input type="text" name="email_mob" required class="form-control shadow-none">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="pass" required class="form-control shadow-none">
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <button type="submit" class="btn btn-dark shadow-none">Tiếp tục</button>
                        <button type="button" class="btn text-secondary text-decoration-none shadow-none p-0"
                            data-bs-toggle="modal" data-bs-target="#forgotModal" data-bs-dismiss="modal">
                            Bạn quên mật khẩu?
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="register-form">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-lines-fill fs-3 me-2"></i> Đăng ký
                    </h5>
                    <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên</label>
                                <input name="name" type="text" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input name="email" type="email" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input name="phonenum" type="number" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ảnh đại diện</label>
                                <input name="profile" type="file" accept=".jpg, .jpeg, .png, .webp"
                                    class="form-control shadow-none">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea name="address" class="form-control shadow-none" rows="1" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã định danh</label>
                                <input name="pincode" type="number" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sinh nhật</label>
                                <input name="dob" type="date" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <input name="pass" type="password" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Xác nhận lại mật khẩu</label>
                                <input name="cpass" type="password" class="form-control shadow-none" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-center my-1">
                        <button type="submit" class="btn btn-dark shadow-none">Đăng ký</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="hostRequestModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="hostRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="host-request-form">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng ký trở thành nhà cung cấp</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên chỗ ở</label>
                            <input type="text" name="property_name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khu vực</label>
                            <input type="text" name="area" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá / đêm</label>
                            <input type="number" min="1" name="price" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số lượng phòng</label>
                            <input type="number" min="1" name="quantity" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Người lớn tối đa</label>
                            <input type="number" min="1" name="adult" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trẻ em tối đa</label>
                            <input type="number" min="0" name="children" class="form-control shadow-none" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Mô tả ngắn</label>
                            <textarea name="description" rows="3" class="form-control shadow-none" required></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Không gian nổi bật</label>
                            <div class="row">
                                <?php
                  $feature_res = selectAll('features');
                  while($feature = mysqli_fetch_assoc($feature_res)){
                    echo "<div class='col-md-4 mb-2'><div class='form-check'><input class='form-check-input' type='checkbox' name='features[]' value='{$feature['id']}' id='feature{$feature['id']}'><label class='form-check-label' for='feature{$feature['id']}'>".htmlspecialchars($feature['name'])."</label></div></div>";
                  }
                ?>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Tiện ích đi kèm</label>
                            <div class="row">
                                <?php
                  $facility_res = selectAll('facilities');
                  while($facility = mysqli_fetch_assoc($facility_res)){
                    echo "<div class='col-md-4 mb-2'><div class='form-check'><input class='form-check-input' type='checkbox' name='facilities[]' value='{$facility['id']}' id='facility{$facility['id']}'><label class='form-check-label' for='facility{$facility['id']}'>".htmlspecialchars($facility['name'])."</label></div></div>";
                  }
                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary shadow-none" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-dark shadow-none">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="forgotModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="forgot-form">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-circle fs-3 me-2"></i> Quên mật khẩu
                    </h5>
                </div>
                <div class="modal-body">
                    <span class="badge rounded-pill bg-light text-dark mb-3 text-wrap lh-base">
                        Ghi chú: Liên kết sẽ được gửi tới địa chỉ email của bạn để tạo lại mật khẩu!
                    </span>
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" required class="form-control shadow-none">
                    </div>
                    <div class="mb-2 text-end">
                        <button type="button" class="btn shadow-none p-0 me-2" data-bs-toggle="modal"
                            data-bs-target="#loginModal" data-bs-dismiss="modal">
                            Huỷ
                        </button>
                        <button type="submit" class="btn btn-dark shadow-none">Gửi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const hostForm = document.getElementById('host-request-form');
    if(hostForm){
        hostForm.addEventListener('submit', function(e){
            e.preventDefault();

            const formData = new FormData(hostForm);
            const selectedFeatures = [];
            const selectedFacilities = [];

            hostForm.querySelectorAll("input[name='features[]']:checked").forEach(function(el){
                selectedFeatures.push(el.value);
            });

            hostForm.querySelectorAll("input[name='facilities[]']:checked").forEach(function(el){
                selectedFacilities.push(el.value);
            });

            formData.append('features', JSON.stringify(selectedFeatures));
            formData.append('facilities', JSON.stringify(selectedFacilities));

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/become_host.php', true);

            xhr.onload = function(){
                if(this.responseText === 'not_logged_in'){
                    alert('error', 'Vui lòng đăng nhập trước khi gửi yêu cầu!');
                }
                else if(this.responseText === 'already_host'){
                    alert('success', 'Bạn đã là nhà cung cấp.');
                }
                else if(this.responseText === 'already_pending'){
                    alert('error', 'Yêu cầu của bạn đang được xử lý.');
                }
                else if(this.responseText === 'request_sent'){
                    alert('success', 'Đã gửi yêu cầu thành công! Vui lòng chờ admin duyệt.');
                    hostForm.reset();
                    const modalEl = document.getElementById('hostRequestModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    setTimeout(function(){ window.location.reload(); }, 1200);
                }
                else{
                    alert('error', 'Gửi yêu cầu thất bại. Vui lòng thử lại!');
                }
            };

            xhr.send(formData);
        });
    }
});
</script>