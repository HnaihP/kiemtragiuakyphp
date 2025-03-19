<?php
require_once 'config.php';
include 'header.php';

// Fetch all students
try {
    $stmt = $conn->prepare("SELECT sv.*, nh.TenNganh 
                          FROM SinhVien sv 
                          JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh 
                          ORDER BY sv.MaSV");
    $stmt->execute();
    $students = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h4>Danh sách sinh viên</h4>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="create.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Thêm mới
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Mã SV</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Ngành học</th>
                        <th>Hình</th>
                        <th>Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $student): ?>
                    <tr>
                        <td><?php echo $student['MaSV']; ?></td>
                        <td><?php echo $student['HoTen']; ?></td>
                        <td><?php echo $student['GioiTinh']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($student['NgaySinh'])); ?></td>
                        <td><?php echo $student['TenNganh']; ?></td>
                        <td>
                            <?php
                            $imagePath = $student['Hinh'];
                            if (empty($imagePath)) {
                                $imagePath = 'Content/images/default.jpg';
                            } else {
                                // Xóa dấu / ở đầu nếu có
                                $imagePath = ltrim($imagePath, '/');
                            }
                            ?>
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo $student['HoTen']; ?>" class="avatar-img">
                        </td>
                        <td>
                            <a href="detail.php?id=<?php echo $student['MaSV']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-info-circle"></i> Chi tiết
                            </a>
                            <a href="edit.php?id=<?php echo $student['MaSV']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="delete.php?id=<?php echo $student['MaSV']; ?>" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>