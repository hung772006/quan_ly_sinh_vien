<?php
session_start();
require_once 'db.php';
include 'header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT * FROM sinhvien";
$params = [];

if (!empty($search)) {
    $query .= " WHERE ma_sv LIKE :search_ma OR ho_ten LIKE :search_ten";
    $params['search_ma'] = "%$search%";
    $params['search_ten'] = "%$search%";
}

$query .= " ORDER BY ma_sv ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sinh_viens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Danh sách Sinh viên</h2>
    <a href="add_student.php" class="btn btn-success">+ Thêm hồ sơ</a>
</div>

<?php if(isset($_SESSION['msg'])): ?>
    <div class="alert alert-info"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
<?php endif; ?>

<form method="GET" action="index.php" class="row g-3 mb-4">
    <div class="col-md-8">
        <input type="text" name="search" class="form-control" placeholder="Nhập Mã SV hoặc Họ tên để tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
    </div>
</form>

<div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-bordered table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Mã SV</th>
                <th>Họ và Tên</th>
                <th>Ngày Sinh</th>
                <th>Lớp</th>
                <th>SĐT</th>
                <th>Email</th>
                <th class="text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sinh_viens) > 0): ?>
                <?php foreach ($sinh_viens as $sv): ?>
                    <tr>
                        <td><?= htmlspecialchars($sv['ma_sv']) ?></td>
                        <td><?= htmlspecialchars($sv['ho_ten']) ?></td>
                        <td><?= date('d/m/Y', strtotime($sv['ngay_sinh'])) ?></td>
                        <td><?= htmlspecialchars($sv['lop']) ?></td>
                        <td><?= htmlspecialchars($sv['so_dien_thoai']) ?></td>
                        <td><?= htmlspecialchars($sv['email']) ?></td>
                        <td class="text-center">
                            <a href="edit_student.php?id=<?= urlencode($sv['ma_sv']) ?>" class="btn btn-sm btn-warning">Sửa</a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('<?= htmlspecialchars($sv['ma_sv']) ?>')">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-danger py-3">Không có hồ sơ nào phù hợp</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete(id) {
    if(confirm("Bạn có chắc chắn muốn xóa hồ sơ này?")) {
        window.location.href = 'delete_student.php?id=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>