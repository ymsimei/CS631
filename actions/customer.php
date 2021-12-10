<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	switch ($_POST["action"]) {
		case "delete":
			$sql = "DELETE FROM CUSTOMER WHERE C_SSN = '" . $_POST["ssn"] . "'";
			break;
		case "post":
			$sql = "INSERT INTO CUSTOMER (C_SSN, BankerSSN, FirstName, LastName, PhoneNo, StreetNo, City, State, ZipCode) VALUES ('" . $_POST["ssn"] . "', '" . $_SESSION["E_SSN"] . "', '" . $_POST["firstname"] . "', '" . $_POST["lastname"] . "', '" . $_POST["phoneno"] . "', '" . $_POST["street"] . "', '" . $_POST["city"] . "', '" . $_POST["state"] . "', '" . $_POST["zipcode"] . "')";
			break;
		case "put":
			$sql = "UPDATE CUSTOMER SET
				FirstName = '" . $_POST["firstname"] . "',
				LastName = '" . $_POST["lastname"] . "',
				PhoneNo = '" . $_POST["phoneno"] . "',
				StreetNo = '" . $_POST["street"] . "',
				City = '" . $_POST["city"] . "',
				State = '" . $_POST["state"] . "',
				ZipCode = '" . $_POST["zipcode"] . "'
			WHERE C_SSN = '" . $_POST["ssn"] . "'";
			break;
	}

	try {
		$stmt = $db -> prepare($sql);
		$stmt -> execute();
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	header("Location: /~afm36/CS631/customer");
?>