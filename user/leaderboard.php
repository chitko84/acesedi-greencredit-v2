<?php
session_start();
include '../includes/db.php';


// Fetch all users with their IDs and names
$user_query = $conn->query("SELECT id, name FROM users");
$users = [];
while ($row = $user_query->fetch_assoc()) {
    $users[$row['id']] = ['name' => $row['name'], 'points' => 0];
}

// Create a reverse lookup array: name => id for team member matching
$nameToIdMap = [];
foreach ($users as $id => $user) {
    $nameToIdMap[$user['name']] = $id;
}

// Fetch approved submissions only. Pending/rejected submissions must not count.
$submissions_query = "SELECT user_id, points, team_members FROM submissions WHERE status = 'approved'";
$result = $conn->query($submissions_query);

if ($result) {
    while ($submission = $result->fetch_assoc()) {
        $submitter_id = $submission['user_id'];
        $points = intval($submission['points']);
        $team_members_json = $submission['team_members'];

        // Add points to submitter
        if (isset($users[$submitter_id])) {
            $users[$submitter_id]['points'] += $points;
        }

        // Add points to each team member by matching name and getting real user id
        $team_members = json_decode($team_members_json, true);
        if (is_array($team_members)) {
            foreach ($team_members as $memberName) {
                if (isset($nameToIdMap[$memberName])) {
                    $memberId = $nameToIdMap[$memberName];
                    // Prevent double-counting submitter if listed in team_members
                    if ($memberId != $submitter_id) {
                        $users[$memberId]['points'] += $points;
                    }
                }
            }
        }
    }
}

// Filter users with points > 0
$users_with_points = array_filter($users, fn($user) => $user['points'] > 0);

// Sort users by points descending while preserving keys (user IDs)
uasort($users_with_points, function($a, $b) {
    return $b['points'] <=> $a['points'];
});

// Leaderboard array with rank and real user IDs
$leaderboard = [];
$rank = 1;
$limit = 5;
foreach ($users_with_points as $user_id => $user_data) {
    if ($rank > $limit) break; // stop once top 5 are added
    $leaderboard[] = [
        'rank' => $rank,
        'id' => $user_id,
        'total_points' => $user_data['points']
    ];
    $rank++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>GreenCredit - Leaderboard</title>
<link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon" />
<style>
  /* Container */
  .leaderboard-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 0 15px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(46, 125, 50, 0.15);
    overflow-x: auto;
  }

  /* Header */
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

  /* Table */
  .leaderboard-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px; /* vertical spacing between rows */
    min-width: 320px; /* allow horizontal scroll on small */
  }

  .leaderboard-table thead tr {
    background: linear-gradient(90deg, #81c784, #388e3c);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    border-radius: 12px;
  }

  .leaderboard-table thead th {
    padding: 12px 16px;
    text-align: center;
  }

  .leaderboard-table tbody tr {
    background: #f9fdfb;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(46, 125, 50, 0.1);
    transition: background-color 0.3s ease;
  }

  .leaderboard-table tbody tr:hover {
    background: #e8f5e9;
  }

  .leaderboard-table tbody td {
    padding: 12px 16px;
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    color: #2e7d32;
    white-space: nowrap;
  }

  .leaderboard-table tbody td.points {
    text-align: right;
  }

  /* Rank badge */
  .rank-badge {
    background-color: #4caf50;
    color: #fff;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 0.9rem;
    display: inline-block;
    min-width: 36px;
  }

  /* Responsive */
  @media (max-width: 480px) {
    .leaderboard-container {
      max-width: 100%;
      padding: 0 10px;
    }
    .leaderboard-table thead th,
    .leaderboard-table tbody td {
      padding: 10px 8px;
      font-size: 0.9rem;
    }
    .rank-badge {
      padding: 4px 10px;
      font-size: 0.8rem;
      min-width: 28px;
    }
  }
 #profileDropdown + .dropdown-menu {
  right: 0;
  transform: translateX(-20px);
}
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="leaderboard-container">
  <h2>Leaderboard</h2>
  <p>Top users based on total points earned from submissions and team participation.</p>

  <table class="leaderboard-table" role="table" aria-label="Leaderboard">
    <thead>
      <tr>
        <th scope="col">Rank</th>
        <th scope="col">User ID</th>
        <th scope="col">Total Points</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($leaderboard)): ?>
        <?php foreach ($leaderboard as $entry): ?>
          <tr>
            <td><span class="rank-badge"><?= htmlspecialchars($entry['rank']); ?></span></td>
            <td><?= htmlspecialchars($entry['id']); ?></td>
            <td class="points"><?= number_format($entry['total_points']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="3" style="text-align:center; padding: 20px;">No points recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
<!-- Add this right before the closing </body> tag -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });

        // Make sure profile dropdown works
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) {
            profileDropdown.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const dropdown = bootstrap.Dropdown.getInstance(profileDropdown);
                dropdown.toggle();
            });
        }

        // Make sure logout link works
        document.getElementById('logoutLink')?.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Close navbar when clicking outside
        document.addEventListener('click', function (event) {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            const isClickInsideNavbar = event.target.closest('.navbar-collapse') || 
                                      event.target.closest('.navbar-toggler') ||
                                      event.target.closest('.dropdown-menu');

            if (navbarCollapse.classList.contains('show') && !isClickInsideNavbar) {
                const collapseInstance = bootstrap.Collapse.getInstance(navbarCollapse);
                if (collapseInstance) {
                    collapseInstance.hide();
                }
            }
        });
    });

    // Theme toggle functionality
    document.addEventListener('DOMContentLoaded', function () {
        const themeToggle = document.getElementById('themeToggle');
        const isDarkMode = localStorage.getItem('theme') === 'dark';

        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            if (themeToggle) themeToggle.checked = true;
        }

        if (themeToggle) {
            themeToggle.addEventListener('change', function () {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('theme', 'light');
                }
            });
        }
    });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const profileDropdown = document.getElementById('profileDropdown');
    const dropdownMenu = profileDropdown?.nextElementSibling;

    if (profileDropdown && dropdownMenu) {
      profileDropdown.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
      });

      // Close dropdown if clicked outside
      document.addEventListener('click', function (event) {
        if (!profileDropdown.contains(event.target) && !dropdownMenu.contains(event.target)) {
          dropdownMenu.classList.remove('show');
        }
      });
    }
  });
</script>
</body>
</html>
