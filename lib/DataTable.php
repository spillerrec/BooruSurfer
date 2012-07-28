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
		public function db_save(){
			$db = Database::get_instance()->db;
			
			$columns = $this->sql_columns();
			$stmt = $db->prepare( "INSERT OR REPLACE INTO $this->name ( $columns ) VALUES ("
				.	$this->pdo_values() . ")"
				);
			$stmt->execute( $this->prepare_array() );
		}
	}
	
	
	
	class Image{
		public $url;
		public $width;
		public $height;
		public $filesize;
		
		function __construct( $path, $w, $h, $size ){
			$this->url = $path;
			$this->width = $w;
			$this->height = $h;
			$this->filesize = $size;
		}
		
		public function to_html( $alt = NULL ){
			if( pathinfo( $this->url, PATHINFO_EXTENSION ) == "swf" ){
				return new htmlObject( 'object', " ", array( 'type'=>'application/x-shockwave-flash', 'data'=>$this->url, 'width'=>$this->width, 'height'=>$this->height ) );
			}
			else{
				return new htmlImage( $this->url, $alt );
			}
		}
	}
	
	
	class DTPost extends DataTable{
		public function id(){ return $this->get( 'id' ); }
		public function parent_id(){ return $this->get( 'parent_id' ); }
		
		//Return if this type is available, NULL if unknown
		public function has_children(){ return $this->get( 'has_children' ); }
		public function has_notes(){ return $this->get( 'has_notes' ); }
		public function has_comments(){ return $this->get( 'has_comments' ); }
		
		
		public function __construct( $prefix, $data = NULL ){
			$this->add( $data, 'id',	true, 'int' );
			$this->add( $data, 'hash',	true );
			$this->add( $data, 'author',	true );
			$this->add( $data, 'creation_date',	true, 'int' );
			
			$this->add( $data, 'parent_id',	true, 'int' );
			$this->add( $data, 'has_children',	false, 'bool' );
			$this->add( $data, 'has_notes',	false, 'bool' );
			$this->add( $data, 'has_comments',	false, 'bool' );
			$this->add( $data, 'source',	false );
			
			$this->add( $data, 'tags',	true );
			$this->add( $data, 'score',	false, 'int' );
			$this->add( $data, 'rating',	false );
			$this->add( $data, 'status',	false );
			
			$this->load_image( $data, true );
			$this->load_image( $data, true, 'thumb_' );
			$this->load_image( $data, false, 'preview_' );
			$this->load_image( $data, false, 'reduced_' );
			
			parent::__construct( $prefix . "_post" );
		}
		
		private function load_image( $data, $required, $prefix="" ){
			$this->add( $data, $prefix . 'url', $required );
			$this->add( $data, $prefix . 'width', $required, 'int' );
			$this->add( $data, $prefix . 'height', $required, 'int' );
			$this->add( $data, $prefix . 'filesize', false, 'int' );
		}
		
		private function get_to_image( $prefix = "" ){
			//Make sure it is available
			if( $this->get( $prefix . "url" ) === NULL )
				return NULL;
			
			return new Image(
					$this->get( $prefix . 'url' ),
					$this->get( $prefix . 'width' ),
					$this->get( $prefix . 'height' ),
					$this->get( $prefix . 'filesize' )
				);
		}
		//TODO: Check for a certain type
		public function get_image( $type = NULL ){
			switch( $type ){
				case 'thumb': return $this->get_to_image( "thumb_" );
				
				case 'preview':
						$img = $this->get_to_image( "preview_" );
						if( $img !== NULL )
							return $img;
						//otherwise, fall through to 'reduced'
					
				case 'reduced':
						$img = $this->get_to_image( "reduced_" );
						if( $img !== NULL )
							return $img;
						//otherwise, fall through to NULL
					
				case NULL:	return $this->get_to_image();
				default:	return NULL;
			}
		}
	}
?>