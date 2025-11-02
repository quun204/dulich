<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-3">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">Bảng điều khiển Host</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hostNavbar" aria-controls="hostNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="hostNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0" id="dashboard-menu">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">Tổng quan</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="rooms.php">Chỗ ở của tôi</a>
        </li>
      </ul>
      <div class="d-flex align-items-center text-white">
        <span class="me-3"><i class="bi bi-person-circle me-2"></i><?php echo $_SESSION['uName']; ?></span>
        <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Trang chủ</a>
        <a href="../logout.php" class="btn btn-warning btn-sm">Đăng xuất</a>
      </div>
    </div>
  </div>
</nav>
