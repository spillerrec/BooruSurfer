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
		private $data = array();
		protected $name; //Of the table
		
		//This constructor MUST be called in subclasses
		public function __construct( $table_name, $data ){
			$this->name = $table_name;
			
			$this->create_data();
			$this->read_row( $data );
			
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
			//Add the data
			$this->data[ $property ] = (object) array(
					'value' => NULL,
					'modified' => false,
					'original' => NULL,
					'required' => $required,
					'type' => $type
				);
		}
		public function get( $property ){
			return $this->data[ $property ]->value;
		}
		protected function set( $property, $value ){
			$data = $this->data[ $property ];
			
			//Save the original content if first edit
			if( !$data->modified ){
				$data->original = $data->value;
				$data->modified = true;
			}
			
			//Store the data
			$data->value = $value;
			$this->data[ $property ] = $data;
		}
		
		//Export as JSON, but don't show internal variables
		public function jsonSerialize(){
			$raw = array();
			foreach( $this->data as $key => $value )
				$raw[$key] = $value->value;
			return $raw;
		}
		
		public function prepare_array(){
			$result = array();
			foreach( $this->data as $key => $value )
				$result[ ":$key" ] = $value->value;
			return $result;
		}
		
		public function sql_columns( $type = false ){
			$query = "";
			foreach( $this->data as $key => $value ){
				$query .= "$key";
				if( $type ){
					switch( $value->type ){
						case 'bool':
						case 'int': $query .= " INT"; break;
						case 'string': $query .= " TEXT"; break;
					}
				}
				$query .= ", ";
			}
			return rtrim( $query, ", " );
		}
		public function pdo_values(){
			$query = "";
			foreach( $this->data as $key => $value )
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
		
		protected final function read_row( $row ){
			if( $row ){
				foreach( $this->data as $prop => $obj ){
					
					$content = NULL;
					//Check if the data is available
					if( isset( $row[ $prop ] ) ){
						//Add the contents
						$content = $row[ $prop ];
						if( $obj->type == 'bool' )
							//Casting with bool doesn't convert (string)"true" and similar as intended
							$content = filter_var( $content, FILTER_VALIDATE_BOOLEAN );
						else
							settype( $content, $obj->type );
					}
					else
						if( $obj->required ){ //Shall this be treated as a fatal error?
							echo "Missing required property: $prop !";
							die;
						}
					
					//Fill data
					$obj->value = $content;
					$obj->modified = false;
				}
				return true;
			}
			return false;
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
			return $stmt->execute( array( 'id' => $id ) ) ? $this->read_row( $stmt->fetch( PDO::FETCH_ASSOC ) ) : false;
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