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
    $stmt = $conn->prepare("SELECT * FROM SinhVien WHERE MaSV = :id");
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

// Fetch all majors for dropdown
try {
    $stmt = $conn->prepare("SELECT * FROM NganhHoc");
    $stmt->execute();
    $majors = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Sanitize and validate input
        $hoten = sanitize($_POST['hoten']);
        $gioitinh = sanitize($_POST['gioitinh']);
        $ngaysinh = sanitize($_POST['ngaysinh']);
        $manganh = sanitize($_POST['manganh']);
        $current_hinh = $student['Hinh'];
        
        // Handle file upload
        $hinh = $current_hinh;
        if (!empty($_FILES["hinh"]["name"])) {
            $target_dir = "Content/images/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["hinh"]["name"], PATHINFO_EXTENSION));
            $new_filename = "sv_" . $student_id . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Check file type
            $allowed_extensions = array("jpg", "jpeg", "png", "gif");
            if (in_array($file_extension, $allowed_extensions)) {
                if (move_uploaded_file($_FILES["hinh"]["tmp_name"], $target_file)) {
                    $hinh = "/" . $target_file; // Store path relative to root
                }
            }
        }
        
        // Update student data
        $stmt = $conn->prepare("UPDATE SinhVien SET HoTen = :hoten, GioiTinh = :gioitinh, 
                              NgaySinh = :ngaysinh, Hinh = :hinh, MaNganh = :manganh 
                              WHERE MaSV = :masv");
        $stmt->bindParam(':hoten', $hoten);
        $stmt->bindParam(':gioitinh', $gioitinh);
        $stmt->bindParam(':ngaysinh', $ngaysinh);
        $stmt->bindParam(':hinh', $hinh);
        $stmt->bindParam(':manganh', $manganh);
        $stmt->bindParam(':masv', $student_id);
        $stmt->execute();
        
        // Redirect to index page after successful update
        header("Location: index.php");
        exit();
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<div class="card">
    <div class="card-header bg-warning text-white">
        <h4>Sửa thông tin sinh viên</h4>
    </div>
    <div class="card-body">
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="masv">Mã sinh viên</label>
                <input type="text" class="form-control" id="masv" value="<?php echo $student['MaSV']; ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="hoten">Họ tên</label>
                <input type="text" class="form-control" id="hoten" name="hoten" value="<?php echo $student['HoTen']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Giới tính</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gioitinh" id="nam" value="Nam" <?php echo ($student['GioiTinh'] == 'Nam') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="nam">Nam</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gioitinh" id="nu" value="Nữ" <?php echo ($student['GioiTinh'] == 'Nữ') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="nu">Nữ</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="ngaysinh">Ngày sinh</label>
                <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" value="<?php echo $student['NgaySinh']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="hinh">Hình</label>
                <?php if (!empty($student['Hinh'])): ?>
                    <div class="mb-2">
                        <img src="<?php echo $student['Hinh']; ?>" alt="<?php echo $student['HoTen']; ?>" class="avatar-img">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control-file" id="hinh" name="hinh">
                <small class="form-text text-muted">Để trống nếu không muốn thay đổi hình hiện tại</small>
            </div>
            
            <div class="form-group">
                <label for="manganh">Ngành học</label>
                <select class="form-control" id="manganh" name="manganh" required>
                    <option value="">-- Chọn ngành học --</option>
                    <?php foreach($majors as $major): ?>
                        <option value="<?php echo $major['MaNganh']; ?>" <?php echo ($student['MaNganh'] == $major['MaNganh']) ? 'selected' : ''; ?>>
                            <?php echo $major['TenNganh']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Trở về
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>