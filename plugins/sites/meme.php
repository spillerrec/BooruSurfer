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
	
	require_once "lib/Api.php";
	
	class ShimmieRssApi extends Api{
	//Support
		public function supports_post_limit(){ return -$this->amount; }
		public function supports_post_count(){ return false; }
		public function supports_all_tags(){ return false; }
		public function supports_related_tags(){ return false; }
		public function supports_login(){ return false; }
		public function thumbnail_size(){ return 192; }
		
	//Site
		private $name;
		protected $url;
		private $code;
		private $amount; //The amount of post fetched in each call
		public function get_name(){ return $this->name; }
		public function get_code(){ return $this->code; }
		public function get_refferer(){ return $this->url; }
		
		public function __construct( $name, $code, $url, $amount ){
			$this->name = $name;
			$this->code = $code;
			$this->url = $url;
			$this->amount = $amount;
		}
		
		
	//Index fetching and parsing functions
		
		//Calculate URL
		protected function get_url( $search, $page=1 ){
			if( $search )
				return $this->url . "rss/images/$search/$page";
			else
				return $this->url . "rss/images/$page";
		}
		
		//Fix url if missing base url
		protected function transform_url( &$url ){
			if( $url ){
				if( $url[0] == '/' )
					$url = rtrim( $this->url, '/' ) . $url;
				$url = str_replace( ' ', '%20', $url );
			}
		}
		
		//Finds the first substr which starts with
		//$start and ends with $end
		protected function find( $s, $start, $end ){
			//Find starting string
			$pos1 = strpos( $s, $start );
			if( $pos1 !== false ){
				//It exists, remove start from array
				$s = substr( $s, $pos1 + strlen( $start ) );
				
				//Find the ending
				$pos2 = strpos( $s, $end );
				if( $pos2 !== false ){
					//Cut the ending
					return substr( $s, 0, $pos2 );
				}
			}
			
			//Couldn't find anything
			return NULL;
		}
		
		//Some sites have broken XML, override this to add stuff
		protected function fix_xml( &$data ){
			//Fix XML where XML declaration is not
			//at the beginning of the file
			$data = ltrim( $data );
		}
		
		//Parse image links in a post
		protected function parse_img( &$post, $data ){
			//Get into namespace
			$ns = $data->getNameSpaces( true );
			$media = $data->children( $ns['media'] );
			
			//Get urls
			$post['thumb_url'] = (string)$media->thumbnail->attributes()->url;
			$post['url'] = (string)$media->content->attributes()->url;
			//TODO: calculate 'img' from thumb if not existent
			
			//Fix urls
			$this->transform_url( $post['url'] );
			$this->transform_url( $post['thumb_url'] );
		}
		
		//Parse title
		protected function parse_title( &$post, $data ){
			$title = (string)$data->title;
			sscanf( $title, '%d - %n', $post['id'], $pos );
			$post['tags'] = substr( $title, $pos );
		}
		
		//Parse title, by default it find the author in the
		//format "Uploaded by AUTHOR</p>"
		protected function parse_description( &$post, $data ){
			$desc = (string)$data->description;
			
			//Find the uploader
			$author = $this->find( $desc, 'Uploaded by ', '</p>' );
			if( $author )
				$post['author'] = $author;
			
			//Find extended information
			$info = $this->find( $desc, ' // ', "'" );
			if( $info ){
				//Parse the string
				$size; $type;
				sscanf(
						$info
					,	"%dx%d // %f%s"
					,	$post['width']
					,	$post['height']
					,	$size
					,	$type
					);
				
				//Fix size
				switch( $type ){
					case 'B': break;
					case 'KB': $size = (float)$size * 1024; break;
					case 'MB': $size = (float)$size * 1024*1024; break;
					default: die( 'Unknown size postfix: ' . $type );
				}
				$post['filesize'] = $size;
			}
		}
		
		//Parse date
		protected function parse_date( &$post, $data ){
			$date = (string)$data->pubDate;
			
			$post['creation_date'] = strtotime( $date );
		}
		
		//Parses a post
		protected function parse_post( $data ){
			//Init variables
			$post = array();
			
			//Parse varius things
			$this->parse_img( $post, $data );
			$this->parse_title( $post, $data );
			$this->parse_description( $post, $data );
			$this->parse_date( $post, $data );
			
			return $post;
		}
		
		//Fetches one page
		public function index( $search = NULL, $page = 1, $limit=NULL ){
			//Fix if search is not surplied
			if( $search === NULL )
				$search = "";
			
			//Get XML from site
			$data = $this->get_content( $this->get_url( $search, $page ) );
			if( !$data )
				die( "Couldn't fetch feed from site" );
			$this->fix_xml( $data );
			$data = simplexml_load_string( $data );
			
		//Start parsing
			$posts = array();
			
			//Check for more posts
			//TODO: check for rel="next"
			$ns = $data->getNameSpaces( true );
			$atom = $data->channel->children( $ns['atom'] );
			$posts['more'] = isset( $atom->link );
			
			//Posts
			foreach( $data->channel->item as $t )
				$posts[] = $this->parse_post( $t );
			
			return $posts;
		}
		
		
	//No-op
		public function post( $id ){
			//TODO: check if possible
			return NULL;
		}
		public function tag_index( $search ){ return NULL; }
		public function related_tags( $tags ){ return NULL; }
		public function notes( $post_id=NULL ){ return NULL; }
	}
	
	class MemeFolderApi extends ShimmieRssApi{
		public function __construct(){
			parent::__construct(
					"Meme folder"
				,	"meme"
				,	"http://memefolder.com/"
				,	25
				);
		}
	}
?>