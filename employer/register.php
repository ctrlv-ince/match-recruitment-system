<?php
include '../db.php';

$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $company_name = $_POST['company_name'];
    $user_type = 'employer';

    // Insert into users table
    $sql = "INSERT INTO users (full_name, email, password_hash, user_type) VALUES ('$full_name', '$email', '$password', '$user_type')";
    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id;

        // Insert into employers table
        $sql = "INSERT INTO employers (employer_id, company_name) VALUES ($user_id, '$company_name')";
        if ($conn->query($sql) === TRUE) {
            // Handle file uploads
            $document_types = [
                'business_permit', 
                'sec_dti_registration', 
                'tin', 
                'bir_certificate', 
                'official_documents'
            ];

            foreach ($document_types as $type) {
                if (isset($_FILES[$type]) && $_FILES[$type]['error'] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES[$type]['name'];
                    $file_tmp = $_FILES[$type]['tmp_name'];
                    $file_path = "uploads/employers/$user_id/$type/$file_name";

                    // Create directory if it doesn't exist
                    if (!is_dir(dirname($file_path))) {
                        mkdir(dirname($file_path), 0777, true);
                    }

                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // Insert document into employer_documents table
                        $sql = "INSERT INTO employer_documents (employer_id, document_type, document_path) VALUES ($user_id, '$type', '$file_path')";
                        $conn->query($sql);
                    }
                }
            }

            $message = "Registration successful! Your documents are under review.";
        } else {
            // Rollback user insertion if employer insertion fails
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            $message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Employer Registration</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
                <form action="register.php" method="POST" enctype="multipart/form-data">
            <!-- Existing fields -->
            <div class="mb-3">
                <label for="business_permit" class="form-label">Business Permit</label>
                <input type="file" class="form-control" id="business_permit" name="business_permit" required>
            </div>
            <div class="mb-3">
                <label for="barangay_clearance" class="form-label">Barangay Clearance</label>
                <input type="file" class="form-control" id="barangay_clearance" name="barangay_clearance" required>
            </div>
            <div class="mb-3">
                <label for="sec_dti_registration" class="form-label">SEC/DTI Registration</label>
                <input type="file" class="form-control" id="sec_dti_registration" name="sec_dti_registration" required>
            </div>
            <div class="mb-3">
                <label for="tin" class="form-label">Company TIN</label>
                <input type="file" class="form-control" id="tin" name="tin" required>
            </div>
            <div class="mb-3">
                <label for="bir_certificate" class="form-label">BIR Certificate</label>
                <input type="file" class="form-control" id="bir_certificate" name="bir_certificate" required>
            </div>
            <div class="mb-3">
                <label for="official_documents" class="form-label">Official Documents (ID, Proof of Address)</label>
                <input type="file" class="form-control" id="official_documents" name="official_documents" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>