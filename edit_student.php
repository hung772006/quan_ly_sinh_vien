<?php
require_once 'db.php';
$message = '';
$errors = [];

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$ma_sv = $_GET['id'];

// Lấy dữ liệu cũ để điền vào form
$stmt = $pdo->prepare("SELECT * FROM sinhvien WHERE ma_sv = :ma_sv");
$stmt->execute(['ma_sv' => $ma_sv]);
$sv = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sv) die("Không tìm thấy hồ sơ.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ho_ten = trim($_POST['ho_ten']);
    $ngay_sinh = trim($_POST['ngay_sinh']);
    $lop = trim($_POST['lop']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $email = trim($_POST['email']);

    if (empty($ho_ten)) {
        $errors['ho_ten'] = "Họ tên không được để trống";
    } elseif (preg_match('/\d/', $ho_ten) || mb_strlen($ho_ten) < 2 || mb_strlen($ho_ten) > 50) {
        $errors['ho_ten'] = "Họ tên phải từ 2–50 ký tự, không chứa chữ số";
    }

    if (empty($ngay_sinh)) {
        $errors['ngay_sinh'] = "Ngày sinh phải đúng định dạng yyyy-mm-dd";
    } else {
        $dob = new DateTime($ngay_sinh);
        $now = new DateTime();
        if ($dob > $now) {
            $errors['ngay_sinh'] = "Ngày sinh không được lớn hơn ngày hiện tại";
        } else {
            $age = $now->diff($dob)->y;
            if ($age < 16 || $age > 40) {
                $errors['ngay_sinh'] = "Tuổi phải trong khoảng 16–40";
            }
        }
    }

    if (empty($lop)) {
        $errors['lop'] = "Lớp không được để trống";
    } elseif (!preg_match('/^[a-zA-Z0-9]{2,20}$/', $lop)) {
        $errors['lop'] = "Lớp phải từ 2–20 ký tự chữ hoặc số, không chứa khoảng trắng";
    }

    if (empty($so_dien_thoai)) {
        $errors['so_dien_thoai'] = "Số điện thoại không được để trống";
    } elseif (!preg_match('/^0\d{9}$/', $so_dien_thoai)) {
        $errors['so_dien_thoai'] = "Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0";
    }

    if (empty($email)) {
        $errors['email'] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email không đúng định dạng";
    } else {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM sinhvien WHERE email = :email AND ma_sv != :ma_sv");
        $stmt_check->execute(['email' => $email, 'ma_sv' => $ma_sv]);
        if ($stmt_check->fetchColumn() > 0) $errors['email'] = "Email đã tồn tại";
    }

    if (empty($errors)) {
        $update_sql = "UPDATE sinhvien SET ho_ten = :ho_ten, ngay_sinh = :ngay_sinh, 
                       lop = :lop, so_dien_thoai = :so_dien_thoai, email = :email WHERE ma_sv = :ma_sv";
        $stmt_upd = $pdo->prepare($update_sql);
        try {
            $stmt_upd->execute([
                'ho_ten' => $ho_ten, 'ngay_sinh' => $ngay_sinh, 'lop' => $lop,
                'so_dien_thoai' => $so_dien_thoai, 'email' => $email, 'ma_sv' => $ma_sv
            ]);
            $message = "<div class='alert alert-success'>Cập nhật hồ sơ thành công</div>";
            $sv = ['ma_sv'=>$ma_sv, 'ho_ten'=>$ho_ten, 'ngay_sinh'=>$ngay_sinh, 'lop'=>$lop, 'so_dien_thoai'=>$so_dien_thoai, 'email'=>$email]; // Cập nhật lại form
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Lỗi cập nhật: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<?php include 'header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 bg-white p-4 rounded shadow-sm">
        <h3 class="mb-4 text-center">Sửa hồ sơ sinh viên</h3>
        <?= $message ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Mã sinh viên</label>
                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($sv['ma_sv']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="ho_ten" class="form-control <?= isset($errors['ho_ten']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['ho_ten'] ?? $sv['ho_ten']) ?>">
                <?php if(isset($errors['ho_ten'])): ?><div class="invalid-feedback"><?= $errors['ho_ten'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày sinh</label>
                <input type="date" name="ngay_sinh" class="form-control <?= isset($errors['ngay_sinh']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['ngay_sinh'] ?? $sv['ngay_sinh']) ?>">
                <?php if(isset($errors['ngay_sinh'])): ?><div class="invalid-feedback"><?= $errors['ngay_sinh'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Lớp</label>
                <input type="text" name="lop" class="form-control <?= isset($errors['lop']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['lop'] ?? $sv['lop']) ?>">
                <?php if(isset($errors['lop'])): ?><div class="invalid-feedback"><?= $errors['lop'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="so_dien_thoai" class="form-control <?= isset($errors['so_dien_thoai']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['so_dien_thoai'] ?? $sv['so_dien_thoai']) ?>">
                <?php if(isset($errors['so_dien_thoai'])): ?><div class="invalid-feedback"><?= $errors['so_dien_thoai'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['email'] ?? $sv['email']) ?>">
                <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-4">Lưu</button>
                <a href="index.php" class="btn btn-secondary px-4">Hủy</a>
            </div>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>