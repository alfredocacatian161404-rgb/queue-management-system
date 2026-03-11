<?php
session_start();
include("../config.php");

/* ===============================
   PROTECT PAGE
================================= */
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* ===============================
   GET ADMIN WINDOW
================================= */
$username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn, "SELECT service_window FROM admin_users WHERE username='$username'");
$adminData = mysqli_fetch_assoc($adminQuery);

$window = $adminData['service_window'];

/* ===============================
   CALL NEXT LOGIC (PER WINDOW)
================================= */
if(isset($_GET['call'])){

    // Set current serving to done ONLY for this window
    mysqli_query($conn, "UPDATE queue 
        SET status='done' 
        WHERE status='serving' 
        AND service_window='$window'");

    // Get next waiting queue for THIS window
    $nextQueue = mysqli_query($conn, "
        SELECT * FROM queue 
        WHERE status='waiting'
        AND service_window='$window'
        ORDER BY 
            CASE 
                WHEN priority_type='Emergency' THEN 1
                WHEN priority_type='PWD' THEN 2
                WHEN priority_type='Senior Citizen' THEN 3
                ELSE 4
            END,
        id ASC
        LIMIT 1
    ");

    if(mysqli_num_rows($nextQueue) > 0){
        $row = mysqli_fetch_assoc($nextQueue);
        $id = $row['id'];

        mysqli_query($conn, "UPDATE queue SET status='serving' WHERE id='$id'");
    }

    header("Location: dashboard.php?played=1");
    exit();
}

/* ===============================
   DASHBOARD COUNTS (PER WINDOW)
================================= */

$waiting = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM queue 
 WHERE status='waiting' 
 AND service_window='$window'"))['total'];

$serving = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM queue 
 WHERE status='serving' 
 AND service_window='$window'"))['total'];

$done = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM queue 
 WHERE status='done' 
 AND service_window='$window'"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background: linear-gradient(135deg, #e3f2fd, #ffffff);">

<div class="container mt-5">
<div class="card shadow-lg border-0 rounded-4">

<div class="card-header bg-primary text-white d-flex align-items-center justify-content-between rounded-top-4">

<div class="d-flex align-items-center">
<img src="../assets/logo.png" width="60" class="me-3">

<div>
<h5 class="mb-0">Palawan Polytechnic College Inc.</h5>
<small><?php echo $window; ?> Window Dashboard</small>
</div>
</div>

<a href="logout.php" class="btn btn-danger btn-sm rounded-3">Logout</a>

</div>

<div class="card-body">

<?php
if(isset($_GET['played'])){
echo "
<audio autoplay>
<source src='../assets/notify.mp3' type='audio/mpeg'>
</audio>
";
}
?>

<div class="row mb-4">

<div class="col-md-4">
<div class="card text-white bg-primary rounded-4 shadow-sm">
<div class="card-body text-center">
<h6>Waiting</h6>
<h2><?php echo $waiting; ?></h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card text-white bg-warning rounded-4 shadow-sm">
<div class="card-body text-center">
<h6>Serving</h6>
<h2><?php echo $serving; ?></h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card text-white bg-success rounded-4 shadow-sm">
<div class="card-body text-center">
<h6>Completed</h6>
<h2><?php echo $done; ?></h2>
</div>
</div>
</div>

</div>

<div class="text-center mb-4">
<a href="?call=true" class="btn btn-success btn-lg rounded-3 shadow">
Call Next <?php echo $window; ?> Queue
</a>
</div>

<hr>

<h5 class="mb-3">Currently Serving</h5>

<?php
$current = mysqli_query($conn, 
"SELECT * FROM queue 
 WHERE status='serving'
 AND service_window='$window'
 LIMIT 1");

if(mysqli_num_rows($current) > 0){
$row = mysqli_fetch_assoc($current);

echo "<div class='alert alert-success text-center rounded-4 shadow-sm'>";
echo "<h2>".$row['queue_number']."</h2>";
echo "<p><strong>Service:</strong> ".$row['service_type']."</p>";
echo "<p><strong>Priority:</strong> ".$row['priority_type']."</p>";
echo "</div>";

}else{

echo "<div class='alert alert-secondary text-center rounded-4'>";
echo "No queue currently being served.";
echo "</div>";

}
?>

<hr>

<h5 class="mb-3">Queue History</h5>

<table class="table table-bordered table-striped rounded-3">

<thead class="table-dark">
<tr>
<th>Queue Number</th>
<th>Service</th>
<th>Priority</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php
$history = mysqli_query($conn, 
"SELECT * FROM queue 
 WHERE status='done'
 AND service_window='$window'
 ORDER BY id DESC");

if(mysqli_num_rows($history) > 0){

while($row = mysqli_fetch_assoc($history)){

echo "<tr>";
echo "<td>".$row['queue_number']."</td>";
echo "<td>".$row['service_type']."</td>";
echo "<td>".$row['priority_type']."</td>";
echo "<td><span class='badge bg-success'>Done</span></td>";
echo "</tr>";

}

}else{

echo "<tr><td colspan='4' class='text-center'>No history yet</td></tr>";

}
?>

</tbody>
</table>

</div>
</div>
</div>

</body>
</html>
