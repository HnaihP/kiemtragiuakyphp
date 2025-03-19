<?php
require_once 'config.php';
include 'header.php';

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
        $masv = sanitize($_POST['masv']);
        $hoten = sanitize($_POST['hoten']);
        $gioitinh = sanitize($_POST['gioitinh']);
        $ngaysinh = sanitize($_POST['ngaysinh']);
        $manganh = sanitize($_POST['manganh']);
        
        // Handle file upload
        $hinh = "";
        if (!empty($_FILES["hinh"]["name"])) {
            $target_dir = "Content/images/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["hinh"]["name"], PATHINFO_EXTENSION));
            $new_filename = "sv_" . $masv . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Check file type
            $allowed_extensions = array("jpg", "jpeg", "png", "gif");
            if (in_array($file_extension, $allowed_extensions)) {
                if (move_uploaded_file($_FILES["hinh"]["tmp_name"], $target_file)) {
                    $hinh = "/" . $target_file; // Store path relative to root
                }
            }
        }
        
        // Insert student data
        $stmt = $conn->prepare("INSERT INTO SinhVien (MaSV, HoTen, GioiTinh, NgaySinh, Hinh, MaNganh) 
                              VALUES (:masv, :hoten, :gioitinh, :ngaysinh, :hinh, :manganh)");
        $stmt->bindParam(':masv', $masv);
        $stmt->bindParam(':hoten', $hoten);
        $stmt->bindParam(':gioitinh', $gioitinh);
        $stmt->bindParam(':ngaysinh', $ngaysinh);
        $stmt->bindParam(':hinh', $hinh);
        $stmt->bindParam(':manganh', $manganh);
        $stmt->execute();
        
        // Redirect to index page after successful insertion
        header("Location: index.php");
        exit();
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<div class="card">
    <div class="card-header bg-success text-white">
        <h4>Thêm sinh viên mới</h4>
    </div>
    <div class="card-body">
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="masv">Mã sinh viên</label>
                <input type="text" class="form-control" id="masv" name="masv" required>
            </div>
            
            <div class="form-group">
                <label for="hoten">Họ tên</label>
                <input type="text" class="form-control" id="hoten" name="hoten" required>
            </div>
            
            <div class="form-group">
                <label>Giới tính</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gioitinh" id="nam" value="Nam" checked>
                        <label class="form-check-label" for="nam">Nam</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gioitinh" id="nu" value="Nữ">
                        <label class="form-check-label" for="nu">Nữ</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="ngaysinh">Ngày sinh</label>
                <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" required>
            </div>
            
            <div class="form-group">
                <label for="hinh">Hình</label>
                <input type="file" class="form-control-file" id="hinh" name="hinh">
            </div>
            
            <div class="form-group">
                <label for="manganh">Ngành học</label>
                <select class="form-control" id="manganh" name="manganh" required>
                    <option value="">-- Chọn ngành học --</option>
                    <?php foreach($majors as $major): ?>
                        <option value="<?php echo $major['MaNganh']; ?>"><?php echo $major['TenNganh']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Trở về
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>