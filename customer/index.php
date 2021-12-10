<?php
	include "../session.php";
	include "../connection.php";

	$sql = "SELECT
		CUSTOMER.C_SSN,
		FirstName,
		LastName,
		PhoneNo,
		StreetNo,
		City,
		State,
		ZipCode,
		COUNT(AccountNo) AS Accounts
	FROM CUSTOMER
		LEFT JOIN CUSTOMER_TO_ACCOUNT ON CUSTOMER_TO_ACCOUNT.C_SSN = CUSTOMER.C_SSN"
	. (isSet($_GET["c_ssn"]) && !empty($_GET["c_ssn"]) ? " WHERE CUSTOMER.C_SSN = " . $_GET["c_ssn"] . " " :  "") . "
	GROUP BY CUSTOMER.C_SSN, FirstName, LastName, PhoneNo, StreetNo, City, State, ZipCode
	ORDER BY LastName ASC, FirstName ASC";

	$db = dbConn::getConnection();

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
				if(!isSet($_GET["c_ssn"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="/~afm36/CS631">Dashboard</a></li>
							<li class="breadcrumb-item active" aria-current="page">Customer</li>
						</ol>
					</nav>');
					print('<h1>' . $_SESSION["FirstName"] . '\'s Customers' . '</h1>
						<table class="table table-striped text-center">
							<thead>
								<tr>
									<th>SSN</th>
									<th>Name</th>
									<th>Phone Number</th>
									<th>Street Number</th>
									<th>City</th>
									<th>State</th>
									<th>Zipcode</th>
									<th>Accounts</th>
								</tr>
							</thead>
						<tbody>');
					if ($result -> rowCount() > 0) {
						while($row = $result -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . $row["C_SSN"] . '</td>
								<td>' . $row["FirstName"] . ' ' . $row["LastName"] . '</td>
								<td>' . $row["PhoneNo"] . '</td>
								<td>' . $row["StreetNo"] . '</td>
								<td>' . $row["City"] . '</td>
								<td>' . $row["State"] . '</td>
								<td>' . $row["ZipCode"] . '</td>
								<td>' . $row["Accounts"] . '</td>
								<td><a href="?c_ssn=' . $row["C_SSN"] . '" class="btn btn-primary">Edit</a></td>
								<td>
									<form name="data" action="../actions/customer.php" method="post" onsubmit="return confirm(\'Do you really want to delete ' . $row["FirstName"] . '?\')">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="ssn" value="' . $row["C_SSN"] . '">
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
									<a href="?c_ssn=">Add a new customer</a>
								</td>
							</tr>
						</tfoot>
					</table>');
				} else {
					if(empty($_GET["c_ssn"])) {
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="/~afm36/CS631">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="/~afm36/CS631/customer">Customer</a></li>
								<li class="breadcrumb-item active" aria-current="page">Add Customer</li>
							</ol>
						</nav>');
						print('<h1>Add a New Customer</h1>
						<form name="data" action="../actions/customer.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="post">
							<div class="row">
								<div class="col-4">
									<label>Social Security Number</label>
									<input type="text" name="ssn" class="form-control" maxlength="9" required>
								</div>
								<div class="col-4">
									<label>First Name</label>
									<input type="text" name="firstname" class="form-control" required>
								</div>
								<div class="col-4">
									<label>Last Name</label>
									<input type="text" name="lastname" class="form-control" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-3">
									<label>Street</label>
									<input type="text" name="street" class="form-control" required>
								</div>
								<div class="col-3">
									<label>City</label>
									<input type="text" name="city" class="form-control" required>
								</div>
								<div class="col-3">
									<label>State</label>
									<input type="text" name="state" class="form-control" required>
								</div>
								<div class="col-3">
									<label>ZipCode</label>
									<input type="text" name="zipcode" class="form-control" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-3">
									<label>Phone Number</label>
									<input type="text" name="phoneno" class="form-control" required>
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
	</body>
</html>