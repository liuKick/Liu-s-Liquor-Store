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

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    header("Location: index.php?error=Product+not+found");
    exit();
}

// Handle form submission
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
        
        // Update product in database
        $stmt = $conn->prepare("UPDATE products SET name = ?, type = ?, price = ?, quantity = ? WHERE id = ?");
        $stmt->bind_param("ssdii", $name, $type, $price, $quantity, $id);
        
        if ($stmt->execute()) {
            header("Location: index.php?success=Product+updated+successfully");
            exit();
        } else {
            throw new Exception("Update failed: " . $conn->error);
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
    <title>Edit Product | Liquor Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container py-5">
        <div class="form-container">
            <div class="text-center mb-4">
                <h2 class="fw-bold"><i class="bi bi-pencil-square"></i> Edit Product</h2>
                <p class="text-muted">Update the product details below</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($row['name']) ?>" required>
                            <div class="invalid-feedback">Please enter a product name.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="type" class="form-label">Product Type</label>
                            <input type="text" class="form-control" id="type" name="type" 
                                   value="<?= htmlspecialchars($row['type']) ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0.01" value="<?= htmlspecialchars($row['price']) ?>" required>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   min="0" value="<?= htmlspecialchars($row['quantity']) ?>" required>
                            <div class="invalid-feedback">Please enter a valid quantity.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>