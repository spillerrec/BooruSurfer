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
	require_once "lib/DTPost.php";
	require_once "lib/DTTag.php";
	require_once "lib/SiteInfo.php";
	
	class Booru{
		private $info;
		private $api;
		private $code;
		const default_fetch = 24;
		private $fetch_amount = Booru::default_fetch;
		private $post_amount = NULL;
		
		public function __construct( $site ){
			$this->info = new SiteInfo();
			$this->api = $this->info->get_api( $site );
			if( $this->api === NULL )
				die( "Unknown site!" );
			$this->code = $this->api->get_code();
		}
		
		public function get_api(){ return $this->api; }
		
		public function get_page_amount(){
			return $this->post_amount ? ceil( $this->post_amount / $this->fetch_amount ) : NULL;
		}
		public function get_fetch_amount(){ return $this->fetch_amount; }
		
		public function index( $search = NULL, $page = 1 ){
			//No caching here yet : \
			$data = $this->api->index( $search, $page, $this->fetch_amount );
			
			//Set post_amount if available
			if( isset( $data[ 'count' ] ) )
				$this->post_amount = $data[ 'count' ];
			else{
				//fetch it explicitly
				//TODO: 
			}
			
			//Convert posts
			$posts = array();
			foreach( $data as $post )
				if( gettype( $post ) == "array" ) //Do not convert extra properties like 'count'
					$posts[] = new DTPost( $this->code, $post );
			
			//Save posts in db
			$db = Database::get_instance()->db;
			$db->beginTransaction();
			foreach( $posts as $post )
				$post->db_save();
			$db->commit();
			
			return $posts;
		}
		
		public function post( $id ){
			$post = new DTPost( $this->code );
			
			//Check database
			if( $post->db_contains( $id ) ){
				//Fetch from database
				$post->db_read( $id );
				return $post;
			}
			else{
				//Not in database, fetch it from site
				$post = new DTPost( $this->code, $this->api->post( $id ) );
				$post->db_save();
				return $post;
			}
		}
		
		
		
		//Change fetch amount
		public function change_fetch_amount( $amount ){
			$this->fetch_amount = $amount;
		}
		
		
	//Links to several places
		public function post_link( $post_id ){
			return "/$this->code/post/$post_id/";
		}
		
		public function site_index_link( $code, $page = 1, $search = NULL ){
			$url = "/$code/index/";
			
			if( $page > 1 || $this->fetch_amount !== Booru::default_fetch ){
				$url .= $page;
				
				if( $this->fetch_amount !== Booru::default_fetch )
					$url .= "-$this->fetch_amount";
				
				$url .= "/";
			}
			
			if( $search )
				$url .= $search;
			
			return $url;
		}
		
		
		public function index_link( $page = 1, $search = NULL ){
			return Booru::site_index_link( $this->code, $page, $search );
		}
		
		public function refresh_tags(){
			$tags = $this->api->all_tags();
			
			$time_start = microtime( true );
			
			//Do it as a single transaction to reduce journaling penalty
			$db = Database::get_instance()->db;
			$db->beginTransaction();
			
			echo "fetched data, saving in db<br>";
			foreach( $tags as $tag_data ){
				$tag = new DTTag( $this->code, $tag_data );
				$tag->db_save();
			}
			echo "done :D<br>";
			
			echo "Time taken: ", microtime( true ) - $time_start , "<br>";
			
			$db->commit();
		}
		
		public function related( $search ){
			if( !$search ){
				$tag = new DTTag( $this->code );
				return $tag->most_used();
			}
			else{
				//Fetch related tags
				//No caching yet : \
				$data = $this->api->related_tags( $search );
				
				$tags_list = array();
				foreach( $data as $key => $value ){
					
					$related = array();
					foreach( $value as $tag_data ){
						$tag = new DTTag( $this->code, $tag_data );
						$tag->db_read( $tag->name() );
						$tag->real_count = $tag_data['count'];
						$related[] = $tag;
					}
					
					$tags_list[$key] = $related;
				}
				
				foreach( $tags_list as $result )
					return $result; //Hackish way of returning first one..
			}
		}
	}
?>