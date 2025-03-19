<?php
require_once 'config.php';
include 'header.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$student_id = sanitize($_GET['id']);

// Fetch student data
try {
    $stmt = $conn->prepare("SELECT sv.*, nh.TenNganh 
                          FROM SinhVien sv 
                          JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh 
                          WHERE sv.MaSV = :id");
    $stmt->bindParam(':id', $student_id);
    $stmt->execute();
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: index.php");
        exit();
    }
    
    // Fetch registration history
    $stmt = $conn->prepare("SELECT dk.MaDK, dk.NgayDK, COUNT(ctdk.MaHP) as SoHocPhan, SUM(hp.SoTinChi) as TongTinChi
                          FROM DangKy dk
                          LEFT JOIN ChiTietDangKy ctdk ON dk.MaDK = ctdk.MaDK
                          LEFT JOIN HocPhan hp ON ctdk.MaHP = hp.MaHP
                          WHERE dk.MaSV = :masv
                          GROUP BY dk.MaDK, dk.NgayDK
                          ORDER BY dk.NgayDK DESC");
    $stmt->bindParam(':masv', $student_id);
    $stmt->execute();
    $registrations = $stmt->fetchAll();
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<style>
    .student-avatar {
        max-width: 100%;
        width: 100%; /* Đảm bảo ảnh chiếm toàn bộ chiều rộng của container */
        height: auto;
        max-height: 500px; /* Tăng kích thước tối đa để ảnh to hơn */
        object-fit: cover; /* Giữ tỷ lệ ảnh, cắt phần thừa nếu cần */
        margin-bottom: 20px;
        border-radius: 10px; /* Bo góc cho ảnh */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Thêm bóng */
    }

    /* Đảm bảo container của ảnh không bị giới hạn kích thước */
    .col-md-5.text-center {
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>

<div class="card">
    <div class="card-header bg-info text-white">
        <h4>Chi tiết sinh viên</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5 text-center">
                <?php
                $imagePath = $student['Hinh'];
                if (empty($imagePath)) {
                    $imagePath = 'Content/images/default.jpg';
                } else {
                    // Xóa dấu / ở đầu nếu có
                    $imagePath = ltrim($imagePath, '/');
                }
                ?>
                <img src="<?php echo $imagePath; ?>" alt="<?php echo $student['HoTen']; ?>" class="img-fluid rounded shadow student-avatar">
            </div>
            <div class="col-md-7">
                <h2 class="mb-4"><?php echo $student['HoTen']; ?></h2>
                <div class="row">
                    <div class="col-md-6">
                        <p class="lead"><strong>Mã SV:</strong> <?php echo $student['MaSV']; ?></p>
                        <p class="lead"><strong>Giới tính:</strong> <?php echo $student['GioiTinh']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="lead"><strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($student['NgaySinh'])); ?></p>
                        <p class="lead"><strong>Ngành học:</strong> <?php echo $student['TenNganh']; ?></p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="edit.php?id=<?php echo $student['MaSV']; ?>" class="btn btn-warning btn-lg mr-2">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a href="delete.php?id=<?php echo $student['MaSV']; ?>" class="btn btn-danger btn-lg mr-2">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Trở về
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (!empty($registrations)): ?>
            <div class="mt-5">  
                <h4 class="mb-3">Lịch sử đăng ký học phần</h4>
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Mã đăng ký</th>
                            <th>Ngày đăng ký</th>
                            <th>Số học phần</th>
                            <th>Tổng tín chỉ</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td><?php echo $reg['MaDK']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reg['NgayDK'])); ?></td>
                                <td><?php echo $reg['SoHocPhan']; ?></td>
                                <td><?php echo $reg['TongTinChi']; ?></td>
                                <td>
                                    <a href="registration_detail.php?id=<?php echo $reg['MaDK']; ?>" class="btn btn-info">
                                        <i class="fas fa-info-circle"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>