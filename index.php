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

// Thống kê nhanh
$total_sv = $pdo->query("SELECT COUNT(*) FROM sinhvien")->fetchColumn();
$total_lop = $pdo->query("SELECT COUNT(DISTINCT lop) FROM sinhvien")->fetchColumn();
?>

<!-- Thống kê tổng quan -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Tổng số sinh viên</div>
                <div class="fs-4 fw-bold"><?= $total_sv ?> <span class="fs-6 fw-normal text-muted">hồ sơ</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-diagram-3-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Số lớp quản lý</div>
                <div class="fs-4 fw-bold"><?= $total_lop ?> <span class="fs-6 fw-normal text-muted">lớp hành chính</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Kết quả hiển thị</div>
                <div class="fs-4 fw-bold"><?= count($sinh_viens) ?> <span class="fs-6 fw-normal text-muted">kết quả</span></div>
            </div>
        </div>
    </div>
</div>

<!-- Tiêu đề & Nút Thêm -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h2 class="fw-bold mb-0 fs-3">Danh sách Sinh viên</h2>
        <p class="text-muted small mb-0">Tra cứu, thêm mới, chỉnh sửa và quản lý hồ sơ sinh viên trong hệ thống</p>
    </div>
    <a href="add_student.php" id="btnThemHoSoMoi" class="btn btn-success shadow-sm">
        + Thêm hồ sơ
    </a>
</div>

<?php if(isset($_SESSION['msg'])): ?>
    <div id="msgAlert" class="alert alert-info d-flex align-items-center shadow-sm border-0 rounded-3">
        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
        <div><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    </div>
<?php endif; ?>

<!-- Thanh tìm kiếm -->
<div class="card-modern p-3 mb-4">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="search" name="search" class="form-control border-start-0 ps-0" placeholder="Nhập Mã SV hoặc Họ tên để tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" id="btnTimKiem" class="btn btn-primary w-100">Tìm kiếm</button>
            <?php if(!empty($search)): ?>
                <a href="index.php" class="btn btn-outline-secondary" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Bảng dữ liệu -->
<div class="card-modern overflow-hidden">
    <div class="table-responsive">
        <table class="table table-modern table-hover align-middle mb-0" id="tableSinhVien">
            <thead>
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
                            <td><span class="code-badge"><?= htmlspecialchars($sv['ma_sv']) ?></span></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($sv['ho_ten']) ?></td>
                            <td class="text-secondary">
                                <i class="bi bi-calendar3 me-1 text-muted"></i><?= date('d/m/Y', strtotime($sv['ngay_sinh'])) ?>
                            </td>
                            <td><span class="class-badge"><?= htmlspecialchars($sv['lop']) ?></span></td>
                            <td>
                                <i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($sv['so_dien_thoai']) ?>
                            </td>
                            <td class="text-secondary">
                                <i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($sv['email']) ?>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="edit_student.php?id=<?= urlencode($sv['ma_sv']) ?>" class="btn btn-sm btn-warning">Sửa</a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('<?= htmlspecialchars($sv['ma_sv']) ?>')">Xóa</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4 fw-semibold">Không có hồ sơ nào phù hợp</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(id) {
    if(confirm("Bạn có chắc chắn muốn xóa hồ sơ này?")) {
        window.location.href = 'delete_student.php?id=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>