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
        $message = "<div id='errAlert' class='alert alert-danger d-flex align-items-center'><i class='bi bi-lock-fill me-2'></i>Điểm đã bị khóa, không thể chỉnh sửa</div>";
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
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="form-header-banner d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2 fs-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold fs-4">Sửa điểm sinh viên</h3>
                    <div class="small opacity-75">Sinh viên: <strong><?= htmlspecialchars($diem_row['ma_sv']) ?></strong> — Môn học: <strong><?= htmlspecialchars($diem_row['ma_mh']) ?></strong></div>
                </div>
            </div>
            <div class="p-4 p-md-5">
                <?= $message ?>
                <form method="POST" action="" id="formEditGrade">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-upc-scan me-1 text-secondary"></i>Mã sinh viên</label>
                            <input type="text" id="ma_sv" class="form-control bg-light fw-bold" value="<?= htmlspecialchars($diem_row['ma_sv']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-book me-1 text-secondary"></i>Mã môn học</label>
                            <input type="text" id="ma_mh" class="form-control bg-light fw-bold" value="<?= htmlspecialchars($diem_row['ma_mh']) ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-123 me-1 text-primary"></i>Điểm giữa kỳ (30%)</label>
                            <input type="text" id="diem_giua_ky" name="diem_giua_ky" class="form-control <?= isset($errors['diem_giua_ky']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['diem_giua_ky'] ?? number_format((float)$diem_row['diem_giua_ky'], 1, '.', '')) ?>">
                            <?php if(isset($errors['diem_giua_ky'])): ?><div id="err_diem_giua_ky" class="invalid-feedback"><?= $errors['diem_giua_ky'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-123 me-1 text-primary"></i>Điểm cuối kỳ (70%)</label>
                            <input type="text" id="diem_cuoi_ky" name="diem_cuoi_ky" class="form-control <?= isset($errors['diem_cuoi_ky']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['diem_cuoi_ky'] ?? number_format((float)$diem_row['diem_cuoi_ky'], 1, '.', '')) ?>">
                            <?php if(isset($errors['diem_cuoi_ky'])): ?><div id="err_diem_cuoi_ky" class="invalid-feedback"><?= $errors['diem_cuoi_ky'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-calculator me-1 text-secondary"></i>Điểm tổng kết (Hệ thống tự tính)</label>
                            <input type="text" id="diem_tong_ket" class="form-control bg-light fw-bold text-primary" value="<?= number_format((float)$diem_row['diem_tong_ket'], 1, '.', '') ?>" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label"><i class="bi bi-calendar-lock me-1 text-secondary"></i>Hạn khóa điểm</label>
                            <input type="text" id="han_khoa_diem" class="form-control bg-light" value="<?= htmlspecialchars($diem_row['han_khoa_diem']) ?>" disabled>
                        </div>
                    </div>
                    <hr class="my-4 text-muted opacity-25">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="grades.php" id="btnHuyDiem" class="btn btn-secondary px-4">Hủy</a>
                        <button type="submit" id="btnLuuDiem" class="btn btn-primary px-4">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const gkEdit = document.getElementById('diem_giua_ky');
const ckEdit = document.getElementById('diem_cuoi_ky');
const tkEdit = document.getElementById('diem_tong_ket');

function updateEditPreview() {
    const gk = parseFloat(gkEdit.value);
    const ck = parseFloat(ckEdit.value);
    if (!isNaN(gk) && !isNaN(ck) && gk >= 0 && gk <= 10 && ck >= 0 && ck <= 10) {
        tkEdit.value = (Math.round((gk * 0.3 + ck * 0.7) * 10) / 10).toFixed(1);
    }
}
gkEdit.addEventListener('input', updateEditPreview);
ckEdit.addEventListener('input', updateEditPreview);
</script>

<?php include 'footer.php'; ?>