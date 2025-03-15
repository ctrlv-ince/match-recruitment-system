<?php
include '../db.php';

$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $message = 'Error: Passwords do not match.';
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoSeekr - Job Seeker Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0073b1;
            --secondary-color: #006097;
            --light-bg: #f3f2ef;
        }

        body {
            background-color: var(--light-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .registration-container {
            max-width: 800px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 30px auto;
        }

        .section-header {
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 24px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .upload-box {
            border: 2px dashed #dee2e6;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .upload-icon {
            font-size: 24px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .upload-label {
            cursor: pointer;
            color: var(--primary-color);
            font-weight: 500;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }

        /* Password strength indicator */
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
        }

        .weak {
            width: 33%;
            background-color: #dc3545;
        }

        .medium {
            width: 66%;
            background-color: #ffc107;
        }

        .strong {
            width: 100%;
            background-color: #28a745;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .progress {
            height: 8px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand logo" href="#">GoSeekr</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Sign In</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="container">
        <div class="registration-container">
            <h2 class="text-center mb-4">Join the GoSeekr Job Seeker Network</h2>
            <p class="text-center text-muted mb-4">Connect with top employers and find your dream job</p>

            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?> mb-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Progress Bar -->
            <div class="progress mb-4">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" role="progressbar" style="width: 33%"></div>
            </div>

            <form action="register.php" method="POST" enctype="multipart/form-data" id="registrationForm">
                <!-- Step 1: Account Information -->
                <div class="form-step active" id="step1">
                    <h4 class="section-header">Account Information</h4>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <small class="text-muted">Password must be at least 8 characters long with letters, numbers and special characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordMatch" class="form-text"></div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary" id="nextToStep2">Next <i class="fas fa-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!-- Step 2: Personal Information -->
                <div class="form-step" id="step2">
                    <h4 class="section-header">Personal Information</h4>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
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

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="backToStep1"><i class="fas fa-arrow-left me-2"></i> Back</button>
                        <button type="button" class="btn btn-primary" id="nextToStep3">Next <i class="fas fa-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!-- Step 3: Document Uploads -->
                <div class="form-step" id="step3">
                    <h4 class="section-header">Required Documents</h4>
                    <p class="text-muted mb-4">Upload the following documents to verify your identity. Accepted formats: PDF, JPG, PNG</p>

                    <div class="upload-box mb-3">
                        <div class="upload-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <label for="valid_id" class="upload-label">Valid ID</label>
                        <input type="file" class="form-control d-none" id="valid_id" name="valid_id" required>
                        <p class="selected-file-name mt-2 mb-0" id="valid_id_name">No file selected</p>
                    </div>

                    <div class="upload-box mb-3">
                        <div class="upload-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <label for="tin" class="upload-label">Tax Identification Number (TIN)</label>
                        <input type="file" class="form-control d-none" id="tin" name="tin" required>
                        <p class="selected-file-name mt-2 mb-0" id="tin_name">No file selected</p>
                    </div>

                    <div class="upload-box mb-3">
                        <div class="upload-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <label for="resume" class="upload-label">Resume or CV</label>
                        <input type="file" class="form-control d-none" id="resume" name="resume" required>
                        <p class="selected-file-name mt-2 mb-0" id="resume_name">No file selected</p>
                    </div>

                    <div class="upload-box mb-3">
                        <div class="upload-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <label for="photo" class="upload-label">Recent Photo</label>
                        <input type="file" class="form-control d-none" id="photo" name="photo" required>
                        <p class="selected-file-name mt-2 mb-0" id="photo_name">No file selected</p>
                    </div>

                    <div class="upload-box mb-3">
                        <div class="upload-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <label for="qualification" class="upload-label">Qualification/Training Documents</label>
                        <input type="file" class="form-control d-none" id="qualification" name="qualification">
                        <p class="selected-file-name mt-2 mb-0" id="qualification_name">No file selected</p>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="backToStep2"><i class="fas fa-arrow-left me-2"></i> Back</button>
                        <button type="submit" class="btn btn-primary">Create Account <i class="fas fa-check ms-2"></i></button>
                    </div>
                </div>
            </form>
            <div class="text-center mt-4">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2025 GoSeekr. All rights reserved.</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Multi-step form navigation
            const progressBar = document.getElementById('progressBar');
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const step3 = document.getElementById('step3');

            // Next buttons
            document.getElementById('nextToStep2').addEventListener('click', function() {
                // Validate step 1
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (!email || !password || !confirmPassword) {
                    alert('Please fill in all fields');
                    return;
                }

                if (password !== confirmPassword) {
                    alert('Passwords do not match');
                    return;
                }

                step1.classList.remove('active');
                step2.classList.add('active');
                progressBar.style.width = '66%';
            });

            document.getElementById('nextToStep3').addEventListener('click', function() {
                // Validate step 2
                const fullName = document.getElementById('full_name').value;
                const location = document.getElementById('location').value;
                const skills = document.getElementById('skills').value;

                if (!fullName || !location || !skills) {
                    alert('Please fill in all fields');
                    return;
                }

                step2.classList.remove('active');
                step3.classList.add('active');
                progressBar.style.width = '100%';
            });

            // Back buttons
            document.getElementById('backToStep1').addEventListener('click', function() {
                step2.classList.remove('active');
                step1.classList.add('active');
                progressBar.style.width = '33%';
            });

            document.getElementById('backToStep2').addEventListener('click', function() {
                step3.classList.remove('active');
                step2.classList.add('active');
                progressBar.style.width = '66%';
            });

            // Password visibility toggle
            document.getElementById('togglePassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });

            document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                const confirmPasswordInput = document.getElementById('confirm_password');
                const icon = this.querySelector('i');

                if (confirmPasswordInput.type === 'password') {
                    confirmPasswordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    confirmPasswordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });

            // Password strength indicator
            document.getElementById('password').addEventListener('input', function() {
                const password = this.value;
                const strength = document.getElementById('passwordStrength');

                // Reset the strength indicator
                strength.className = 'password-strength';

                if (!password) {
                    return;
                }

                // Check password strength
                let score = 0;

                // Length check
                if (password.length >= 8) score++;

                // Character variety checks
                if (/[A-Z]/.test(password)) score++;
                if (/[a-z]/.test(password)) score++;
                if (/[0-9]/.test(password)) score++;
                if (/[^A-Za-z0-9]/.test(password)) score++;

                // Update the strength indicator
                if (score <= 2) {
                    strength.classList.add('weak');
                } else if (score <= 4) {
                    strength.classList.add('medium');
                } else {
                    strength.classList.add('strong');
                }
            });

            // Password match indicator
            document.getElementById('confirm_password').addEventListener('input', function() {
                const password = document.getElementById('password').value;
                const confirmPassword = this.value;
                const matchIndicator = document.getElementById('passwordMatch');

                if (!confirmPassword) {
                    matchIndicator.textContent = '';
                    return;
                }

                if (password === confirmPassword) {
                    matchIndicator.textContent = 'Passwords match';
                    matchIndicator.style.color = '#28a745';
                } else {
                    matchIndicator.textContent = 'Passwords do not match';
                    matchIndicator.style.color = '#dc3545';
                }
            });

            // File upload handling
            const fileInputs = [{
                    input: 'valid_id',
                    display: 'valid_id_name'
                },
                {
                    input: 'tin',
                    display: 'tin_name'
                },
                {
                    input: 'resume',
                    display: 'resume_name'
                },
                {
                    input: 'photo',
                    display: 'photo_name'
                },
                {
                    input: 'qualification',
                    display: 'qualification_name'
                }
            ];

            fileInputs.forEach(file => {
                // Make the whole upload box clickable
                document.querySelector(`label[for="${file.input}"]`).parentElement.addEventListener('click', function() {
                    document.getElementById(file.input).click();
                });

                // Display selected filename
                document.getElementById(file.input).addEventListener('change', function() {
                    if (this.files.length > 0) {
                        document.getElementById(file.display).textContent = this.files[0].name;
                    } else {
                        document.getElementById(file.display).textContent = 'No file selected';
                    }
                });
            });

            // Form validation before submit
            document.getElementById('registrationForm').addEventListener('submit', function(event) {
                // Check if terms checkbox is checked
                if (!document.getElementById('terms').checked) {
                    alert('Please agree to the Terms and Conditions');
                    event.preventDefault();
                    return;
                }

                // Validate passwords match again before submission
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (password !== confirmPassword) {
                    alert('Passwords do not match');
                    event.preventDefault();
                    return;
                }
            });
        });
    </script>
</body>

</html>