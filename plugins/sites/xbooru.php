<?php
	require_once "plugins/sites/gel.php";
	
	class XbooruApi extends GelbooruApi{
		public function __construct(){
			$this->url = "http://xbooru.com/";
		}
		
		public function get_name(){ return "Xbooru"; }
		public function get_code(){ return "xbooru"; }
	}
	
	
?>