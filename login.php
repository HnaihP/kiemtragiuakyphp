<?php
require_once 'config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['student'])) {
    header("Location: index.php");
    exit();
}

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $masv = sanitize($_POST['masv']);
    
    try {
        // Check if student exists
        $stmt = $conn->prepare("SELECT * FROM SinhVien WHERE MaSV = :masv");
        $stmt->bindParam(':masv', $masv);
        $stmt->execute();
        $student = $stmt->fetch();
        
        if ($student) {
            // Set session
            $_SESSION['student'] = $student;
            
            // Redirect to previous page or home
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header("Location: $redirect");
            exit();
        } else {
            $error_message = "Mã sinh viên không tồn tại. Vui lòng kiểm tra lại.";
        }
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Include header without worrying about session_start() since we've updated it
include 'header.php';
?>

<div class="card mx-auto" style="max-width: 500px;">
    <div class="card-header bg-primary text-white">
        <h4>Đăng nhập</h4>
    </div>
    <div class="card-body">
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="masv">Mã sinh viên</label>
                <input type="text" class="form-control" id="masv" name="masv" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
            </div>
        </form>
        
        <div class="alert alert-info mt-3">
            <p class="mb-0">
                <i class="fas fa-info-circle"></i> Hệ thống không yêu cầu mật khẩu, chỉ cần nhập mã sinh viên để đăng nhập.
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>