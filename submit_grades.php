<?php

// Check if user is logged in and 'username' is set in the session
session_start();

if (!isset($_SESSION["loggedin"]) || !isset($_SESSION["username"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.html");
    exit;
}

##########################################################################
#                     Start the connection with the db                   #
##########################################################################
include("db_login.php");

$connection = mysqli_connect($db_host, $db_username, $db_password, $db_database);

if (!$connection) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// check the state of user: first time to save their grades or not
if ($_GET["is_first_time"] == 1)
{
    ##########################################################################
    #                   The user initially saved their grades                #
    #                Execute an update query on the grades table             #
    ##########################################################################
    $sql_query =   "INSERT INTO `grades` 
                    VALUES (?, ?, ?);";

    foreach ($_POST as $module_code => $module_new_grade)
    {
        $stmt = mysqli_prepare($connection, $sql_query);
        mysqli_stmt_bind_param($stmt, "ssd", $_SESSION["username"], $module_code, $module_new_grade);
        mysqli_stmt_execute($stmt);
    }

}else {

    if ($_GET["is_first_time"] == 0){
        ##########################################################################
        #                 The user has previously saved their grades             #
        #                Execute an update query on the grades table             #
        ##########################################################################
        $sql_query =   "UPDATE `grades`
                    set `grades`.`mark` = ?
                    where `grades`.`Username` = ?
                    and `grades`.`module_id` = ? ;";
        
        foreach ($_POST as $module_code => $module_new_grade)
        {
            $stmt = mysqli_prepare($connection, $sql_query);
            mysqli_stmt_bind_param($stmt, "dss", $module_new_grade, $_SESSION["username"], $module_code);
            mysqli_stmt_execute($stmt);
        }


    }else {
        echo "Error";
        // header("Location: grading.php");
    }
}

##########################################################################
#   Send a sql request to get all modules's marks of the current user    #
#                Execute an select query on the grades table             #
##########################################################################

// Use prepared statement to prevent SQL injection
$sql_query =   "SELECT g.mark, g.module_id, m.module_name, m.credit
                FROM grades AS g 
                INNER JOIN module AS m
                    on g.module_id = m.module_id  
                WHERE Username= ?";

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

// check the contenant of the reponse 
// print_r($list_of_modules_with_marks);



// close the connection with the db
$connection = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results</title>
    <link rel="stylesheet" href="Grading_style.css"> 
    <style>
        table {
            width: 70%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
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

    <table>
    <tr>
        <th>Code</th>
        <th>Module Name</th>
        <th>Marks</th>
        <th>Grade A</th>
        <th>Grade B</th>
        <th>Grade C</th>
        <th>Grade F</th>
        <th>Credit</th>
    </tr>

    <?php 
        #initialize variables in order to calculate totals (for marks, credits, grades)
        $total_marks = 0; 
        $total_credits = 0; 
        $total_grade_A = 0;
        $total_grade_B = 0;
        $total_grade_C = 0;
        $total_grade_F = 0;

        # initialize the mark of MSc Dissertation in Computing Subjects : to classify the award of the MSc in Computing Science
        $mark_msc_diss = 0;

        foreach ($list_of_modules_with_marks as $module_with_mark)
        {
            # check if the current module is MSc Dissertation in Computing Subjects (to store its mark)
            if ($module_with_mark["module_id"] == "TECH7009")
                $mark_msc_diss = $module_with_mark["mark"];

            # calculate the grade for the current module
            $A = $module_with_mark["mark"] >= 70  ? '✔' : ""; 
            $B = (( $module_with_mark["mark"] < 70 ) && ( $module_with_mark["mark"] >= 60 )) ? '✔' : ''; 
            $C = (( $module_with_mark["mark"] < 60 ) && ( $module_with_mark["mark"] >= 50 )) ? '✔' : ''; 
            $F =  $module_with_mark["mark"] < 50 ? '✔' : ''; 

            # calculate the credit 
            $credit = $module_with_mark["mark"] >= 50 ? $module_with_mark["credit"] : 0;

            # calculate the total marks 
            $total_marks += $module_with_mark["mark"];
            $total_credits += $credit; 

            # calculate the total number of modules with grade A, B, C and F
            if ($A == '✔')
                $total_grade_A++;
            else {
                if ($B == '✔')
                    $total_grade_B++;
                else {
                    if ($C == '✔')
                        $total_grade_C++; 
                    else 
                        $total_grade_F++; 
                }

            }

            # display module informations like : "Module code", "Module name", "mark", "grade" 
            echo    "<tr>
                        <td>". $module_with_mark["module_id"] . "</td>
                        <td>". $module_with_mark["module_name"] . "</td>
                        <td>". $module_with_mark["mark"] . "</td>
                        <td>". $A . "</td>
                        <td>". $B . "</td>
                        <td>". $C . "</td>
                        <td>". $F . "</td>
                        <td>". $credit . "</td>
                    </tr>";


            echo "</tr>"; 
                        
        }

        # displaying totals 
        echo    "<tr>
                        <td></td>
                        <td><b>Total</b></td>
                        <td><b>". $total_marks . "</b></td>
                        <td><b>". $total_grade_A . "</b></td>
                        <td><b>". $total_grade_B . "</b></td>
                        <td><b>". $total_grade_C . "</b></td>
                        <td><b>". $total_grade_F . "</b></td>
                        <td><b>". $total_credits . "</b></td>
                    </tr>";
    ?>

    <table>

    <table>
        <tr>
            <th>Qualification</th>
            <th>Total Average Mark</th>
            <th>Award of the MSc</th>
        </tr>
        <tr>
            <td><?php echo $total_credits >= 180 ? "MSc in Computing Science" : ($total_credits >= 120 ? "PG Diploma in Computing" : "No qualification") ?></td>
            <td><?php echo $total_marks/8 ?></td>
            <td><?php echo $total_marks/8 >= 70 ? ($mark_msc_diss >= 68 ? "Distinction": ($mark_msc_diss >= 58 ? "Merit" : "")) :($total_marks/8 >= 60 ? ($mark_msc_diss >= 58 ? "Merit" : "") : "")  ?></td>
        </tr>
    </table>
    

    <!-- I will Do the rest of the module after -->
   
    <!-- </table>
        <button type="submit">Submit Grades</button>
    </form> -->

    

</body>
</html>
