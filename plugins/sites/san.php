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
	
	require_once "plugins/sites/dan.php";
	
	abstract class SanApi extends DanApi{
		
		public function index( $search=NULL, $page=1, $limit=NULL ){
			//We need to overload this because we have to fetch
			//it from JSON instead.
			
			//Format parameters
			$para = array();
			if( $search != NULL && $search != "" )
				$para['tags'] = $search;
			if( $limit )
				$para['limit'] = $limit;
			if( $page > 1 )
				$para['page'] = $page;
			
			//Retrive raw data from the server
			$url = $this->get_url( 'post', 'index', 'json', $para );
			$data = $this->get_json( $url );
			if( !$data )	//Kill if failed
				return NULL;
			
			//Parse array of posts
			$posts = array();
			foreach( $data as $post_data )
				$posts[] = $this->parse_post( $post_data );
			
			//Return posts
			return $posts;
		}
		
		public function index_count( $search = NULL ){
			//Fetch the index count from XML
			$para = array();
			$para['tags'] = $search;
			
			$url = $this->get_url( 'post', 'index', 'xml', $para );
			$data = $this->get_xml( $url );
			
			return (int)$data['count'];
		}
		
		protected function transform_date( &$date ){
			//Stored as unix time in the 's' property
			$date = $date->{'s'};
		}
		
		protected function transform_post( &$data ){
			parent::transform_post( $data );
			
			//Fix issue with NULL in isset()
			if( $data['parent_id'] === NULL )
				$data['parent_id'] = 0;
		}
	}
	
	class SankakuChannelApi extends SanApi{
		public function __construct(){
			$this->url = "http://chan.sankakucomplex.com/";
		}
		
		protected function get_post_mapping(){ return SankakuChannelApi::$post_mapping; }
		public static $post_mapping = array(
			'id'	=>	'id',
			'hash'	=>	'md5',
			'author'	=>	'author',
			'creation_date'	=>	'created_at',
			
			'parent_id'	=>	'parent_id',
			'has_children'	=>	'has_children',
			'has_notes'	=>	'has_notes',
			'has_comments'	=>	'has_comments',
			'source'	=>	'source',
			
			'tags'	=>	'tags',
			'score'	=>	'score',
			'rating'	=>	'rating',
			'status'	=>	'status',
			
			'url'	=>	'file_url',
			'width'	=>	'width',
			'height'	=>	'height',
			'filesize'	=>	'file_size',
			
			'thumb_url'	=>	'preview_url',
			'thumb_width'	=>	'preview_width',
			'thumb_height'	=>	'preview_height',
		//	'thumb_filesize'	=>	NULL,
			
			'preview_url'	=>	'sample_url',
			'preview_width'	=>	'sample_width',
			'preview_height'	=>	'sample_height',
		//	'preview_filesize'	=>	'sample_file_size',
			
		//	'reduced_url'	=>	'jpeg_url',
		//	'reduced_width'	=>	'jpeg_width',
		//	'reduced_height'	=>	'jpeg_height',
		//	'reduced_filesize'	=>	'jpeg_file_size'
		);
		
		private static $tag_mapping = array(
			'id' => 'name',
			'name' => 'name',
			'type' => 'type',
			'count' => 'count'
		);
		
		public function get_name(){ return "Sankaku Channel"; }
		public function get_code(){ return "san"; }
	}
	
	
?>