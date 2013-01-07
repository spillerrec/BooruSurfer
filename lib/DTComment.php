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
	
	
	class DTComment extends DataTable{
	//Access
		public function creator(){ return $this->get( 'creator' ); }
		public function created_at(){ return $this->get( 'created_at' ); }
		public function score(){ return $this->get( 'score' ); }
		public function body(){ return $this->get( 'body' ); }
		
		
		public function __construct( $prefix, $data = NULL ){
			parent::__construct( $prefix . "_comments", $data );
			$this->prefix = $prefix;
		}
		private $prefix;
		
		protected function create_data(){
			$this->add( 'id',	true, 'int' );
			$this->add( 'post_id',	true, 'int' );
			
			$this->add( 'creator',	false );
			$this->add( 'created_at',	true );
			$this->add( 'score',	false, 'int' );
			$this->add( 'body',	true );
		}
		
		//Fetch all comments with a specified post id
		public function post( $id ){
			if( !$this->table_created  )
				return array();
			
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name WHERE post_id = " . (int)$id . " ORDER BY created_at" );
			
			//Fetch
			$notes = array();
			foreach( $result as $row )
				$notes[] = new DTComment( $this->prefix, $row );
			
			return $notes;
		}
	}
	
	
	function DTCommentSort( $a, $b ){
		if(  $a->values['created_at'] ==  $b->values['created_at'] )
			return 0 ;
		return ( $a->values['created_at'] < $b->values['created_at'] ) ? -1 : 1;
	}
?>