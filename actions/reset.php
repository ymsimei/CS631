<?php
	include "../session.php";
	include "../credentials.php";
	include "../connection.php";

	$db = dbConn::getConnection();

	$sql = "
		DROP TABLE IF EXISTS account,accountType,adminLogin,branch,branchManager,checkingOverdraftAccount,customer,customerToAccount,employee,employeeDependent,loanAccount,transaction,transactionType;

		CREATE TABLE adminLogin (
			username VARCHAR(100) PRIMARY KEY,
			password VARCHAR(100) NOT NULL
		);

		CREATE TABLE branch (
			branchID INTEGER AUTO_INCREMENT PRIMARY KEY,
			branchName VARCHAR(25) NOT NULL,
			streetNo VARCHAR(25) NOT NULL,
			buildingNo VARCHAR(25) NULL,
			city VARCHAR(25) NOT NULL,
			state VARCHAR(25) NOT NULL,
			zipcode VARCHAR(25) NOT NULL
		);

		CREATE TABLE employee (
			employeeSSN VARCHAR(15) PRIMARY KEY,
			branchID INTEGER,
			firstName VARCHAR(25) NOT NULL,
			lastName VARCHAR(25) NOT NULL,
			phoneNo VARCHAR(25) NOT NULL,
			startDate DATETIME NOT NULL,
				FOREIGN KEY (branchID) REFERENCES branch(branchID) ON DELETE SET NULL
		);

		CREATE TABLE employeeDependent (
			employeeSSN VARCHAR(15),
			firstName VARCHAR(25) NOT NULL,
			lastName VARCHAR(25) NOT NULL,
				PRIMARY KEY (employeeSSN, firstName, lastName),
				FOREIGN KEY (employeeSSN) REFERENCES employee(employeeSSN) ON DELETE CASCADE
		);

		CREATE TABLE branchManager (
			branchID INTEGER PRIMARY KEY,
			managerSSN VARCHAR(15),
			assistantManagerSSN VARCHAR(15),
				FOREIGN KEY (branchID) REFERENCES branch(branchID) ON DELETE CASCADE,
				FOREIGN KEY (managerSSN) REFERENCES employee(employeeSSN) ON DELETE SET NULL,
				FOREIGN KEY (assistantManagerSSN) REFERENCES employee(employeeSSN) ON DELETE SET NULL
		);

		CREATE TABLE customer (
			customerSSN VARCHAR(15) PRIMARY KEY,
			bankerSSN VARCHAR(15),
			firstName VARCHAR(25) NOT NULL,
			lastName VARCHAR(25) NOT NULL,
			phoneNo VARCHAR(25) NOT NULL,
			buildingNo VARCHAR(25) NULL,
			streetNo VARCHAR(25) NOT NULL,
			city VARCHAR(25) NOT NULL,
			state VARCHAR(25) NOT NULL,
			zipcode VARCHAR(25) NOT NULL,
				FOREIGN KEY (bankerSSN) REFERENCES employee(employeeSSN) ON DELETE SET NULL
		);

		CREATE TABLE accountType (
			accountType VARCHAR(25) PRIMARY KEY,
			interestType VARCHAR(25) CHECK (interestType IN ('FIXED', 'VARIABLE') )
		);

		CREATE TABLE account (
			accountID INTEGER AUTO_INCREMENT PRIMARY KEY,
			accountType VARCHAR(25),
			branchID INTEGER,
			interestRate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
			balance DECIMAL(25,2) NOT NULL DEFAULT 0.00,
			lastAccessedDate DATETIME NOT NULL,
				FOREIGN KEY (accountType) REFERENCES accountType(accountType),
				FOREIGN KEY (branchID) REFERENCES branch(branchID)
		);

		CREATE TABLE loanAccount (
			accountID INTEGER PRIMARY KEY,
			monthlyRepayAmount DECIMAL(25,2) NOT NULL DEFAULT 0.00,
				FOREIGN KEY (accountID) REFERENCES account(accountID) ON DELETE CASCADE
		);

		CREATE TABLE checkingOverdraftAccount (
			accountID INTEGER PRIMARY KEY,
			checkingAccountID INTEGER,
			overdraftAccountID INTEGER,
			accountLimit DECIMAL(25,2) NOT NULL DEFAULT 0.00,
			accountCharge DECIMAL(25,2) NOT NULL DEFAULT 0.00,
				FOREIGN KEY (accountID) REFERENCES account(accountID) ON DELETE CASCADE,
				FOREIGN KEY (checkingAccountID) REFERENCES account(accountID) ON DELETE CASCADE,
				FOREIGN KEY (overdraftAccountID) REFERENCES account(accountID),
				CHECK (checkingAccountID <> overdraftAccountID)
		);

		CREATE TABLE customerToAccount (
			customerSSN VARCHAR(15),
			accountID INTEGER,
				PRIMARY KEY (customerSSN, accountID),
				FOREIGN KEY (customerSSN) REFERENCES customer(customerSSN),
				FOREIGN KEY (accountID) REFERENCES account(accountID) ON DELETE CASCADE
		);

		CREATE TABLE transactionType (
			transactionCode VARCHAR(10) PRIMARY KEY,
			transactionName VARCHAR(70) NOT NULL,
			transactionType VARCHAR(10) CHECK (transactionType IN ('DEBIT', 'CREDIT')),
			transactionCharge DECIMAL(5,2) NOT NULL DEFAULT 0.00
		);

		CREATE TABLE transaction (
			transactionID INTEGER AUTO_INCREMENT PRIMARY KEY,
			accountID INTEGER,
			transactionCode VARCHAR(25),
			transactionDate DATETIME NOT NULL,
			transactionAmount DECIMAL(25,2) NOT NULL DEFAULT 0.00,
			transactionNote VARCHAR(100),
			accountBalanceAsOf INTEGER,
				FOREIGN KEY (accountID) REFERENCES account(accountID) ON DELETE CASCADE,
				FOREIGN KEY (transactionCode) REFERENCES transactionType(transactionCode)
		);

		INSERT INTO adminLogin
			(username,password)
		VALUES
			('" . $defaultUsername . "','" . password_hash($defaultPassword, PASSWORD_DEFAULT) . "');

		INSERT INTO branch
			(branchName,streetNo,buildingNo,city,state,zipcode)
		VALUES
			('Uptown','700 S Carrollton Ave',NULL,'New Orleans','LA','70118'),
			('Midcity','3915 Canal St','Suite 225','New Orleans','LA','70119');

		INSERT INTO employee
			(employeeSSN,branchID,firstName,lastName,phoneNo,startDate)
		VALUES
			('314827384',1,'Alvin','Hurley','(372) 736-1442','2020-11-08 19:08:57'),
			('558032165',1,'Elaine','Cain','(175) 526-6780','2020-11-03 10:00:07'),
			('477758051',1,'Hyatt','Mercer','(199) 266-9883','2020-11-29 11:24:21'),
			('777612638',1,'Amos','Gonzalez','(187) 442-2851','2020-11-07 03:56:39'),
			('657865422',1,'Kai','Fisher','(817) 238-5575','2020-11-15 05:23:23'),
			('288611678',2,'Ferris','Bennett','(768) 385-9816','2020-11-06 20:20:07'),
			('083169763',2,'Nell','Wyatt','(431) 822-7481','2020-12-08 11:07:44'),
			('345774238',2,'Vincent','Caldwell','(358) 496-2167','2020-12-01 08:30:41'),
			('273553646',2,'Arsenio','Velazquez','(774) 615-0718','2020-11-23 14:51:23'),
			('127630536',2,'Orli','Saunders','(772) 323-8402','2020-11-10 19:37:27');

		INSERT INTO employeeDependent
			(employeeSSN,firstName,lastName)
		VALUES
			('314827384','Christiana','Hurley'),
			('314827384','Reilly','Hurley'),
			('777612638','Lilli','Gonzalez'),
			('345774238','Myles','Caldwell'),
			('273553646','Geoffrey','Velazquez');

		INSERT INTO branchManager
			(branchID,managerSSN,assistantManagerSSN)
		VALUES
			(1,'314827384','558032165'),
			(2,'288611678','083169763');

		INSERT INTO customer
			(customerSSN,bankerSSN,firstName,lastName,phoneNo,streetNo,city,state,zipcode)
		VALUES
			('126425772','777612638','Nolan','Kerr','(314) 256-3486','771 Curae Road','Cedar Rapids','TN','54411'),
			('357611475','777612638','Quyn','Vinson','(314) 455-8457','1016 Mauris St.','Rockford','MO','20184'),
			('256856411','777612638','Jael','Howe','(816) 356-5523','5951 Interdum Rd.','Kenosha','FL','78103'),
			('545834330','657865422','Lawrence','Stafford','(318) 262-5481','4104 Maecenas St.','Casper','TX','65852'),
			('753578924','477758051','Amos','Alvarado','(513) 835-4296','8160 All Street','Henderson','OK','99770'),
			('731878823','657865422','Otto','Hardy','(243) 183-4588','1366 Odio, Road','Oklahoma City','PA','21236'),
			('689732216','777612638','Shafira','Jimenez','(526) 672-5441','9049 Ultrices Ave','Salt Lake City','WA','25284'),
			('051392436','657865422','Claire','Johns','(627) 482-2911','7112 Sed Av.','Rochester','MD','49615'),
			('692883719','777612638','Aurora','Espinoza','(872) 912-7076','9690 Phasellus Road','Richmond','MN','11848'),
			('667364463','345774238','Ulysses','Washington','(383) 693-7594','4518 Enim. Rd.','Birmingham','NE','61610');

		INSERT INTO accountType
			(accountType,interestType)
		VALUES
			('Savings','FIXED'),
			('Checking','FIXED'),
			('Loan','FIXED'),
			('Money Market','VARIABLE'),
			('Special Charge','FIXED');

		INSERT INTO account
			(accountType,branchID,lastAccessedDate)
		VALUES
			('Checking',1,NOW()),
			('Checking',1,NOW()),
			('Savings',1,NOW()),
			('Loan',1,NOW()),
			('Checking',2,NOW()),
			('Checking',2,NOW()),
			('Savings',2,NOW()),
			('Savings',2,NOW());

		INSERT INTO loanAccount
			(accountID,monthlyRepayAmount)
		VALUES
			(4,2000);

		INSERT INTO customerToAccount
			(customerSSN,accountID)
		VALUES
			('126425772',1),
			('357611475',2),
			('126425772',3),
			('545834330',4),
			('731878823',5),
			('051392436',6),
			('753578924',7),
			('256856411',8);

		INSERT INTO transactionType
			(transactionCode,transactionName,transactionType,transactionCharge)
		VALUES
			('CD','Cash Deposit','CREDIT','0.00'),
			('WD','Cash Withdrawal','DEBIT','0.00'),
			('CQD','Cheque Deposit','CREDIT','1.00'),
			('CQW','Cheque Withdrawal','DEBIT','1.00'),
			('SC','Service Charge Debit','DEBIT','0.00'),
			('MSC','Monthly Service Charge Debit','DEBIT','10.00'),
			('SCCHRG','Service Charge Credit in Charge Account','CREDIT','0.00'),
			('MSCCHRG','Monthly Service Charge Credit in Charge Account','CREDIT','0.00');

		INSERT INTO transaction
			(accountID,transactionCode,transactionDate,transactionAmount,transactionNote,accountBalanceAsOf)
		VALUES
			(1,'CQD',NOW(),500,'Balance Forward',500),
			(2,'CQD',NOW(),500,'Balance Forward',500),
			(5,'CQD',NOW(),500,'Balance Forward',500),
			(6,'CQD',NOW(),500,'Balance Forward',500);

		UPDATE account
		SET balance = 500
		WHERE accountID IN (1,2,5,6);

		UPDATE account
		SET balance = 24000
		WHERE accountID = 4;

	";

	try {
		$result = $db -> prepare($sql);
		$result -> execute();
	} catch(PDOException $e) {
		echo $e -> getMessage();
	}

	include "logout.php";
?>