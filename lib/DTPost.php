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
	
	
	class DTPost extends DataTable{
		public function id(){ return $this->get( 'id' ); }
		public function parent_id(){ return $this->get( 'parent_id' ); }
		
		//Return if this type is available, NULL if unknown
		public function has_children(){ return $this->get( 'has_children' ); }
		public function has_notes(){ return $this->get( 'has_notes' ); }
		public function has_comments(){ return $this->get( 'has_comments' ); }
		public function rating(){ return $this->get( 'rating' ); }
		
		
		public function __construct( $prefix, $data = NULL ){
			parent::__construct( $prefix . "_post", $data );
			$this->prefix = $prefix;
		}
		private $prefix;
		
		const SAFE = 's';
		const QUESTIONABLE = 'q';
		const ADULT = 'e';
		
		protected function create_data(){
			$this->add( 'id',	true, 'int' );
			$this->add( 'hash',	false );
			$this->add( 'author',	false );
			$this->add( 'creation_date',	false, 'int' );
			
			$this->add( 'parent_id',	false, 'int' );
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
			$this->add( $prefix . 'width', false, 'int' );
			$this->add( $prefix . 'height', false, 'int' );
			$this->add( $prefix . 'filesize', false, 'int' );
		}
		
		
		//Construct a name based on the tags
		private $name_cache = NULL;
		public function name(){
			//Get from cache if already contructed
			if( $this->name_cache !== NULL )
				return $this->name_cache;
			
			//Types of tags
			$tags = $this->get_tags();
			$authors = array();
			$copy = array();
			$charac = array();
			$others = array();
			
			//Sepearate tags
			foreach( $tags as $tag ){
				switch( $tag->get_type() ){
					case DTTag::ARTIST:	$authors[$tag->name()]	= $tag->get_count(); break;
					case DTTag::COPYRIGHT:	$copy[$tag->name()]	= $tag->get_count(); break;
					case DTTag::CHARACTER:	$charac[$tag->name()]	= $tag->get_count(); break;
					default:	$others[$tag->name()]	= $tag->get_count(); break;
				}
			}
			
			//Build name
			$text;
			$this->add_to_name( $text, $authors );
			$text .= ' - ';
			$this->add_to_name( $text, $copy );
			$text .= ' - ';
			$this->add_to_name( $text, $charac );
			$text .= ' - ';
			$this->add_to_name( $text, $others );
			
			//Set cache and return
			$this->name_cache = $text;
			return $this->name_cache;
		}
		
		//Converts an array of tags to names
		private function tags_to_name( $tags ){
			$name = "";
			foreach( $tags as $text => $count ){
				$name .= ($name) ? ' ' : '';
				$name .= $text;
			}
			return $name;
		}
		
		//Adding a tag group to $name, while keeping the character count
		//below $limit. Returns false if nothing added, otherwise true
		private function add_to_name( &$name, $tag_group, $limit=128 ){
			//Just skip if it is already too late
			if( mb_strlen( $name ) >= $limit )
				return false;
			
			//Add the whole thing if enough space
			$temp = $this->tags_to_name( $tag_group );
			if( mb_strlen( $name . $temp ) < $limit ){
				$name .= $temp;
				return true;
			}
			
			//Sort the thing
			arsort( $tag_group );
			
			//Remove until there is room
			while( count( $tag_group ) ){
				//Remove
				array_pop( $tag_group );
				$temp = $this->tags_to_name( $tag_group );
				
				//stop if short enough
				if( mb_strlen( $name . $temp ) < $limit ){
					$name .= $temp;
					return true;
				}
			}
			
			//Nothing added
			return false;
		}
		
		private function proxy_url( $url, $type ){
			//Don't do anything for thumbnails
			if( $type == 'thumb_' )
				return $url;
			
			//create postfix for filename
			$post = '.';
			if( $type ){
				$type = rtrim( $type, '_' ); //Avoid the '_'
				$post .= $type . '.';
			}
			
			//Fix the extension
			$ext = pathinfo($url, PATHINFO_EXTENSION);
			$ext = ($ext == 'jpeg') ? 'jpg' : $ext;
			
			$post .= $ext;
			
			//Create prefix
			$pre = $this->prefix . ' ' . $this->id() . ' - ';
			
			//avoid empty type
			$type = ($type == '') ? 'original' : $type;
			
			//Get name and remove throublesome characters
			$name = $this->name();
			//Windows illegal characters, from
			//http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247(v=vs.85).aspx#naming_conventions
			$name = str_replace( '<', '', $name );
			$name = str_replace( '>', '', $name );
			$name = str_replace( ':', '', $name );
			$name = str_replace( '"', '', $name );
			$name = str_replace( '/', '', $name );
			$name = str_replace( '\\', '', $name );
			$name = str_replace( '|', '-', $name ); //Fancy ;)
			$name = str_replace( '?', '', $name );
			$name = str_replace( '*', '', $name );
			//Characters messing up filenames in Opera
			$name = str_replace( ';', '', $name );
			$name = str_replace( '{', '', $name );
			$name = str_replace( '}', '', $name );
			$name = str_replace( '#', '', $name );
			
			//Create full url
			return "/$this->prefix/proxy/$type/$pre" . $name . $post;
		}
		
		//Get all infomation about an image, with a specific prefix
		private function get_to_image( $prefix = "" ){
			//Make sure it is available
			if( $this->get( $prefix . "url" ) === NULL )
				return NULL;
			
			//make a proxy url
			$real_url = $this->get( $prefix . 'url' );
			$url = $this->proxy_url( $real_url, $prefix );
			
			//Create the full object
			return (object)array(
					'url'	=>	$url,
					'width'	=>	$this->get( $prefix . 'width' ),
					'height'	=>	$this->get( $prefix . 'height' ),
					'filesize'	=>	$this->get( $prefix . 'filesize' ),
					'prefix'	=> $prefix,
					'real_url'	=> $real_url
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
		
		
		//Get the tags as DTTag
		//Process is only calculated once per post
		private $tags_cache = NULL;
		public function get_tags(){
			if( $this->tags_cache === NULL ){
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
				
				$this->tags_cache = $tags;
			}
			return $this->tags_cache;
		}
	}
?>