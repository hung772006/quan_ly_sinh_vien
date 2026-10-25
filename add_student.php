<?php 
require_once 'db.php';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_sv = trim($_POST['ma_sv']);
    $ho_ten = trim($_POST['ho_ten']);
    $ngay_sinh = $_POST['ngay_sinh'];
    $gioi_tinh = $_POST['gioi_tinh'];
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $dia_chi = trim($_POST['dia_chi']);

    // Chức năng: Kiểm tra sự tồn tại của Mã SV
    $check_sql = "SELECT COUNT(*) FROM sinhvien WHERE ma_sv = :ma_sv";
    $stmt_check = $pdo->prepare($check_sql);
    $stmt_check->execute(['ma_sv' => $ma_sv]);
    $exists = $stmt_check->fetchColumn();

    if ($exists > 0) {
        $message = "<div class='alert alert-danger'>Lỗi: Mã sinh viên '$ma_sv' đã tồn tại trong hệ thống!</div>";
    } else {
        // Chức năng: Thêm mới hồ sơ
        $insert_sql = "INSERT INTO sinhvien (ma_sv, ho_ten, ngay_sinh, gioi_tinh, so_dien_thoai, dia_chi) 
                       VALUES (:ma_sv, :ho_ten, :ngay_sinh, :gioi_tinh, :so_dien_thoai, :dia_chi)";
        $stmt_insert = $pdo->prepare($insert_sql);
        
        try {
            $stmt_insert->execute([
                'ma_sv' => $ma_sv, 'ho_ten' => $ho_ten, 'ngay_sinh' => $ngay_sinh,
                'gioi_tinh' => $gioi_tinh, 'so_dien_thoai' => $so_dien_thoai, 'dia_chi' => $dia_chi
            ]);
            $message = "<div class='alert alert-success'>Thêm sinh viên thành công!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Lỗi hệ thống: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h3 class="mb-4">Thêm hồ sơ sinh viên</h3>
        <?= $message ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Mã sinh viên</label>
                <input type="text" name="ma_sv" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="ho_ten" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày sinh</label>
                <input type="date" name="ngay_sinh" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Giới tính</label>
                <select name="gioi_tinh" class="form-select">
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="so_dien_thoai" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="dia_chi" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Lưu hồ sơ</button>
            <a href="index.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>