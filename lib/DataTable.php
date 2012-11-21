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
	
	require_once "lib/Database.php";
	
	/*	Provides a data array which can be stored in a database table
	**	
	*/
	abstract class DataTable implements JsonSerializable{
		protected $name; //Of the table
		
		public $values = array();
		private $type = array();
		private $required = array();
		
		private static $values_cache = array();
		private static $type_cache = array();
		private static $required_cache = array();
		//This constructor MUST be called in subclasses
		public function __construct( $table_name, $data ){
			$this->name = $table_name;
			
			//Create data and cache
			//* 
			if( isset( DataTable::$values_cache[ $table_name ] ) ){
				$this->values = DataTable::$values_cache[ $table_name ];
				$this->type = DataTable::$type_cache[ $table_name ];
				$this->required = DataTable::$required_cache[ $table_name ];
			}
			else{
				$this->create_data();
				DataTable::$values_cache[ $table_name ] = $this->values;
				DataTable::$type_cache[ $table_name ] = $this->type;
				DataTable::$required_cache[ $table_name ] = $this->required;
			}
			/*/
			$this->create_data();//*/
			if( $data )
				array_walk( $this->type, array( $this, 'read_row_internal' ), $data );
			//$this->read_row( $data );
			
			$db = Database::get_instance();
			if( !$db->table_exists( $this->name ) )
				$this->create_table();
		}
		
		abstract protected function create_data();
		
	//Data array functions
		
		//Returns whether a row is loaded or not
		public function is_loaded(){
			return $this->get !== NULL;
		}
		
		//Die and emit an error message if not loaded
		public function must_be_loaded(){
			if( !$this->is_loaded() ){
				echo "$this->name has not a row loaded and cannot be processed.";
				die();
			}
		}
		
		//Add an entry to the data-table
		//$required specifies if the lack of this parameter should be treated as an error
		//$type will cause the variable to be casted to this type
		protected function add( $property, $required, $type = "string" ){
			//Only add if it does not exist already
			if( !isset( $this->values[ $property ] ) ){
				$this->values[ $property ] = null;
				$this->required[ $property ] = $required;
				$this->type[ $property ] = $type;
			}
			else
				die( "$property already exist in DataTable." );
		}
		public function get( $property ){
			return $this->values[ $property ];
		}
		protected function set( $property, $value ){
			$this->values[ $property ] = $value;
		}
		
		//Export as JSON, but don't show internal variables
		public function jsonSerialize(){
			$raw = array();
			foreach( $this->values as $key => $value )
				$raw[$key] = $value->value;
			return $raw;
		}
		
		public function prepare_array(){
			$result = array();
			reset( $this->values );
			while( list( $key, $val ) = each( $this->values ) )
				$result[ ":$key" ] = $val;
			return $result;
		}
		
		public function sql_columns( $type = false ){
			$query = "";
			reset( $this->type );
			foreach( $this->values as $key => $value ){
				$query .= "$key";
				if( $type ){
					switch( current( $this->type ) ){
						case 'bool':
						case 'int': $query .= " INT"; break;
						case 'string': $query .= " TEXT"; break;
					}
					next( $this->type );
				}
				$query .= ", ";
			}
			return rtrim( $query, ", " );
		}
		public function pdo_values(){
			$query = "";
			//TODO: use that array function instead!
			foreach( $this->values as $key => $value )
				$query .= ":$key, ";
			return rtrim( $query, ", " );
		}
		
		
	//Database functions
		public function create_table(){
			$db = Database::get_instance();
			$query = "CREATE TABLE IF NOT EXISTS $this->name ( ";
			
			$query .= $this->sql_columns( true );
			$query .= ", PRIMARY KEY( id ) )";
			
			$db->db->exec( $query );
			$db->table_created( $this->name );
		}
		public function delete_table(){
			$db = Database::get_instance()->db;
			$db->exec( "DROP TABLE $this->name" );
		}
		
		public function delete_contents(){
			$db = Database::get_instance()->db;
			$db->exec( "DELETE FROM $this->name" );
		}
		
		//Check if the data with the id '$id' is already stored
		//TODO: return insertion time?
		public function db_contains( $id ){
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name WHERE id = " . $db->quote( $id ) );
			if( $result->fetch( PDO::FETCH_ASSOC ) ){
				//TODO: insertion time?
				return true;
			}
			return false;
		}
		
		protected function read_row_internal( $type, $prop, $input ){
			//$prop = $key[$i];
			$obj = NULL;
			//Check if the data is available
			if( isset( $input[ $prop ] ) ){
				//Add the contents
				$obj = $input[ $prop ];
				if( $type === 'bool' )
					//Casting with bool doesn't convert (string)"true" and similar as intended
					$obj = filter_var( $obj, FILTER_VALIDATE_BOOLEAN );
				else
					settype( $obj, $type );
			}
			//else
				//if( $required ){ //Shall this be treated as a fatal error?
				//	echo "Missing required property: $prop !";
				//	die;
				//}
			
			//Fill data
			$this->values[ $prop ] = $obj;
		//	$required = next( $this->required );
		}
		
		protected final function read_row( $row ){
			if( !$row )
				return false;
			
			array_walk( $this->type, array( $this, 'read_row_internal' ), $row );
			/*/
			//$key = array_keys($this->values);
			//$size = sizeOf($key);
			$type = reset( $this->type );
		//	$required = reset( $this->required );
			//for ($i=0; $i<$size; $i++){// $this->values[$key[$i]] .= "a";
			//foreach( $this->values as $prop => $obj ){
			foreach( array_keys( $this->values) as $prop ){
				//$prop = $key[$i];
				$obj = NULL;
				//Check if the data is available
				if( isset( $row[ $prop ] ) ){
					//Add the contents
					$obj = $row[ $prop ];
					if( $type === 'bool' )
						//Casting with bool doesn't convert (string)"true" and similar as intended
						$obj = filter_var( $obj, FILTER_VALIDATE_BOOLEAN );
					else
						settype( $obj, $type );
				}
				//else
					//if( $required ){ //Shall this be treated as a fatal error?
					//	echo "Missing required property: $prop !";
					//	die;
					//}
				
				//Fill data
				$this->values[ $prop ] = $obj;
				$type = next( $this->type );
			//	$required = next( $this->required );
			}
				//*/
			return true;
		}
		
		private static $read_prepares = array();
		//Retrive the contents with the id = '$id'
		public function db_read( $id ){
			$stmt = NULL;
			if( isset( DataTable::$read_prepares[ $this->name ] ) )
				$stmt = DataTable::$read_prepares[ $this->name ];
			else{
				//Create query and add to cache
				$db = Database::get_instance()->db;
				$stmt = $db->prepare( "SELECT * FROM $this->name WHERE id = :id LIMIT 1" );
				DataTable::$read_prepares[ $this->name ] = $stmt;
			}
			
			//Read row, or return false on failure
			//return $stmt->execute( array( 'id' => $id ) ) ? $this->read_row( $stmt->fetch( PDO::FETCH_ASSOC ) ) : false;
			if( $stmt->execute( array( 'id' => $id ) ) ){
				$row = $stmt->fetch( PDO::FETCH_ASSOC );
				if( $row ){
					array_walk( $this->type, array( $this, 'read_row_internal' ), $row );
					return true;
				}
			}
			
			return false;
		}
		
		//Save the contents in the database
		public function db_save( $overwrite = true ){
			$con = Database::get_instance();
			$db = $con->db;
			
			//If overwriting is turned off, exit if row exists
			if( !$overwrite ){
				$stmt = $db->query( "SELECT * FROM $this->name WHERE id = " . $db->quote( $this->get( 'id' ) ) );
				if( $con->has_rows( $stmt ) )
					return;
			}
			
			//Insert or overwrite data
			$columns = $this->sql_columns();
			$stmt = $db->prepare( "INSERT OR REPLACE INTO $this->name ( $columns ) VALUES ("
				.	$this->pdo_values() . ")"
				);
			$stmt->execute( $this->prepare_array() );
		}
	}
?>