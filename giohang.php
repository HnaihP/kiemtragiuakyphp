<?php
require_once 'config.php';
include 'header.php';

// Check if student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: login.php?redirect=giohang.php");
    exit();
}

// Handle remove course from cart
if (isset($_GET['remove']) && !empty($_GET['remove'])) {
    $course_id = sanitize($_GET['remove']);
    
    // Find and remove the course from the cart
    if (isset($_SESSION['cart']) && in_array($course_id, $_SESSION['cart'])) {
        $key = array_search($course_id, $_SESSION['cart']);
        if ($key !== false) {
            unset($_SESSION['cart'][$key]);
            
            // Reindex the array
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            
            $success_message = "Học phần đã được xóa khỏi giỏ đăng ký.";
        }
    }
}

// Handle clear all from cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $success_message = "Tất cả học phần đã được xóa khỏi giỏ đăng ký.";
}

// Fetch course details for items in cart
$cart_courses = [];
$total_credits = 0;

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    try {
        // Convert array of course IDs to string of placeholders
        $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
        
        $stmt = $conn->prepare("SELECT * FROM HocPhan WHERE MaHP IN ($placeholders)");
        
        // Bind course IDs as parameters
        foreach ($_SESSION['cart'] as $key => $course_id) {
            $stmt->bindValue($key + 1, $course_id);
        }
        
        $stmt->execute();
        $cart_courses = $stmt->fetchAll();
        
        // Calculate total credits
        foreach ($cart_courses as $course) {
            $total_credits += $course['SoTinChi'];
        }
        
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<div class="card">
    <div class="card-header bg-success text-white">
        <h4>Giỏ đăng ký học phần</h4>
    </div>
    <div class="card-body">
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="mb-3">
            <a href="hocphan.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Tiếp tục đăng ký
            </a>
            
            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <a href="save_registration.php" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu đăng ký
                </a>
                <a href="giohang.php?clear=1" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tất cả học phần khỏi giỏ đăng ký?');">
                    <i class="fas fa-trash-alt"></i> Xóa đăng ký
                </a>
            <?php endif; ?>
        </div>
        
        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Mã HP</th>
                            <th>Tên học phần</th>
                            <th>Số tín chỉ</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_courses as $index => $course): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $course['MaHP']; ?></td>
                            <td><?php echo $course['TenHP']; ?></td>
                            <td><?php echo $course['SoTinChi']; ?></td>
                            <td>
                                <a href="giohang.php?remove=<?php echo $course['MaHP']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa học phần này?');">
                                    <i class="fas fa-trash-alt"></i> Xóa
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <th colspan="3" class="text-right">Tổng số tín chỉ:</th>
                            <th><?php echo $total_credits; ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Giỏ đăng ký trống. <a href="hocphan.php">Đăng ký học phần ngay</a>.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>