<?php 
require_once 'db.php';
$message = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_sv = trim($_POST['ma_sv']);
    $ho_ten = trim($_POST['ho_ten']);
    $ngay_sinh = trim($_POST['ngay_sinh']);
    $lop = trim($_POST['lop']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $email = trim($_POST['email']);

    // Validate Mã SV
    if (empty($ma_sv)) {
        $errors['ma_sv'] = "Mã SV không được để trống";
    } elseif (!preg_match('/^SV\d{6}$/', $ma_sv)) {
        $errors['ma_sv'] = "Mã SV phải có dạng SV + 6 chữ số";
    } else {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM sinhvien WHERE ma_sv = :ma_sv");
        $stmt_check->execute(['ma_sv' => $ma_sv]);
        if ($stmt_check->fetchColumn() > 0) $errors['ma_sv'] = "Mã SV đã tồn tại";
    }

    // Validate Họ tên
    if (empty($ho_ten)) {
        $errors['ho_ten'] = "Họ tên không được để trống";
    } elseif (preg_match('/\d/', $ho_ten) || mb_strlen($ho_ten) < 2 || mb_strlen($ho_ten) > 50) {
        $errors['ho_ten'] = "Họ tên phải từ 2–50 ký tự, không chứa chữ số";
    }

    // Validate Ngày sinh
    if (empty($ngay_sinh)) {
        $errors['ngay_sinh'] = "Ngày sinh phải đúng định dạng yyyy-mm-dd";
    } else {
        // Tách năm, tháng, ngày để kiểm tra lịch hợp lệ (tránh lỗi 30/02 hoặc 31/04)
        $date_parts = explode('-', $ngay_sinh);
        if (count($date_parts) === 3 && checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
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
        } else {
            $errors['ngay_sinh'] = "Ngày sinh phải đúng định dạng yyyy-mm-dd";
        }
    }

    // Validate Lớp
    if (empty($lop)) {
        $errors['lop'] = "Lớp không được để trống";
    } elseif (!preg_match('/^[a-zA-Z0-9]{2,20}$/', $lop)) {
        $errors['lop'] = "Lớp phải từ 2–20 ký tự chữ hoặc số, không chứa khoảng trắng";
    }

    // Validate Số điện thoại
    if (empty($so_dien_thoai)) {
        $errors['so_dien_thoai'] = "Số điện thoại không được để trống";
    } elseif (!preg_match('/^0\d{9}$/', $so_dien_thoai)) {
        $errors['so_dien_thoai'] = "Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0";
    }

    // Validate Email
    if (empty($email)) {
        $errors['email'] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email không đúng định dạng";
    } else {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM sinhvien WHERE email = :email");
        $stmt_check->execute(['email' => $email]);
        if ($stmt_check->fetchColumn() > 0) $errors['email'] = "Email đã tồn tại";
    }

    // Nếu không có lỗi, thực hiện Insert
    if (empty($errors)) {
        $insert_sql = "INSERT INTO sinhvien (ma_sv, ho_ten, ngay_sinh, lop, so_dien_thoai, email) 
                       VALUES (:ma_sv, :ho_ten, :ngay_sinh, :lop, :so_dien_thoai, :email)";
        $stmt_insert = $pdo->prepare($insert_sql);
        try {
            $stmt_insert->execute([
                'ma_sv' => $ma_sv, 'ho_ten' => $ho_ten, 'ngay_sinh' => $ngay_sinh,
                'lop' => $lop, 'so_dien_thoai' => $so_dien_thoai, 'email' => $email
            ]);
            $message = "<div class='alert alert-success'>Thêm hồ sơ thành công</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Lỗi hệ thống: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 bg-white p-4 rounded shadow-sm">
        <h3 class="mb-4 text-center">Thêm hồ sơ sinh viên</h3>
        <?= $message ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Mã sinh viên</label>
                <input type="text" name="ma_sv" class="form-control <?= isset($errors['ma_sv']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['ma_sv'] ?? '') ?>">
                <?php if(isset($errors['ma_sv'])): ?><div class="invalid-feedback"><?= $errors['ma_sv'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="ho_ten" class="form-control <?= isset($errors['ho_ten']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>">
                <?php if(isset($errors['ho_ten'])): ?><div class="invalid-feedback"><?= $errors['ho_ten'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày sinh</label>
                <input type="date" name="ngay_sinh" class="form-control <?= isset($errors['ngay_sinh']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['ngay_sinh'] ?? '') ?>">
                <?php if(isset($errors['ngay_sinh'])): ?><div class="invalid-feedback"><?= $errors['ngay_sinh'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Lớp</label>
                <input type="text" name="lop" class="form-control <?= isset($errors['lop']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['lop'] ?? '') ?>">
                <?php if(isset($errors['lop'])): ?><div class="invalid-feedback"><?= $errors['lop'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="so_dien_thoai" class="form-control <?= isset($errors['so_dien_thoai']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['so_dien_thoai'] ?? '') ?>">
                <?php if(isset($errors['so_dien_thoai'])): ?><div class="invalid-feedback"><?= $errors['so_dien_thoai'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-4">Thêm</button>
                <a href="index.php" class="btn btn-secondary px-4">Hủy</a>
            </div>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>