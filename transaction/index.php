<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	if(isSet($_GET["txnid"]) && empty($_GET["txnid"])) {
		$sql = "SELECT
			ACCOUNT.AccountNo,
			ACCOUNT.AccType,
			CUSTOMER.FirstName,
			CUSTOMER.LastName
		FROM ACCOUNT
			LEFT JOIN CUSTOMER_TO_ACCOUNT ON CUSTOMER_TO_ACCOUNT.AccountNo = ACCOUNT.AccountNo
			LEFT JOIN CUSTOMER ON CUSTOMER.C_SSN = CUSTOMER_TO_ACCOUNT.C_SSN
		ORDER BY CUSTOMER.LastName ASC, CUSTOMER.FirstName ASC";

		try {
			$sourceaccounts = $db -> prepare($sql);
			$sourceaccounts -> execute();

			$destaccounts = $db -> prepare($sql);
			$destaccounts -> execute();
		} catch(PDOException $e) {
			echo $e -> getMessage();
		}

		$sql = "SELECT
			TxnName,
			TxnType
		FROM TRANSACTION_TYPE
		ORDER BY TRANSACTION_TYPE.TxnType ASC";

		try {
			$types = $db -> prepare($sql);
			$types -> execute();
		} catch(PDOException $e) {
			echo $e -> getMessage();
		}
	} else {
		$sql = "SELECT
			TRANSACTION.TxnID,
			CUSTOMER.FirstName + ' ' + CUSTOMER.LastName AS TxnCustomerName,
			TRANSACTION_TYPE.TxnName,
			TRANSACTION.TxnDate + ' ' + TRANSACTION.TxnTime AS TxnDateTime,
			TRANSACTION.TxnAmount,
			TRANSACTION.TxnNotes
		FROM TRANSACTION
			LEFT JOIN TRANSACTION_TYPE ON TRANSACTION_TYPE.TxnType = TRANSACTION.TxnType
			LEFT JOIN CUSTOMER_TO_ACCOUNT ON CUSTOMER_TO_ACCOUNT.AccountNo = TRANSACTION.AccountNo
			LEFT JOIN CUSTOMER ON CUSTOMER.C_SSN = CUSTOMER_TO_ACCOUNT.C_SSN"
		. (isSet($_GET["txnid"]) && !empty($_GET["txnid"]) ? " WHERE TRANSACTION.TxnID = " . $_GET["txnid"] . " " :  "") . "
		ORDER BY TxnDate DESC, TxnTime DESC";
	}

	try {
		$result = $db -> prepare($sql);
		$result -> execute();
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
	</head>
	<body>
		<div class="container py-5">
			<?php
				if(!isSet($_GET["txnid"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="/~afm36/CS631">Dashboard</a></li>
							<li class="breadcrumb-item active" aria-current="page">Transaction</li>
						</ol>
					</nav>');
					print('<h1>Transactions</h1>
						<table class="table table-striped text-center">
							<thead>
								<tr>
									<th>Record ID</th>
									<th>Account Holder</th>
									<th>Type</th>
									<th>Amount</th>
									<th>Date</th>
									<th>Notes</th>
								</tr>
							</thead>
						<tbody>');
					if ($result -> rowCount() > 0) {
						while($row = $result -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . $row["TxnID"] . '</td>
								<td>' . $row["TxnCustomerName"] . '</td>
								<td>' . $row["TxnName"] . '</td>
								<td>' . $row["TxnAmount"] . '</td>
								<td>' . $row["TxnDateTime"] . '</td>
								<td>' . $row["TxnNotes"] . '</td>
								<td><a href="?txnid=' . $row["TxnID"] . '" class="btn btn-primary">Edit</a></td>
								<td>
									<form name="data" action="../actions/transaction.php" method="post" onsubmit="return confirm(\'Do you really want to delete this transaction?\')">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="txnid" value="' . $row["TxnID"] . '">
										<button type="submit" class="btn btn-danger">Delete</button>
									</form>
								</td>
							</tr>');
						}
					} else {
						print('<tr><td colspan="8">Nothing here...</td></tr>');
					}
					print('</tbody>
						<tfoot>
							<tr>
								<td colspan="10">
									<a href="?txnid=">Add a new transaction</a>
								</td>
							</tr>
						</tfoot>
					</table>');
				} else {
					if(empty($_GET["txnid"])) {
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="/~afm36/CS631">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="/~afm36/CS631/transaction">Transaction</a></li>
								<li class="breadcrumb-item active" aria-current="page">Add Transaction</li>
							</ol>
						</nav>');
						print('<h1>Add a New Transaction</h1>
						<form name="data" action="../actions/transaction.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="post">
							<div class="row">
								<div class="col-4">
									<label>Amount</label>
									<input type="number" name="amount" class="form-control" required>
								</div>
								<div class="col-8">
									<label>Transaction Type</label>
									<select class="form-select" name="type" required>
										<option value="" disabled selected>Select</option>');
										if ($types -> rowCount() > 0) {
											while($row = $types -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["TxnType"] . '">' . $row["TxnName"] . '</option>');
											}
										}
									print('</select>
								</div>
							</div>');
							print('<div class="row pt-2">
								<div class="col-6">
									<label>From</label>
									<select class="form-select" name="source" onchange="disableSelect(\'destination\')" required>
										<option value="" disabled selected>Select</option>');
										if ($sourceaccounts -> rowCount() > 0) {
											while($row = $sourceaccounts -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["AccountNo"] . '">' . $row["FirstName"] . ' ' . $row["LastName"] . ' - ' . $row["AccType"] . '</option>');
											}
										}
									print('</select>
								</div>
								<div class="col-6">
									<label>To</label>
									<select class="form-select" name="destination" onchange="disableSelect(\'source\')" required>
										<option value="" disabled selected>Select</option>');
										if ($destaccounts -> rowCount() > 0) {
											while($row = $destaccounts -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["AccountNo"] . '">' . $row["FirstName"] . ' ' . $row["LastName"] . ' - ' . $row["AccType"] . '</option>');
											}
										}
									print('</select>
								</div>
							</div>
							<hr />
							<div class="row pt-2">
								<div class="col-12">
									<button type="submit" class="btn btn-primary">Add</button>
								</div>
							</div>
						</form>');
					} else {
						$result = $result -> fetch(PDO::FETCH_ASSOC);

						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="/~afm36/CS631">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="/~afm36/CS631/customer">Customer</a></li>
								<li class="breadcrumb-item active" aria-current="page">Edit Customer</li>
							</ol>
						</nav>');
						print('<h1>Edit an existing Customer</h1>
						<form name="data" action="../actions/customer.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="put">
							<input type="hidden" name="ssn" value="' . $result["C_SSN"] . '">
							<div class="row">
								<div class="col-4">
									<label>Social Security Number</label>
									<input type="text" name="ssn" class="form-control" maxlength="9" value="' . $result["C_SSN"] . '" required disabled>
								</div>
								<div class="col-4">
									<label>First Name</label>
									<input type="text" name="firstname" class="form-control" value="' . $result["FirstName"] . '" required>
								</div>
								<div class="col-4">
									<label>Last Name</label>
									<input type="text" name="lastname" class="form-control" value="' . $result["LastName"] . '" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-3">
									<label>Street</label>
									<input type="text" name="street" class="form-control" value="' . $result["StreetNo"] . '" required>
								</div>
								<div class="col-3">
									<label>City</label>
									<input type="text" name="city" class="form-control" value="' . $result["City"] . '" required>
								</div>
								<div class="col-3">
									<label>State</label>
									<input type="text" name="state" class="form-control" value="' . $result["State"] . '" required>
								</div>
								<div class="col-3">
									<label>ZipCode</label>
									<input type="text" name="zipcode" class="form-control" value="' . $result["ZipCode"] . '" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-3">
									<label>Phone Number</label>
									<input type="text" name="phoneno" class="form-control" value="' . $result["PhoneNo"] . '" required>
								</div>
							</div>
							<hr />
							<div class="row pt-2">
								<div class="col-12">
									<button type="submit" class="btn btn-primary">Save</button>
								</div>
							</div>
						</form>');
					}
				}
			?>
		</div>
		<script>
			function disableSelect(name) {
				var options = document.querySelectorAll('select[name="' + name + '"] option:not([value=""])');
				for(var i = 0; i < options.length; i++) {
					options[i].disabled = false;
				}
				var option = document.querySelector('select[name="' + name + '"] option[value="' + document.querySelector('select[name="' + (name == 'source' ? 'destination' : 'source') + '"]').value + '"]');
				option.disabled = true;
			}
		</script>
	</body>
</html>