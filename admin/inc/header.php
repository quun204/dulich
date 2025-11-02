<div class="container-fluid bg-dark text-light p-3 d-flex align-items-center justify-content-between sticky-top">
    <h5 class="mb-0 fw-bold h-font">dulich</h3>
        <a href="logout.php" class="btn btn-light btn-sm">Đăng xuất</a>
</div>

<div class="col-lg-2 bg-dark border-top border-3 border-secondary" id="dashboard-menu">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid flex-lg-column align-items-stretch">
            <h4 class="mt-2 text-light">Trang quản lý</h4>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#adminDropdown" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="adminDropdown">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="dashboard.php">Bảng theo dõi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="hosts.php">Quản lý nhà cung cấp</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="users.php">Người dùng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="user_queries.php">Thông báo & Tin nhắn</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="rate_review.php">Đánh giá</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>