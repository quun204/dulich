<?php
// db_config.php
// Kết nối DB + các hàm DB tiện ích
// -- Thay thế toàn bộ file hiện tại bằng file này

// Database credentials
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'dulich';

// Bật báo lỗi mysqli (tuỳ chọn, hữu ích khi debug)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    // Thiết lập charset
    mysqli_set_charset($con, 'utf8mb4');
} catch (Exception $e) {
    // Lỗi kết nối
    // Trong production, bạn nên log lỗi thay vì in ra màn hình
    die("Cannot connect to database: " . $e->getMessage());
}

/**
 * filteration
 * - Nếu truyền mảng, trả về mảng đã lọc
 * - Nếu truyền chuỗi, trả về chuỗi đã lọc
 * - Không gây lỗi nếu gọi nhiều lần (kiểm tra trước khi định nghĩa)
 */
if (!function_exists('filteration')) {
    function filteration($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // đảm bảo string
                if (is_string($value)) {
                    $value = trim($value);
                    $value = stripslashes($value);
                    $value = strip_tags($value);
                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
                $data[$key] = $value;
            }
            return $data;
        } else {
            // scalar
            $value = $data;
            if (is_string($value)) {
                $value = trim($value);
                $value = stripslashes($value);
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            return $value;
        }
    }
}

/**
 * selectAll: SELECT * FROM $table
 * Trả về mysqli_result hoặc false
 */
if (!function_exists('selectAll')) {
    function selectAll($table) {
        global $con;
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table); // basic safety
        $sql = "SELECT * FROM `$table`";
        return mysqli_query($con, $sql);
    }
}

/**
 * select: prepared select
 * $sql: câu SQL có dấu ? placeholders
 * $values: mảng giá trị
 * $datatypes: chuỗi datatypes như 'ssi'
 * Trả về mysqli_result
 */
if (!function_exists('select')) {
    function select($sql, $values = [], $datatypes = '') {
        global $con;
        if ($values && $datatypes === '') {
            // Nếu có values nhưng chưa truyền datatypes, cố tự suy đoán (an toàn hơn nên cung cấp datatypes)
            $datatypes = str_repeat('s', count($values));
        }
        if ($stmt = mysqli_prepare($con, $sql)) {
            if ($values) {
                mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        } else {
            // Thông báo lỗi useful cho debug. Trong production, log thay vì die.
            die("Query cannot be prepared - Select. SQL: $sql");
        }
    }
}

/**
 * insert: chạy INSERT/UPDATE/DELETE dạng prepared
 * Trả về số hàng ảnh hưởng hoặc false
 */
if (!function_exists('insert')) {
    function insert($sql, $values = [], $datatypes = '') {
        global $con;
        if ($values && $datatypes === '') {
            $datatypes = str_repeat('s', count($values));
        }
        if ($stmt = mysqli_prepare($con, $sql)) {
            if ($values) {
                mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
            }
            $ok = mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $ok ? $affected : 0;
        } else {
            die("Query cannot be prepared - Insert/Update/Delete. SQL: $sql");
        }
    }
}

/**
 * update: wrapper (giữ tương thích cũ)
 */
if (!function_exists('update')) {
    function update($sql, $values = [], $datatypes = '') {
        return insert($sql, $values, $datatypes);
    }
}

/**
 * delete: wrapper (giữ tương thích cũ)
 */
if (!function_exists('delete')) {
    function delete($sql, $values = [], $datatypes = '') {
        return insert($sql, $values, $datatypes);
    }
}
?>