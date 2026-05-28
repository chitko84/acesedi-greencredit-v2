<?php
session_start();
include '../includes/db.php';

// -------------------------
// Pagination config
// -------------------------
$per_page_param = $_GET['per_page'] ?? '10';
$allowed_per_page = ['10', '25', '50', '100', 'all'];
$per_page_param = in_array($per_page_param, $allowed_per_page, true) ? $per_page_param : '10';
$per_page = (int)($per_page_param === 'all' ? 10 : $per_page_param);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

// -------------------------
// Fetch all users with info
// -------------------------
$user_query = $conn->query("SELECT id, name, email, date_of_birth, phone_number, 
                            program_of_study, intake, country, gender, department, 
                            expected_graduation_year, created_at, eco_points 
                            FROM users");
$users = [];
if ($user_query) {
    while ($row = $user_query->fetch_assoc()) {
        $users[$row['id']] = [
            'name' => $row['name'],
            'email' => $row['email'],
            'date_of_birth' => $row['date_of_birth'],
            'phone_number' => $row['phone_number'],
            'program_of_study' => $row['program_of_study'],
            'intake' => $row['intake'],
            'country' => $row['country'],
            'gender' => $row['gender'],
            'department' => $row['department'],
            'expected_graduation_year' => $row['expected_graduation_year'],
            'created_at' => $row['created_at'],
            'eco_points' => $row['eco_points'],
            'calculated_points' => 0
        ];
    }
}

// -------------------------
// Reverse lookup: name => id
// -------------------------
$nameToIdMap = [];
foreach ($users as $id => $user) {
    // Note: if names are not unique, consider normalizing or adding student ID to team_members instead
    $nameToIdMap[$user['name']] = $id;
}

// -------------------------
// Fetch approved submissions only and compute calculated_points.
// Pending/rejected submissions must not count toward leaderboard ranking.
// -------------------------
$submissions_query = "SELECT user_id, points, team_members FROM submissions WHERE status = 'approved'";
$result = $conn->query($submissions_query);

if ($result) {
    while ($submission = $result->fetch_assoc()) {
        $submitter_id = (int) $submission['user_id'];
        $points = (int) $submission['points'];
        $team_members_json = $submission['team_members'];

        // Add points to submitter
        if (isset($users[$submitter_id])) {
            $users[$submitter_id]['calculated_points'] += $points;
        }

        // Add points to each team member by matching name and getting real user id
        $team_members = json_decode($team_members_json, true);
        if (is_array($team_members)) {
            foreach ($team_members as $memberName) {
                if (isset($nameToIdMap[$memberName])) {
                    $memberId = $nameToIdMap[$memberName];
                    // Prevent double-counting submitter if listed as a team member
                    if ($memberId !== $submitter_id && isset($users[$memberId])) {
                        $users[$memberId]['calculated_points'] += $points;
                    }
                }
            }
        }
    }
}

// -------------------------
// Filter users with >0 calculated points and sort desc
// -------------------------
$users_with_points = array_filter($users, fn($u) => $u['calculated_points'] > 0);

uasort($users_with_points, function ($a, $b) {
    // Primary: calculated_points desc
    $cmp = $b['calculated_points'] <=> $a['calculated_points'];
    if ($cmp !== 0) return $cmp;
    // Secondary (optional): eco_points desc
    $cmp2 = $b['eco_points'] <=> $a['eco_points'];
    if ($cmp2 !== 0) return $cmp2;
    // Tertiary: name asc for stable ordering
    return strcmp($a['name'], $b['name']);
});

// -------------------------
// Build leaderboard array (complete), then paginate
// -------------------------
$leaderboard = [];
foreach ($users_with_points as $user_id => $user_data) {
    $leaderboard[] = [
        'id' => $user_id,
        'name' => $user_data['name'],
        'email' => $user_data['email'],
        'date_of_birth' => $user_data['date_of_birth'],
        'phone_number' => $user_data['phone_number'],
        'program_of_study' => $user_data['program_of_study'],
        'intake' => $user_data['intake'],
        'country' => $user_data['country'],
        'gender' => $user_data['gender'],
        'department' => $user_data['department'],
        'expected_graduation_year' => $user_data['expected_graduation_year'],
        'created_at' => $user_data['created_at'],
        'eco_points' => (int) $user_data['eco_points'],
        'calculated_points' => (int) $user_data['calculated_points']
    ];
}

$total_users = count($leaderboard);
$per_page = $per_page_param === 'all' ? max(1, $total_users) : $per_page;
$total_pages = $per_page_param === 'all' ? 1 : max(1, (int) ceil($total_users / $per_page));

// Clamp page if out of bounds
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}

