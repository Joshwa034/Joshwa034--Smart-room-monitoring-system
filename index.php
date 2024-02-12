<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        text-align: left;
        padding: 8px;
        border: 1px solid black;
    }

    img {
        display: block;
        margin: 0 auto;
        max-width: 100%;
        height: auto;
    }
</style>

<!-- Display the HTML form -->
<form method="POST">
    <label>From Date:</label>
    <input type="date" name="from_date"><br>

    <label>To Date:</label>
    <input type="date" name="to_date"><br>

    <label>From Time:</label>
    <input type="time" name="from_time"><br>

    <label>To Time:</label>
    <input type="time" name="to_time"><br>

    <input type="submit" value="Search">
</form>

<?php

// Connect to the database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "motion_detection2";
$conn = mysqli_connect($host, $username, $password, $dbname);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from_date = $_POST["from_date"];
    $to_date = $_POST["to_date"];
    $from_time = $_POST["from_time"];
    $to_time = $_POST["to_time"];

    // Build SQL query to retrieve images within the specified date and time range
    $sql = "SELECT id, filename, date, time FROM images WHERE date BETWEEN '$from_date' AND '$to_date' AND time BETWEEN '$from_time' AND '$to_time'";

    // Execute the query
    $result = mysqli_query($conn, $sql);

    // Check if any images are found
    if (mysqli_num_rows($result) > 0) {
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
        echo "<p>No images found within the specified date and time range.</p>";
    }
}

// Close the database connection
mysqli_close($conn);

?>
