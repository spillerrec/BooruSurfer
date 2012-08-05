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
	abstract class DataTable{
		private $data = array();
		protected $name; //Of the table
		
		//Call this after the data array have been filled!
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
			$db = Database::get_instance()->db;
			$query = "CREATE TABLE IF NOT EXISTS $this->name ( ";
			
			$query .= $this->sql_columns( true );
			$query .= ", PRIMARY KEY( id ) )";
			
			$db->exec( $query );
			
		}
		public function delete_table(){
			$db = Database::get_instance()->db;
			$db->exec( "DROP TABLE $this->name" );
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
		
		//Retrive the contents with the id = '$id'
		public function db_read( $id ){
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name WHERE id = " . $db->quote( $id ) );
			//Read row, or return false on failure
			return $result ? $this->read_row( $result->fetch( PDO::FETCH_ASSOC ) ) : false;
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