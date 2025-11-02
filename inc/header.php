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
            $path = USERS_IMG_PATH;
            $hostStatus = $_SESSION['hostStatus'] ?? null;
            $isHost = !empty($_SESSION['isHost']);

            if($isHost){
              $hostMenuItems = '<li><a class="dropdown-item" href="admin/dashboard.php" target="_blank">Trang Host</a></li>';
            } else {
              if($hostStatus === 'pending'){
                $hostMenuItems = '<li><span class="dropdown-item text-muted">Yêu cầu Host đang chờ duyệt</span></li>';
              } else {
                $hostMenuItems = '<li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#hostRequestModal">Yêu cầu trở thành Host</button></li>';
                if($hostStatus === 'rejected'){
                  $hostMenuItems .= '<li><span class="dropdown-item small text-danger">Yêu cầu trước đã bị từ chối. Vui lòng gửi lại.</span></li>';
                }
              }
            }
            echo<<<data
              <div class="btn-group">
                <button type="button" class="btn btn-outline-dark shadow-none dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                  <img src="$path$_SESSION[uPic]" style="width: 25px; height: 25px;" class="me-1 rounded-circle">
                  $_SESSION[uName]
                </button>
                <ul class="dropdown-menu dropdown-menu-lg-end">
                  <li><a class="dropdown-item" href="profile.php">Hồ sơ cá nhân</a></li>
                  <li><a class="dropdown-item" href="bookings.php">Lịch sử đặt phòng</a></li>
                  $hostMenuItems
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
<?php
  $featureOptions = selectAll('features');
  $facilityOptions = selectAll('facilities');
?>

<div class="modal fade" id="hostRequestModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="hostRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="host-request-form" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="hostRequestLabel">
                        <i class="bi bi-house-add fs-3 me-2"></i> Yêu cầu trở thành Host
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tên chỗ ở</label>
                            <input type="text" name="property_name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Khu vực</label>
                            <input type="text" name="area" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giá mỗi đêm</label>
                            <input type="number" name="price" min="1" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Số lượng phòng</label>
                            <input type="number" name="quantity" min="1" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Người lớn tối đa</label>
                            <input type="number" name="adult" min="1" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Trẻ em tối đa</label>
                            <input type="number" name="children" min="0" class="form-control shadow-none" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Mô tả chi tiết</label>
                            <textarea name="description" rows="3" class="form-control shadow-none" required></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Không gian</label>
                            <div class="row">
                                <?php if($featureOptions){ while($feature = mysqli_fetch_assoc($featureOptions)){ ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="<?php echo $feature['id']; ?>"
                                                id="feature_<?php echo $feature['id']; ?>">
                                            <label class="form-check-label" for="feature_<?php echo $feature['id']; ?>"><?php echo $feature['name']; ?></label>
                                        </div>
                                    </div>
                                <?php }} ?>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Tiện nghi</label>
                            <div class="row">
                                <?php if($facilityOptions){ while($facility = mysqli_fetch_assoc($facilityOptions)){ ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="facilities[]" value="<?php echo $facility['id']; ?>"
                                                id="facility_<?php echo $facility['id']; ?>">
                                            <label class="form-check-label" for="facility_<?php echo $facility['id']; ?>"><?php echo $facility['name']; ?></label>
                                        </div>
                                    </div>
                                <?php }} ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn text-secondary shadow-none" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-dark shadow-none">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const hostRequestForm = document.getElementById('host-request-form');
if (hostRequestForm) {
    hostRequestForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(hostRequestForm);
        const selectedFeatures = [];
        const selectedFacilities = [];

        hostRequestForm.querySelectorAll('input[name="features[]"]:checked').forEach((input) => {
            selectedFeatures.push(input.value);
        });

        hostRequestForm.querySelectorAll('input[name="facilities[]"]:checked').forEach((input) => {
            selectedFacilities.push(input.value);
        });

        formData.append('features', JSON.stringify(selectedFeatures));
        formData.append('facilities', JSON.stringify(selectedFacilities));

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax/become_host.php', true);

        xhr.onload = function () {
            const response = this.responseText.trim();
            if (response === 'not_logged_in') {
                alert('Bạn cần đăng nhập trước!');
            } else if (response === 'already_requested') {
                alert('Bạn đã gửi yêu cầu trước đó và đang được xử lý!');
            } else if (response === 'already_host') {
                alert('Tài khoản của bạn đã là Host!');
            } else if (response === 'invalid_input') {
                alert('Vui lòng kiểm tra lại thông tin và thử lại!');
            } else if (response === 'request_sent') {
                alert('Yêu cầu đã được gửi. Vui lòng chờ quản trị viên duyệt!');
                hostRequestForm.reset();
                const modalElement = document.getElementById('hostRequestModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
                location.reload();
            } else {
                alert('Gửi yêu cầu thất bại! Vui lòng thử lại.');
            }
        };

        xhr.send(formData);
    });
}
</script>