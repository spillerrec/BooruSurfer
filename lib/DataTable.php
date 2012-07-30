<?php
	require_once "lib/Database.php";
	
	/*	Provides a data array which can be stored in a database table
	**	
	*/	
	class DataTable{
		private $data = array();
		private $name; //Of the table
		
		//Call this after the data array have been filled!
		public function __construct( $table_name ){
			$this->name = $table_name;
			
			$db = Database::get_instance();
			if( !$db->table_exists( $this->name ) )
				$this->create_table();
		}
		
	//Data array functions
		//Add data to the data-table
		//$required specifies if the lack of this parameter should be treated as an error
		//$type will cause the variable to be casted to this type
		protected function add( $arr, $property, $required, $type = "string" ){
			$content = NULL;
			//Check if the data is available
			if( isset( $arr[ $property ] ) ){
				//Add the contents
				$content = $arr[ $property ];
				if( $type == 'bool' )
					//Casting with bool doesn't convert (string)"true" and similar as intended
					$content = filter_var( $content, FILTER_VALIDATE_BOOLEAN );
				else
					settype( $content, $type );
			}
			else
				if( $required && $arr !== NULL ){ //Shall this be treated as a fatal error?
					echo "Missing required property: $property !";
					die;
				}
			
			//Add the data
			$this->data[ $property ] = (object) array(
					'value' => $content,
					'modified' => false,
					'original' => NULL
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
		
		public function sql_columns(){
			$query = "";
			foreach( $this->data as $key => $value )
				$query .= "$key, ";
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
			
			$first = true;
			foreach( $this->data as $prop => $settings ){
				$query .= $prop . ", ";
			}
			$query .= "PRIMARY KEY( id ) )";
			
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
		
		//Retrive the contents with the id = '$id'
		public function db_read( $id ){
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name WHERE id = " . $db->quote( $id ) );
			if( $result ){
				$row = $result->fetch( PDO::FETCH_ASSOC );
				if( $row ){
					foreach( $this->data as $prop => $settings ){
						$settings->value = $row[ $prop ];
						$settings->modified = false;
					}
					return true;
				}
			}
			
			return false; //Post not loaded
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