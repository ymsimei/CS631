<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	switch ($_POST["action"]) {
		case "delete":
			$sql = "DELETE FROM customer WHERE customerSSN = '" . $_POST["customerssn"] . "'";
			break;
		case "post":
			$sql = "INSERT INTO customer
				(customerSSN,bankerSSN,firstName,lastName,phoneNo,streetNo,city,state,zipCode)
			VALUES
				('" . $_POST["customerssn"] . "', '" . $_POST["bankerssn"] . "', '" . $_POST["firstname"] . "', '" . $_POST["lastname"] . "', '" . $_POST["phone"] . "', '" . $_POST["street"] . "', '" . $_POST["city"] . "', '" . $_POST["state"] . "', '" . $_POST["zipcode"] . "')";
			break;
		case "put":
			$sql = "UPDATE customer SET
				bankerSSN = '" . $_POST["bankerssn"] . "',
				firstName = '" . $_POST["firstname"] . "',
				lastName = '" . $_POST["lastname"] . "',
				phoneNo = '" . $_POST["phone"] . "',
				streetNo = '" . $_POST["street"] . "',
				city = '" . $_POST["city"] . "',
				state = '" . $_POST["state"] . "',
				zipCode = '" . $_POST["zipcode"] . "'
			customerSSN C_SSN = '" . $_POST["customerssn"] . "'";
			break;
	}

	try {
		$stmt = $db -> prepare($sql);
		$stmt -> execute();
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	header("Location: ../customers");
?>