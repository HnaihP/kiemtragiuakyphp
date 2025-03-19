<?php
require_once 'config.php';
include 'header.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$registration_id = sanitize($_GET['id']);

// Fetch registration data
try {
    // Get registration header info
    $stmt = $conn->prepare("SELECT dk.*, sv.HoTen 
                          FROM DangKy dk 
                          JOIN SinhVien sv ON dk.MaSV = sv.MaSV 
                          WHERE dk.MaDK = :id");
    $stmt->bindParam(':id', $registration_id);
    $stmt->execute();
    $registration = $stmt->fetch();
    
    if (!$registration) {
        header("Location: index.php");
        exit();
    }
    
    // Get registration details
    $stmt = $conn->prepare("SELECT ctdk.*, hp.TenHP, hp.SoTinChi 
                          FROM ChiTietDangKy ctdk 
                          JOIN HocPhan hp ON ctdk.MaHP = hp.MaHP 
                          WHERE ctdk.MaDK = :id");
    $stmt->bindParam(':id', $registration_id);
    $stmt->execute();
    $courses = $stmt->fetchAll();
    
    // Calculate total credits
    $total_credits = 0;
    foreach ($courses as $course) {
        $total_credits += $course['SoTinChi'];
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Display success message
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<div class="card">
    <div class="card-header bg-info text-white">
        <h4>Chi tiết đăng ký học phần</h4>
    </div>
    <div class="card-body">
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Thông tin đăng ký</h5>
                <p><strong>Mã đăng ký:</strong> <?php echo $registration['MaDK']; ?></p>
                <p><strong>Ngày đăng ký:</strong> <?php echo date('d/m/Y', strtotime($registration['NgayDK'])); ?></p>
                <p><strong>Sinh viên:</strong> <?php echo $registration['HoTen']; ?> (<?php echo $registration['MaSV']; ?>)</p>
            </div>
            <div class="col-md-6 text-right">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Trở về
                </a>
                <a href="giohang.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Đăng ký thêm
                </a>
            </div>
        </div>
        
        <h5>Danh sách học phần đã đăng ký</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Mã HP</th>
                        <th>Tên học phần</th>
                        <th>Số tín chỉ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $index => $course): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $course['MaHP']; ?></td>
                        <td><?php echo $course['TenHP']; ?></td>
                        <td><?php echo $course['SoTinChi']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <th colspan="3" class="text-right">Tổng số tín chỉ:</th>
                        <th><?php echo $total_credits; ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>