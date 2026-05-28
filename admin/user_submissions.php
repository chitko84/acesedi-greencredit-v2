<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// ensure DB connection exists
if (!isset($conn) || (isset($conn) && $conn->connect_errno)) {
    $_SESSION['error'] = 'Database connection error.';
    header('Location: manageuser.php');
    exit();
}

// get and validate user_id (the page shows submissions where this user is submitter OR team member)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$user_id) {
    header('Location: manageuser.php');
    exit();
}

// get user's name (used to search JSON array and heading)
$user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
if (!$user_stmt) {
    die("DB prepare error: " . $conn->error);
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
if (method_exists($user_stmt, 'get_result')) {
    $user_res = $user_stmt->get_result();
    $user_row = $user_res->fetch_assoc();
} else {
    $user_stmt->bind_result($u_name_fetched);
    $user_stmt->fetch();
    $user_row = ['name' => $u_name_fetched ?? null];
}
$user_stmt->close();

$user_name = $user_row['name'] ?? 'Unknown User';

// Pagination configuration
$per_page_param = $_GET['per_page'] ?? '10';
$allowed_per_page = ['10', '25', '50', '100', 'all'];
$per_page_param = in_array($per_page_param, $allowed_per_page, true) ? $per_page_param : '10';
$results_per_page = (int)($per_page_param === 'all' ? 10 : $per_page_param);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Prepare JSON_CONTAINS argument: a JSON string value with quotes, e.g. '"John Doe"'
$name_json = '"' . $user_name . '"';

// Count total matching rows for pagination (submitter OR member)
$count_query = "SELECT COUNT(*) AS total 
                FROM submissions s
                WHERE s.user_id = ? OR JSON_CONTAINS(s.team_members, ?)";
$count_stmt = $conn->prepare($count_query);
if (!$count_stmt) {
    die("DB prepare error (count): " . $conn->error);
}
$count_stmt->bind_param("is", $user_id, $name_json);
$count_stmt->execute();
if (method_exists($count_stmt, 'get_result')) {
    $count_res = $count_stmt->get_result();
    $total_rows = (int)$count_res->fetch_assoc()['total'];
} else {
    // mysqlnd absent fallback
    $count_stmt->bind_result($total_count_fetched);
    $count_stmt->fetch();
    $total_rows = (int)($total_count_fetched ?? 0);
}
$count_stmt->close();

$results_per_page = $per_page_param === 'all' ? max(1, (int)$total_rows) : $results_per_page;
$offset = $per_page_param === 'all' ? 0 : (($page - 1) * $results_per_page);
$total_pages = $per_page_param === 'all' ? 1 : (($total_rows > 0) ? (int)ceil($total_rows / $results_per_page) : 0);

// Ensure page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    header("Location: ?user_id=" . $user_id . "&page=" . $total_pages);
    exit();
}

// Fetch paginated submissions with submitter name (join)
$query = "
    SELECT s.*, u.name AS submitter_name
    FROM submissions s
    LEFT JOIN users u ON u.id = s.user_id
    WHERE s.user_id = ? OR JSON_CONTAINS(s.team_members, ?)
    ORDER BY s.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("DB prepare error (select): " . $conn->error);
}
$stmt->bind_param("isii", $user_id, $name_json, $results_per_page, $offset);
$stmt->execute();

