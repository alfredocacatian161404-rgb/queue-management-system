```php
<?php
include("config.php");

/* ===============================
   GET CURRENT SERVING PER WINDOW
================================= */

function getServing($conn, $window){
    $query = mysqli_query($conn,
        "SELECT queue_number 
         FROM queue 
         WHERE status='serving' 
         AND service_window='$window'
         LIMIT 1");

    if(mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_assoc($query);
        return $row['queue_number'];
    }else{
        return "---";
    }
}

$registrar = getServing($conn, "Registrar");
$finance = getServing($conn, "Finance");
$accounts = getServing($conn, "Student Accounts");
$admissions = getServing($conn, "Admissions");

?>

<!DOCTYPE html>
<html>
<head>

<title>Queue Display</title>

<meta http-equiv="refresh" content="5">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#0d6efd;
color:white;
text-align:center;
}

.title{
font-size:50px;
font-weight:bold;
margin-bottom:40px;
}

.window-box{
background:white;
color:black;
padding:40px;
border-radius:15px;
margin:20px;
}

.queue-number{
font-size:70px;
font-weight:bold;
color:#0d6efd;
}

.window-name{
font-size:25px;
margin-bottom:10px;
}

</style>

</head>

<body>

<div class="container mt-5">

<div class="title">
NOW SERVING
</div>

<div class="row">

<div class="col-md-6">
<div class="window-box">
<div class="window-name">Registrar</div>
<div class="queue-number"><?php echo $registrar; ?></div>
</div>
</div>

<div class="col-md-6">
<div class="window-box">
<div class="window-name">Finance</div>
<div class="queue-number"><?php echo $finance; ?></div>
</div>
</div>

<div class="col-md-6">
<div class="window-box">
<div class="window-name">Student Accounts</div>
<div class="queue-number"><?php echo $accounts; ?></div>
</div>
</div>

<div class="col-md-6">
<div class="window-box">
<div class="window-name">Admissions</div>
<div class="queue-number"><?php echo $admissions; ?></div>
</div>
</div>

</div>

</div>

</body>
</html>
```

