<?php
	require_once "plugins/sites/gel.php";
	
	class SafebooruApi extends GelbooruApi{
		public function __construct(){
			$this->url = "http://safebooru.org/";
		}
		
		public function get_name(){ return "Safebooru"; }
		public function get_code(){ return "safe"; }
	}
	
	
?>