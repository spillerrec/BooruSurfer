<?php
	require_once "plugins/sites/dan.php";
	
	class VectorbooruApi extends DanbooruApi{
		public function __construct(){
			$this->url = "http://ichijou.org/";
		}
		
		public function get_name(){ return "vectorbooru"; }
		public function get_code(){ return "vector"; }
	}
	
	
?>