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

// Initialize variables
$error = '';
$success = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate and sanitize inputs
        $name = trim(htmlspecialchars($_POST['name'] ?? ''));
        $type = trim(htmlspecialchars($_POST['type'] ?? ''));
        $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $quantity = filter_var($_POST['quantity'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        
        // Validate required fields
        if (empty($name)) {
            throw new Exception("Product name is required!");
        }
        
        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Invalid price value!");
        }
        
        if (!is_numeric($quantity) || $quantity < 0) {
            throw new Exception("Invalid quantity value!");
        }
        
        // Insert into MySQL database (without image_path)
        $stmt = $conn->prepare("INSERT INTO products (name, type, price, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $name, $type, $price, $quantity);
        
        if ($stmt->execute()) {
            $success = "Product added successfully!";
            // Clear form
            $name = $type = $price = $quantity = '';
            header("Location: index.php?success=Product+added+successfully");
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Liquor Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="form-container bg-white p-4 rounded shadow">
            <h2 class="mb-4"><i class="bi bi-plus-circle"></i> Add New Product</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label required-field">Product Name</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($name ?? '') ?>" 
                                   class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Product Type</label>
                            <input type="text" name="type" id="type" value="<?= htmlspecialchars($type ?? '') ?>" 
                                   class="form-control">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="price" class="form-label required-field">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" id="price" value="<?= htmlspecialchars($price ?? '') ?>" 
                                       step="0.01" min="0.01" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label required-field">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="<?= htmlspecialchars($quantity ?? '') ?>" 
                                   min="0" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="index.php" class="btn btn-outline-secondary me-md-2">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>