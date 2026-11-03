<?php
session_start();
require_once 'db.php';
$message = '';
$errors = [];

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$ma_sv = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM sinhvien WHERE ma_sv = :ma_sv");
$stmt->execute(['ma_sv' => $ma_sv]);
$sv = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sv) {
    $_SESSION['msg'] = "Không tìm thấy hồ sơ.";
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
    $lop = trim($_POST['lop'] ?? '');
    $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($ho_ten)) {
        $errors['ho_ten'] = "Họ tên không được để trống";
    } elseif (preg_match('/\d/', $ho_ten) || mb_strlen($ho_ten) < 2 || mb_strlen($ho_ten) > 50) {
        $errors['ho_ten'] = "Họ tên phải từ 2–50 ký tự, không chứa chữ số";
    }

    if (empty($ngay_sinh)) {
        $errors['ngay_sinh'] = "Ngày sinh phải đúng định dạng yyyy-mm-dd";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngay_sinh)) {
        $errors['ngay_sinh'] = "Ngày sinh phải đúng định dạng yyyy-mm-dd";
    } else {
        $date_parts = explode('-', $ngay_sinh);
        if (count($date_parts) === 3 && checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
            $dob = new DateTime($ngay_sinh);
            $now = new DateTime('today');
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
            $_SESSION['msg'] = "Cập nhật hồ sơ thành công";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $message = "<div id='msgAlert' class='alert alert-danger'>Lỗi cập nhật: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<?php include 'header.php'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="form-header-banner d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2 fs-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold fs-4">Sửa hồ sơ sinh viên</h3>
                    <div class="small opacity-75">Cập nhật thông tin cho mã sinh viên: <strong><?= htmlspecialchars($sv['ma_sv']) ?></strong></div>
                </div>
            </div>
            <div class="p-4 p-md-5">
                <?= $message ?>
                <form method="POST" action="" id="formEditStudent">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-upc-scan me-1 text-secondary"></i>Mã sinh viên</label>
                            <input type="text" id="ma_sv" class="form-control bg-light fw-bold" value="<?= htmlspecialchars($sv['ma_sv']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-person-badge me-1 text-primary"></i>Họ và tên</label>
                            <input type="text" id="ho_ten" name="ho_ten" class="form-control <?= isset($errors['ho_ten']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['ho_ten'] ?? $sv['ho_ten']) ?>">
                            <?php if(isset($errors['ho_ten'])): ?><div id="err_ho_ten" class="invalid-feedback"><?= $errors['ho_ten'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-calendar-event me-1 text-primary"></i>Ngày sinh (yyyy-mm-dd)</label>
                            <input type="text" id="ngay_sinh" name="ngay_sinh" placeholder="yyyy-mm-dd" class="form-control <?= isset($errors['ngay_sinh']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['ngay_sinh'] ?? $sv['ngay_sinh']) ?>">
                            <?php if(isset($errors['ngay_sinh'])): ?><div id="err_ngay_sinh" class="invalid-feedback"><?= $errors['ngay_sinh'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-building me-1 text-primary"></i>Lớp</label>
                            <input type="text" id="lop" name="lop" class="form-control <?= isset($errors['lop']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['lop'] ?? $sv['lop']) ?>">
                            <?php if(isset($errors['lop'])): ?><div id="err_lop" class="invalid-feedback"><?= $errors['lop'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-telephone me-1 text-primary"></i>Số điện thoại</label>
                            <input type="text" id="so_dien_thoai" name="so_dien_thoai" class="form-control <?= isset($errors['so_dien_thoai']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['so_dien_thoai'] ?? $sv['so_dien_thoai']) ?>">
                            <?php if(isset($errors['so_dien_thoai'])): ?><div id="err_so_dien_thoai" class="invalid-feedback"><?= $errors['so_dien_thoai'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-envelope-at me-1 text-primary"></i>Email</label>
                            <input type="text" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['email'] ?? $sv['email']) ?>">
                            <?php if(isset($errors['email'])): ?><div id="err_email" class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
                        </div>
                    </div>
                    <hr class="my-4 text-muted opacity-25">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" id="btnHuy" class="btn btn-secondary px-4">Hủy</a>
                        <button type="submit" id="btnLuu" class="btn btn-primary px-4">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>