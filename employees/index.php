<?php
	include "../session.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	try {
		$employees = $db -> prepare(
			"SELECT
				employee.employeeSSN,
				employee.firstName,
				employee.lastName,
				CONCAT(employee.firstName, ' ', employee.lastName) AS employeeName,
				employee.phoneNo,
				employee.startDate,
				branch.branchID,
				branch.branchName,
				COUNT(employeeDependent.employeeSSN) AS numEmployeeDependents
			FROM employee
				LEFT JOIN employeeDependent ON employeeDependent.employeeSSN = employee.employeeSSN
				LEFT JOIN branch ON branch.branchID = employee.branchID"
			. ((isSet($_GET["employeessn"]) && !empty($_GET["employeessn"])) ? (" WHERE employee.employeeSSN = '" . $_GET["employeessn"] . "' ") : " ") .
			"GROUP BY employee.employeeSSN,employee.firstName,employee.lastName,employee.phoneNo,employee.startDate,branch.branchID,branch.branchName"
		);
		$employees -> execute();

		if(isSet($_GET["employeessn"])) {
			$branches = $db -> prepare(
				"SELECT
					branch.branchID,
					branch.branchName
				FROM branch"
			);
			$branches -> execute();
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
				if(!isSet($_GET["employeessn"])) {
					print('<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
							<li class="breadcrumb-item active">Employees</li>
						</ol>
					</nav>
					<h1>Bank Employees</h1>
						<table class="table table-striped text-center align-middle">
							<thead>
								<tr>
									<th>Social Security Number</th>
									<th>Name</th>
									<th>Branch</th>
									<th>Phone</th>
									<th>Start Date</th>
									<th>Dependents</th>
								</tr>
							</thead>
						<tbody>');
					if($employees -> rowCount() > 0) {
						while($row = $employees -> fetch(PDO::FETCH_ASSOC)) {
							print('<tr>
								<td>' . $row["employeeSSN"] . '</td>
								<td>' . $row["employeeName"] . '</td>
								<td>' . $row["branchName"] . '</td>
								<td>' . $row["phoneNo"] . '</td>
								<td>' . date('F d, o', strtotime($row["startDate"])) . '</td>
								<td>' . $row["numEmployeeDependents"] . '</td>
								<td><a href="?employeessn=' . $row["employeeSSN"] . '" class="btn btn-primary"><i class="bi bi-pencil-square"></i></a></td>
								<td>
									<form name="data" action="../actions/employee.php" method="post" onsubmit="return confirm(\'Do you really want to delete ' . $row["employeeName"] . '?\')">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="employeessn" value="' . $row["employeeSSN"] . '">
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
									<a href="?employeessn="><button class="btn btn-success btn-sm">Create a new employee</button></a>
								</td>
							</tr>
						</tfoot>
					</table>');
				} else {
					if(empty($_GET["employeessn"])) {
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="./">Employees</a></li>
								<li class="breadcrumb-item active">Create Employee</li>
							</ol>
						</nav>
						<h1>Create a New Employee</h1>
						<form name="data" action="../actions/employee.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="post">
							<div class="row">
								<div class="col-4">
									<label>Social Security Number</label>
									<input type="text" name="employeessn" class="form-control" maxlength="25" placeholder="XXX-XX-XXXX" required>
								</div>
								<div class="col-4">
									<label>First Name</label>
									<input type="text" name="firstname" class="form-control" maxlength="25" placeholder="John" required>
								</div>
								<div class="col-4">
									<label>Last Name</label>
									<input type="text" name="lastname" class="form-control" maxlength="25" placeholder="Smith" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-4">
									<label>Branch</label>
									<select class="form-select" name="branchid" required>
										<option value="" disabled selected>Select</option>');
										if($branches -> rowCount() > 0) {
											while($row = $branches -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["branchID"] . '">' . $row["branchName"] . '</option>');
											}
										}
									print('</select>
								</div>
								<div class="col-3">
									<label>Phone</label>
									<input type="text" name="phone" class="form-control" placeholder="(123) 456 - 7890" required>
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
						$employee = $employees -> fetch(PDO::FETCH_ASSOC);
						print('<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="../">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="./">Employees</a></li>
								<li class="breadcrumb-item active">Modify Employee</li>
							</ol>
						</nav>
						<h1>Modify an Existing Branch</h1>
						<form name="data" action="../actions/branch.php" method="post" accept-charset="uft-8" enctype="multipart/form-data">
							<input type="hidden" name="action" value="put">
							<div class="row">
								<div class="col-4">
									<label>Social Security Number</label>
									<input type="text" name="employeessn" class="form-control" maxlength="25" placeholder="XXX-XX-XXXX" value="' . $employee["employeeSSN"] . '" readonly>
								</div>
								<div class="col-4">
									<label>First Name</label>
									<input type="text" name="firstname" class="form-control" maxlength="25" placeholder="John" value="' . $employee["firstName"] . '" required>
								</div>
								<div class="col-4">
									<label>Last Name</label>
									<input type="text" name="lastname" class="form-control" maxlength="25" placeholder="Smith" value="' . $employee["lastName"] . '" required>
								</div>
							</div>
							<div class="row pt-2">
								<div class="col-4">
									<label>Branch</label>
									<select class="form-select" name="branchid" required>
										<option value="" disabled selected>Select</option>');
										if($branches -> rowCount() > 0) {
											while($row = $branches -> fetch(PDO::FETCH_ASSOC)) {
												print('<option value="' . $row["branchID"] . '"' . ($employee["branchID"] == $row["branchID"] ? " selected" : "") . '>' . $row["branchName"] . '</option>');
											}
										}
									print('</select>
								</div>
								<div class="col-3">
									<label>Phone</label>
									<input type="text" name="phone" class="form-control" placeholder="(123) 456 - 7890" value="' . $employee["phoneNo"] . '" required>
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