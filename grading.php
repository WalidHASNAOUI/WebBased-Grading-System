<?php

include("db_login.php");

$connection = mysqli_connect($db_host, $db_username, $db_password, $db_database);

if (!$connection) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}

##########################################################################
#                Get all modules stored in the database                  #
##########################################################################
// Prepare SQL statement
$sql = "SELECT module_id, module_name,credit FROM module ";

$result = mysqli_query($connection, $sql);

##########################################################################
#     Retrieve the latest module grades for the signed-in student        #
##########################################################################

// Use prepared statement to prevent SQL injection
$sql_query =  "SELECT mark, module_id FROM grades WHERE Username= ?";

$stmt = mysqli_prepare($connection, $sql_query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION["username"]);

mysqli_stmt_execute($stmt);

$result2 = mysqli_stmt_get_result($stmt); 

$list_of_modules_with_marks = array(); 

# create an associative array based on resulteSet received from the db
while ($module_with_mark = mysqli_fetch_assoc($result2) )
{
    array_push($list_of_modules_with_marks ,$module_with_mark);
}

// print_r($list_of_modules_with_marks);

// $is_first_fill = count(mysqli_fetch_all($modules_with_marks)) == 0; 

// print_r(mysqli_fetch_all($result2));

// echo count(mysqli_fetch_all($modules_with_grades));
    

##########################################################################
#                Close the connection with the db-server                 #
##########################################################################
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading System</title>
    <link rel="stylesheet" href="Grading_style.css">
</head>
<body>
   
    <nav class="navbar">
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="logout.php">Logout</a></li> 
        </ul>
    </nav>
    <div class="container">
        <div class="grading-content">
            <h2>Grading System</h2>

                <?php
                // list all modules exist on the db 
                    if (count($list_of_modules_with_marks) == 0)
                    {
                        echo "<form action='submit_grades.php?is_first_time=1' method='POST'>";

                         while( $row = mysqli_fetch_assoc($result) ) {
                             echo "<label for='".$row["module_id"]."'>".$row["module_id"]." ".$row["module_name"]."  (Credits: ".$row["credit"]."):</label>";
                             echo "<input value='0' type='number' id='".$row["module_id"]."' name='".$row["module_id"]."' min='0' max='100' required><br><br>";
                                
                         }
                         // lancer une request insert 
                    }else {
                        echo "<form action='submit_grades.php?is_first_time=0' method='POST'>";

                         while( $row = mysqli_fetch_assoc($result) ) {
                             echo "<label for='".$row["module_id"]."'>".$row["module_id"]." ".$row["module_name"]."  (Credits: ".$row["credit"]."):</label>";
                            
                             # iterate over list_of_modules_with_marks array (wich we created above)
                             foreach ($list_of_modules_with_marks as $module_with_mark)
                             {
                                 # check if the current module is the same as the one the outside while loop iteration
                                 if ($module_with_mark["module_id"] == $row["module_id"])
                                 {
                                 # if yes : then insert the input tag with module's mark as a value 

                                     echo "<input value='".$module_with_mark["mark"]."' type='number' id='".$row["module_id"]."' name='".$row["module_id"]."' min='0' max='100' required><br><br>";
                                 }

                                 # if not : move to the next module (don't do anythink)
                             }
                         }
                    }
                ?>

                <input type="submit" value="Submit">
                
            </form>
        </div>
    </div>
    <footer class="footer">
        <div class="contact-info">
            <p>Contact Information:</p>
            <p>Address: Oxford Brookes University, Oxford, OX3 0BP, UK</p>
            <p>Email: info@brookes.ac.uk</p>
            <p>Phone: +44 (0) 1865 741111</p>
        </div>
    </footer>
</body>
</html>



