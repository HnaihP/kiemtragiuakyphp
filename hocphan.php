<?php
require_once 'config.php';
include 'header.php';

// Fetch all courses
try {
    $stmt = $conn->prepare("SELECT * FROM HocPhan ORDER BY MaHP");
    $stmt->execute();
    $courses = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle course registration (add to cart)
if (isset($_POST['register_course']) && isset($_POST['course_id'])) {
    $course_id = sanitize($_POST['course_id']);
    
    // Check if student is logged in
    if (!isset($_SESSION['student'])) {
        header("Location: login.php");
        exit();
    }
    
    // Initialize the cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if the course is already in the cart
    if (!in_array($course_id, $_SESSION['cart'])) {
        // Add course to cart
        $_SESSION['cart'][] = $course_id;
        $success_message = "Học phần đã được thêm vào giỏ đăng ký";
    } else {
        $error_message = "Học phần này đã có trong giỏ đăng ký";
    }
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h4>Danh sách học phần</h4>
    </div>
    <div class="card-body">
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="mb-3">
            <?php if(isset($_SESSION['student'])): ?>
                <a href="giohang.php" class="btn btn-success">
                    <i class="fas fa-shopping-cart"></i> Xem giỏ đăng ký
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge badge-light"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Bạn cần <a href="login.php">đăng nhập</a> để đăng ký học phần.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Mã HP</th>
                        <th>Tên học phần</th>
                        <th>Số tín chỉ</th>
                        <th>Số lượng còn</th>
                        <th>Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $course): ?>
                    <tr>
                        <td><?php echo $course['MaHP']; ?></td>
                        <td><?php echo $course['TenHP']; ?></td>
                        <td><?php echo $course['SoTinChi']; ?></td>
                        <td><?php echo $course['SoLuong']; ?></td>
                        <td>
                            <?php if(isset($_SESSION['student'])): ?>
                                <?php if (!isset($_SESSION['cart']) || !in_array($course['MaHP'], $_SESSION['cart'])): ?>
                                    <?php if ($course['SoLuong'] > 0): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="course_id" value="<?php echo $course['MaHP']; ?>">
                                            <button type="submit" name="register_course" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Đăng ký
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-ban"></i> Hết chỗ
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Đã thêm vào giỏ
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-sm btn-info">
                                    <i class="fas fa-sign-in-alt"></i> Đăng nhập để đăng ký
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>