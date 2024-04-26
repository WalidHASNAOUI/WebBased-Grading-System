<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats</title>
    <link rel="stylesheet" href="Grading_style.css"> 
    <style>
        
        table {
            width: 50%;
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
    <h2>Results</h2>
    <table>
        <tr>
            <th>Module</th>
            <th>Marks</th>
        </tr>
        <tr>
            <td>COMP7001 Object-Oriented Programming</td>
            <td><?php echo $oop; ?></td>
        </tr>
        <tr>
            <td>COMP7002 Modern Computer Systems</td>
            <td><?php echo $mcs; ?></td>
        </tr>
        

        <tr>
            <td>Total Marks</td>
            <td><?php echo $total_marks; ?></td>
        </tr>
    </table>
    <a href="logout.php">logout</a>
</body>
</html>
