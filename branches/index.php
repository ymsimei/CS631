<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	try {
		$branches = $db -> prepare(
			"SELECT
				branch.branchID,
				branch.branchName,
				branch.streetNo,
				branch.buildingNo,
				branch.city,
				branch.state,
				branch.zipcode,
				manager.employeeSSN AS managerSSN,
				CONCAT(manager.firstName, ' ', manager.lastName) AS managerName,
				assistantManager.employeeSSN AS assistantManagerSSN,
				CONCAT(assistantManager.firstName, ' ', assistantManager.lastName) AS assistantManagerName,
				SUM(account.balance) AS balance
			FROM branch
				LEFT JOIN branchManager ON branchManager.branchID = branch.branchID
				LEFT JOIN employee AS manager ON branchManager.managerSSN = manager.employeeSSN
				LEFT JOIN employee AS assistantManager ON branchManager.assistantManagerSSN = assistantManager.employeeSSN
				LEFT JOIN account AS account ON account.branchID = branch.branchID"
			. ((isSet($_GET["branchid"]) && !empty($_GET["branchid"])) ? (" WHERE branch.branchID = " . $_GET["branchid"]) . " " : " ") .
			"GROUP BY branch.branchID,branch.branchName,branch.streetNo,branch.buildingNo,branch.city,branch.state,branch.zipcode,manager.employeeSSN"
		);
		$branches -> execute();

		if(isSet($_GET["branchid"])) {
			$managers = $db -> prepare(
				"SELECT
					employee.employeeSSN,
					CONCAT(employee.firstName, ' ', employee.lastName) AS employeeName
				FROM employee
				" . (empty($_GET["branchid"]) ? "" : "WHERE branchID = " . $_GET["branchid"])
			);
			$managers -> execute();

			$assistantManagers = $db -> prepare(
				"SELECT
					employee.employeeSSN,
					CONCAT(employee.firstName, ' ', employee.lastName) AS employeeName
				FROM employee
				" . (empty($_GET["branchid"]) ? "" : "WHERE branchID = " . $_GET["branchid"])
			);
			$assistantManagers -> execute();
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
				if(!isSet($_GET["branchid"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
							<li class="breadcrumb-item active">Branches</li>
						</ol>
					</nav>');
					print('<h1>Bank Branches</h1>
						<table class="table table-striped text-center align-middle">
							<thead>
								<tr>
									<th>ID</th>
									<th>Name</th>
									<th>Address</th>
									<th>Manager</th>
									<th>Assistant Manager</th>
									<th>Balance</th>
								</tr>
							</thead>
						<tbody>');
					if($branches -> rowCount() > 0) {
						while($row = $branches -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . $row["branchID"] . '</td>
								<td>' . $row["branchName"] . '</td>
								<td>' . $row["streetNo"] . (strlen($row["buildingNo"]) > 0 ? '<br/>' . $row["buildingNo"] : '') . '<br/>' . $row["city"] . ', ' . $row["state"] . ' ' . $row["zipcode"] .'</td>
								<td>' . $row["managerName"] . '</td>
								<td>' . $row["assistantManagerName"] . '</td>
								<td>' . $row["balance"] . '</td>
								<td><a href="?branchid=' . $row["branchID"] . '" class="btn btn-primary"><i class="bi bi-pencil-square"></i></a></td>
								<td>
									<form name="data" action="../actions/branch.php" method="post" onsubmit="return confirm(\'Do you really want to delete ' . $row["branchName"] . '?\')">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="branchid" value="' . $row["branchID"] . '">
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
									<a href="?branchid="><button class="btn btn-success btn-sm">Create a new branch</button></a>
								</td>
							</tr>
						</tfoot>
					</table>');
				} else {
					if(empty($_GET["branchid"])) {
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="./">Branches</a></li>
								<li class="breadcrumb-item active">Create Branch</li>
							</ol>
						</nav>');
						print('<h1>Create a New Branch</h1>
						<form name="data" action="../actions/branch.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="post">
							<div class="row">
								<div class="col-4">
									<label>Branch Name</label>
									<input type="text" name="name" class="form-control" maxlength="25" placeholder="First Branch" required>
								</div>
								<div class="col-4">
									<label>Manager Name</label>
									<select class="form-select" name="managerssn" onchange="disableSelect(\'assistantmanagerssn\')" required>
										<option value="" disabled selected>Select</option>');
										if($managers -> rowCount() > 0) {
											while($row = $managers -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["employeeSSN"] . '">' . $row["employeeName"] . '</option>');
											}
										}
									print('</select>
								</div>
								<div class="col-4">
									<label>Manager Name</label>
									<select class="form-select" name="assistantmanagerssn" onchange="disableSelect(\'managerssn\')" required>
										<option value="" disabled selected>Select</option>');
										if($assistantManagers -> rowCount() > 0) {
											while($row = $assistantManagers -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["employeeSSN"] . '">' . $row["employeeName"] . '</option>');
											}
										}
									print('</select>
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
							<hr />
							<div class="row pt-2">
								<div class="col-12">
									<button type="submit" class="btn btn-success">Create</button>
									<a href="./"><button type="button" class="btn btn-outline-danger">Cancel</button></a>
								</div>
							</div>
						</form>');
					} else {
						$branch = $branches -> fetch(PDO::FETCH_ASSOC);
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="./">Branches</a></li>
								<li class="breadcrumb-item active">Modify Branch</li>
							</ol>
						</nav>');
						print('<h1>Modify an Existing Branch</h1>
						<form name="data" action="../actions/branch.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="put">
							<input type="hidden" name="branchid" value="' . $branch["branchID"] . '">
							<div class="row">
								<div class="col-4">
									<label>Branch Name</label>
									<input type="text" name="name" class="form-control" maxlength="25" placeholder="First Branch" value="' . $branch["branchName"] . '" required>
								</div>
								<div class="col-4">
									<label>Manager Name</label>
									<select class="form-select" name="managerssn" onchange="disableSelect(\'assistantmanagerssn\')" required>
										<option value="" disabled selected>Select</option>');
										if($managers -> rowCount() > 0) {
											while($row = $managers -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["employeeSSN"] . '"' . ($branch["managerSSN"] == $row["employeeSSN"] ? " selected" : "") . '>' . $row["employeeName"] . '</option>');
											}
										}
									print('</select>
								</div>
								<div class="col-4">
									<label>Manager Name</label>
									<select class="form-select" name="assistantmanagerssn" onchange="disableSelect(\'managerssn\')" required>
										<option value="" disabled>Select</option>');
										if($assistantManagers -> rowCount() > 0) {
											while($row = $assistantManagers -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["employeeSSN"] . '"' . ($branch["assistantManagerSSN"] == $row["employeeSSN"] ? " selected" : "") . '>' . $row["employeeName"] . '</option>');
											}
										}
									print('</select>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-3">
									<label>Street</label>
									<input type="text" name="street" class="form-control" placeholder="1 Main St." value="' . $branch["streetNo"] . '" required>
								</div>
								<div class="col-3">
									<label>City</label>
									<input type="text" name="city" class="form-control" placeholder="New Alexandria" value="' . $branch["city"] . '" required>
								</div>
								<div class="col-3">
									<label>State</label>
									<select class="form-select" name="state" required>
										<option value="" disabled>Select</option>
										<option value="AL"' . ($branch["state"] == "AL" ? " selected" : "") . '>Alabama</option>
										<option value="AK"' . ($branch["state"] == "AK" ? " selected" : "") . '>Alaska</option>
										<option value="AZ"' . ($branch["state"] == "AZ" ? " selected" : "") . '>Arizona</option>
										<option value="AR"' . ($branch["state"] == "AR" ? " selected" : "") . '>Arkansas</option>
										<option value="CA"' . ($branch["state"] == "CA" ? " selected" : "") . '>California</option>
										<option value="CO"' . ($branch["state"] == "CO" ? " selected" : "") . '>Colorado</option>
										<option value="CT"' . ($branch["state"] == "CT" ? " selected" : "") . '>Connecticut</option>
										<option value="DE"' . ($branch["state"] == "DE" ? " selected" : "") . '>Delaware</option>
										<option value="DC"' . ($branch["state"] == "DC" ? " selected" : "") . '>District Of Columbia</option>
										<option value="FL"' . ($branch["state"] == "FL" ? " selected" : "") . '>Florida</option>
										<option value="GA"' . ($branch["state"] == "GA" ? " selected" : "") . '>Georgia</option>
										<option value="HI"' . ($branch["state"] == "HI" ? " selected" : "") . '>Hawaii</option>
										<option value="ID"' . ($branch["state"] == "ID" ? " selected" : "") . '>Idaho</option>
										<option value="IL"' . ($branch["state"] == "IL" ? " selected" : "") . '>Illinois</option>
										<option value="IN"' . ($branch["state"] == "IN" ? " selected" : "") . '>Indiana</option>
										<option value="IA"' . ($branch["state"] == "IA" ? " selected" : "") . '>Iowa</option>
										<option value="KS"' . ($branch["state"] == "KS" ? " selected" : "") . '>Kansas</option>
										<option value="KY"' . ($branch["state"] == "KY" ? " selected" : "") . '>Kentucky</option>
										<option value="LA"' . ($branch["state"] == "LA" ? " selected" : "") . '>Louisiana</option>
										<option value="ME"' . ($branch["state"] == "ME" ? " selected" : "") . '>Maine</option>
										<option value="MD"' . ($branch["state"] == "MD" ? " selected" : "") . '>Maryland</option>
										<option value="MA"' . ($branch["state"] == "MA" ? " selected" : "") . '>Massachusetts</option>
										<option value="MI"' . ($branch["state"] == "MI" ? " selected" : "") . '>Michigan</option>
										<option value="MN"' . ($branch["state"] == "MN" ? " selected" : "") . '>Minnesota</option>
										<option value="MS"' . ($branch["state"] == "MS" ? " selected" : "") . '>Mississippi</option>
										<option value="MO"' . ($branch["state"] == "MO" ? " selected" : "") . '>Missouri</option>
										<option value="MT"' . ($branch["state"] == "MT" ? " selected" : "") . '>Montana</option>
										<option value="NE"' . ($branch["state"] == "NE" ? " selected" : "") . '>Nebraska</option>
										<option value="NV"' . ($branch["state"] == "NV" ? " selected" : "") . '>Nevada</option>
										<option value="NH"' . ($branch["state"] == "NH" ? " selected" : "") . '>New Hampshire</option>
										<option value="NJ"' . ($branch["state"] == "NJ" ? " selected" : "") . '>New Jersey</option>
										<option value="NM"' . ($branch["state"] == "NM" ? " selected" : "") . '>New Mexico</option>
										<option value="NY"' . ($branch["state"] == "NY" ? " selected" : "") . '>New York</option>
										<option value="NC"' . ($branch["state"] == "NC" ? " selected" : "") . '>North Carolina</option>
										<option value="ND"' . ($branch["state"] == "ND" ? " selected" : "") . '>North Dakota</option>
										<option value="OH"' . ($branch["state"] == "OH" ? " selected" : "") . '>Ohio</option>
										<option value="OK"' . ($branch["state"] == "OK" ? " selected" : "") . '>Oklahoma</option>
										<option value="OR"' . ($branch["state"] == "OR" ? " selected" : "") . '>Oregon</option>
										<option value="PA"' . ($branch["state"] == "PA" ? " selected" : "") . '>Pennsylvania</option>
										<option value="RI"' . ($branch["state"] == "RI" ? " selected" : "") . '>Rhode Island</option>
										<option value="SC"' . ($branch["state"] == "SC" ? " selected" : "") . '>South Carolina</option>
										<option value="SD"' . ($branch["state"] == "SD" ? " selected" : "") . '>South Dakota</option>
										<option value="TN"' . ($branch["state"] == "TN" ? " selected" : "") . '>Tennessee</option>
										<option value="TX"' . ($branch["state"] == "TX" ? " selected" : "") . '>Texas</option>
										<option value="UT"' . ($branch["state"] == "UT" ? " selected" : "") . '>Utah</option>
										<option value="VT"' . ($branch["state"] == "VT" ? " selected" : "") . '>Vermont</option>
										<option value="VA"' . ($branch["state"] == "VA" ? " selected" : "") . '>Virginia</option>
										<option value="WA"' . ($branch["state"] == "WA" ? " selected" : "") . '>Washington</option>
										<option value="WV"' . ($branch["state"] == "WV" ? " selected" : "") . '>West Virginia</option>
										<option value="WI"' . ($branch["state"] == "WI" ? " selected" : "") . '>Wisconsin</option>
										<option value="WY"' . ($branch["state"] == "WY" ? " selected" : "") . '>Wyoming</option>
									</select>
								</div>
								<div class="col-3">
									<label>Zipcode</label>
									<input type="number" name="zipcode" class="form-control" placeholder="12345" value="' . $branch["zipcode"] . '"required>
								</div>
							</div>
							<hr />
							<div class="row pt-2">
								<div class="col-12">
									<button type="submit" class="btn btn-primary">Save</button>
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