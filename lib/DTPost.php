<?php
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
			$this->prefix = $prefix;
		}
		private $prefix;
		
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