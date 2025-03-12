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

// Fetch approved jobs that match the search term, skills, and location
$sql = "SELECT *, 
        CASE 
            WHEN skills LIKE '%$seeker_skills%' THEN 1 
            ELSE 0 
        END AS skill_match,
        CASE 
            WHEN location = '$seeker_location' THEN 1 
            ELSE 0 
        END AS location_match 
        FROM job_postings 
        WHERE status = 'approved' AND quota > 0 AND (title LIKE '%$search%' OR skills LIKE '%$search%') 
        ORDER BY location_match DESC, skill_match DESC, title ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Search Jobs</h2>
        <form action="search_jobs.php" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by title or skills..." value="<?php echo $search; ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <h3>Job Listings</h3>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card mb-3'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>{$row['title']}</h5>";
                echo "<p class='card-text'>{$row['description']}</p>";
                echo "<p class='card-text'><strong>Requirements:</strong> {$row['requirements']}</p>";
                echo "<p class='card-text'><strong>Skills:</strong> {$row['skills']}</p>";
                echo "<p class='card-text'><strong>Location:</strong> {$row['location']}</p>";
                if (!empty($seeker_skills) && strpos($row['skills'], $seeker_skills) !== false) {
                    echo "<p class='text-success'><strong>This job matches your skills!</strong></p>";
                }
                if ($row['location'] === $seeker_location) {
                    echo "<p class='text-success'><strong>This job is in your location!</strong></p>";
                }
                echo "<a href='view_job.php?id={$row['job_id']}' class='btn btn-primary'>View Details</a>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No jobs found.</p>";
        }
        ?>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>