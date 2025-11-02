<?php
$host_account = hostLogin();
?>
<div class="container-fluid bg-dark text-light p-3 d-flex align-items-center justify-content-between sticky-top">
    <h5 class="mb-0 fw-bold h-font">Khu vực Nhà cung cấp</h5>
    <div class="d-flex align-items-center">
        <span class="me-3">Xin chào, <?php echo htmlspecialchars($_SESSION['uName'] ?? $host_account['name']); ?></span>
        <a href="../logout.php" class="btn btn-light btn-sm">Đăng xuất</a>
    </div>
</div>

<div class="col-lg-2 bg-dark border-top border-3 border-secondary" id="dashboard-menu">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid flex-lg-column align-items-stretch">
            <h4 class="mt-2 text-light">Bảng điều khiển</h4>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#hostDropdown" aria-controls="hostDropdown" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="hostDropdown">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="dashboard.php">Tổng quan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="bookings.php">Yêu cầu đặt chỗ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="rooms.php">Chỗ ở của tôi</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>
