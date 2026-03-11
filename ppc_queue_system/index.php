
<?php
include("config.php");

/* ===============================
   HANDLE FORM SUBMISSION
================================= */
if(isset($_POST['submit'])){

    $student_name = $_POST['student_name'];
    $service_type = $_POST['service_type'];
    $service_window = $_POST['service_window']; // NEW
    $priority_type = $_POST['priority_type'];

// Determine prefix based on window
if($service_window == "Registrar"){
    $prefix = "R";
}
elseif($service_window == "Finance"){
    $prefix = "F";
}
elseif($service_window == "Student Accounts"){
    $prefix = "SA";
}
else{
    $prefix = "A";
}

// Count queues only for that window
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM queue WHERE service_window='$service_window'");
$row = mysqli_fetch_assoc($result);
$nextNumber = $row['total'] + 1;

// Generate queue number
$queue_number = $prefix . "-" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);

    mysqli_query($conn, "INSERT INTO queue (queue_number, service_type, service_window, priority_type) 
                         VALUES ('$queue_number', '$service_type', '$service_window', '$priority_type')");

    header("Location: index.php?queue=$queue_number");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PPC Smart Queue System</title>

    <!-- MOBILE RESPONSIVE -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background: linear-gradient(135deg, #e3f2fd, #ffffff);">

<div class="container mt-3 px-3">
    <div class="card shadow-lg border-0 rounded-4">

        <!-- HEADER WITH LOGO -->
        <div class="card-header bg-primary text-white d-flex align-items-center rounded-top-4">
            <img src="assets/logo.png" class="img-fluid me-3" style="max-width:50px;">
            <div>
                <h6 class="mb-0">Palawan Polytechnic College Inc.</h6>
                <small>Registrar Smart Queue Management System</small>
            </div>
        </div>

        <div class="card-body">

<?php
/* ===============================
   SHOW SUCCESS + ESTIMATED TIME
================================= */
if(isset($_GET['queue'])){

    $queue_number = $_GET['queue'];

    $queueData = mysqli_query($conn, "SELECT * FROM queue WHERE queue_number='$queue_number' LIMIT 1");
    $queueRow = mysqli_fetch_assoc($queueData);

    if($queueRow){

        $priority = $queueRow['priority_type'];
        $currentId = $queueRow['id'];

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

        echo "<div class='alert alert-success rounded-3 text-center'>";
        echo "<h5 class='mb-2'>Queue Registered Successfully!</h5>";
        echo "Your Queue Number is <strong>$queue_number</strong><br>";
        echo "Estimated Waiting Time: <strong>$estimatedTime minutes</strong>";

        echo "<br><a href='track.php?queue=$queue_number' 
                 class='btn btn-outline-primary mt-3 w-100'>
                 Track My Queue Live
              </a>";

        echo "</div>";
    }
}
?>

            <!-- FORM -->
            <form method="POST" class="mt-3">
                
                <div class="mb-3">
                    <label class="form-label">Student Name</label>
                    <input type="text" name="student_name" class="form-control rounded-3" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Service Type</label>
                    <select name="service_type" class="form-select rounded-3" required>
                        <option value="">Select Service</option>
                        <option value="Enrollment">Enrollment</option>
                        <option value="TOR Request">TOR Request</option>
                        <option value="ID Processing">ID Processing</option>
                        <option value="Payment Concern">Payment Concern</option>
                    </select>
                </div>

                <!-- NEW SERVICE WINDOW -->
                <div class="mb-3">
                    <label class="form-label">Service Window</label>
                    <select name="service_window" class="form-select rounded-3" required>
                        <option value="">Select Window</option>
                        <option value="Registrar">Registrar</option>
                        <option value="Finance">Finance</option>
                        <option value="Student Accounts">Student Accounts</option>
                        <option value="Admissions">Admissions</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Priority Type</label>
                    <select name="priority_type" class="form-select rounded-3">
                        <option value="Regular">Regular</option>
                        <option value="Senior Citizen">Senior Citizen</option>
                        <option value="PWD">PWD</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-primary w-100 rounded-3">
                    Get Queue Number
                </button>
            </form>

<hr>

<h5 class="mt-4">Current Waiting Queue</h5>

<div class="table-responsive">

<table class="table table-bordered table-striped rounded-3">
    <thead class="table-dark">
        <tr>
            <th>Queue Number</th>
            <th>Service</th>
            <th>Window</th>
            <th>Priority</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>

<?php
$queueList = mysqli_query($conn, "
    SELECT * FROM queue 
    WHERE status='waiting'
    ORDER BY 
        CASE 
            WHEN priority_type='Emergency' THEN 1
            WHEN priority_type='PWD' THEN 2
            WHEN priority_type='Senior Citizen' THEN 3
            ELSE 4
        END,
    id ASC
");

while($row = mysqli_fetch_assoc($queueList)){
    echo "<tr>
            <td>".$row['queue_number']."</td>
            <td>".$row['service_type']."</td>
            <td>".$row['service_window']."</td>
            <td>".$row['priority_type']."</td>
            <td><span class='badge bg-secondary'>".$row['status']."</span></td>
          </tr>";
}
?>

    </tbody>
</table>

</div>

        </div>
    </div>
</div>

</body>
</html>

