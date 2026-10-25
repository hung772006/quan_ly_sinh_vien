<?php
session_start();
require_once 'db.php';

if (isset($_GET['id'])) {
    $ma_sv = $_GET['id'];
    
    // Kiểm tra hồ sơ còn tồn tại không
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM sinhvien WHERE ma_sv = :ma_sv");
    $stmt_check->execute(['ma_sv' => $ma_sv]);
    
    if ($stmt_check->fetchColumn() > 0) {
        try {
            $sql = "DELETE FROM sinhvien WHERE ma_sv = :ma_sv";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['ma_sv' => $ma_sv]);
            $_SESSION['msg'] = "Xóa hồ sơ thành công";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Lỗi xóa dữ liệu: " . $e->getMessage();
        }
    } else {
        $_SESSION['msg'] = "Không tìm thấy hồ sơ cần xóa (có thể đã bị xóa trước đó)";
    }
}

header("Location: index.php");
exit();
?>