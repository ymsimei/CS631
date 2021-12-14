<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	try {
		$accounts = $db -> prepare(
			"SELECT
				account.accountID,
				account.accountType,
				account.interestRate,
				account.balance,
				account.lastAccessedDate,
				branch.branchName
			FROM account
				LEFT JOIN branch AS branch ON branch.branchID = account.branchID
			WHERE account.accountType NOT IN ('Special Charge')"
		);
		$accounts -> execute();

		if(isSet($_GET["accountid"]) && !empty($_GET["accountid"])) {
				$transactions = $db -> prepare(
				"SELECT
					transaction.transactionDate,
					transaction.transactionCode,
					transactionType.transactionName,
					CASE WHEN transactionType.transactionType = 'CREDIT' THEN transaction.transactionAmount ELSE '-' END AS credits,
					CASE WHEN transactionType.transactionType = 'DEBIT' THEN transaction.transactionAmount ELSE '-' END AS debits,
					transaction.accountBalanceAsOf,
					transaction.transactionNote
				FROM transaction
					LEFT JOIN transactionType ON transactionType.transactionCode = transaction.transactionCode
					LEFT JOIN account ON account.accountID = transaction.accountID
				WHERE transaction.transactionAmount > 0
					AND transaction.accountID = " . $_GET["accountid"]
			);
			$transactions -> execute();
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
				if(!isSet($_GET["accountid"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
							<li class="breadcrumb-item active">Accounts</li>
						</ol>
					</nav>');
					print('<h1>Bank Accounts</h1>
						<table class="table table-striped text-center align-middle">
							<thead>
								<tr>
									<th>ID</th>
									<th>Type</th>
									<th>Balance</th>
									<th>Branch</th>
									<th>Last Accessed</th>
								</tr>
							</thead>
						<tbody>');
					if($accounts -> rowCount() > 0) {
						while($row = $accounts -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . $row["accountID"] . '</td>
								<td>' . $row["accountType"] . '</td>
								<td>' . $row["balance"] . '</td>
								<td>' . $row["branchName"] . '</td>
								<td>' . date('F d, o', strtotime($row["lastAccessedDate"])) . '<br/>' . date('g:ia', strtotime($row["lastAccessedDate"])) . '</td>
								<td><a href="?accountid=' . $row["accountID"] . '" class="btn btn-primary">Passbook</a></td>
							</tr>');
						}
					} else {
						print('<tr><td colspan="8">Nothing here...</td></tr>');
					}
					print('</tbody>
					</table>');
				} elseif(!empty($_GET["accountid"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="../accounts">Accounts</a></li>
							<li class="breadcrumb-item active">Account Passbook</li>
						</ol>
					</nav>
					<h1>Account ' . $_GET["accountid"] . ' Passbook</h1>
						<table class="table table-striped text-center align-middle">
							<thead>
								<tr>
									<th>Date</th>
									<th>Transaction Code</th>
									<th>Transaction Name</th>
									<th>Credits</th>
									<th>Debits</th>
									<th>Balance</th>
									<th>Note</th>
								</tr>
							</thead>
						<tbody>');
					if($transactions -> rowCount() > 0) {
						while($row = $transactions -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . date('F d, o', strtotime($row["transactionDate"])) . '<br/>' . date('g:ia', strtotime($row["transactionDate"])) . '</td>
								<td>' . $row["transactionCode"] . '</td>
								<td>' . $row["transactionName"] . '</td>
								<td>' . $row["credits"] . '</td>
								<td>' . $row["debits"] . '</td>
								<td>' . $row["accountBalanceAsOf"] . '</td>
								<td>' . $row["transactionNote"] . '</td>
							</tr>');
						}
					} else {
						print('<tr><td colspan="7">Nothing here...</td></tr>');
					}
					print('</tbody>
					</table>');
				}
			?>
		</div>
		<script>
			function disableSelect(target) {
				var options = document.querySelectorAll('select[name="' + target + '"] option:not([value=""])');
				for(var i = 0; i < options.length; i++) {
					options[i].disabled = false;
				}
				var option = document.querySelector('select[name="' + target + '"] option[value="' + document.querySelector('select[name="' + (target == 'managerssn' ? 'assistantmanagerssn' : 'managerssn') + '"]').value + '"]');
				option.disabled = true;
			}

			window.onload = function(event) {
				disableSelect("managerssn");
				disableSelect("assistantmanagerssn");
			};
		</script>
	</body>
</html>