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
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Process deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // First check if student has registrations
        $stmt = $conn->prepare("SELECT * FROM DangKy WHERE MaSV = :masv");
        $stmt->bindParam(':masv', $student_id);
        $stmt->execute();
        $registrations = $stmt->fetchAll();
        
        // If student has registrations, delete them first
        foreach ($registrations as $registration) {
            // Delete registration details
            $stmt = $conn->prepare("DELETE FROM ChiTietDangKy WHERE MaDK = :madk");
            $stmt->bindParam(':madk', $registration['MaDK']);
            $stmt->execute();
            
            // Delete registration
            $stmt = $conn->prepare("DELETE FROM DangKy WHERE MaDK = :madk");
            $stmt->bindParam(':madk', $registration['MaDK']);
            $stmt->execute();
        }
        
        // Delete student
        $stmt = $conn->prepare("DELETE FROM SinhVien WHERE MaSV = :masv");
        $stmt->bindParam(':masv', $student_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to index page after successful deletion
        header("Location: index.php");
        exit();
    } catch(PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<div class="card">
    <div class="card-header bg-danger text-white">
        <h4>Xóa sinh viên</h4>
    </div>
    <div class="card-body">
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="alert alert-warning">
            <p><strong>Cảnh báo:</strong> Bạn có chắc chắn muốn xóa sinh viên này?</p>
            <p>Mọi thông tin liên quan đến đăng ký học phần của sinh viên này cũng sẽ bị xóa.</p>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <div class="user-info">
                    <img src="<?php echo $student['Hinh'] ?: 'Content/images/default.jpg'; ?>" alt="<?php echo $student['HoTen']; ?>" class="avatar-img">
                    <div class="user-details">
                        <h5><?php echo $student['HoTen']; ?> (<?php echo $student['MaSV']; ?>)</h5>
                        <p>
                            <strong>Giới tính:</strong> <?php echo $student['GioiTinh']; ?><br>
                            <strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($student['NgaySinh'])); ?><br>
                            <strong>Ngành học:</strong> <?php echo $student['TenNganh']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="post">
            <div class="form-group">
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Xác nhận xóa
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Trở về
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>