<?php
session_start();
require_once 'db.php';
$message = '';
$errors = [];

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

    // 2. Validate Mã môn học
    if (empty($ma_mh)) {
        $errors['ma_mh'] = "Mã môn học không được để trống";
    } elseif (!preg_match('/^[A-Z]{3}\d{3}$/', $ma_mh)) {
        $errors['ma_mh'] = "Mã môn học phải gồm 3 chữ cái in hoa và 3 chữ số (ví dụ INT101)";
    } elseif ($sv_exists) {
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

    // 5. Validate Hạn khóa điểm
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
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="form-header-banner d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2 fs-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-file-earmark-plus-fill"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold fs-4">Thêm điểm sinh viên</h3>
                    <div class="small opacity-75">Nhập điểm học phần mới (Hệ thống tự động tính điểm tổng kết)</div>
                </div>
            </div>
            <div class="p-4 p-md-5">
                <?= $message ?>
                <form method="POST" action="" id="formAddGrade">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-upc-scan me-1 text-primary"></i>Mã sinh viên</label>
                            <input type="text" id="ma_sv" name="ma_sv" class="form-control <?= isset($errors['ma_sv']) ? 'is-invalid' : '' ?>" placeholder="VD: SV000001" value="<?= htmlspecialchars($_POST['ma_sv'] ?? '') ?>">
                            <?php if(isset($errors['ma_sv'])): ?><div id="err_ma_sv" class="invalid-feedback"><?= $errors['ma_sv'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-book me-1 text-primary"></i>Mã môn học</label>
                            <input type="text" id="ma_mh" name="ma_mh" class="form-control <?= isset($errors['ma_mh']) ? 'is-invalid' : '' ?>" placeholder="VD: INT101" value="<?= htmlspecialchars($_POST['ma_mh'] ?? '') ?>">
                            <?php if(isset($errors['ma_mh'])): ?><div id="err_ma_mh" class="invalid-feedback"><?= $errors['ma_mh'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-123 me-1 text-primary"></i>Điểm giữa kỳ (30%)</label>
                            <input type="text" id="diem_giua_ky" name="diem_giua_ky" class="form-control <?= isset($errors['diem_giua_ky']) ? 'is-invalid' : '' ?>" placeholder="0.0 - 10.0" value="<?= htmlspecialchars($_POST['diem_giua_ky'] ?? '') ?>">
                            <?php if(isset($errors['diem_giua_ky'])): ?><div id="err_diem_giua_ky" class="invalid-feedback"><?= $errors['diem_giua_ky'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-123 me-1 text-primary"></i>Điểm cuối kỳ (70%)</label>
                            <input type="text" id="diem_cuoi_ky" name="diem_cuoi_ky" class="form-control <?= isset($errors['diem_cuoi_ky']) ? 'is-invalid' : '' ?>" placeholder="0.0 - 10.0" value="<?= htmlspecialchars($_POST['diem_cuoi_ky'] ?? '') ?>">
                            <?php if(isset($errors['diem_cuoi_ky'])): ?><div id="err_diem_cuoi_ky" class="invalid-feedback"><?= $errors['diem_cuoi_ky'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-calculator me-1 text-secondary"></i>Điểm tổng kết (Dự kiến)</label>
                            <input type="text" id="preview_tong_ket" class="form-control bg-light fw-bold text-primary" value="--" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label"><i class="bi bi-calendar-lock me-1 text-primary"></i>Hạn khóa điểm (yyyy-mm-dd)</label>
                            <input type="text" id="han_khoa_diem" name="han_khoa_diem" class="form-control <?= isset($errors['han_khoa_diem']) ? 'is-invalid' : '' ?>" placeholder="yyyy-mm-dd" value="<?= htmlspecialchars($_POST['han_khoa_diem'] ?? '') ?>">
                            <?php if(isset($errors['han_khoa_diem'])): ?><div id="err_han_khoa_diem" class="invalid-feedback"><?= $errors['han_khoa_diem'] ?></div><?php endif; ?>
                        </div>
                    </div>
                    <hr class="my-4 text-muted opacity-25">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="grades.php" id="btnHuyDiem" class="btn btn-secondary px-4">Hủy</a>
                        <button type="submit" id="btnThemDiem" class="btn btn-primary px-4">Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Tự động nhẩm Điểm tổng kết khi nhập trên giao diện
const gkInput = document.getElementById('diem_giua_ky');
const ckInput = document.getElementById('diem_cuoi_ky');
const previewInput = document.getElementById('preview_tong_ket');

function updatePreview() {
    const gk = parseFloat(gkInput.value);
    const ck = parseFloat(ckInput.value);
    if (!isNaN(gk) && !isNaN(ck) && gk >= 0 && gk <= 10 && ck >= 0 && ck <= 10) {
        previewInput.value = (Math.round((gk * 0.3 + ck * 0.7) * 10) / 10).toFixed(1);
    } else {
        previewInput.value = '--';
    }
}
gkInput.addEventListener('input', updatePreview);
ckInput.addEventListener('input', updatePreview);
updatePreview();
</script>

<?php include 'footer.php'; ?>