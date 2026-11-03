<?php
session_start();
require_once 'db.php';
$message = '';
$errors = [];

// Hàm kiểm tra điểm hợp lệ (số từ 0 đến 10, tối đa 1 chữ số thập phân)
function validateScore($score_str, $field_label) {
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
    $ma_sv = trim($_POST['ma_sv'] ?? '');
    $ma_mh = trim($_POST['ma_mh'] ?? '');
    $diem_giua_ky = trim($_POST['diem_giua_ky'] ?? '');
    $diem_cuoi_ky = trim($_POST['diem_cuoi_ky'] ?? '');
    $han_khoa_diem = trim($_POST['han_khoa_diem'] ?? '');

    // 1. Validate Mã sinh viên
    $sv_exists = false;
    if (empty($ma_sv)) {
        $errors['ma_sv'] = "Mã sinh viên không được để trống";
    } elseif (!preg_match('/^SV\d{6}$/', $ma_sv)) {
        $errors['ma_sv'] = "Mã sinh viên không tồn tại";
    } else {
        $stmt_sv = $pdo->prepare("SELECT COUNT(*) FROM sinhvien WHERE ma_sv = :ma_sv");
        $stmt_sv->execute(['ma_sv' => $ma_sv]);
        if ($stmt_sv->fetchColumn() == 0) {
            $errors['ma_sv'] = "Mã sinh viên không tồn tại";
        } else {
            $sv_exists = true;
        }
    }

    // 2. Validate Mã môn học (3 chữ cái in hoa + 3 chữ số, VD: INT101)
    if (empty($ma_mh)) {
        $errors['ma_mh'] = "Mã môn học không được để trống";
    } elseif (!preg_match('/^[A-Z]{3}\d{3}$/', $ma_mh)) {
        $errors['ma_mh'] = "Mã môn học phải gồm 3 chữ cái in hoa và 3 chữ số (ví dụ INT101)";
    } elseif ($sv_exists) {
        // Kiểm tra trùng cặp (Mã SV, Mã môn học)
        $stmt_dup = $pdo->prepare("SELECT COUNT(*) FROM diem WHERE ma_sv = :ma_sv AND ma_mh = :ma_mh");
        $stmt_dup->execute(['ma_sv' => $ma_sv, 'ma_mh' => $ma_mh]);
        if ($stmt_dup->fetchColumn() > 0) {
            $errors['ma_mh'] = "Điểm môn học đã tồn tại";
        }
    }

    // 3. Validate Điểm giữa kỳ
    $err_gk = validateScore($diem_giua_ky, "Điểm giữa kỳ");
    if ($err_gk !== '') {
        $errors['diem_giua_ky'] = $err_gk;
    }

    // 4. Validate Điểm cuối kỳ
    $err_ck = validateScore($diem_cuoi_ky, "Điểm cuối kỳ");
    if ($err_ck !== '') {
        $errors['diem_cuoi_ky'] = $err_ck;
    }

    // 5. Validate Hạn khóa điểm (yyyy-mm-dd, >= ngày hiện tại)
    if (empty($han_khoa_diem)) {
        $errors['han_khoa_diem'] = "Hạn khóa điểm không được để trống";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $han_khoa_diem)) {
        $errors['han_khoa_diem'] = "Hạn khóa điểm phải đúng định dạng yyyy-mm-dd";
    } else {
        $parts = explode('-', $han_khoa_diem);
        if (count($parts) === 3 && checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            $today = date('Y-m-d');
            if ($han_khoa_diem < $today) {
                $errors['han_khoa_diem'] = "Hạn khóa điểm không được nhỏ hơn ngày hiện tại";
            }
        } else {
            $errors['han_khoa_diem'] = "Hạn khóa điểm phải đúng định dạng yyyy-mm-dd";
        }
    }

    // 6. Nếu hợp lệ, tự tính Điểm tổng kết và lưu vào CSDL
    if (empty($errors)) {
        $gk = (float)$diem_giua_ky;
        $ck = (float)$diem_cuoi_ky;
        $diem_tong_ket = round($gk * 0.3 + $ck * 0.7, 1);
        $trang_thai = 'Chưa khóa';

        $sql_ins = "INSERT INTO diem (ma_sv, ma_mh, diem_giua_ky, diem_cuoi_ky, diem_tong_ket, han_khoa_diem, trang_thai)
                    VALUES (:ma_sv, :ma_mh, :diem_giua_ky, :diem_cuoi_ky, :diem_tong_ket, :han_khoa_diem, :trang_thai)";
        $stmt_ins = $pdo->prepare($sql_ins);
        try {
            $stmt_ins->execute([
                'ma_sv' => $ma_sv,
                'ma_mh' => $ma_mh,
                'diem_giua_ky' => $gk,
                'diem_cuoi_ky' => $ck,
                'diem_tong_ket' => $diem_tong_ket,
                'han_khoa_diem' => $han_khoa_diem,
                'trang_thai' => $trang_thai
            ]);
            $_SESSION['msg'] = "Thêm điểm thành công";
            header("Location: grades.php");
            exit();
        } catch (PDOException $e) {
            $message = "<div id='msgAlert' class='alert alert-danger'>Lỗi hệ thống: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 bg-white p-4 rounded shadow-sm">
        <h3 class="mb-4 text-center">Thêm điểm sinh viên</h3>
        <?= $message ?>
        <form method="POST" action="" id="formAddGrade">
            <div class="mb-3">
                <label class="form-label">Mã sinh viên</label>
                <input type="text" id="ma_sv" name="ma_sv" class="form-control <?= isset($errors['ma_sv']) ? 'is-invalid' : '' ?>" placeholder="VD: SV000001" value="<?= htmlspecialchars($_POST['ma_sv'] ?? '') ?>">
                <?php if(isset($errors['ma_sv'])): ?><div id="err_ma_sv" class="invalid-feedback"><?= $errors['ma_sv'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Mã môn học</label>
                <input type="text" id="ma_mh" name="ma_mh" class="form-control <?= isset($errors['ma_mh']) ? 'is-invalid' : '' ?>" placeholder="VD: INT101" value="<?= htmlspecialchars($_POST['ma_mh'] ?? '') ?>">
                <?php if(isset($errors['ma_mh'])): ?><div id="err_ma_mh" class="invalid-feedback"><?= $errors['ma_mh'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Điểm giữa kỳ</label>
                <input type="text" id="diem_giua_ky" name="diem_giua_ky" class="form-control <?= isset($errors['diem_giua_ky']) ? 'is-invalid' : '' ?>" placeholder="0.0 - 10.0" value="<?= htmlspecialchars($_POST['diem_giua_ky'] ?? '') ?>">
                <?php if(isset($errors['diem_giua_ky'])): ?><div id="err_diem_giua_ky" class="invalid-feedback"><?= $errors['diem_giua_ky'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Điểm cuối kỳ</label>
                <input type="text" id="diem_cuoi_ky" name="diem_cuoi_ky" class="form-control <?= isset($errors['diem_cuoi_ky']) ? 'is-invalid' : '' ?>" placeholder="0.0 - 10.0" value="<?= htmlspecialchars($_POST['diem_cuoi_ky'] ?? '') ?>">
                <?php if(isset($errors['diem_cuoi_ky'])): ?><div id="err_diem_cuoi_ky" class="invalid-feedback"><?= $errors['diem_cuoi_ky'] ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Hạn khóa điểm (yyyy-mm-dd)</label>
                <input type="text" id="han_khoa_diem" name="han_khoa_diem" class="form-control <?= isset($errors['han_khoa_diem']) ? 'is-invalid' : '' ?>" placeholder="yyyy-mm-dd" value="<?= htmlspecialchars($_POST['han_khoa_diem'] ?? '') ?>">
                <?php if(isset($errors['han_khoa_diem'])): ?><div id="err_han_khoa_diem" class="invalid-feedback"><?= $errors['han_khoa_diem'] ?></div><?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <button type="submit" id="btnThemDiem" class="btn btn-primary px-4">Thêm</button>
                <a href="grades.php" id="btnHuyDiem" class="btn btn-secondary px-4">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>