<?php
	require_once "lib/DataTable.php";
	
	
	class DTTag extends DataTable{
		public $real_count = NULL;
		
	//Access
		public function name(){ return $this->get( 'id' ); }
		public function get_count(){ return $this->get( 'count' ); }
		public function get_ratio(){
			return $this->real_count !== NULL ? $this->real_count / $this->get_count() : NULL;
		}
		public function get_type(){ return $this->get( 'type' ); }
		
	//Type
		const NONE = 0;
		const ARTIST = 1;
		const COPYRIGHT = 2;
		const CHARACTER = 3;
		const SPECIAL = 4;
		const COMPANY = 5;
		const UNKNOWN = 255;
		
		
		public function __construct( $prefix, $data = NULL ){
			parent::__construct( $prefix . "_tags", $data );
			$this->prefix = $prefix;
		}
		private $prefix;
		
		protected function create_data(){
			$this->add( 'id',	true );
			$this->add( 'count',	false, 'int' );
			$this->add( 'type',	false, 'int' );
			$this->add( 'ambiguous',	false, 'bool' );
		}
		
		
		//Even if the read fails, we want to set the id
		public function db_read( $id ){
			if( !parent::db_read( $id ) ){
				$this->set( 'id', $id );
				return false;
			}
			else
				return true;
		}
		
		public function most_used(){
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name ORDER BY count DESC LIMIT 30" );
			
			//Fetch
			$tags = array();
			foreach( $result as $row )
				$tags[] = new DTTag( $this->prefix, $row );
			
			return $tags;
		}
	}
?>