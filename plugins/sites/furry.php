<?php
	require_once "plugins/sites/gel.php";
	
	class FurryBooruApi extends GelbooruApi{
		public function __construct(){
			$this->url = "http://furry.booru.org/";
		}
		
		public function get_name(){ return "FurryBooru"; }
		public function get_code(){ return "furry"; }
	}
	
	
?>