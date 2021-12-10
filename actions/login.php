<?php
	include "../connection.php";

	$db = dbConn::getConnection();
	$sql = "SELECT E_SSN, FirstName, LastName, UIPassword FROM EMPLOYEE WHERE E_SSN = '" . $_POST[ssn] . "'";

	try {
		$stmt = $db->prepare($sql);
		$stmt -> execute();
		$result = $stmt -> fetch(PDO::FETCH_ASSOC);
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	if (password_verify($_POST["password"], $result["UIPassword"])) {
		session_start();
		$_SESSION["loggedIn"] = "1";
		$_SESSION["E_SSN"] = $result["E_SSN"];
		$_SESSION["FirstName"] = $result["FirstName"];
		$_SESSION["LastName"] = $result["LastName"];
	}

	header("Location: /~afm36/CS631");
?>