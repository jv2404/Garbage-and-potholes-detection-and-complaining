<?php
    // Connect to MySQL database
    $db = mysqli_connect("localhost:3306", "root","", "data");
    if (!$db) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get the ID and status from the POST request
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Update the status of the image in the database
    $sql = "UPDATE predictions SET status='$status' WHERE id=$id";
    $result = mysqli_query($db, $sql);
    if ($result) {
        echo "Status updated successfully";
    } else {
        echo "Error updating status: " . mysqli_error($db);
    }

    mysqli_close($db);
    
?>
