<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Hồ sơ & Điểm Sinh viên</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            --surface-bg: #f1f5f9;
            --card-radius: 16px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--surface-bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        /* Navbar hiện đại */
        .navbar-custom {
            background: var(--primary-gradient);
            box-shadow: 0 4px 20px rgba(30, 58, 138, 0.18);
            padding: 0.75rem 0;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.3px;
            font-size: 1.25rem;
        }
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 10px;
            margin-left: 0.25rem;
            transition: all 0.2s ease;
        }
        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.16);
        }
        /* Card & Thống kê */
        .card-modern {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05);
        }
        .stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.15rem 1.35rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        /* Bảng dữ liệu */
        .table-modern thead th {
            background-color: #1e293b;
            color: #f8fafc;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: none;
            padding: 0.9rem 1rem;
        }
        .table-modern tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }
        .table-modern tbody tr:hover {
            background-color: #f8fafc;
        }
        .code-badge {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
            font-size: 0.88rem;
            border: 1px solid #bfdbfe;
        }
        .class-badge {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        /* Form nhập liệu */
        .form-header-banner {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: var(--card-radius) var(--card-radius) 0 0;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #334155;
            margin-bottom: 0.4rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.65rem 0.95rem;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }
        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.55rem 1.2rem;
            transition: all 0.2s ease;
        }
        .btn-sm {
            border-radius: 8px;
            padding: 0.35rem 0.75rem;
        }
        .footer-custom {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1rem 0;
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-mortarboard-fill fs-4"></i>
            <span>Hệ thống QLSV</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= in_array($current_page, ['index.php', 'edit_student.php']) ? 'active' : '' ?>" id="navHoSo" href="index.php">
                        <i class="bi bi-people-fill me-1"></i> Quản lý Hồ sơ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'add_student.php' ? 'active' : '' ?>" id="navThemHoSo" href="add_student.php">
                        <i class="bi bi-person-plus-fill me-1"></i> Thêm Hồ sơ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array($current_page, ['grades.php', 'edit_grade.php']) ? 'active' : '' ?>" id="navDiem" href="grades.php">
                        <i class="bi bi-journal-check me-1"></i> Quản lý Điểm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'add_grade.php' ? 'active' : '' ?>" id="navThemDiem" href="add_grade.php">
                        <i class="bi bi-plus-circle-fill me-1"></i> Thêm Điểm
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container pb-5">