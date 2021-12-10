<?php include "session.php" ?>

<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
	</head>
	<body>
		<?php
			if ($_SESSION["loggedIn"] == "1") {
				print('<div class="container py-5 text-center">
					<h1 class="fw-light">Welcome '. $_SESSION["FirstName"] . '</h1>
					<a href="actions/logout.php">Logout</a>
				</div>
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-4">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-cash-coin fs-1"></i>
									<h5 class="card-title">Transaction Application</h5>
									<p class="card-text">Create, delete, and modify transactions.</p>
									<a href="transaction" class="btn btn-primary">Go to App</a>
								</div>
							</div>
						</div>
						<div class="col-4">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-journals fs-1"></i>
									<h5 class="card-title">Passbook Application</h5>
									<p class="card-text">Display passbooks for customers.</p>
									<a href="passbook" class="btn btn-primary">Go to App</a>
								</div>
							</div>
						</div>
						<div class="col-4">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-person-lines-fill fs-1"></i>
									<h5 class="card-title">Customer Application</h5>
									<p class="card-text">Create, delete, and modify customers.</p>
									<a href="customer" class="btn btn-primary">Go to App</a>
								</div>
							</div>
						</div>
					</div>
				</div>');
			} else {
				print('<div class="container py-5">
					<div class="row">
						<div class="col-8 offset-2">
							<div class="login-form bg-light mt-4 p-4">
								<form name="data" action="actions/login.php" method="post" accept-charset="uft-8" enctype="multipart/form-data" class="row g-3">
									<h4>CS631 Bank Login</h4>
									<div class="col-12">
										<label>Social Security Number</label>
										<input type="text" name="ssn" class="form-control" placeholder="SSN" maxlength="9" required>
									</div>
									<div class="col-12">
										<label>Password</label>
										<input type="password" name="password" class="form-control" placeholder="Password" maxlength="100" required>
									</div>
									<div class="col-12">
										<button type="submit" class="btn btn-dark float-end">Login</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>');
			}
		?>
	</body>
</html>