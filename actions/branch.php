<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	switch ($_POST["action"]) {
		case "delete":
			$sql = "
				DELETE FROM account WHERE branchID = " . $_POST["branchid"] . ";
				DELETE FROM branch WHERE branchID = " . $_POST["branchid"] . ";
			";
			break;
		case "post":
			$sql = "
				INSERT INTO branch
					(branchName,streetNo,city,state,zipcode)
				VALUES
					('" . $_POST["name"] . "','" . $_POST["street"] . "','" . $_POST["city"] . "','" . $_POST["state"] . "','" . $_POST["zipcode"] . "');
				INSERT INTO branchManager
					(branchID,managerSSN,assistantManagerSSN)
				VALUES
					(@@IDENTITY,'" . $_POST["managerssn"] . "','" . $_POST["assistantmanagerssn"] . "');
			";
			break;
		case "put":
			$sql = "
				UPDATE branch
				SET branchName = '" . $_POST["name"] . "',
					streetNo = '" . $_POST["street"] . "',
					city = '" . $_POST["city"] . "',
					state = '" . $_POST["state"] . "',
					zipcode = '" . $_POST["zipcode"] . "'
				WHERE branchID = " . $_POST["branchid"] . ";

				UPDATE branchManager
				SET managerSSN = '" . $_POST["managerssn"] . "',
					assistantManagerSSN = '" . $_POST["assistantmanagerssn"] . "'
				WHERE branchID = " . $_POST["branchid"] . ";
			";
			break;
	}

	try {
		$stmt = $db -> prepare($sql);
		$stmt -> execute();
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	header("Location: ../branches");
?>