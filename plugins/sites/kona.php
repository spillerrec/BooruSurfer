<?php
	require_once 'plugins/sites/dan.php';
	
	class KonachanApi extends DanApi{
		public function __construct(){
			$this->url = "http://konachan.com/";
		}
		
		protected function get_post_mapping(){ return KonachanApi::$post_mapping; }
		public static $post_mapping = array(
			'id'	=>	'id',
			'hash'	=>	'md5',
			'author'	=>	'author',
			'creation_date'	=>	'created_at',
			
			'parent_id'	=>	'parent_id',
			'has_children'	=>	'has_children',
		//	'has_notes'	=>	NULL,
		//	'has_comments'	=>	NULL,
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
			'thumb_width'	=>	'actual_preview_width',
			'thumb_height'	=>	'actual_preview_height',
		//	'thumb_filesize'	=>	NULL,
			
			'preview_url'	=>	'sample_url',
			'preview_width'	=>	'sample_width',
			'preview_height'	=>	'sample_height',
			'preview_filesize'	=>	'sample_file_size',
			
			'reduced_url'	=>	'jpeg_url',
			'reduced_width'	=>	'jpeg_width',
			'reduced_height'	=>	'jpeg_height',
			'reduced_filesize'	=>	'jpeg_file_size'
		);
		
		private static $tag_mapping = array(
			'id' => 'name',
			'name' => 'name',
			'type' => 'type',
			'count' => 'count'
		);
		
		public function get_name(){ return "Konachan"; }
		public function get_code(){ return "kona"; }
	}
?>