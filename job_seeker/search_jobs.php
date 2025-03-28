<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Fetch job seeker's skills and location
$sql = "SELECT skills, location FROM job_seekers WHERE seeker_id = $user_id";
$result = $conn->query($sql);
$seeker_data = $result->fetch_assoc();
$seeker_skills = $seeker_data['skills'];
$seeker_location = $seeker_data['location'];

// Helper function to check skill matches
function getSkillMatchPercentage($seekerSkills, $jobSkills)
{
    if (empty($seekerSkills) || empty($jobSkills)) {
        return 0;
    }

    // Convert skills strings to arrays
    $seekerSkillsArray = array_map('trim', explode(',', strtolower($seekerSkills)));
    $jobSkillsArray = array_map('trim', explode(',', strtolower($jobSkills)));

    // Count matching skills
    $matchingSkills = array_intersect($seekerSkillsArray, $jobSkillsArray);
    $matchCount = count($matchingSkills);
    $jobSkillCount = count($jobSkillsArray);

    // Calculate match percentage
    return $jobSkillCount > 0 ? round(($matchCount / $jobSkillCount) * 100) : 0;
}

// Helper function to check if locations are in the same area
function areLocationsNearby($location1, $location2)
{
    // Convert to lowercase for case-insensitive comparison
    $loc1 = strtolower($location1);
    $loc2 = strtolower($location2);

    // Exact match
    if ($loc1 === $loc2) {
        return true;
    }

    // Extract city names and areas
    $loc1_parts = preg_split('/[,\s]+/', $loc1);
    $loc2_parts = preg_split('/[,\s]+/', $loc2);

    // Check for common city names or areas
    $common_parts = array_intersect($loc1_parts, $loc2_parts);

    // If there are at least 2 common parts, consider them nearby
    // This will match cases like "Lower Bicutan, Taguig City" and "Western Bicutan, Taguig City"
    return count($common_parts) >= 2;
}

// Fetch approved jobs that match the search term, skills, and location
$sql = "SELECT *, 
        CASE 
            WHEN location = '$seeker_location' THEN 1 
            ELSE 0 
        END AS location_match 
        FROM job_postings 
        WHERE status = 'approved' AND quota > 0 AND (title LIKE '%$search%' OR skills LIKE '%$search%') 
        ORDER BY location_match DESC, title ASC";
$result = $conn->query($sql);

// Fetch the number of unread notifications
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0073b1;
            --secondary-color: #0a66c2;
            --accent-color: #f5f5f5;
            --text-muted: #666;
            --border-color: #e0e0e0;
        }

        body {
            background-color: #f3f2ef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2 i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .input-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 115, 177, 0.2);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #005d91;
            border-color: #005d91;
        }

        .btn-secondary {
            background-color: var(--text-muted);
            border-color: var(--text-muted);
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background-color: #555;
            border-color: #555;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .card-text {
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .text-success {
            color: #28a745 !important;
            font-weight: 600;
        }

        .job-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .job-detail i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand brand-logo" href="dashboard.php">
                <i class="fas fa-briefcase"></i> GoSeekr
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="search_jobs.php">
                            <i class="fas fa-search"></i> Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_interview.php">
                            <i class="fas fa-calendar-check"></i> Interviews
                        </a>
                    </li>
                    <li class="nav-item nav-notification">
                        <a class="nav-link position-relative" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_profile.php">
                            <i class="fas fa-user-edit"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_applications.php">
                            <i class="fas fa-file-alt"></i> Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2><i class="fas fa-search"></i>Search Jobs</h2>
        <form action="search_jobs.php" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by title or skills..." value="<?php echo $search; ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>Search
                </button>
            </div>
        </form>

        <h3><i class="fas fa-briefcase"></i> Job Listings</h3>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $matchPercentage = getSkillMatchPercentage($seeker_skills, $row['skills']);

                echo "<div class='card mb-3'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>{$row['title']}</h5>";
                echo "<div class='job-detail'><i class='fas fa-file-alt'></i><p class='card-text'><strong>Description: </strong>{$row['description']}</p></div>";
                echo "<div class='job-detail'><i class='fas fa-clipboard-list'></i><p class='card-text'><strong>Requirements:</strong> {$row['requirements']}</p></div>";
                echo "<div class='job-detail'><i class='fas fa-tools'></i><p class='card-text'><strong>Skills:</strong> {$row['skills']}</p></div>";
                echo "<div class='job-detail'><i class='fas fa-map-marker-alt'></i><p class='card-text'><strong>Location:</strong> {$row['location']}</p></div>";

                // Skill matching feedback
                if ($matchPercentage > 0) {
                    if ($matchPercentage == 100) {
                        echo "<p class='text-success'><i class='fas fa-check-circle'></i> Your skills perfectly match all required skills!</p>";
                    } elseif ($matchPercentage >= 75) {
                        echo "<p class='text-success'><i class='fas fa-check-circle'></i> Your skills match most of the required skills ($matchPercentage% match)!</p>";
                    } elseif ($matchPercentage >= 50) {
                        echo "<p class='text-warning'><i class='fas fa-check-circle'></i> Your skills match some of the required skills ($matchPercentage% match)</p>";
                    } else {
                        echo "<p class='text-info'><i class='fas fa-info-circle'></i> Your skills partially match the required skills ($matchPercentage% match)</p>";
                    }
                } else {
                    echo "<p class='text-muted'><i class='fas fa-info-circle'></i> Your skills don't match the required skills</p>";
                }

                // Location matching feedback
                if ($row['location'] === $seeker_location) {
                    echo "<p class='text-success'><i class='fas fa-check-circle'></i>This job is in your exact location!</p>";
                } elseif (areLocationsNearby($row['location'], $seeker_location)) {
                    echo "<p class='text-success'><i class='fas fa-check-circle'></i>This job is near your location!</p>";
                } else {
                    echo "<p class='text-muted'><i class='fas fa-info-circle'></i>This job is in a different location</p>";
                }

                echo "<a href='view_job.php?id={$row['job_id']}' class='btn btn-primary'>
                    <i class='fas fa-eye'></i>View Details
                </a>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No jobs found.</p>";
        }
        ?>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>Back to Dashboard
        </a>
    </div>
</body>
<footer class="footer mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; 2025 GoSeekr. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

</html>