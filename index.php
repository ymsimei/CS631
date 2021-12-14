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
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item active">Dashboard</li>
						</ol>
					</nav>
					<h1 class="fw-light">Welcome '. $_SESSION["username"] . '</h1>
					<a href="actions/logout.php">Logout</a>
					<a href="actions/reset.php">Reset</a>
				</div>
				<div class="container">
					<div class="row g-2">
						<div class="col-6">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-tree-fill fs-1"></i>
									<h5 class="card-title">Branches</h5>
									<p class="card-text">Create, modify, and delete branches.</p>
									<a href="branches" class="btn btn-primary">View Branches</a>
								</div>
							</div>
						</div>
						<div class="col-6">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-person-badge-fill fs-1"></i>
									<h5 class="card-title">Employees</h5>
									<p class="card-text">Create, modify, and delete employees.</p>
									<a href="employees" class="btn btn-primary">View Employees</a>
								</div>
							</div>
						</div>
					</div>
					<div class="row g-2 py-2">
						<div class="col-4">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-people-fill fs-1"></i>
									<h5 class="card-title">Customers</h5>
									<p class="card-text">Create, modify, and delete customers.</p>
									<a href="customers" class="btn btn-primary">View Customers</a>
								</div>
							</div>
						</div>
						<div class="col-4">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-piggy-bank-fill fs-1"></i>
									<h5 class="card-title">Accounts</h5>
									<p class="card-text">Create, modify, and delete accounts.</p>
									<a href="accounts" class="btn btn-primary">View Accounts</a>
								</div>
							</div>
						</div>
						<div class="col-4">
							<div class="card text-center">
								<div class="card-body">
									<i class="bi bi-cash-coin fs-1"></i>
									<h5 class="card-title">Transactions</h5>
									<p class="card-text">Create, modify, and delete transactions.</p>
									<a href="transactions" class="btn btn-primary">View Transactions</a>
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
										<label>Username</label>
										<input type="text" name="username" class="form-control" placeholder="Username" maxlength="9" required>
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