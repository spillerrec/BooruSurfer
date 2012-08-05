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
	require_once "lib/DTTag.php";
	
	
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
	}
	
	
	class DTPost extends DataTable{
		public function id(){ return $this->get( 'id' ); }
		public function parent_id(){ return $this->get( 'parent_id' ); }
		
		//Return if this type is available, NULL if unknown
		public function has_children(){ return $this->get( 'has_children' ); }
		public function has_notes(){ return $this->get( 'has_notes' ); }
		public function has_comments(){ return $this->get( 'has_comments' ); }
		
		
		public function __construct( $prefix, $data = NULL ){
			parent::__construct( $prefix . "_post", $data );
			$this->prefix = $prefix;
		}
		private $prefix;
		
		
		protected function create_data(){
			$this->add( 'id',	true, 'int' );
			$this->add( 'hash',	true );
			$this->add( 'author',	true );
			$this->add( 'creation_date',	true, 'int' );
			
			$this->add( 'parent_id',	true, 'int' );
			$this->add( 'has_children',	false, 'bool' );
			$this->add( 'has_notes',	false, 'bool' );
			$this->add( 'has_comments',	false, 'bool' );
			$this->add( 'source',	false );
			
			$this->add( 'tags',	true );
			$this->add( 'score',	false, 'int' );
			$this->add( 'rating',	false );
			$this->add( 'status',	false );
			
			$this->load_image( true );
			$this->load_image( true, 'thumb_' );
			$this->load_image( false, 'preview_' );
			$this->load_image( false, 'reduced_' );
		}
		
		private function load_image( $required, $prefix="" ){
			$this->add( $prefix . 'url', $required );
			$this->add( $prefix . 'width', $required, 'int' );
			$this->add( $prefix . 'height', $required, 'int' );
			$this->add( $prefix . 'filesize', false, 'int' );
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
		
		
		public function get_tags(){
			//Tags are space separated
			$raw = explode( ' ', $this->get( 'tags' ) );
			$tags = array();
			
			//Convert the tags
			foreach( $raw as $name )
				if( $name ){	//Avoid empty tags
					$tag = new DTTag( $this->prefix );
					$tag->db_read( $name );	//Lookup addional info in database
					$tags[] = $tag;
				}
			
			return $tags;
		}
	}
?>