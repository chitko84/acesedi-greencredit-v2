<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, AVG(sustainability_score) AS avg_score
    FROM sustainability_scores
    WHERE user_id = ?
    GROUP BY month
    ORDER BY month ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$scores = [];

while ($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $scores[] = round($row['avg_score'], 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Sustainability Trend</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <h2 class="text-center mb-4">Your Monthly Sustainability Score Trend</h2>
    <canvas id="trendChart"></canvas>
</div>

<script>
const ctx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Average Sustainability Score',
            data: <?php echo json_encode($scores); ?>,
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
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
