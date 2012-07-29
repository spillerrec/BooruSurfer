<?php
	require_once "lib/Api.php";
	require_once "lib/DataTable.php";
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
		
		public function get_page_amount(){
			return $this->post_amount ? ceil( $this->post_amount / $this->fetch_amount ) : NULL;
		}
		
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
			foreach( $posts as $post )
				$post->db_save();
			
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
	}
?>