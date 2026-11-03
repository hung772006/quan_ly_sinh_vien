<?php
session_start();
require_once 'db.php';
include 'header.php';

// Chức năng Khóa điểm tự động theo ngày hiện tại
$today = date('Y-m-d');
$stmt_lock = $pdo->prepare("
    UPDATE diem 
    SET trang_thai = CASE 
        WHEN :today <= han_khoa_diem THEN 'Chưa khóa' 
        ELSE 'Đã khóa' 
    END
");
$stmt_lock->execute(['today' => $today]);

// Lấy danh sách điểm
$query = "SELECT * FROM diem ORDER BY ma_sv ASC, ma_mh ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$ds_diem = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê nhanh
$total_diem = count($ds_diem);
$count_locked = 0;
$count_unlocked = 0;
foreach ($ds_diem as $item) {
    if ($today > $item['han_khoa_diem']) {
        $count_locked++;
    } else {
        $count_unlocked++;
    }
}
?>

<!-- Thống kê tổng quan Điểm -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-journal-bookmark-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Tổng bản ghi điểm</div>
                <div class="fs-4 fw-bold"><?= $total_diem ?> <span class="fs-6 fw-normal text-muted">bản ghi</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-unlock-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Trạng thái Chưa khóa</div>
                <div class="fs-4 fw-bold text-success"><?= $count_unlocked ?> <span class="fs-6 fw-normal text-muted">được phép sửa</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Trạng thái Đã khóa</div>
                <div class="fs-4 fw-bold text-danger"><?= $count_locked ?> <span class="fs-6 fw-normal text-muted">đã chốt sổ</span></div>
            </div>
        </div>
    </div>
</div>

<!-- Tiêu đề & Nút Thêm điểm -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h2 class="fw-bold mb-0 fs-3">Danh sách Điểm Sinh viên</h2>
        <p class="text-muted small mb-0">Công thức tính: Điểm tổng kết = Giữa kỳ × 0.3 + Cuối kỳ × 0.7 (Tự động khóa khi quá hạn)</p>
    </div>
    <a href="add_grade.php" id="btnThemDiemMoi" class="btn btn-success shadow-sm">
        + Thêm điểm
    </a>
</div>

<?php if(isset($_SESSION['msg'])): ?>
    <div id="msgAlert" class="alert alert-info d-flex align-items-center shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['err_msg'])): ?>
    <div id="errAlert" class="alert alert-danger d-flex align-items-center shadow-sm border-0 rounded-3">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div><?= htmlspecialchars($_SESSION['err_msg']); unset($_SESSION['err_msg']); ?></div>
    </div>
<?php endif; ?>

<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-modern table-hover align-middle mb-0" id="tableDiem">
            <thead>
                <tr>
                    <th>Mã sinh viên</th>
                    <th>Mã môn học</th>
                    <th class="text-center">Điểm giữa kỳ</th>
                    <th class="text-center">Điểm cuối kỳ</th>
                    <th class="text-center">Điểm tổng kết</th>
                    <th>Hạn khóa điểm</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($ds_diem) > 0): ?>
                    <?php foreach ($ds_diem as $d): ?>
                        <?php $is_locked = ($today > $d['han_khoa_diem']); ?>
                        <tr>
                            <td><span class="code-badge"><?= htmlspecialchars($d['ma_sv']) ?></span></td>
                            <td><span class="class-badge"><?= htmlspecialchars($d['ma_mh']) ?></span></td>
                            <td class="text-center fw-medium"><?= number_format((float)$d['diem_giua_ky'], 1, '.', '') ?></td>
                            <td class="text-center fw-medium"><?= number_format((float)$d['diem_cuoi_ky'], 1, '.', '') ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary fs-6 fw-bold px-3 py-1">
                                    <?= number_format((float)$d['diem_tong_ket'], 1, '.', '') ?>
                                </span>
                            </td>
                            <td class="text-secondary">
                                <i class="bi bi-calendar-check me-1 text-muted"></i><?= htmlspecialchars($d['han_khoa_diem']) ?>
                            </td>
                            <td>
                                <?php if ($is_locked): ?>
                                    <span class="badge bg-danger px-2 py-1"><i class="bi bi-lock-fill me-1"></i>Đã khóa</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-2 py-1"><i class="bi bi-unlock-fill me-1"></i>Chưa khóa</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($is_locked): ?>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled>Sửa</button>
                                <?php else: ?>
                                    <a href="edit_grade.php?ma_sv=<?= urlencode($d['ma_sv']) ?>&ma_mh=<?= urlencode($d['ma_mh']) ?>" class="btn btn-sm btn-warning">Sửa</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-danger py-4 fw-semibold">Không tìm thấy bản ghi điểm</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>