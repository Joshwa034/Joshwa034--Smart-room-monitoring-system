<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Search Form</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.box {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin: 20px auto;
    max-width: 400px;
}

.box label {
    font-weight: bold;
}

.box input[type="date"],
.box input[type="time"],
.box input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    border-radius: 3px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

.box input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
}

.filter-box {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin: 20px auto;
    max-width: 400px;
}

.filter-box input[type="time"],
.filter-box input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    border-radius: 3px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #f2f2f2;
    color: #333;
    font-weight: bold;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #e0e0e0;
}

table tr:hover td {
    transition: background-color 0.3s ease;
}

table td:first-child {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

table td:last-child {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}


img {
    max-width: 100%;
    height: auto;
}


.no-images-message {
    text-align: center;
    font-weight: bold;
    color: #555;
    margin-top: 20px;
}

.no-images-message:before,
.no-images-message:after {
    content: "";
    display: inline-block;
    vertical-align: middle;
    width: 20px;
    height: 1px;
    background-color: #ccc;
    margin: 0 10px;
}

.no-images-message:before {
    margin-left: -100%;
}

.no-images-message:after {
    margin-right: -100%;
}


    </style>
    <script>
        function toggleTimeInput() {
            const timeInputSection = document.querySelector('.time-input');
            const timeToggle = document.querySelector('#time-toggle');
            timeInputSection.style.display = timeToggle.checked ? 'block' : 'none';
        }
    </script>
</head>
<body>

<div class="box">
    <form method="POST">
        <label>Date:</label>
        <input type="date" name="date" required><br>

        <label>
            <input type="checkbox" id="time-toggle" onclick="toggleTimeInput()">
            Include Time
        </label>

        <div class="time-input">
            <label>From Time:</label>
            <input type="time" name="from_time"><br>

            <label>To Time:</label>
            <input type="time" name="to_time"><br>
        </div>

        <input type="submit" value="Search">
    </form>
</div>

<div class="filter-box" id="filter-box" style="display: none;">
    <form method="POST" id="filter-form">
        <input type="hidden" name="date" id="filter-date">
        <label>From Time:</label>
        <input type="time" name="filter_from_time"><br>

        <label>To Time:</label>
        <input type="time" name="filter_to_time"><br>

        <input type="submit" value="Filter">
    </form>
</div>

<?php

// Connect to the database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "motion_detection2";
$conn = mysqli_connect($host, $username, $password, $dbname);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST["date"];
    $from_time = isset($_POST["from_time"]) && !empty($_POST["from_time"]) ? $_POST["from_time"] : "00:00:00";
    $to_time = isset($_POST["to_time"]) && !empty($_POST["to_time"]) ? $_POST["to_time"] : "23:59:59";

    if (isset($_POST["filter_from_time"]) && !empty($_POST["filter_from_time"]) && isset($_POST["filter_to_time"]) && !empty($_POST["filter_to_time"])) {
        $from_time = $_POST["filter_from_time"];
        $to_time = $_POST["filter_to_time"];
    }

    // Build SQL query to retrieve images within the specified date and time range
    $sql = "SELECT id, filename, date, time FROM images WHERE date = '$date' AND time BETWEEN '$from_time' AND '$to_time'";

    // Execute the query
    $result = mysqli_query($conn, $sql);

    // Check if any images are found
    if (mysqli_num_rows($result) > 0) {
        echo '<script>document.getElementById("filter-box").style.display = "block"; document.getElementById("filter-date").value = "' . $date . '";</script>';
        // Start the table
        echo "<table>";
        echo "<tr><th>ID</th><th>Image</th><th>Date</th><th>Time</th></tr>";

        // Loop through the rows and display the images
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row["id"];
            $filename = $row["filename"];
            $date = $row["date"];
            $time = $row["time"];
            $image_path = "MDimages/" . basename($filename);

            // Check if the image file exists
            if (file_exists($image_path)) {
                echo "<tr>";
                echo "<td>$id</td>";
                echo "<td><img src='$image_path' alt='Image not found' width='300'></td>";
                echo "<td>$date</td>";
                echo "<td>$time</td>";
                echo "</tr>";
            } else {
                echo "<tr>";
                echo "<td>$id</td>";
                echo "<td>Image not found: $filename</td>";
                echo "<td>$date</td>";
                echo "<td>$time</td>";
                echo "</tr>";
            }
        }

        // End the table
        echo "</table>";
    } else {
        echo "<p class='no-images-message'>No images found within the specified date and time range.</p>";

    }
}

// Close the database connection
mysqli_close($conn);

?>

</body>
</html>
