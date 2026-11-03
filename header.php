<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Hồ sơ & Điểm Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Hệ thống QLSV</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" id="navHoSo" href="index.php">Quản lý Hồ sơ</a></li>
                <li class="nav-item"><a class="nav-link" id="navThemHoSo" href="add_student.php">Thêm Hồ sơ</a></li>
                <li class="nav-item"><a class="nav-link" id="navDiem" href="grades.php">Quản lý Điểm</a></li>
                <li class="nav-item"><a class="nav-link" id="navThemDiem" href="add_grade.php">Thêm Điểm</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container pb-5">