<?php
include '../db.php';

$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $location = $_POST['location'];
    $skills = $_POST['skills']; // Added skills field
    $user_type = 'job_seeker';

    // Insert into users table
    $sql = "INSERT INTO users (full_name, email, password_hash, user_type) VALUES ('$full_name', '$email', '$password', '$user_type')";
    if ($conn->query($sql)) {
        $user_id = $conn->insert_id;

        // Insert into job_seekers table
        $sql = "INSERT INTO job_seekers (seeker_id, location, skills) VALUES ($user_id, '$location', '$skills')";
        if ($conn->query($sql)) {
            // Handle file uploads
            $document_types = ['valid_id', 'tin', 'resume', 'photo', 'qualification'];
            foreach ($document_types as $type) {
                if (isset($_FILES[$type]) && $_FILES[$type]['error'] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES[$type]['name'];
                    $file_tmp = $_FILES[$type]['tmp_name'];
                    $upload_dir = "uploads/job_seekers/$user_id/$type/";

                    // Create directory if it doesn't exist
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true); // Create directory recursively
                    }

                    $file_path = $upload_dir . basename($file_name);

                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // Insert document into job_seeker_documents table
                        $sql = "INSERT INTO job_seeker_documents (seeker_id, document_type, document_path) VALUES ($user_id, '$type', '$file_path')";
                        if (!$conn->query($sql)) {
                            $message = "Error uploading $type: " . $conn->error;
                            break;
                        }
                    } else {
                        $message = "Error moving uploaded file for $type.";
                        break;
                    }
                }
            }

            if (empty($message)) {
                $message = "Registration successful!";
            }
        } else {
            // Rollback user insertion if job seeker insertion fails
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
    <title>Job Seeker Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Job Seeker Registration</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <!-- Existing fields -->
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Your Skills</label>
                <input type="text" class="form-control" id="skills" name="skills" required>
                <small class="form-text text-muted">Separate skills with commas (e.g., PHP, MySQL, JavaScript).</small>
            </div>
            <div class="mb-3">
                <label for="valid_id" class="form-label">Valid ID</label>
                <input type="file" class="form-control" id="valid_id" name="valid_id" required>
            </div>
            <div class="mb-3">
                <label for="tin" class="form-label">Tax Identification Number (TIN)</label>
                <input type="file" class="form-control" id="tin" name="tin" required>
            </div>
            <div class="mb-3">
                <label for="resume" class="form-label">Resume or CV</label>
                <input type="file" class="form-control" id="resume" name="resume" required>
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Recent Photo</label>
                <input type="file" class="form-control" id="photo" name="photo" required>
            </div>
            <div class="mb-3">
                <label for="qualification" class="form-label">Qualification/Training Documents</label>
                <input type="file" class="form-control" id="qualification" name="qualification">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>