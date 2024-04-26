<?php
include("db_login.php");

$connection = mysqli_connect($db_host, $db_username, $db_password, $db_database);

session_start();

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["Username"];
    $password = $_POST["Password"];

    // Use prepared statement to prevent SQL injection
    $query = "SELECT Username, Password FROM student WHERE Username = ? AND Password = ?";
    $stmt = mysqli_prepare($connection, $query);
    
    // Bind parameters and execute query
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    // To prevent SQL injection by separating the SQL query from the data being supplied
    if (mysqli_stmt_num_rows($stmt) == 1) {
        // Authentication successful, redirect to grading page
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;

        header("Location: grading.php");
        exit;
    } else {
        // Authentication failed, redirect back to login page with error parameter
        header("Location: index.html?error=1");
        exit;
    }
}
?>
