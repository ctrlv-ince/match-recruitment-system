<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

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
    return count($common_parts) >= 2;
}

// Fetch all job postings
$sql = "SELECT job_postings.*, users.full_name 
        FROM job_postings 
        JOIN users ON job_postings.employer_id = users.user_id 
        ORDER BY job_postings.created_at DESC";
$job_postings_result = $conn->query($sql);

// Fetch all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Job Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        .match-percentage {
            font-weight: bold;
        }

        .perfect-match {
            color: #28a745;
        }

        .good-match {
            color: #5cb85c;
        }

        .partial-match {
            color: #f0ad4e;
        }

        .low-match {
            color: #d9534f;
        }

        .location-match {
            color: #28a745;
        }

        .location-partial {
            color: #f0ad4e;
        }

        .location-mismatch {
            color: #d9534f;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table thead th {
            background-color: #0073b1;
            color: white;
            font-weight: 500;
            border-bottom: none;
            padding: 12px 15px;
            position: sticky;
            top: 0;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 115, 177, 0.05);
        }

        .table td,
        .table th {
            border-right: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table td:first-child,
        .table th:first-child {
            border-left: 1px solid #e0e0e0;
        }

        .table tr:first-child th:first-child {
            border-top-left-radius: 8px;
        }

        .table tr:first-child th:last-child {
            border-top-right-radius: 8px;
        }

        .table tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .table tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        /* Modal table specific styles */
        .modal-content .table {
            margin-top: 15px;
        }

        .modal-content .table thead th {
            background-color: #0a66c2;
        }

        /* Improved button styles in tables */
        .table .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 4px;
        }

        /* Document list styles */
        .table .list-unstyled {
            margin-bottom: 0;
        }

        .table .list-unstyled li {
            padding: 2px 0;
        }

        .table .list-unstyled li a {
            color: #0a66c2;
            text-decoration: none;
        }

        .table .list-unstyled li a:hover {
            text-decoration: underline;
        }

        /* Status text styles */
        .table .text-muted {
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Include sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main content area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Active Job Listings</h2>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Employer</th>
                                <th>Quota</th>
                                <th>Candidates</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch active job postings
                            $sql = "SELECT job_postings.*, users.full_name 
                                    FROM job_postings 
                                    JOIN users ON job_postings.employer_id = users.user_id 
                                    WHERE job_postings.status = 'approved' 
                                    ORDER BY job_postings.created_at DESC";
                            $active_jobs_result = $conn->query($sql);

                            if ($active_jobs_result->num_rows > 0) {
                                while ($row = $active_jobs_result->fetch_assoc()) {
                                    $job_id = $row['job_id'];
                                    echo "<tr>";
                                    echo "<td>{$row['title']}</td>";
                                    echo "<td>{$row['full_name']}</td>";
                                    echo "<td>{$row['quota']}</td>";

                                    // Fetch candidates for this job
                                    $sql_candidates = "SELECT applications.*, users.full_name, job_seekers.skills, job_seekers.location
                                                      FROM applications 
                                                      JOIN users ON applications.seeker_id = users.user_id 
                                                      JOIN job_seekers ON applications.seeker_id = job_seekers.seeker_id
                                                      WHERE applications.job_id = $job_id";
                                    $candidates_result = $conn->query($sql_candidates);

                                    echo "<td>";
                                    if ($candidates_result->num_rows > 0) {
                                        echo "<button type='button' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#candidatesModal{$job_id}'>
                                            View {$candidates_result->num_rows} Candidates
                                        </button>";

                                        // Create modal for candidates
                                        echo "<div class='modal fade' id='candidatesModal{$job_id}' tabindex='-1' aria-labelledby='candidatesModalLabel{$job_id}' aria-hidden='true'>
                                            <div class='modal-dialog modal-xl'>
                                                <div class='modal-content'>
                                                    <div class='modal-header'>
                                                        <h5 class='modal-title' id='candidatesModalLabel{$job_id}'>Candidates for {$row['title']}</h5>
                                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                    </div>
                                                    <div class='modal-body'>
                                                        <div class='row mb-3'>
                                                            <div class='col-md-12'>
                                                                <h6>Job Requirements:</h6>
                                                                <p><strong>Skills:</strong> {$row['skills']}</p>
                                                                <p><strong>Location:</strong> {$row['location']}</p>
                                                            </div>
                                                        </div>
                                                        <table class='table table-bordered'>
                                                            <thead>
                                                                <tr>
                                                                    <th>Candidate</th>
                                                                    <th>Skills Match</th>
                                                                    <th>Location Match</th>
                                                                    <th>Status</th>
                                                                    <th>Documents</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>";

                                        // Reset the pointer to the beginning of the result set
                                        $candidates_result->data_seek(0);

                                        while ($candidate = $candidates_result->fetch_assoc()) {
                                            $application_id = $candidate['application_id'];
                                            $seeker_id = $candidate['seeker_id'];
                                            $seeker_skills = $candidate['skills'];
                                            $seeker_location = $candidate['location'];

                                            // Calculate skill match percentage
                                            $matchPercentage = getSkillMatchPercentage($seeker_skills, $row['skills']);

                                            // Determine skill match class
                                            $skillMatchClass = '';
                                            if ($matchPercentage == 100) {
                                                $skillMatchClass = 'perfect-match';
                                            } elseif ($matchPercentage >= 75) {
                                                $skillMatchClass = 'good-match';
                                            } elseif ($matchPercentage >= 50) {
                                                $skillMatchClass = 'partial-match';
                                            } else {
                                                $skillMatchClass = 'low-match';
                                            }

                                            // Check location match
                                            $locationMatch = false;
                                            $locationMatchClass = 'location-mismatch';
                                            $locationMatchText = 'Different location';

                                            if ($row['location'] === $seeker_location) {
                                                $locationMatch = true;
                                                $locationMatchClass = 'location-match';
                                                $locationMatchText = 'Exact match';
                                            } elseif (areLocationsNearby($row['location'], $seeker_location)) {
                                                $locationMatch = true;
                                                $locationMatchClass = 'location-partial';
                                                $locationMatchText = 'Nearby location';
                                            }

                                            echo "<tr>
                                                <td>{$candidate['full_name']}</td>
                                                <td>
                                                    <span class='match-percentage {$skillMatchClass}'>
                                                        {$matchPercentage}% match
                                                    </span><br>
                                                    <small>{$seeker_skills}</small>
                                                </td>
                                                <td class='{$locationMatchClass}'>
                                                    {$locationMatchText}<br>
                                                    <small>{$seeker_location}</small>
                                                </td>
                                                <td>" . ($candidate['status'] ?? 'pending') . "</td>
                                                <td>";

                                            // Fetch uploaded documents for this candidate's application
                                            $sql_documents = "SELECT application_documents.document_type, application_documents.document_path 
                                                              FROM application_documents 
                                                              JOIN applications ON application_documents.application_id = applications.application_id 
                                                              WHERE applications.seeker_id = $seeker_id AND applications.job_id = $job_id";
                                            $documents_result = $conn->query($sql_documents);

                                            if ($documents_result->num_rows > 0) {
                                                echo "<ul class='list-unstyled'>";
                                                while ($document = $documents_result->fetch_assoc()) {
                                                    echo "<li><a href='../job_seeker/{$document['document_path']}' target='_blank'>{$document['document_type']}</a></li>";
                                                }
                                                echo "</ul>";
                                            } else {
                                                echo "No documents uploaded.";
                                            }

                                            echo "</td>
                                                <td>";

                                            // Only show "Shortlist" and "Reject" buttons for candidates with status 'applied'
                                            if ($candidate['status'] === 'applied') {
                                                // Hide buttons if the hiring process is completed
                                                if ($candidate['employer_decision'] === 'approved' || $candidate['employer_decision'] === 'rejected') {
                                                    echo "<p class='text-muted'>Hiring process completed.</p>";
                                                } else {
                                                    echo "<form action='shortlist_candidate.php' method='POST' style='display:inline;'>
                                                        <input type='hidden' name='application_id' value='{$application_id}'>
                                                        <button type='submit' class='btn btn-success btn-sm'>Shortlist</button>
                                                    </form>
                                                    <form action='reject_candidate.php' method='POST' style='display:inline;'>
                                                        <input type='hidden' name='application_id' value='{$application_id}'>
                                                        <button type='submit' class='btn btn-danger btn-sm'>Reject</button>
                                                    </form>";
                                                }
                                            } else {
                                                echo "<p class='text-muted'>Status: {$candidate['status']}</p>";
                                            }

                                            echo "</td>
                                            </tr>";
                                        }

                                        echo "</tbody>
                                                        </table>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>";
                                    } else {
                                        echo "No candidates yet.";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No active job listings found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>