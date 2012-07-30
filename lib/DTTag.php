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
		const UNKNOWN = 5;
		
		
		public function __construct( $prefix, $data = NULL ){
			$this->add( $data, 'id',	true );
			$this->add( $data, 'count',	true, 'int' );
			$this->add( $data, 'type',	true, 'int' );
			
			parent::__construct( $prefix . "_tags" );
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
	}
?>