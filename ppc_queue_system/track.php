
<?php
include("config.php");

if(!isset($_GET['queue'])){
    die("No queue specified.");
}

$queue_number = $_GET['queue'];

// Get queue details
$queueData = mysqli_query($conn, "SELECT * FROM queue WHERE queue_number='$queue_number' LIMIT 1");
$queueRow = mysqli_fetch_assoc($queueData);

if(!$queueRow){
    die("Invalid queue number.");
}

$priority = $queueRow['priority_type'];
$currentId = $queueRow['id'];
$status = $queueRow['status'];

// Count people ahead
$positionQuery = mysqli_query($conn, "
    SELECT COUNT(*) as position
    FROM queue
    WHERE status='waiting'
    AND (
        CASE 
            WHEN priority_type='Emergency' THEN 1
            WHEN priority_type='PWD' THEN 2
            WHEN priority_type='Senior Citizen' THEN 3
            ELSE 4
        END
        <
        CASE 
            WHEN '$priority'='Emergency' THEN 1
            WHEN '$priority'='PWD' THEN 2
            WHEN '$priority'='Senior Citizen' THEN 3
            ELSE 4
        END
        OR (
            priority_type='$priority' AND id < '$currentId'
        )
    )
");

$position = mysqli_fetch_assoc($positionQuery)['position'];

$averageTime = 5;
$estimatedTime = $position * $averageTime;

// Total people in queue when registered (for progress %)
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM queue WHERE id <= '$currentId'");
$totalAtRegistration = mysqli_fetch_assoc($totalQuery)['total'];

$completedQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM queue WHERE status='done' AND id <= '$currentId'");
$completedCount = mysqli_fetch_assoc($completedQuery)['total'];

$progressPercent = ($totalAtRegistration > 0) ? 
    round(($completedCount / $totalAtRegistration) * 100) : 0;

// Get currently serving
$currentServing = mysqli_query($conn, "SELECT queue_number FROM queue WHERE status='serving' LIMIT 1");
$currentServingRow = mysqli_fetch_assoc($currentServing);
$currentServingNumber = $currentServingRow ? $currentServingRow['queue_number'] : "None";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Queue Tracking</title>

    <!-- MOBILE RESPONSIVE -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Auto refresh -->
    <meta http-equiv="refresh" content="5">
</head>

<body style="background: linear-gradient(135deg, #e3f2fd, #ffffff);">

<div class="container mt-3 px-3">
    <div class="card shadow-lg border-0 rounded-4">

        <!-- HEADER -->
        <div class="card-header bg-primary text-white d-flex align-items-center rounded-top-4">
            <img src="assets/logo.png" class="img-fluid me-3" style="max-width:50px;">
            <div>
                <h6 class="mb-0">Palawan Polytechnic College Inc.</h6>
                <small>Live Queue Tracking</small>
            </div>
        </div>

        <div class="card-body text-center">

            <h5>Your Queue Number</h5>

            <!-- BIG QUEUE NUMBER -->
            <h1 class="display-3 text-primary fw-bold mb-4">
                <?php echo $queue_number; ?>
            </h1>

            <h6>Current Serving</h6>
            <h3 class="text-success mb-3">
                <?php echo $currentServingNumber; ?>
            </h3>

            <h6>People Ahead of You</h6>
            <h3 class="mb-3">
                <?php echo $position; ?>
            </h3>

            <h6>Estimated Waiting Time</h6>
            <h3 class="mb-4">
                <?php echo $estimatedTime; ?> minutes
            </h3>

            <h6>Progress</h6>

            <div class="progress mt-2" style="height:30px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                     role="progressbar"
                     style="width: <?php echo $progressPercent; ?>%;">
                     <?php echo $progressPercent; ?>%
                </div>
            </div>

            <p class="mt-3 text-muted">
                This page refreshes automatically every 5 seconds.
            </p>

        </div>
    </div>
</div>

</body>
</html>

