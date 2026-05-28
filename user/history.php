<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
} else {
    $_SESSION['error'] = "User data not found!";
    header('Location: ../login.php');
    exit();
}

$user_name = $user_data['name'];

// Initialize pagination variables
$total_submissions = 0;
$total_pages = 0;
$current_page = 1;
$per_page = 8;
$offset = 0;

// Only calculate pagination if there are submissions
$total_submissions_query = "
    SELECT COUNT(*) AS total_submissions
    FROM submissions s
    LEFT JOIN users u ON u.id = s.user_id
    WHERE s.user_id = ? OR JSON_CONTAINS(s.team_members, '\"" . $conn->real_escape_string($user_name) . "\"')
";

$total_submissions_stmt = $conn->prepare($total_submissions_query);
$total_submissions_stmt->bind_param("i", $user_id);
$total_submissions_stmt->execute();
$total_submissions_result = $total_submissions_stmt->get_result();
$total_submissions = $total_submissions_result->fetch_assoc()['total_submissions'];

// Only proceed with pagination and fetching submissions if there are any
if ($total_submissions > 0) {
    // Calculate total pages
    $total_pages = ceil($total_submissions / $per_page);

    // Get the current page
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($current_page < 1) $current_page = 1;
    if ($current_page > $total_pages) $current_page = $total_pages;

    // Calculate the offset for the query
    $offset = ($current_page - 1) * $per_page;

    // ---------------- Fetch the submissions with pagination -------------------
    $submission_query = "
        SELECT s.*, u.name AS submitter_name
        FROM submissions s
        LEFT JOIN users u ON u.id = s.user_id
        WHERE s.user_id = ? OR JSON_CONTAINS(s.team_members, '\"" . $conn->real_escape_string($user_name) . "\"')
        ORDER BY s.created_at DESC
        LIMIT ?, ?
    ";

    $submission_stmt = $conn->prepare($submission_query);
    $submission_stmt->bind_param("iii", $user_id, $offset, $per_page);
    $submission_stmt->execute();
    $submission_result = $submission_stmt->get_result();
} else {
    // No submissions found
    $submission_result = false;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GreenCredit - Submission History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css" />
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <style>
        .table tbody tr:nth-child(1) td:first-child::before {
            content: '';
            margin-right: 8px;
        }

        .table tbody tr:nth-child(2) td:first-child::before {
            content: '';
            margin-right: 8px;
        }

        .table tbody tr:nth-child(3) td:first-child::before {
            content: '';
            margin-right: 8px;
        }
        
        .table-responsive {
            margin: 0 auto;
            max-width: 100%;
            overflow-x: auto;
        }
        table.table {
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            max-width: 1200px; /* Increased width to accommodate new column */
        }
        
        .evidence-btn {
            white-space: nowrap;
        }
        
        .file-icon {
            margin-right: 5px;
        }
        
        #preloader {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #fff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        body.loaded #preloader {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        
        .read-more {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.9rem;
        }
        
        .read-more:hover {
            color: #0056b3;
        }
        
        @media (max-width: 991.98px) {
            .container {
                padding: 30px 15px;
            }
            .row {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .col-md-4, .col-md-8 {
                width: 100%;
                margin-bottom: 20px;
            }
            .table td, .table th {
                font-size: 0.9rem;
                padding: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 10px;
            }
            .rounded-circle {
                width: 100px;
                height: 100px;
            }
            h2.text-center {
                font-size: 1.5rem;
            }
            .table td, .table th {
                padding: 0.75rem 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 15px 5px;
            }
            .rounded-circle {
                width: 80px;
                height: 80px;
            }
            h2.text-center {
                font-size: 1.25rem;
            }
            .table td, .table th {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
<!-- Preloader -->
<!--<div id="preloader">-->
<!--  <div class="spinner-border text-success" role="status">-->
<!--    <span class="visually-hidden">Loading...</span>-->
<!--  </div>-->
<!--</div>-->

<?php include 'includes/header.php'; ?>


<div class="container my-5" style="box-shadow: 0 0 20px rgba(0,0,0,0.2); border-radius: 15px; padding: 40px 20px; background: #fff; width: 100%; max-width: 1300px; margin: 0 auto;">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Your Submission History</h2>
        </div>
        <?php if ($total_submissions > 0): ?>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <a href="download_submissions_csv.php" class="btn btn-outline-primary">
                    <i class="fas fa-download"></i> Download All as CSV
                </a>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-sort"></i> Sort By
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Sort Options</h6></li>
                    <li><a class="dropdown-item sort-option" href="#" data-sort="category">Category</a></li>
                    <li><a class="dropdown-item sort-option" href="#" data-sort="month">Month</a></li>
                    <li><a class="dropdown-item sort-option" href="#" data-sort="cluster">3ZERO Cluster</a></li>
                    <li><a class="dropdown-item sort-option" href="#" data-sort="status">Status</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item sort-option" href="#" data-sort="default">Default (Newest First)</a></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Submission ID</th>
                    <th>Club ID</th>
                    <th>Category</th>
                    <th>Action</th>
                    <th>Points</th>
                    <th>Status</th>
                    <th>Submitted Date</th>
                    <th>Verification Date</th>
                    <th>Team Number</th>
                    <th>Team Members</th>
                    <th>Submitted By</th>
                    <th>3ZERO Cluster</th>
                    <th>Evidence</th>
                    <th>Description</th>
                    <th>Admin Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($total_submissions > 0 && $submission_result) {
                    while ($submission = $submission_result->fetch_assoc()) { 
                        // Decode team members JSON array (names)
                        $team_members = json_decode($submission['team_members'], true);
                        if (!is_array($team_members)) {
                            $team_members = [];
                        }

                        // Format verified date
                        $verified_date = $submission['verified_date'] ?? null;
                        if ($verified_date && $verified_date !== '0000-00-00 00:00:00') {
                            $verified_date_formatted = date('d M Y, H:i', strtotime($verified_date));
                        } else {
                            $verified_date_formatted = '-';
                        }

                        // Handle evidence (proof_image) - can be images or PDF
                        $evidence = json_decode($submission['proof_image'], true);
                        $modalId = "evidenceModal" . $submission['id'];
                        
                        // Superadmin remarks
                        $superadmin_remarks = $submission['superadmin_remarks'] ?? '';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($submission['id']); ?></td>
                        <td>
                            <?php 
                            if ($submission['category'] == 'low impact') {
                                echo '0';
                            } else {
                                echo htmlspecialchars($submission['club_id'] ?? '-');
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($submission['category']); ?></td>
                        <td><?= htmlspecialchars($submission['action']); ?></td>
                        <td><?= htmlspecialchars($submission['points']); ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                if ($submission['status'] == 'pending') echo 'bg-warning';
                                elseif ($submission['status'] == 'approved') echo 'bg-success';
                                else echo 'bg-danger';
                                ?>">
                                <?= ucfirst(htmlspecialchars($submission['status'])); ?>
                            </span>
                        </td>
                        <td><?= date('d M Y, H:i', strtotime($submission['created_at'])); ?></td>
                        <td><?= $verified_date_formatted ?></td>
                        <td><?= htmlspecialchars($submission['team_number']); ?></td>
                        <td>
                            <?php 
                            if (!empty($team_members)) {
                                $bold_names = array_map(function($name) {
                                    return "<strong>" . htmlspecialchars($name) . "</strong>";
                                }, $team_members);
                                echo implode(", ", $bold_names);
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($submission['submitter_name'] ?? 'Unknown'); ?></td>
                        <td><?= htmlspecialchars($submission['three_zero_cluster'] ?? '-'); ?></td>
                        <td>
                            <?php if (!empty($evidence) && is_array($evidence)) { ?>
                                <button class="btn btn-sm btn-primary evidence-btn" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                                    View Evidence
                                </button>

                                <!-- Evidence Modal -->
                                <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
                                  <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title" id="<?= $modalId ?>Label">Evidence for Submission #<?= htmlspecialchars($submission['id']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                        <?php foreach ($evidence as $file) { 
                                            $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                                            $file_path = "uploads/" . htmlspecialchars($file);
                                        ?>
                                            <div class="mb-4">
                                                <?php if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                    <img src="<?= $file_path ?>" alt="Evidence Image" class="img-fluid mb-2" style="max-height:300px;">
                                                    <div class="text-center mt-2">
                                                        <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                <?php elseif (strtolower($file_ext) == 'pdf'): ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-file-pdf file-icon text-danger" style="font-size: 2rem;"></i>
                                                        <a href="<?= $file_path ?>" target="_blank" class="ms-2">View PDF</a>
                                                        <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-primary ms-3">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-download"></i> Download File
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php } ?>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            <?php } else {
                                echo "No evidence submitted";
                            } ?>
                        </td>
                        <td>
                            <?php
                            $description_raw = $submission['description'] ?? '-';
                            $description = htmlspecialchars($description_raw);
                            $max_length = 50;
                            $has_long_desc = strlen($description) > $max_length;
                            $short_description = $has_long_desc ? substr($description, 0, $max_length) . '...' : $description;
                            ?>
                            
                            <?php if ($has_long_desc): ?>
                                <span class='short-description'><?= $short_description; ?></span>
                                <a href='description_view.php?id=<?= $submission['id'] ?>' class='read-more' target="_blank">
                                    Read More
                                </a>
                            <?php else: ?>
                                <?= $description; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $superadmin_remarks_display = htmlspecialchars($superadmin_remarks);
                            $remarks_max_length = 50;
                        
                            if (strlen($superadmin_remarks_display) > $remarks_max_length) {
                                $short_remarks = substr($superadmin_remarks_display, 0, $remarks_max_length) . '...';
                                echo "<span class='short-remarks'>$short_remarks</span>";
                                echo "<span class='full-remarks' style='display:none;'>$superadmin_remarks_display</span>";
                                echo "<a href='javascript:void(0)' class='read-more-remarks'>Read More</a>";
                            } else {
                                echo nl2br($superadmin_remarks_display);
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($submission['status'] == 'pending'): ?>
                                <div class="d-flex flex-column gap-2">
                                    <!--No need edit for now-->
                                    <!--<a href="edit_submission.php?id=<?= $submission['id'] ?>" class="btn btn-sm btn-warning">-->
                                    <!--    <i class="fas fa-edit"></i> Edit-->
                                    <!--</a>-->
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $submission['id'] ?>">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Cannot edit approved/rejected submissions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    }
                } else { 
                ?>
                    <tr><td colspan="16" class="text-center">No submissions found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination - Only show if there are submissions -->
    <?php if ($total_submissions > 0): ?>
    <div class="pagination-container">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active_class = ($i == $current_page) ? 'active' : '';
                    echo "<li class='page-item $active_class'><a class='page-link' href='?page=$i'>$i</a></li>";
                }
                ?>
                <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    // Delete confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const submissionId = this.getAttribute('data-id');
            const runDelete = () => {
                fetch('delete_submission.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + submissionId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Submission deleted successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the submission.');
                });
            };
            if (window.showConfirmModal) {
                window.showConfirmModal('Are you sure you want to delete this submission? This action cannot be undone.', runDelete);
            } else if (confirm('Are you sure you want to delete this submission? This action cannot be undone.')) {
                runDelete();
            }
        });
    });

    // Sorting functionality
    document.querySelectorAll('.sort-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const sortBy = this.getAttribute('data-sort');

            // Get all table rows (except header)
            const rows = Array.from(document.querySelectorAll('table tbody tr'));

            // Sort the rows based on the selected option
            rows.sort((a, b) => {
                if (sortBy === 'default') {
                    // For default sorting, sort by submission ID (col 0, higher = newer)
                    return parseInt(b.cells[0].textContent) - parseInt(a.cells[0].textContent);
                } else if (sortBy === 'month') {
                    // Submitted Date = col 6
                    const aDate = new Date(a.cells[6].textContent);
                    const bDate = new Date(b.cells[6].textContent);
                    return bDate - aDate; // Newest first
                } else {
                    const aValue = getSortValue(a, sortBy);
                    const bValue = getSortValue(b, sortBy);
                    return aValue.localeCompare(bValue);
                }
            });

            // Re-append the sorted rows
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    function getSortValue(row, sortBy) {
        switch (sortBy) {
            case 'category':
                return row.cells[2].textContent.toLowerCase(); // Category col
            case 'cluster':
                return row.cells[11].textContent.toLowerCase(); // 3ZERO Cluster col
            case 'month':
                return row.cells[6].textContent; // Submitted Date col
            default:
                return '';
        }
    }
    
    // Add event listeners to all "Read More" links for descriptions
    document.querySelectorAll('.read-more').forEach(function(link) {
        link.addEventListener('click', function() {
            var shortDesc = this.previousElementSibling.previousElementSibling;
            var fullDesc = this.previousElementSibling;
            
            // Toggle visibility of short and full descriptions
            if (fullDesc.style.display === 'none') {
                fullDesc.style.display = 'inline';
                shortDesc.style.display = 'none';
                this.textContent = 'Read Less';
            } else {
                fullDesc.style.display = 'none';
                shortDesc.style.display = 'inline';
                this.textContent = 'Read More';
            }
        });
    });
    
    // Add event listeners to all "Read More" links for superadmin remarks
    document.querySelectorAll('.read-more-remarks').forEach(function(link) {
        link.addEventListener('click', function() {
            var shortRemarks = this.previousElementSibling.previousElementSibling;
            var fullRemarks = this.previousElementSibling;
            
            // Toggle visibility of short and full remarks
            if (fullRemarks.style.display === 'none') {
                fullRemarks.style.display = 'inline';
                shortRemarks.style.display = 'none';
                this.textContent = 'Read Less';
            } else {
                fullRemarks.style.display = 'none';
                shortRemarks.style.display = 'inline';
                this.textContent = 'Read More';
            }
        });
    });
    
    document.querySelectorAll('.sort-option').forEach(option => {
    option.addEventListener('click', function(e) {
        e.preventDefault();
        const sortBy = this.getAttribute('data-sort');
        const rows = Array.from(document.querySelectorAll('table tbody tr'))
            .filter(row => row.querySelectorAll('td').length > 0); // ignore "no submissions" row

        rows.sort((a, b) => {
            if (sortBy === 'default') {
                // Submission ID col = 0
                return parseInt(b.cells[0].textContent) - parseInt(a.cells[0].textContent);
            } else if (sortBy === 'month') {
                // Submitted Date col = 6 (format: d M Y, H:i)
                const aDate = new Date(a.cells[6].textContent);
                const bDate = new Date(b.cells[6].textContent);
                return bDate - aDate; // Newest first
            } else if (sortBy === 'status') {
                // Status col = 5
                const statusOrder = { 'pending': 1, 'approved': 2, 'rejected': 3 };
                const aStatus = a.cells[5].textContent.trim().toLowerCase();
                const bStatus = b.cells[5].textContent.trim().toLowerCase();
                return statusOrder[aStatus] - statusOrder[bStatus]; // Sort by status
            } else {
                const aValue = getSortValue(a, sortBy);
                const bValue = getSortValue(b, sortBy);
                return aValue.localeCompare(bValue);
            }
        });

        const tbody = document.querySelector('table tbody');
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
    });
});
    
</script>
</body>
</html>
