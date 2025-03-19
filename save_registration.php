<?php
require_once 'config.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: login.php?redirect=giohang.php");
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    $_SESSION['error_message'] = "Giỏ đăng ký trống. Không thể lưu đăng ký.";
    header("Location: giohang.php");
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Check if all courses have available slots (Câu 6)
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $conn->prepare("SELECT MaHP, SoLuong FROM HocPhan WHERE MaHP IN ($placeholders)");
    
    // Bind course IDs as parameters
    foreach ($_SESSION['cart'] as $key => $course_id) {
        $stmt->bindValue($key + 1, $course_id);
    }
    
    $stmt->execute();
    $courses = $stmt->fetchAll();
    
    // Check available slots
    $error_courses = [];
    foreach ($courses as $course) {
        if ($course['SoLuong'] <= 0) {
            $error_courses[] = $course['MaHP'];
        }
    }
    
    // If there are unavailable courses, abort registration
    if (count($error_courses) > 0) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Không thể đăng ký. Các học phần sau đã hết chỗ: " . implode(', ', $error_courses);
        header("Location: giohang.php");
        exit();
    }
    
    // Get student ID
    $masv = $_SESSION['student']['MaSV'];
    
    // Create registration entry
    $stmt = $conn->prepare("INSERT INTO DangKy (NgayDK, MaSV) VALUES (NOW(), :masv)");
    $stmt->bindParam(':masv', $masv);
    $stmt->execute();
    
    // Get the registration ID
    $madk = $conn->lastInsertId();
    
    // Insert registration details
    foreach ($_SESSION['cart'] as $course_id) {
        $stmt = $conn->prepare("INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (:madk, :mahp)");
        $stmt->bindParam(':madk', $madk);
        $stmt->bindParam(':mahp', $course_id);
        $stmt->execute();
        
        // Decrease the available slots (Câu 6)
        $stmt = $conn->prepare("UPDATE HocPhan SET SoLuong = SoLuong - 1 WHERE MaHP = :mahp");
        $stmt->bindParam(':mahp', $course_id);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Clear the cart after successful registration
    $_SESSION['cart'] = [];
    
    // Set success message and redirect
    $_SESSION['success_message'] = "Đăng ký học phần thành công! Mã đăng ký: " . $madk;
    header("Location: registration_detail.php?id=$madk");
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    $_SESSION['error_message'] = "Lỗi khi đăng ký học phần: " . $e->getMessage();
    header("Location: giohang.php");
    exit();
}
?>