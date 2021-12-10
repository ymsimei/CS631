<?php
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	if ($_SESSION["loggedIn"] != "1" && $_SERVER['REQUEST_URI'] != "/~afm36/CS631/") {
		header("Location: /~afm36/CS631/");
	}
?>