<?php
	require_once 'lib/Api.php';
	
	abstract class DanApi extends Api{
		protected $url;
		
	//Parsing of post data
		abstract protected function get_post_mapping(); //This changes so much, must be implemented
		protected function transform_post( &$data ){
			//This function may be overloaded to standalize formatting
			//but remember to call this implementation too!
			
			//Remove dublicate images
			if(	isset( $data['preview_url'] )
				&&	(	$data['preview_url'] == $data['url']
					||	( isset( $data['reduced_url'] )
						&& $data['preview_url'] == $data['reduced_url'] 
						)
					)
				){
				$data['preview_url'] = NULL;
				$data['preview_height'] = NULL;
				$data['preview_width'] = NULL;
				$data['preview_filesize'] = NULL;
			}
			if( isset( $data['reduced_url'] ) && $data['reduced_url'] == $data['url'] ){
				$data['reduced_url'] = NULL;
				$data['reduced_height'] = NULL;
				$data['reduced_width'] = NULL;
				$data['reduced_filesize'] = NULL;
			}
			
			//Get rid of some commonly wrong NULLs
			if( $data['source'] === "" )
				$data['source'] = NULL;
		}
		protected final function parse_post( $data ){
			$arr = $this->element_to_array( $data );
			$post = $this->transform_array( $arr, $this->get_post_mapping() );
			$this->transform_post( $post );
			return $post;
		}
		
		
	//Parsing of tag data
		protected function get_tag_mapping(){
			//This seems pretty much the same across the boorus
			//so lets just make a default implementation
			return array(
					'id' => 'name',
					'type' => 'type',
					'count' => 'count',
					'ambiguous' => 'ambiguous'
				);
		}
		protected function transform_tag( &$data ){
			//Nothing to do here
		}
		protected final function parse_tag( $data ){
			$arr = $this->element_to_array( $data );
			$tag = $this->transform_array( $arr, $this->get_tag_mapping() );
			$this->transform_tag( $tag );
			return $tag;
		}
		
	//Parsing of note data
		//protected function parse_note( $data );
		
		
		
		protected function get_url( $handler, $action, $format, $parameters=array(), $login=false ){
			if( $login ){
				//TODO: add autorisation
			}
			
			//make parameters
			$para = "?";
			foreach( $parameters as $key => $value )
				$para .= "$key=$value&";
			$para = rtrim( $para, "&" );
			
			//Create URL
			return $this->url . "$handler/$action.$format$para";
		}
		
	//Fetch methods
		public function index( $search=NULL, $page=1, $limit=NULL ){
			//Format parameters
			$para = array();
			if( $search != NULL && $search != "" )
				$para['tags'] = $search;
			if( $limit )
				$para['limit'] = $limit;
			if( $page > 1 )
				$para['page'] = $page;
			
			//Retrive raw data from the server
			$url = $this->get_url( 'post', 'index', 'xml', $para );
			$data = $this->get_xml( $url );
			if( !$data )	//Kill if failed
				return NULL;
			
			//Parse array of posts
			$posts = array();
			foreach( $data->post as $post_data )
				$posts[] = $this->parse_post( $post_data );
			
			//Get amount of posts total
			$posts['count'] = (string)$data['count'];
			
			//Return posts
			return $posts;
		}
		
		public function post( $id ){
			$posts = $this->index( 'id:' . $id );
			return isset( $posts[0] ) ? $posts[0] : NULL;
		}
		
		public function tag_index( $search, $page=1, $limit=NULL ){
			//Format parameters
			$para = array();
			if( $search != NULL && $search != "" )
				$para['name_pattern'] = $search;
			if( $limit )
				$para['limit'] = $limit;
			if( $page > 1 )
				$para['page'] = $page;
			
			
			//Retrive raw data from the server
			$url = $this->get_url( 'tag', 'index', 'xml', $para );
			$data = $this->get_xml( $url );
			if( !$data )	//Kill if failed
				return NULL;
		}
	}
	
	
?>