<?php
	class dbConn {

		protected static $db;

		private function __construct() {
			include "credentials.php";

			try {
				self::$db = new PDO("mysql:host=$host;dbname=$database", $username, $password );
				self::$db -> setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			} catch (PDOException $e) {
				echo "Connection Error: " . $e -> getMessage();
			}
		}

		public static function getConnection() {
			if (!self::$db) {
				new dbConn();
			}

			return self::$db;
		}
	}
?>