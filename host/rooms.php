<?php
require('inc/essentials.php');
require('../admin/inc/db_config.php');
hostLogin();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chỗ ở của tôi</title>
  <?php require('inc/links.php'); ?>
</head>
<body>
  <?php require('inc/header.php'); ?>
  <main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">Chỗ ở của tôi</h4>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
        <i class="bi bi-plus-circle me-2"></i>Đăng chỗ ở mới
      </button>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle text-center">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Tên chỗ ở</th>
                <th>Khu vực</th>
                <th>Sức chứa</th>
                <th>Giá / đêm</th>
                <th>Số lượng</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody id="host-room-data"></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal thêm mới -->
  <div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="host_add_room_form" autocomplete="off">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Đăng chỗ ở mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Tên chỗ ở</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Khu vực</label>
                <input type="text" name="area" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Giá (VND)</label>
                <input type="number" name="price" min="1" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Số lượng</label>
                <input type="number" name="quantity" min="1" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Người lớn tối đa</label>
                <input type="number" name="adult" min="1" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Trẻ em tối đa</label>
                <input type="number" name="children" min="0" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Không gian</label>
                <div class="row">
                  <?php
                  $res = selectAll('features');
                  while($opt = mysqli_fetch_assoc($res)){
                    echo "<div class='col-md-3 mb-2'>
                      <div class='form-check'>
                        <input class='form-check-input' type='checkbox' name='features' value='{$opt['id']}'>
                        <label class='form-check-label'>{$opt['name']}</label>
                      </div>
                    </div>";
                  }
                  ?>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Tiện ích</label>
                <div class="row">
                  <?php
                  $res = selectAll('facilities');
                  while($opt = mysqli_fetch_assoc($res)){
                    echo "<div class='col-md-3 mb-2'>
                      <div class='form-check'>
                        <input class='form-check-input' type='checkbox' name='facilities' value='{$opt['id']}'>
                        <label class='form-check-label'>{$opt['name']}</label>
                      </div>
                    </div>";
                  }
                  ?>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Mô tả chi tiết</label>
                <textarea name="desc" rows="4" class="form-control" required></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
            <button type="submit" class="btn btn-primary">Đăng chỗ ở</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal chỉnh sửa -->
  <div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="host_edit_room_form" autocomplete="off">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cập nhật thông tin chỗ ở</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="room_id">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Tên chỗ ở</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Khu vực</label>
                <input type="text" name="area" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Giá (VND)</label>
                <input type="number" name="price" min="1" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Số lượng</label>
                <input type="number" name="quantity" min="1" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Người lớn tối đa</label>
                <input type="number" name="adult" min="1" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Trẻ em tối đa</label>
                <input type="number" name="children" min="0" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Không gian</label>
                <div class="row" id="edit-features-wrapper">
                  <?php
                  $res = selectAll('features');
                  while($opt = mysqli_fetch_assoc($res)){
                    echo "<div class='col-md-3 mb-2'>
                      <div class='form-check'>
                        <input class='form-check-input' type='checkbox' name='features' value='{$opt['id']}'>
                        <label class='form-check-label'>{$opt['name']}</label>
                      </div>
                    </div>";
                  }
                  ?>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Tiện ích</label>
                <div class="row" id="edit-facilities-wrapper">
                  <?php
                  $res = selectAll('facilities');
                  while($opt = mysqli_fetch_assoc($res)){
                    echo "<div class='col-md-3 mb-2'>
                      <div class='form-check'>
                        <input class='form-check-input' type='checkbox' name='facilities' value='{$opt['id']}'>
                        <label class='form-check-label'>{$opt['name']}</label>
                      </div>
                    </div>";
                  }
                  ?>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Mô tả chi tiết</label>
                <textarea name="desc" rows="4" class="form-control" required></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php require('inc/footer.php'); ?>
  <?php require('inc/scripts.php'); ?>
  <script src="scripts/rooms.js"></script>
</body>
</html>
