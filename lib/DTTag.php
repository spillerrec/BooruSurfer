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
		
	//Tag cache
		private static $cached_table = NULL;
		private static $cached_tags;
		public static function get_tag( $prefix, $id ){
			//Empty cache if new table is queried
			if( $prefix != DTTag::$cached_table ){
				DTTag::$cached_table = $prefix;
				DTTag::$cached_tags = array();
			}
			
			if( isset( DTTag::$cached_tags[ $id ] ) )
				return DTTag::$cached_tags[ $id ];
			else{
				//Create new one and add it
				$temp = new DTTag( $prefix );
				$temp->db_read( $id );
				return (DTTag::$cached_tags[ $id ] = $temp);
			}
		}
		
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
				$this->values['id'] = $id;
				return false;
			}
			else
				return true;
		}
		
		public function most_used(){
			if( !$this->table_created  )
				return array();
			
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name ORDER BY count DESC LIMIT 30" );
			
			//Fetch
			$tags = array();
			foreach( $result as $row )
				$tags[] = new DTTag( $this->prefix, $row );
			
			return $tags;
		}
		
		public function similar_tags( $search ){
			if( !$this->table_created  )
				return array();
			
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name WHERE id LIKE " . $db->quote('%'.$search.'%') . " ORDER BY count DESC LIMIT 10" );
			
			//Fetch
			$tags = array();
			foreach( $result as $row )
				$tags[] = new DTTag( $this->prefix, $row );
			
			return $tags;
		}
	}
?>