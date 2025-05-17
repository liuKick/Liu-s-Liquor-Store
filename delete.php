<?php
include 'config.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=Invalid+product+ID");
    exit();
}
$id = (int)$_GET['id'];

try {
    // Delete the product
    $sql = "DELETE FROM products WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    
    if (!sqlsrv_execute($stmt)) {
        throw new Exception("Delete failed: " . print_r(sqlsrv_errors(), true));
    }
    
    // Check if any rows were affected
    $rowsAffected = sqlsrv_rows_affected($stmt);
    if ($rowsAffected === false || $rowsAffected < 1) {
        throw new Exception("Product not found or already deleted");
    }
    
    header("Location: index.php?success=Product+deleted+successfully");
    exit();
    
} catch (Exception $e) {
    // Log the error (in a real app, you'd log to a file)
    error_log($e->getMessage());
    
    // Redirect with error message
    header("Location: index.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>