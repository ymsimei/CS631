<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	switch ($_POST["action"]) {
		case "delete":
			break;
		case "post":
			break;
		case "put":
			break;
	}

	// try {
	// 	$stmt = $db -> prepare($sql);
	// 	$stmt -> execute();
	// } catch(PDOException $e) {
	// 	echo $e -> getMessage();
	// }

	header("Location: /~afm36/CS631/transaction");
?>