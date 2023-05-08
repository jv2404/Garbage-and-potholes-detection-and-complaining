<?php
	// Connect to MySQL database
	$db = mysqli_connect("localhost:3306", "root", "", "data");
	if (!$db) {
		die("Connection failed: " . mysqli_connect_error());
	}
	// Retrieve data from the predictions table
	$complain_filter = isset($_POST['complain_filter']) ? $_POST['complain_filter'] : '';
	$status_filter = isset($_POST['status_filter']) ? $_POST['status_filter'] : '';
	
	$sql = "SELECT * FROM predictions WHERE 1=1";
	
	if ($complain_filter == 'garbage') {
		$sql .= " AND prediction='garbage'";
	} elseif ($complain_filter == 'potholes') {
		$sql .= " AND prediction='potholes'";
	}
	
	if ($status_filter == 'not_seen') {
		$sql .= " AND status='not_seen'";
	} elseif ($status_filter == 'work_in_progress') {
		$sql .= " AND status='work_in_progress'";
	} elseif ($status_filter == 'done') {
		$sql .= " AND status='done'";
	}
	else {
		$sql = "SELECT * FROM predictions";
	}

	
	$result = mysqli_query($db, $sql);
	if (mysqli_num_rows($result) > 0) {
		echo '<form method="POST">';
		echo '<label for="complain_filter">Filter by complain:</label>';
		echo '<select id="complain_filter" name="complain_filter" onchange="this.form.submit()">';
		echo '<option value="">All</option>';
		echo '<option value="garbage" '.($complain_filter=='garbage'?'selected':'').'>Garbage</option>';
		echo '<option value="potholes" '.($complain_filter=='potholes'?'selected':'').'>Pothole</option>';
		echo '</select>';
		
		echo '<label for="status_filter">Filter by status:</label>';
		echo '<select id="status_filter" name="status_filter" onchange="this.form.submit()">';
		echo '<option value="">All</option>';
		echo '<option value="not_seen" '.($status_filter=='not_seen'?'selected':'').'>Not Seen</option>';
		echo '<option value="work_in_progress" '.($status_filter=='work_in_progress'?'selected':'').'>Work In Progress</option>';
		echo '<option value="done" '.($status_filter=='done'?'selected':'').'>Done</option>';
		echo '</select>';
		echo '</form>';
		echo '<table>';
		echo '<tr><th>Image</th><th>Filename</th><th>Location</th><th>COMPLIANS</th><th>Status</th></tr>';
		while ($row = mysqli_fetch_assoc($result)) {
			$image_data = base64_encode($row["image"]);
			echo '<tr>';
			echo '<td><img src="data:image/jpeg;base64,'.$image_data.'" onclick="openModal(this)" data-modal-image="'.$image_data.'"></td>';
			echo '<td>'.$row["filename"].'</td>';
			echo '<td>'.$row["location"].'</td>';
			echo '<td>'.$row["prediction"].'</td>';
			echo '<td><select onchange="updateStatus(this, '.$row["id"].')">';
			echo '<option value="not_seen" '.($row["status"]=='not_seen'?'selected':'').'>Not Seen</option>';
			echo '<option value="work_in_progress" '.($row["status"]=='work_in_progress'?'selected':'').'>Work In Progress</option>';
			echo '<option value="done" '.($row["status"]=='done'?'selected':'').'>Done</option>';
			echo '</select></td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo "No results found.";
	}
	mysqli_close($db);
?>

<style>
	table {
		border-collapse: collapse;
		width: 100%;
	}
	th, td {
		padding: 8px;
		text-align: left;
		border-bottom: 1px solid #ddd;
	}
	th {
		background-color: #f2f2f2;
		color: #333;
	}
	tr:hover {
		background-color: #f5f5f5;
	}
	img {
		width: 100px;
		height: 100px;
		object-fit: cover;
		cursor: pointer;
	}
</style>
<script>
function updateStatus(select, id) {
    var status = select.value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_status.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log(xhr.responseText);
            alert('Status updated successfully.');
        }
    };
    xhr.send('id=' + id + '&status=' + status);
}

</script>