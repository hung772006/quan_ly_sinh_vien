<?php
session_start();
require_once 'db.php';
$message = '';
$errors = [];

$ma_sv = $_GET['ma_sv'] ?? '';
$ma_mh = $_GET['ma_mh'] ?? '';

if (empty($ma_sv) || empty($ma_mh)) {
    $_SESSION['err_msg'] = "Không tìm thấy bản ghi điểm";
    header("Location: grades.php");
    exit();
}

// Kiểm tra bản ghi điểm có tồn tại không
$stmt = $pdo->prepare("SELECT * FROM diem WHERE ma_sv = :ma_sv AND ma_mh = :ma_mh");
$stmt->execute(['ma_sv' => $ma_sv, 'ma_mh' => $ma_mh]);
$diem_row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$diem_row) {
    $_SESSION['err_msg'] = "Không tìm thấy bản ghi điểm";
    header("Location: grades.php");
    exit();
}

function validateScoreEdit($score_str, $field_label) {
    if ($score_str === '') {
        return "$field_label không được để trống";
    }
    if (!is_numeric($score_str) || !preg_match('/^-?\d+(\.\d+)?$/', $score_str)) {
        return "$field_label phải là số từ 0 đến 10, tối đa 1 chữ số thập phân";
    }
    $val = (float)$score_str;
    if ($val < 0 || $val > 10) {
        return "$field_label phải là số từ 0 đến 10, tối đa 1 chữ số thập phân";
    }
    if (!preg_match('/^\d+(\.\d)?$/', $score_str)) {
        return "$field_label phải là số từ 0 đến 10, tối đa 1 chữ số thập phân";
    }
    return '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $today = date('Y-m-d');
    
    // Kiểm tra xem bản ghi điểm đã quá hạn khóa chưa
    if ($today > $diem_row['han_khoa_diem']) {
        $pdo->prepare("UPDATE diem SET trang_thai = 'Đã khóa' WHERE ma_sv = :ma_sv AND ma_mh = :ma_mh")
            ->execute(['ma_sv' => $ma_sv, 'ma_mh' => $ma_mh]);
        $message = "<div id='errAlert' class='alert alert-danger'>Điểm đã bị khóa, không thể chỉnh sửa</div>";
    } else {
        $diem_giua_ky = trim($_POST['diem_giua_ky'] ?? '');
        $diem_cuoi_ky = trim($_POST['diem_cuoi_ky'] ?? '');

        $err_gk = validateScoreEdit($diem_giua_ky, "Điểm giữa kỳ");
        if ($err_gk !== '') {
            $errors['diem_giua_ky'] = $err_gk;
        }

        $err_ck = validateScoreEdit($diem_cuoi_ky, "Điểm cuối kỳ");
        if ($err_ck !== '') {
            $errors['diem_cuoi_ky'] = $err_ck;
        }

        if (empty($errors)) {
            $gk = (float)$diem_giua_ky;
            $ck = (float)$diem_cuoi_ky;
            $diem_tong_ket = round($gk * 0.3 + $ck * 0.7, 1);

            $upd_sql = "UPDATE diem 
                        SET diem_giua_ky = :gk, diem_cuoi_ky = :ck, diem_tong_ket = :tk, trang_thai = 'Chưa khóa'
                        WHERE ma_sv = :ma_sv AND ma_mh = :ma_mh";
            $stmt_upd = $pdo->prepare($upd_sql);
            try {
                $stmt_upd->execute([
                    'gk' => $gk,
                    'ck' => $ck,
                    'tk' => $diem_tong_ket,
                    'ma_sv' => $ma_sv,
                    'ma_mh' => $ma_mh
                ]);
                $_SESSION['msg'] = "Cập nhật điểm thành công";
                header("Location: grades.php");
                exit();
            } catch (PDOException $e) {
                $message = "<div id='errAlert' class='alert alert-danger'>Lỗi cập nhật: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 bg-white p-4 rounded shadow-sm">
        <h3 class="mb-4 text-center">Sửa điểm sinh viên</h3>
        <?= $message ?>
        <form method="POST" action="" id="formEditGrade">
            <div class="mb-3">
                <label class="form-label">Mã sinh viên</label>
                <input type="text" id="ma_sv" class="form-control bg-light" value="<?= htmlspecialchars($diem_row['ma_sv']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Mã môn học</label>
                <input type="text" id="ma_mh" class="form-control bg-light" value="<?= htmlspecialchars($diem_row['ma_mh']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Điểm giữa kỳ</label>
                <input type="text" id="diem_giua_ky" name="diem_giua_ky" class="form-control <?= isset($errors['diem_giua_ky']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['diem_giua_ky'] ?? number_format((float)$diem_row['diem_giua_ky'], 1, '.', '')) ?>">
                <?php if(isset($errors['diem_giua_ky'])): ?><div id="err_diem_giua_ky" class="invalid-feedback"><?= $errors['diem_giua_ky'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Điểm cuối kỳ</label>
                <input type="text" id="diem_cuoi_ky" name="diem_cuoi_ky" class="form-control <?= isset($errors['diem_cuoi_ky']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['diem_cuoi_ky'] ?? number_format((float)$diem_row['diem_cuoi_ky'], 1, '.', '')) ?>">
                <?php if(isset($errors['diem_cuoi_ky'])): ?><div id="err_diem_cuoi_ky" class="invalid-feedback"><?= $errors['diem_cuoi_ky'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Điểm tổng kết (Hệ thống tự tính)</label>
                <input type="text" id="diem_tong_ket" class="form-control bg-light" value="<?= number_format((float)$diem_row['diem_tong_ket'], 1, '.', '') ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Hạn khóa điểm</label>
                <input type="text" id="han_khoa_diem" class="form-control bg-light" value="<?= htmlspecialchars($diem_row['han_khoa_diem']) ?>" disabled>
            </div>
            <div class="text-center mt-4">
                <button type="submit" id="btnLuuDiem" class="btn btn-primary px-4">Lưu</button>
                <a href="grades.php" id="btnHuyDiem" class="btn btn-secondary px-4">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>