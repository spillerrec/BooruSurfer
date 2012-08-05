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
	
	require_once 'plugins/sites/dan.php';
	
	class KonachanApi extends DanApi{
		public function __construct(){
			$this->url = "http://konachan.com/";
		}
		
		protected function transform_date( &$date ){
			//Already in UNIX time
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
		
		
		protected function transform_tag( &$data ){
			//Nothing to do here
			switch( $data['type'] ){
				case 0:	$data['type'] = DTTag::NONE; break;
				case 1:	$data['type'] = DTTag::ARTIST; break;
				case 3:	$data['type'] = DTTag::COPYRIGHT; break;
				case 4:	$data['type'] = DTTag::CHARACTER; break;
				case 5:	$data['type'] = DTTag::SPECIAL; break;
				case 6:	$data['type'] = DTTag::COMPANY; break;
				default: $data['type'] = DTTag::UNKNOWN; break;
			}
		}
		
		public function get_name(){ return "Konachan"; }
		public function get_code(){ return "kona"; }
	}
?>