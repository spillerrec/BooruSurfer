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
	
	
	class DTNote extends DataTable{
	//Access
		public function x(){ return $this->get( 'x' ); }
		public function y(){ return $this->get( 'y' ); }
		public function width(){ return $this->get( 'width' ); }
		public function height(){ return $this->get( 'height' ); }
		public function body(){ return $this->get( 'body' ); }
		
		public function is_active(){ return $this->get( 'active' ); }
		
		
		public function __construct( $prefix, $data = NULL ){
			parent::__construct( $prefix . "_notes", $data );
			$this->prefix = $prefix;
		}
		private $prefix;
		
		protected function create_data(){
			$this->add( 'id',	true, 'int' );
			$this->add( 'post_id',	true, 'int' );
			
			$this->add( 'x',	true, 'int' );
			$this->add( 'y',	true, 'int' );
			$this->add( 'width',	true, 'int' );
			$this->add( 'height',	true, 'int' );
			$this->add( 'body',	true );
			
			$this->add( 'active',	false, 'bool' );
			$this->add( 'created_at',	false, 'int' );
			$this->add( 'updated_at',	false, 'int' );
			$this->add( 'version',	false, 'int' );
		}
		
		//Fetch all notes with a specified post id
		public function post( $id ){
			$db = Database::get_instance()->db;
			$result = $db->query( "SELECT * FROM $this->name WHERE post_id = " . (int)$id );
			
			//Fetch
			$notes = array();
			foreach( $result as $row )
				$notes[] = new DTNote( $this->prefix, $row );
			
			return $notes;
		}
	}
?>