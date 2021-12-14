<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	try {
		$customers = $db -> prepare(
			"SELECT
				customer.customerSSN,
				customer.bankerSSN,
				customer.firstName,
				customer.lastName,
				CONCAT(customer.firstName, ' ', customer.lastName) AS customerName,
				customer.phoneNo,
				customer.streetNo,
				customer.buildingNo,
				customer.city,
				customer.state,
				customer.zipcode,
				CONCAT(employee.firstName, ' ', employee.lastName) AS bankerName,
				COUNT(customerToAccount.accountID) AS numCustomerAccounts
			FROM customer
				LEFT JOIN customerToAccount ON customerToAccount.customerSSN = customer.customerSSN
				LEFT JOIN employee ON employee.employeeSSN = customer.bankerSSN"
			. ((isSet($_GET["customerssn"]) && !empty($_GET["customerssn"])) ? (" WHERE customer.customerSSN = '" . $_GET["customerssn"] . "' ") : " ") .
			"GROUP BY customer.customerSSN,customer.bankerSSN,customer.firstName,customer.lastName,customer.phoneNo,customer.streetNo,customer.buildingNo,customer.city,customer.state,customer.zipcode,employee.firstName,employee.lastName"
		);
		$customers -> execute();

		if(isSet($_GET["customerssn"])) {
			$employees = $db -> prepare(
				"SELECT
					employee.employeeSSN,
					CONCAT(employee.firstName, ' ', employee.lastName) AS bankerName
				FROM employee"
			);
			$employees -> execute();
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
				if(!isSet($_GET["customerssn"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
							<li class="breadcrumb-item active">Customers</li>
						</ol>
					</nav>
					<h1>Bank Customers</h1>
						<table class="table table-striped text-center align-middle">
							<thead>
								<tr>
									<th>Social Security Number</th>
									<th>Name</th>
									<th>Phone</th>
									<th>Address</th>
									<th>Banker</th>
									<th>Accounts</th>
								</tr>
							</thead>
						<tbody>');
					if($customers -> rowCount() > 0) {
						while($row = $customers -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . $row["customerSSN"] . '</td>
								<td>' . $row["customerName"] . '</td>
								<td>' . $row["phoneNo"] . '</td>
								<td>' . $row["streetNo"] . (strlen($row["buildingNo"]) > 0 ? '<br/>' . $row["buildingNo"] : '') . '<br/>' . $row["city"] . ', ' . $row["state"] . ' ' . $row["zipcode"] .'</td>
								<td>' . $row["bankerName"] . '</td>
								<td>' . $row["numCustomerAccounts"] . '</td>
								<td><a href="?customerssn=' . $row["customerSSN"] . '" class="btn btn-primary"><i class="bi bi-pencil-square"></i></a></td>
								<td>
									<form name="data" action="../actions/customer.php" method="post" onsubmit="return confirm(\'Do you really want to delete ' . $row["customerName"] . '?\')">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="customerssn" value="' . $row["customerSSN"] . '">
										<button type="submit" class="btn btn-danger"><i class="bi bi-trash-fill"></i></button>
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
								<td colspan="8">
									<a href="?customerssn="><button class="btn btn-success btn-sm">Create a new Customer</button></a>
								</td>
							</tr>
						</tfoot>
					</table>');
				} else {
					if(empty($_GET["customerssn"])) {
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="./">Customers</a></li>
								<li class="breadcrumb-item active">Create Customer</li>
							</ol>
						</nav>
						<h1>Create a New Customer</h1>
						<form name="data" action="../actions/customer.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="post">
							<div class="row">
								<div class="col-3">
									<label>Social Security Number</label>
									<input type="text" name="customerssn" class="form-control" maxlength="25" placeholder="XXX-XX-XXXX" required>
								</div>
								<div class="col-3">
									<label>First Name</label>
									<input type="text" name="firstname" class="form-control" maxlength="25" placeholder="John" required>
								</div>
								<div class="col-3">
									<label>Last Name</label>
									<input type="text" name="lastname" class="form-control" maxlength="25" placeholder="Smith" required>
								</div>
								<div class="col-3">
									<label>Phone</label>
									<input type="text" name="phone" class="form-control" placeholder="(123) 456 - 7890" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-3">
									<label>Street</label>
									<input type="text" name="street" class="form-control" placeholder="1 Main St." required>
								</div>
								<div class="col-3">
									<label>City</label>
									<input type="text" name="city" class="form-control" placeholder="New Alexandria" required>
								</div>
								<div class="col-3">
									<label>State</label>
									<select class="form-select" name="state" required>
										<option value="" disabled selected>Select</option>
										<option value="AL">Alabama</option>
										<option value="AK">Alaska</option>
										<option value="AZ">Arizona</option>
										<option value="AR">Arkansas</option>
										<option value="CA">California</option>
										<option value="CO">Colorado</option>
										<option value="CT">Connecticut</option>
										<option value="DE">Delaware</option>
										<option value="DC">District Of Columbia</option>
										<option value="FL">Florida</option>
										<option value="GA">Georgia</option>
										<option value="HI">Hawaii</option>
										<option value="ID">Idaho</option>
										<option value="IL">Illinois</option>
										<option value="IN">Indiana</option>
										<option value="IA">Iowa</option>
										<option value="KS">Kansas</option>
										<option value="KY">Kentucky</option>
										<option value="LA">Louisiana</option>
										<option value="ME">Maine</option>
										<option value="MD">Maryland</option>
										<option value="MA">Massachusetts</option>
										<option value="MI">Michigan</option>
										<option value="MN">Minnesota</option>
										<option value="MS">Mississippi</option>
										<option value="MO">Missouri</option>
										<option value="MT">Montana</option>
										<option value="NE">Nebraska</option>
										<option value="NV">Nevada</option>
										<option value="NH">New Hampshire</option>
										<option value="NJ">New Jersey</option>
										<option value="NM">New Mexico</option>
										<option value="NY">New York</option>
										<option value="NC">North Carolina</option>
										<option value="ND">North Dakota</option>
										<option value="OH">Ohio</option>
										<option value="OK">Oklahoma</option>
										<option value="OR">Oregon</option>
										<option value="PA">Pennsylvania</option>
										<option value="RI">Rhode Island</option>
										<option value="SC">South Carolina</option>
										<option value="SD">South Dakota</option>
										<option value="TN">Tennessee</option>
										<option value="TX">Texas</option>
										<option value="UT">Utah</option>
										<option value="VT">Vermont</option>
										<option value="VA">Virginia</option>
										<option value="WA">Washington</option>
										<option value="WV">West Virginia</option>
										<option value="WI">Wisconsin</option>
										<option value="WY">Wyoming</option>
									</select>
								</div>
								<div class="col-3">
									<label>Zipcode</label>
									<input type="number" name="zipcode" class="form-control" placeholder="12345" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-4">
									<label>Banker</label>
									<select class="form-select" name="bankerssn" required>
										<option value="" disabled selected>Select</option>');
										if($employees -> rowCount() > 0) {
											while($row = $employees -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["employeeSSN"] . '">' . $row["bankerName"] . '</option>');
											}
										}
									print('</select>
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
					} else {
						$customer = $customers -> fetch(PDO::FETCH_ASSOC);
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="./">Customers</a></li>
								<li class="breadcrumb-item active">Modify Customer</li>
							</ol>
						</nav>
						<h1>Modify an Existing Customer</h1>
						<form name="data" action="../actions/customer.php" method="put" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="post">
							<div class="row">
								<div class="col-3">
									<label>Social Security Number</label>
									<input type="text" name="customerssn" class="form-control" maxlength="25" placeholder="XXX-XX-XXXX" value="' . $customer["customerSSN"] . '" readonly>
								</div>
								<div class="col-3">
									<label>First Name</label>
									<input type="text" name="firstname" class="form-control" maxlength="25" placeholder="John" value="' . $customer["firstName"] . '" required>
								</div>
								<div class="col-3">
									<label>Last Name</label>
									<input type="text" name="lastname" class="form-control" maxlength="25" placeholder="Smith" value="' . $customer["lastName"] . '" required>
								</div>
								<div class="col-3">
									<label>Phone</label>
									<input type="text" name="phone" class="form-control" placeholder="(123) 456 - 7890" value="' . $customer["phoneNo"] . '" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-3">
									<label>Street</label>
									<input type="text" name="street" class="form-control" placeholder="1 Main St." value="' . $customer["street"] . '" required>
								</div>
								<div class="col-3">
									<label>City</label>
									<input type="text" name="city" class="form-control" placeholder="New Alexandria" value="' . $customer["city"] . '" required>
								</div>
								<div class="col-3">
									<label>State</label>
									<select class="form-select" name="state" required>
										<option value="" disabled>Select</option>
										<option value="AL"' . ($customer["state"] == "AL" ? " selected" : "") . '>Alabama</option>
										<option value="AK"' . ($customer["state"] == "AK" ? " selected" : "") . '>Alaska</option>
										<option value="AZ"' . ($customer["state"] == "AZ" ? " selected" : "") . '>Arizona</option>
										<option value="AR"' . ($customer["state"] == "AR" ? " selected" : "") . '>Arkansas</option>
										<option value="CA"' . ($customer["state"] == "CA" ? " selected" : "") . '>California</option>
										<option value="CO"' . ($customer["state"] == "CO" ? " selected" : "") . '>Colorado</option>
										<option value="CT"' . ($customer["state"] == "CT" ? " selected" : "") . '>Connecticut</option>
										<option value="DE"' . ($customer["state"] == "DE" ? " selected" : "") . '>Delaware</option>
										<option value="DC"' . ($customer["state"] == "DC" ? " selected" : "") . '>District Of Columbia</option>
										<option value="FL"' . ($customer["state"] == "FL" ? " selected" : "") . '>Florida</option>
										<option value="GA"' . ($customer["state"] == "GA" ? " selected" : "") . '>Georgia</option>
										<option value="HI"' . ($customer["state"] == "HI" ? " selected" : "") . '>Hawaii</option>
										<option value="ID"' . ($customer["state"] == "ID" ? " selected" : "") . '>Idaho</option>
										<option value="IL"' . ($customer["state"] == "IL" ? " selected" : "") . '>Illinois</option>
										<option value="IN"' . ($customer["state"] == "IN" ? " selected" : "") . '>Indiana</option>
										<option value="IA"' . ($customer["state"] == "IA" ? " selected" : "") . '>Iowa</option>
										<option value="KS"' . ($customer["state"] == "KS" ? " selected" : "") . '>Kansas</option>
										<option value="KY"' . ($customer["state"] == "KY" ? " selected" : "") . '>Kentucky</option>
										<option value="LA"' . ($customer["state"] == "LA" ? " selected" : "") . '>Louisiana</option>
										<option value="ME"' . ($customer["state"] == "ME" ? " selected" : "") . '>Maine</option>
										<option value="MD"' . ($customer["state"] == "MD" ? " selected" : "") . '>Maryland</option>
										<option value="MA"' . ($customer["state"] == "MA" ? " selected" : "") . '>Massachusetts</option>
										<option value="MI"' . ($customer["state"] == "MI" ? " selected" : "") . '>Michigan</option>
										<option value="MN"' . ($customer["state"] == "MN" ? " selected" : "") . '>Minnesota</option>
										<option value="MS"' . ($customer["state"] == "MS" ? " selected" : "") . '>Mississippi</option>
										<option value="MO"' . ($customer["state"] == "MO" ? " selected" : "") . '>Missouri</option>
										<option value="MT"' . ($customer["state"] == "MT" ? " selected" : "") . '>Montana</option>
										<option value="NE"' . ($customer["state"] == "NE" ? " selected" : "") . '>Nebraska</option>
										<option value="NV"' . ($customer["state"] == "NV" ? " selected" : "") . '>Nevada</option>
										<option value="NH"' . ($customer["state"] == "NH" ? " selected" : "") . '>New Hampshire</option>
										<option value="NJ"' . ($customer["state"] == "NJ" ? " selected" : "") . '>New Jersey</option>
										<option value="NM"' . ($customer["state"] == "NM" ? " selected" : "") . '>New Mexico</option>
										<option value="NY"' . ($customer["state"] == "NY" ? " selected" : "") . '>New York</option>
										<option value="NC"' . ($customer["state"] == "NC" ? " selected" : "") . '>North Carolina</option>
										<option value="ND"' . ($customer["state"] == "ND" ? " selected" : "") . '>North Dakota</option>
										<option value="OH"' . ($customer["state"] == "OH" ? " selected" : "") . '>Ohio</option>
										<option value="OK"' . ($customer["state"] == "OK" ? " selected" : "") . '>Oklahoma</option>
										<option value="OR"' . ($customer["state"] == "OR" ? " selected" : "") . '>Oregon</option>
										<option value="PA"' . ($customer["state"] == "PA" ? " selected" : "") . '>Pennsylvania</option>
										<option value="RI"' . ($customer["state"] == "RI" ? " selected" : "") . '>Rhode Island</option>
										<option value="SC"' . ($customer["state"] == "SC" ? " selected" : "") . '>South Carolina</option>
										<option value="SD"' . ($customer["state"] == "SD" ? " selected" : "") . '>South Dakota</option>
										<option value="TN"' . ($customer["state"] == "TN" ? " selected" : "") . '>Tennessee</option>
										<option value="TX"' . ($customer["state"] == "TX" ? " selected" : "") . '>Texas</option>
										<option value="UT"' . ($customer["state"] == "UT" ? " selected" : "") . '>Utah</option>
										<option value="VT"' . ($customer["state"] == "VT" ? " selected" : "") . '>Vermont</option>
										<option value="VA"' . ($customer["state"] == "VA" ? " selected" : "") . '>Virginia</option>
										<option value="WA"' . ($customer["state"] == "WA" ? " selected" : "") . '>Washington</option>
										<option value="WV"' . ($customer["state"] == "WV" ? " selected" : "") . '>West Virginia</option>
										<option value="WI"' . ($customer["state"] == "WI" ? " selected" : "") . '>Wisconsin</option>
										<option value="WY"' . ($customer["state"] == "WY" ? " selected" : "") . '>Wyoming</option>
									</select>
								</div>
								<div class="col-3">
									<label>Zipcode</label>
									<input type="number" name="zipcode" class="form-control" placeholder="12345" value="' . $customer["zipcode"] . '" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-4">
									<label>Banker</label>
									<select class="form-select" name="bankerssn" required>
										<option value="" disabled selected>Select</option>');
										if($employees -> rowCount() > 0) {
											while($row = $employees -> fetch(PDO::FETCH_ASSOC)) {
print('<option value="' . $row["employeeSSN"] . '"' . ($row["employeeSSN"] == $customer["bankerSSN"] ? " selected" : "") . '>' . $row["bankerName"] . '</option>');
											}
										}
									print('</select>
								</div>
							</div>
							<hr />
							<div class="row pt-2">
								<div class="col-12">
									<button type="submit" class="btn btn-primary">Update</button>
									<a href="./"><button type="button" class="btn btn-outline-danger">Cancel</button></a>
								</div>
							</div>
						</form>');
					}
				}
			?>
		</div>
	</body>
</html>