<?php
session_start();
require_once 'db.php';
include 'header.php';

// Chức năng Khóa điểm tự động: Tự động cập nhật trạng thái trong CSDL dựa theo ngày hiện tại
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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Danh sách Điểm Sinh viên</h2>
    <a href="add_grade.php" id="btnThemDiemMoi" class="btn btn-success">+ Thêm điểm</a>
</div>

<?php if(isset($_SESSION['msg'])): ?>
    <div id="msgAlert" class="alert alert-info"><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
<?php endif; ?>

<?php if(isset($_SESSION['err_msg'])): ?>
    <div id="errAlert" class="alert alert-danger"><?= htmlspecialchars($_SESSION['err_msg']); unset($_SESSION['err_msg']); ?></div>
<?php endif; ?>

<div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-bordered table-hover align-middle mb-0" id="tableDiem">
        <thead class="table-dark">
            <tr>
                <th>Mã sinh viên</th>
                <th>Mã môn học</th>
                <th>Điểm giữa kỳ</th>
                <th>Điểm cuối kỳ</th>
                <th>Điểm tổng kết</th>
                <th>Hạn khóa điểm</th>
                <th>Trạng thái</th>
                <th class="text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($ds_diem) > 0): ?>
                <?php foreach ($ds_diem as $d): ?>
                    <?php 
                        // Xác định trạng thái theo hạn khóa điểm
                        $is_locked = ($today > $d['han_khoa_diem']);
                        $trang_thai_hien_thi = $is_locked ? 'Đã khóa' : 'Chưa khóa';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($d['ma_sv']) ?></td>
                        <td><?= htmlspecialchars($d['ma_mh']) ?></td>
                        <td><?= number_format((float)$d['diem_giua_ky'], 1, '.', '') ?></td>
                        <td><?= number_format((float)$d['diem_cuoi_ky'], 1, '.', '') ?></td>
                        <td class="fw-bold"><?= number_format((float)$d['diem_tong_ket'], 1, '.', '') ?></td>
                        <td><?= htmlspecialchars($d['han_khoa_diem']) ?></td>
                        <td>
                            <?php if ($is_locked): ?>
                                <span class="badge bg-danger">Đã khóa</span>
                            <?php else: ?>
                                <span class="badge bg-success">Chưa khóa</span>
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
                    <td colspan="8" class="text-center text-danger py-3">Không tìm thấy bản ghi điểm</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>