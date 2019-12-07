 <?php
	$servername = "localhost";
	$username = "hmtradin_root";
	$password = "mailme24hr";
	$dbname = "hmtradin_fatake_fund";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$sql = "INSERT INTO system_users (name, email_id, password, phone_no, role)
	VALUES ('John', 'john@example.com', md5('john'), 9730226518, 1)";

	if ($conn->query($sql) === TRUE) {
	    echo "New record created successfully";
	} else {
	    echo "Error: " . $sql . "<br>" . $conn->error;
	}

	$conn->close();
?>