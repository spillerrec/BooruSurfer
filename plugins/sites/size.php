<?php
	require_once "plugins/sites/gel.php";
	
	class SizeBooruApi extends GelbooruApi{
		public function __construct(){
			$this->url = "http://size.booru.org/";
		}
		
		public function get_name(){ return "SizeBooru"; }
		public function get_code(){ return "size"; }
	}
	
	
?>