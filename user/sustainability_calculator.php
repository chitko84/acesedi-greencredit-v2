<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$sustainability_score = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $water_usage = floatval($_POST['water_usage']);
    $reusable_items = intval($_POST['reusable_items']);
    $walking_days = intval($_POST['walking_days']);

    $water_score = min(30, (100 / max($water_usage, 1)) * 30);
    $reusable_score = min(30, ($reusable_items / 10) * 30);
    $walking_score = min(40, ($walking_days / 7) * 40);

    $sustainability_score = $water_score + $reusable_score + $walking_score;
    $_SESSION['sustainability_score'] = $sustainability_score;

    $query = "INSERT INTO sustainability_scores (user_id, water_usage, reusable_items, walking_days, sustainability_score) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idddi", $user_id, $water_usage, $reusable_items, $walking_days, $sustainability_score);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query = "SELECT DATE(created_at) AS entry_date, sustainability_score FROM sustainability_scores WHERE user_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
$scores = [];

while ($row = $result->fetch_assoc()) {
    $dates[] = $row['entry_date'];
    $scores[] = round($row['sustainability_score'], 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Sustainability Calculator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'includes/header.php'; ?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success text-center">
        <?= htmlspecialchars($_SESSION['message']); ?>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<div class="container my-5">
    <h2 class="text-center mb-4">Sustainability Calculator</h2>
    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="water_usage" class="form-label">
                        How much water (in liters) did you use this week? (Try to use less than 100 liters total over 7 days)
                    </label>
                    <input type="number" class="form-control" id="water_usage" name="water_usage" required>
                </div>
                <div class="mb-3">
                    <label for="reusable_items" class="form-label">
                        How many times did you use reusable items (bottles, containers, bags, etc.) instead of disposables this week? (Out of 10 opportunities)
                    </label>
                    <input type="number" class="form-control" id="reusable_items" name="reusable_items" required>
                </div>
                <div class="mb-3">
                    <label for="walking_days" class="form-label">
                        How many days this week did you choose walking or cycling instead of using a vehicle? (Maximum is 7 days)
                    </label>
                    <input type="number" class="form-control" id="walking_days" name="walking_days" required>
                </div>
                <button type="submit" class="btn btn-success">Calculate Score</button>
            </div>
            <div class="col-md-6">
                <?php if ($sustainability_score !== null): ?>
                    <div class="alert alert-info mt-3">
                        <h4>Your Sustainability Score:</h4>
                        <p><?php echo number_format($sustainability_score, 2); ?> / 100</p>
                    </div>
                <?php endif; ?>

                <h4 class="mt-5">Your Score Trend Over Time</h4>
                <canvas id="trendChart"></canvas>
                <h4 class="mt-5">Score Breakdown (Bar Chart)</h4>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </form>
    <div class="mt-4 d-flex gap-2">
    <a href="export_csv.php" class="btn btn-outline-primary">Download as CSV</a>
    <form action="clear_trend.php" method="post" data-confirm="Are you sure you want to clear your trend data?">
        <button type="submit" class="btn btn-outline-danger">Clear Trend</button>
    </form>
    </div>
</div>

<script>
const labels = <?php echo json_encode($dates); ?>;
const data = <?php echo json_encode($scores); ?>;

new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Sustainability Score',
            data: data,
            backgroundColor: 'rgba(46, 139, 87, 0.2)',
            borderColor: 'rgba(46, 139, 87, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

new Chart(document.getElementById('barChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Sustainability Score',
            data: data,
            backgroundColor: 'rgba(0, 123, 255, 0.5)',
            borderColor: 'rgba(0, 123, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
