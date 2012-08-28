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
	
	class ZeroApi extends Api{
	//Support
		public function supports_post_limit(){ return -100; }
		public function supports_post_count(){ return true; }
		public function supports_all_tags(){ return false; }
		public function supports_related_tags(){ return false; }
		public function supports_login(){ return false; }
		
	//Site
		protected $url = 'http://www.zerochan.net/';
		public function get_name(){ return 'Zerochan'; }
		public function get_code(){ return 'zero'; }
		public function get_refferer(){ return $this->url; }
		
		
	//Index fetching and parsing functions
		
		//Calculate URL
		protected function get_url( $search, $page=1 ){
			//Search uses '+' instead of '_'
			//and ',' instead of ' '
			$search = str_replace( ' ', '%2C', $search );
			$search = str_replace( '_', '+', $search );
			
			$url = $this->url . "$search?s=id&xml";
			if( $page > 1 )
				echo $url;
			return $url;
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
		
		//Parses a post
		//TODO: check why certain tags are selected to be titles
		//Perhaps it is used in the full URL?
		protected function parse_post( $data ){
			//Init variables
			$post = array();
			$media = $data->children( 'http://search.yahoo.com/mrss/' );
			$post['tags'] = '';
			
			//Get id
			sscanf( (string)$data->guid
				,	'http://www.zerochan.net/%d'
				,	$post['id']
				);
			
			//Get tags and convert it to a space separated array
			$tags = (string)$media->keywords;
			$tags = explode( ',', $tags );
			foreach( $tags as $tag ){
				$tag = trim( $tag );
				$tag = str_replace( ' ', '_', $tag );
				$tag = str_replace( "\n\t", '_', $tag );
				$post['tags'] .= $tag . ' ';
			}
			
			//Get rating
			$post['rating'] = 's';
			if( $media->rating && (string)$media->rating == 'adult' )
				$post['rating'] = 'e';
			
			//Thumbnails and previews
			$post['thumb_url'] = (string)$media->thumbnail->attributes()->url;
			$post['preview_url'] = (string)$media->content->attributes()->url;
			
			//Full resolution image
			//Name part is based on the title
			$title = (string)$data->title;
			$title = str_replace( ' ', '.', $title );
			$post['url'] = str_replace( '.600', "$title.full", $post['preview_url'] );
			$post['width'] = (string)$media->content->attributes()->width;
			$post['height'] = (string)$media->content->attributes()->height;
			
			return $post;
		}
		
		//Fetches one page
		public function index( $search = NULL, $page = 1, $limit=NULL ){
			//Get XML from site
			$data = $this->get_xml( $this->get_url( $search, $page ) );
			
			//Start parsing
			$posts = array();
			
			//Posts
			foreach( $data->channel->item as $t )
				$posts[] = $this->parse_post( $t );
			
			//Find post count
			$desc = $data->channel->description;
			if( $desc ){
				$amount = $this->find( (string)$desc, 'Zerochan has ', ' ' );
				$amount = str_replace( ',', '', $amount );
				$posts['count'] = (int)$amount;
			}
			else{
				//Just indicate if more pages can be fetched
				$ns = $data->getNameSpaces( true );
				$atom = $data->channel->children( $ns['atom'] );
				$more = false;
				foreach( $atom->link as $link )
					if( (string)$link->attributes()->rel == 'next' )
						$more = true;
				
				$posts['more'] = $more;
			}
			
			return $posts;
		}
		
		
	//No-op
		public function post( $id ){
			//TODO: check if possible
			return NULL;
		}
		public function tag_index( $search ){ return NULL; }
		public function related_tags( $tags ){ return NULL; }
	}
?>