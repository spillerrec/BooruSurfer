<?php
	require_once "plugins/sites/dan.php";
	
	class NekobooruApi extends DanbooruApi{
		public function __construct(){
			$this->url = "http://nekobooru.net/";
		}
		
		public function get_name(){ return "nekobooru"; }
		public function get_code(){ return "neko"; }
	}
	
	
?>