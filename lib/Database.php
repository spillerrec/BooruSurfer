<?php
	/*	This file is part of BooruSurfer.

		BooruSurfer is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version.

		BooruSurfer is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with BooruSurfer.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	
	//Generic name ftw
	class Database{
		private static $instance = NULL;
		public $db;
		
		private function __construct(){
			$this->db = new PDO('sqlite:cache/db.sqlite' );
			$this->db->exec( "PRAGMA foreign_keys = ON" );
		}
		public static function get_instance(){
			if( !Database::$instance )
				Database::$instance = new Database();
			return Database::$instance;
		}
		
		public function table_exists( $name ){
			$stmt = $this->db->query( "SELECT * FROM sqlite_master WHERE name = " . $this->db->quote( $name ) );
			return $this->has_rows( $stmt );
		}
		
		//Checks if a query returned any rows
		public function has_rows( $stmt ){
			return $stmt !== false && $stmt->fetch( PDO::FETCH_ASSOC );
		}
	}
?>