if (method_exists($stmt, 'get_result')) {
    $submissions_result = $stmt->get_result();
} else {
    // If mysqlnd not available the codebase likely won't support fetching arbitrary columns easily.
    // For simplicity, fallback by executing a non-prepared query (escaped) — less ideal but workable.
    // Build a safe-escaped query as fallback.
    $escaped_name_json = $conn->real_escape_string($name_json);
    $fallback_sql = "
        SELECT s.*, u.name AS submitter_name
        FROM submissions s
        LEFT JOIN users u ON u.id = s.user_id
        WHERE s.user_id = " . intval($user_id) . " OR JSON_CONTAINS(s.team_members, '$escaped_name_json')
        ORDER BY s.created_at DESC
        LIMIT " . intval($results_per_page) . " OFFSET " . intval($offset);
    $submissions_result = $conn->query($fallback_sql);
    if (!$submissions_result) {
        die("DB query error (fallback): " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Submissions for <?= htmlspecialchars($user_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/submissions.css" />
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        .table-responsive { margin: 0 auto; max-width: 100%; overflow-x: auto; }
        table.table { margin-left: auto; margin-right: auto; width: 100%; max-width: 1200px; }
        .evidence-btn { white-space: nowrap; }
        .file-icon { margin-right: 5px; }
        .pagination { justify-content: center; margin-top: 20px; }
        .page-info { text-align: center; margin: 10px 0; color: #6c757d; }
        @media (max-width: 991.98px) {
            .container { padding: 30px 15px; }
            .row { display: flex; flex-direction: column; align-items: center; }
            .col-md-4, .col-md-8 { width: 100%; margin-bottom: 20px; }
            .table td, .table th { font-size: 0.9rem; padding: 0.5rem; }
        }
        @media (max-width: 768px) {
            .container { padding: 20px 10px; }
            h2.text-center { font-size: 1.5rem; }
            .table td, .table th { padding: 0.75rem 0.5rem; }
            .pagination .page-link { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        }
        @media (max-width: 576px) {
            .container { padding: 15px 5px; }
            h2.text-center { font-size: 1.25rem; }
            .table td, .table th { padding: 0.5rem; }
            .pagination .page-item { margin: 0 2px; }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-5" style="box-shadow: 0 0 20px rgba(0,0,0,0.2); border-radius: 15px; padding: 40px 20px; background: #fff; width: 100%; max-width: 1300px; margin: 0 auto;">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0">Submissions for <?= htmlspecialchars($user_name) ?></h2>
            <div class="d-flex gap-2 flex-wrap">
                <a href="export_csv.php?table=submissions" class="btn btn-outline-success">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="user_id" value="<?= (int)$user_id ?>">
                    <label for="per_page" class="mb-0 fw-semibold">Rows</label>
                    <select id="per_page" name="per_page" class="form-select" onchange="this.form.submit()">
                        <?php foreach (['10','25','50','100','all'] as $option): ?>
                            <option value="<?= $option ?>" <?= $per_page_param === $option ? 'selected' : '' ?>><?= $option === 'all' ? 'All rows' : $option . ' rows' ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-inline-flex align-items-center gap-2">
                <span class="badge bg-primary fs-6">Total Records: <?= number_format($total_rows) ?></span>
                <div class="btn-group">
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
        </div>
    </div>

    <?php 
    if (!empty($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
    }
    if (!empty($_SESSION['success'])) {
        echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
        unset($_SESSION['success']);
    }
    ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
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
                </tr>
            </thead>
            <tbody>
            <?php if ($total_rows > 0): ?>
                <?php while ($submission = $submissions_result->fetch_assoc()): 
                    // Team members
                    $team_members = json_decode($submission['team_members'], true);
                    if (!is_array($team_members)) { $team_members = []; }

                    // Verified date
                    $verified_date = $submission['verified_date'] ?? null;
                    $verified_date_formatted = ($verified_date && $verified_date !== '0000-00-00 00:00:00')
                        ? date('d M Y, H:i', strtotime($verified_date))
                        : '-';

                    // Evidence
                    $evidence = json_decode($submission['proof_image'], true);
                    $evidence = is_array($evidence) ? $evidence : (!empty($submission['proof_image']) ? [$submission['proof_image']] : []);
                    $modalId = "evidenceModalUser" . $submission['id'];

                    // Description trimmed/expandable
                    $description_raw = $submission['description'] ?? '-';
                    $description = htmlspecialchars($description_raw);
                    $max_length = 50;
                    $has_long_desc = strlen($description) > $max_length;
                    $short_description = $has_long_desc ? substr($description, 0, $max_length) . '...' : $description;
                ?>
                <tr>
                    <td><?= htmlspecialchars($submission['id']); ?></td>
                    <td>
                        <?php 
                        if (strtolower($submission['category']) === 'low impact') {
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
                    <td><?= $verified_date_formatted; ?></td>
                    <td><?= htmlspecialchars($submission['team_number'] ?? '-'); ?></td>
                    <td>
                        <?php 
                        if (!empty($team_members)) {
                            $bold_names = array_map(function($name){ return "<strong>" . htmlspecialchars($name) . "</strong>"; }, $team_members);
                            echo implode(", ", $bold_names);
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($submission['submitter_name'] ?? 'Unknown'); ?></td>
                    <td><?= htmlspecialchars($submission['three_zero_cluster'] ?? '-'); ?></td>
                    <td>
                        <?php if (!empty($evidence)) { ?>
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
                                    <?php foreach ($evidence as $file):
                                        $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                                        $file_path = "../user/uploads/" . htmlspecialchars($file);
                                    ?>
                                        <div class="mb-4">
                                            <?php if (in_array(strtolower($file_ext), ['jpg','jpeg','png','gif'])): ?>
                                                <img src="<?= $file_path ?>" alt="Evidence Image" class="img-fluid mb-2" style="max-height:300px;">
                                                <div class="text-center mt-2">
                                                    <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            <?php elseif (strtolower($file_ext) === 'pdf'): ?>
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
                                    <?php endforeach; ?>
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
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="14" class="text-center">No submissions found for this user.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <div class="page-info">
        Showing <?= (($page - 1) * $results_per_page) + 1 ?> to 
        <?= min($page * $results_per_page, $total_rows) ?> of <?= number_format($total_rows) ?>
    </div>
    
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <!-- Previous Page Link -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?user_id=<?= $user_id ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            
            <?php
            // Show limited page numbers with ellipsis
            $max_visible_pages = 5;
            $start_page = max(1, $page - floor($max_visible_pages / 2));
            $end_page = min($total_pages, $start_page + $max_visible_pages - 1);
            if ($end_page - $start_page < $max_visible_pages - 1) {
                $start_page = max(1, $end_page - $max_visible_pages + 1);
            }
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?user_id=' . $user_id . '&page=1">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active = $i == $page ? 'active' : '';
                echo '<li class="page-item ' . $active . '"><a class="page-link" href="?user_id=' . $user_id . '&page=' . $i . '">' . $i . '</a></li>';
            }
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="?user_id=' . $user_id . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
            }
            ?>
            
            <!-- Next Page Link -->
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?user_id=<?= $user_id ?>&page=<?= $page + 1 ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    // Sorting functionality
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

    function getSortValue(row, sortBy) {
        switch (sortBy) {
            case 'category': return row.cells[2].textContent.toLowerCase();
            case 'cluster':  return row.cells[11].textContent.toLowerCase();
            case 'month':    return row.cells[6].textContent;
            default:         return '';
        }
    }

    // Read More / Read Less
    document.querySelectorAll('.read-more').forEach(function(link) {
        link.addEventListener('click', function() {
            const fullDesc  = this.previousElementSibling;
            const shortDesc = fullDesc.previousElementSibling;
            if (fullDesc.style.display === 'none' || fullDesc.style.display === '') {
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
