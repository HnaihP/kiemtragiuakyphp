<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Học Phần</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            padding-top: 20px;
        }
        .avatar-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-details {
            margin-left: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="index.php">Đăng Ký Học Phần</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Sinh Viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="hocphan.php">Học Phần</a>
                    </li>
                    <?php if(isset($_SESSION['student'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="giohang.php">Giỏ Đăng Ký</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <?php if(isset($_SESSION['student'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">Xin chào, <?php echo $_SESSION['student']['HoTen']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Đăng Xuất</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng Nhập</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>