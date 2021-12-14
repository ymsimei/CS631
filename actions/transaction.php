<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	switch ($_POST["action"]) {
		case "post":
			$sql = "
				INSERT INTO transaction
					(accountID,transactionCode,transactionDate,transactionAmount,transactionNote,accountBalanceAsOf)
				SELECT
					" . $_POST["toaccount"] . ",
					'" . $_POST["type"] . "',
					NOW(),
					" . $_POST["amount"] . ",
					'" . $_POST["note"] . "',
					CASE WHEN '" . $_POST["type"] . "' IN ('CD','CQD','MSCCHRG','SCCHRG') THEN balance + " . $_POST["amount"] . " ELSE balance - " . $_POST["amount"] . " END
				FROM account WHERE accountID = " . $_POST["toaccount"] . ";

				UPDATE account
					SET
						balance = CASE WHEN '" . $_POST["type"] . "' IN ('CD','CQD','MSCCHRG','SCCHRG') THEN balance + " . $_POST["amount"] . " ELSE balance - " . $_POST["amount"] . " END,
						lastAccessedDate = NOW()
				WHERE accountID = " . $_POST["toaccount"] . ";

				INSERT INTO transaction
					(accountID,transactionCode,transactionDate,transactionAmount,accountBalanceAsOf)
				SELECT
					" . $_POST["fromaccount"] . ",
					CASE WHEN '" . $_POST["type"] . "' = 'CD' THEN 'WD' WHEN '" . $_POST["type"] . "' = 'CQD' THEN 'CQW' WHEN '" . $_POST["type"] . "' = 'MSCCHRG' THEN 'MSC' WHEN '" . $_POST["type"] . "' = 'SCCHRG' THEN 'SC' ELSE ''END,
					NOW(),
					" . $_POST["amount"] . ",
					CASE WHEN '" . $_POST["type"] . "' IN ('CD','CQD','MSCCHRG','SCCHRG') THEN balance - " . $_POST["amount"] . " ELSE balance + " . $_POST["amount"] . " END
				FROM account WHERE accountID = " . $_POST["fromaccount"] . ";

				UPDATE account
					SET
						balance = CASE WHEN '" . $_POST["type"] . "' IN ('CD','CQD','MSCCHRG','SCCHRG') THEN balance - " . $_POST["amount"] . " ELSE balance + " . $_POST["amount"] . " END,
						lastAccessedDate = NOW()
				WHERE accountID = " . $_POST["fromaccount"] . ";
			";
			break;
	}

	try {
		$stmt = $db -> prepare($sql);
		$stmt -> execute();
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	header("Location: ../transactions");
?>