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
	require_once "lib/Index.php";
	
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
		
		public function get_fetch_amount(){ return $this->fetch_amount; }
		
		public function index( $search = NULL ){
			return new Index( $this, $search );
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
			$time = time();
			$tags = $this->api->all_tags();
			
			$time_start = microtime( true );
			
			//Create this before the transaction
			//in case the database doesn't exist
			$t = new DTTag( $this->code );
			
			//Do it as a single transaction to reduce journaling penalty
			$db = Database::get_instance()->db;
			$db->beginTransaction();
			
			//set update time in info
			$db->exec( "UPDATE site_info SET tags_updated = $time WHERE id = '$this->code'" );
			//Delete previous contents
			$t->delete_contents();
			
			echo "fetched data, saving in db<br>";
			foreach( $tags as $tag_data ){
				$tag = new DTTag( $this->code, $tag_data );
				$tag->db_save();
			}
			echo "done :D<br>";
			
			echo "Time taken: ", microtime( true ) - $time_start , "<br>";
			
			$db->commit();
		}
	}
?>