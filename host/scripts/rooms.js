let host_add_room_form = document.getElementById('host_add_room_form');
let host_edit_room_form = document.getElementById('host_edit_room_form');

function serializeChecked(inputs) {
  if (!inputs) {
    return [];
  }

  let list = inputs.forEach ? Array.from(inputs) : [inputs];
  let selected = [];
  list.forEach(el => {
    if (el.checked) {
      selected.push(el.value);
    }
  });
  return selected;
}

if (host_add_room_form) {
  host_add_room_form.addEventListener('submit', function (e) {
    e.preventDefault();

    let data = new FormData();
    data.append('add_room', '');
    data.append('name', host_add_room_form.elements['name'].value);
    data.append('area', host_add_room_form.elements['area'].value);
    data.append('price', host_add_room_form.elements['price'].value);
    data.append('quantity', host_add_room_form.elements['quantity'].value);
    data.append('adult', host_add_room_form.elements['adult'].value);
    data.append('children', host_add_room_form.elements['children'].value);
    data.append('desc', host_add_room_form.elements['desc'].value);

    data.append('features', JSON.stringify(serializeChecked(host_add_room_form.elements['features'])));
    data.append('facilities', JSON.stringify(serializeChecked(host_add_room_form.elements['facilities'])));

    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/rooms.php', true);

    xhr.onload = function () {
      if (this.responseText.trim() === 'invalid_session') {
        alert('error', 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!');
        window.location.href = '../index.php';
        return;
      }

      if (this.responseText.trim() === '1') {
        alert('success', 'Đăng chỗ ở thành công!');
        host_add_room_form.reset();
        let modalEl = document.getElementById('addRoomModal');
        bootstrap.Modal.getInstance(modalEl).hide();
        get_all_rooms();
      } else {
        alert('error', 'Không thể tạo chỗ ở. Vui lòng thử lại!');
      }
    };

    xhr.send(data);
  });
}

function get_all_rooms() {
  let xhr = new XMLHttpRequest();
  xhr.open('POST', 'ajax/rooms.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function () {
    if (this.responseText.trim() === 'invalid_session') {
      alert('error', 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!');
      window.location.href = '../index.php';
      return;
    }
    document.getElementById('host-room-data').innerHTML = this.responseText;
  };

  xhr.send('get_all_rooms');
}

if (document.getElementById('host-room-data')) {
  get_all_rooms();
}

function edit_room(id) {
  let xhr = new XMLHttpRequest();
  xhr.open('POST', 'ajax/rooms.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function () {
    if (this.responseText.trim() === 'invalid_session') {
      alert('error', 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!');
      window.location.href = '../index.php';
      return;
    }

    let data = JSON.parse(this.responseText);
    host_edit_room_form.elements['room_id'].value = data.roomdata.id;
    host_edit_room_form.elements['name'].value = data.roomdata.name;
    host_edit_room_form.elements['area'].value = data.roomdata.area;
    host_edit_room_form.elements['price'].value = data.roomdata.price;
    host_edit_room_form.elements['quantity'].value = data.roomdata.quantity;
    host_edit_room_form.elements['adult'].value = data.roomdata.adult;
    host_edit_room_form.elements['children'].value = data.roomdata.children;
    host_edit_room_form.elements['desc'].value = data.roomdata.description;

    host_edit_room_form.elements['features'].forEach(el => {
      el.checked = data.features.includes(Number(el.value));
    });

    host_edit_room_form.elements['facilities'].forEach(el => {
      el.checked = data.facilities.includes(Number(el.value));
    });

    let modalEl = document.getElementById('editRoomModal');
    let instance = new bootstrap.Modal(modalEl);
    instance.show();
  };

  xhr.send('get_room=' + id);
}

if (host_edit_room_form) {
  host_edit_room_form.addEventListener('submit', function (e) {
    e.preventDefault();

    let data = new FormData();
    data.append('edit_room', '');
    data.append('room_id', host_edit_room_form.elements['room_id'].value);
    data.append('name', host_edit_room_form.elements['name'].value);
    data.append('area', host_edit_room_form.elements['area'].value);
    data.append('price', host_edit_room_form.elements['price'].value);
    data.append('quantity', host_edit_room_form.elements['quantity'].value);
    data.append('adult', host_edit_room_form.elements['adult'].value);
    data.append('children', host_edit_room_form.elements['children'].value);
    data.append('desc', host_edit_room_form.elements['desc'].value);

    data.append('features', JSON.stringify(serializeChecked(host_edit_room_form.elements['features'])));
    data.append('facilities', JSON.stringify(serializeChecked(host_edit_room_form.elements['facilities'])));

    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/rooms.php', true);

    xhr.onload = function () {
      if (this.responseText.trim() === 'invalid_session') {
        alert('error', 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!');
        window.location.href = '../index.php';
        return;
      }

      if (this.responseText.trim() === '1') {
        alert('success', 'Cập nhật thông tin thành công!');
        let modalEl = document.getElementById('editRoomModal');
        bootstrap.Modal.getInstance(modalEl).hide();
        get_all_rooms();
      } else {
        alert('error', 'Không thể cập nhật chỗ ở.');
      }
    };

    xhr.send(data);
  });
}

function toggle_room_status(id, value) {
  let xhr = new XMLHttpRequest();
  xhr.open('POST', 'ajax/rooms.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function () {
    if (this.responseText.trim() === 'invalid_session') {
      alert('error', 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!');
      window.location.href = '../index.php';
      return;
    }

    if (this.responseText.trim() === '1') {
      alert('success', 'Cập nhật trạng thái thành công!');
      get_all_rooms();
    } else {
      alert('error', 'Không thể cập nhật trạng thái.');
    }
  };

  xhr.send('toggle_status=' + id + '&value=' + value);
}

function remove_room(id) {
  if (!confirm('Bạn có chắc chắn muốn xoá chỗ ở này?')) {
    return;
  }

  let xhr = new XMLHttpRequest();
  xhr.open('POST', 'ajax/rooms.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function () {
    if (this.responseText.trim() === 'invalid_session') {
      alert('error', 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!');
      window.location.href = '../index.php';
      return;
    }

    if (this.responseText.trim() === '1') {
      alert('success', 'Đã xoá chỗ ở!');
      get_all_rooms();
    } else {
      alert('error', 'Không thể xoá chỗ ở.');
    }
  };

  xhr.send('remove_room=' + id);
}
