<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	switch ($_POST["action"]) {
		case "delete":
			$sql = "
				DELETE FROM employeeDependent WHERE employeeSSN = " . $_POST["employeessn"] . ";
				DELETE FROM employee WHERE employeeSSN = " . $_POST["employeessn"] . ";
			";
			break;
		case "post":
			$sql = "
				INSERT INTO employee
					(employeeSSN,branchID,firstName,lastName,phoneNo,startDate)
				VALUES
					('" . $_POST["employeessn"] . "','" . $_POST["branchid"] . "','" . $_POST["firstname"] . "','" . $_POST["lastname"] . "','" . $_POST["phone"] . "',NOW())
			";
			break;
		case "put":
			$sql = "
				UPDATE employee
				SET branchID = '" . $_POST["branchid"] . "',
					firstName = '" . $_POST["firstname"] . "',
					lastName = '" . $_POST["lastname"] . "',
					phoneNo = '" . $_POST["phone"] . "'
				VALUES
					('" . $_POST["employeessn"] . "','" . $_POST["branchid"] . "','" . $_POST["firstname"] . "','" . $_POST["lastname"] . "','" . $_POST["phone"] . "',NOW())
				WHERE = '" . $_POST["employeessn"] . "'
			";
			break;
	}

	try {
		$stmt = $db -> prepare($sql);
		$stmt -> execute();
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	header("Location: ../employees");
?>