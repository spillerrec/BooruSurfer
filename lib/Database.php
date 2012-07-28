<?php
	
	//Generic name ftw
	class Database{
		private static $instance = NULL;
		public $db;
		
		private function __construct(){
			$this->db = new PDO('sqlite:cache/db.sqlite' );
		}
		public static function get_instance(){
			if( !Database::$instance )
				Database::$instance = new Database();
			return Database::$instance;
		}
		
		public function table_exists( $name ){
			$stmt = $this->db->query( "SELECT * FROM sqlite_master WHERE name = " . $this->db->quote( $name ) );
			return $stmt !== false && $stmt->fetch( PDO::FETCH_ASSOC );
		}
	}
?>