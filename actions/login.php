<?php
	include "../connection.php";

	$db = dbConn::getConnection();
	$sql = "SELECT username, password FROM adminLogin WHERE username = '" . $_POST[username] . "'";

	try {
		$result = $db -> prepare($sql);
		$result -> execute();
		$user = $result -> fetch(PDO::FETCH_ASSOC);
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	if (password_verify($_POST["password"], $user["password"])) {
		session_start();
		$_SESSION["loggedIn"] = "1";
		$_SESSION["username"] = $user["username"];
	}

	header("Location: ../");
?>