// Paginate AFTER sorting to keep global rank correct
$paginated_leaderboard = $per_page_param === 'all' ? $leaderboard : array_slice($leaderboard, $offset, $per_page);

// For "showing X–Y of Z"
$show_from = $total_users > 0 ? $offset + 1 : 0;
$show_to = min($offset + $per_page, $total_users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GreenCredit - Leaderboard</title>
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .leaderboard-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.15);
        }

        .leaderboard-container h2 {
            text-align: center;
            color: #2e7d32;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .leaderboard-container p {
            text-align: center;
            color: #4a784a;
            margin-bottom: 24px;
            font-size: 1rem;
        }

        .table-responsive {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }

        .leaderboard-table thead tr {
            background: linear-gradient(90deg, #81c784, #388e3c);
            color: white;
        }

        .leaderboard-table thead th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            vertical-align: middle;
            white-space: nowrap;
        }

        .leaderboard-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.3s ease;
        }

        .leaderboard-table tbody tr:hover {
            background: #e8f5e9;
        }

        .leaderboard-table tbody td {
            padding: 12px 16px;
            text-align: left;
            font-size: 0.95rem;
            color: #333;
            vertical-align: middle;
            white-space: nowrap;
        }

        .rank-badge {
            background-color: #4caf50;
            color: #fff;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.9rem;
            display: inline-block;
            min-width: 36px;
            text-align: center;
        }

        .points-cell {
            font-weight: bold;
            color: #2e7d32;
            text-align: center;
        }

        .table-filter {
            margin-bottom: 20px;
        }

        .results-meta {
            color: #4a784a;
            font-size: 0.95rem;
            margin-bottom: 10px;
            text-align: right;
        }

        .pagination .page-link {
            color: #2e7d32;
        }
        .pagination .page-item.active .page-link {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
            display: none;
        }

        @media (max-width: 992px) {
            .leaderboard-container {
                padding: 15px;
            }
            .table-responsive {
                font-size: 0.9rem;
            }
            .leaderboard-table thead th, 
            .leaderboard-table tbody td {
                padding: 8px 10px;
            }
        }

        @media (max-width: 768px) {
            .leaderboard-container {
                margin: 20px auto;
                padding: 10px;
            }
            .table-responsive {
                font-size: 0.85rem;
            }
            .leaderboard-table thead th, 
            .leaderboard-table tbody td {
                padding: 6px 8px;
            }
            .rank-badge {
                padding: 4px 10px;
                font-size: 0.8rem;
                min-width: 28px;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="leaderboard-container">
    <h2>GreenCredit Leaderboard</h2>
    <p>Top users based on total points earned from submissions and team participation.</p>

    <div class="table-filter mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search users (current page only)...">
    </div>
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <a href="export_csv.php?table=leaderboard" class="btn btn-outline-success">
            <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label for="per_page" class="mb-0 fw-semibold">Rows</label>
            <select id="per_page" name="per_page" class="form-select" onchange="this.form.submit()">
                <?php foreach (['10','25','50','100','all'] as $option): ?>
                    <option value="<?= $option ?>" <?= $per_page_param === $option ? 'selected' : '' ?>><?= $option === 'all' ? 'All rows' : $option . ' rows' ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="results-meta">
        Showing <strong><?php echo number_format($show_from); ?></strong> –
        <strong><?php echo number_format($show_to); ?></strong> of
        <strong><?php echo number_format($total_users); ?></strong> users
    </div>

    <div class="table-responsive">
        <table class="table leaderboard-table" id="leaderboardTable">
            <thead>
                <tr>
                    <th scope="col">Rank</th>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Date of Birth</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Program</th>
                    <th scope="col">Intake</th>
                    <th scope="col">Country</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Department</th>
                    <th scope="col">Grad Year</th>
                    <th scope="col">Join Date</th>
                    <th scope="col">Total Points</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($paginated_leaderboard)): ?>
                    <?php foreach ($paginated_leaderboard as $i => $entry): 
                        // Global rank based on page + index within page
                        $display_rank = $offset + $i + 1;
                    ?>
                        <tr class="searchable-row">
                            <td><span class="rank-badge"><?= htmlspecialchars($display_rank); ?></span></td>
                            <td><?= htmlspecialchars($entry['id']); ?></td>
                            <td><?= htmlspecialchars($entry['name']); ?></td>
                            <td><?= htmlspecialchars($entry['email']); ?></td>
                            <td><?= htmlspecialchars($entry['date_of_birth']); ?></td>
                            <td><?= htmlspecialchars($entry['phone_number']); ?></td>
                            <td><?= htmlspecialchars($entry['program_of_study']); ?></td>
                            <td><?= htmlspecialchars($entry['intake']); ?></td>
                            <td><?= htmlspecialchars($entry['country']); ?></td>
                            <td><?= htmlspecialchars($entry['gender']); ?></td>
                            <td><?= htmlspecialchars($entry['department']); ?></td>
                            <td><?= htmlspecialchars($entry['expected_graduation_year']); ?></td>
                            <td><?= htmlspecialchars($entry['created_at']); ?></td>
                            <td class="points-cell"><?= number_format((int)$entry['calculated_points']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="15" style="text-align:center; padding: 20px;">
                            No points recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div id="noResults" class="no-results">No matching users found on this page.</div>
    </div>

    <!-- Pagination controls -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Leaderboard pagination">
        <ul class="pagination justify-content-center">
            <!-- Prev -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= max(1, $page - 1) ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo; Prev</span>
                </a>
            </li>

            <?php
            // Compact page number display with window and ellipses
            $window = 2; // how many pages to show around current
            $start = max(1, $page - $window);
            $end = min($total_pages, $page + $window);

            if ($start > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                if ($start > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($p = $start; $p <= $end; $p++) {
                $active = ($p === $page) ? 'active' : '';
                echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $p . '">' . $p . '</a></li>';
            }

            if ($end < $total_pages) {
                if ($end < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
            }
            ?>

            <!-- Next -->
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= min($total_pages, $page + 1) ?>" aria-label="Next">
                    <span aria-hidden="true">Next &raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('#leaderboardTable tbody .searchable-row');
        const noResultsMessage = document.getElementById('noResults');
        
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase().trim();
            let visibleRows = 0;
            
            rows.forEach(row => {
                let rowText = '';
                // Get text from all cells in the row
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => {
                    rowText += cell.textContent.toLowerCase() + ' ';
                });
                
                // Check if search text exists in any cell
                if (rowText.includes(searchText)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            if (visibleRows === 0 && searchText !== '') {
                noResultsMessage.style.display = 'block';
            } else {
                noResultsMessage.style.display = 'none';
            }
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function simpleDropdown(toggleSelector) {
        const toggle = document.querySelector(toggleSelector);
        if (!toggle) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault(); // stop page jump
            const menu = toggle.nextElementSibling;
            if (!menu) return;

            // toggle visibility
            if (menu.classList.contains('show')) {
                menu.classList.remove('show');
            } else {
                menu.classList.add('show');
            }
        });
    }

    simpleDropdown('#adminDropdown');
    simpleDropdown('#adminProfileDropdown');
});
</script>

</body>
</html>
