<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	try {
		$transactions = $db -> prepare(
			"SELECT
				transaction.transactionID,
				transaction.transactionAmount,
				transactionType.transactionName,
				account.accountID,
				account.accountType,
				transaction.transactionDate,
				transaction.transactionNote
			FROM transaction
				LEFT JOIN transactionType ON transactionType.transactionCode = transaction.transactionCode
				LEFT JOIN account ON account.accountID = transaction.accountID
			WHERE transaction.transactionAmount > 0"
			. ((isSet($_GET["transactionid"]) && !empty($_GET["transactionid"])) ? (" AND transaction.transactionID = '" . $_GET["transactionid"] . "' ") : " ")
		);
		$transactions -> execute();

		if(isSet($_GET["transactionid"])) {
			$transactionTypes = $db -> prepare(
				"SELECT
					transactionCode,
					transactionName,
					transactionType,
					transactionCharge
				FROM transactionType"
			);
			$transactionTypes -> execute();

			$toAccounts = $db -> prepare(
				"SELECT
					account.accountID,
					account.balance,
					CONCAT(account.accountID, ' - ', account.accountType) AS accountName
				FROM account"
			);
			$toAccounts -> execute();

			$fromAccounts = $db -> prepare(
				"SELECT
					account.accountID,
					account.balance,
					CONCAT(account.accountID, ' - ', account.accountType) AS accountName
				FROM account"
			);
			$fromAccounts -> execute();
		}
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}
?>

<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
	</head>
	<body>
		<div class="container py-5">
			<?php
				if(!isSet($_GET["transactionid"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
							<li class="breadcrumb-item active">Transactions</li>
						</ol>
					</nav>
					<h1>Bank Transactions</h1>
						<table class="table table-striped text-center align-middle">
							<thead>
								<tr>
									<th>ID</th>
									<th>Amount</th>
									<th>Type</th>
									<th>Account ID</th>
									<th>Account Type</th>
									<th>Date</th>
									<th>Note</th>
								</tr>
							</thead>
						<tbody>');
					if($transactions -> rowCount() > 0) {
						while($row = $transactions -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . $row["transactionID"] . '</td>
								<td>' . $row["transactionAmount"] . '</td>
								<td>' . $row["transactionName"] . '</td>
								<td><a href="../accounts/?accountid=' . $row["accountID"] . '">' . $row["accountID"] . '</a></td>
								<td>' . $row["accountType"] . '</td>
								<td>' . date('F d, o', strtotime($row["transactionDate"])) . '<br/>' . date('g:ia', strtotime($row["transactionDate"])) . '</td>
								<td>' . $row["transactionNote"] . '</td>
							</tr>');
						}
					} else {
						print('<tr><td colspan="7">Nothing here...</td></tr>');
					}
					print('</tbody>
						<tfoot>
							<tr>
								<td colspan="7">
									<a href="?transactionid="><button class="btn btn-success btn-sm">Create a new transaction</button></a>
								</td>
							</tr>
						</tfoot>
					</table>');
				} else {
					if(empty($_GET["transactionid"])) {
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="./">Transactions</a></li>
								<li class="breadcrumb-item active">Create Transaction</li>
							</ol>
						</nav>
						<h1>Create a New Transaction</h1>
						<form name="data" action="../actions/transaction.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="post">
							<div class="row">
								<div class="col-4">
									<label>Amount</label>
									<input type="number" name="amount" class="form-control" placeholder="10" required>
								</div>
								<div class="col-4">
									<label>Transaction Type</label>
									<select class="form-select" name="type" required>
										<option value="" disabled selected>Select</option>');
										if($transactionTypes -> rowCount() > 0) {
											while($row = $transactionTypes -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["transactionCode"] . '">' . $row["transactionName"] . '</option>');
											}
										}
									print('</select>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-4">
									<label>From</label>
									<select class="form-select" name="fromaccount" onchange="disableSelect(\'toaccount\')" required>
										<option value="" disabled selected>Select</option>');
										if($fromAccounts -> rowCount() > 0) {
											while($row = $fromAccounts -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["accountID"] . '">' . $row["accountName"] . ' (' . $row["balance"] . ')</option>');
											}
										}
									print('</select>
								</div>
								<div class="col-4">
									<label>From</label>
									<select class="form-select" name="toaccount" onchange="disableSelect(\'fromaccount\')" required>
										<option value="" disabled selected>Select</option>');
										if($toAccounts -> rowCount() > 0) {
											while($row = $toAccounts -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["accountID"] . '">' . $row["accountName"] . ' (' . $row["balance"] . ')</option>');
											}
										}
									print('</select>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-12">
									<label>Notes</label>
									<textarea type="textarea" name="note" class="form-control" placeholder="Additional details..." required></textarea>
								</div>
							</div>
							<hr />
							<div class="row pt-2">
								<div class="col-12">
									<button type="submit" class="btn btn-success">Create</button>
									<a href="./"><button type="button" class="btn btn-outline-danger">Cancel</button></a>
								</div>
							</div>
						</form>');
					}
				}
			?>
		</div>
		<script>
			function disableSelect(target) {
				var options = document.querySelectorAll('select[name="' + target + '"] option:not([value=""])');
				for(var i = 0; i < options.length; i++) {
					options[i].disabled = false;
				}
				var option = document.querySelector('select[name="' + target + '"] option[value="' + document.querySelector('select[name="' + (target == 'toaccount' ? 'fromaccount' : 'toaccount') + '"]').value + '"]');
				option.disabled = true;
			}

			window.onload = function(event) {
				disableSelect("toaccount");
				disableSelect("fromaccount");
			};
		</script>
	</body>
</html>