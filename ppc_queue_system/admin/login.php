<?php
session_start();
include("../config.php");

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, 
        "SELECT * FROM admin_users WHERE username='$username'"
    );

    if(mysqli_num_rows($query) > 0){

        $user = mysqli_fetch_assoc($query);

        if(password_verify($password, $user['password'])){

            $_SESSION['admin'] = $username;

            header("Location: dashboard.php");
            exit();

        }else{
            $error = "Invalid username or password!";
        }

    }else{
        $error = "Invalid username or password!";
    }

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow col-md-4 mx-auto">

<div class="card-header bg-dark text-white">
<h4>Admin Login</h4>
</div>

<div class="card-body">

<?php
if(isset($error)){
echo "<div class='alert alert-danger'>$error</div>";
}
?>

<form method="POST">

<div class="mb-3">
<label>Username</label>
<input type="text" name="username" class="form-control" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<button type="submit" name="login" class="btn btn-dark w-100">
Login
</button>

</form>

</div>
</div>
</div>

</body>
</html>
