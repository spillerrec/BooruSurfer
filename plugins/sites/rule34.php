<?php
	require_once "plugins/sites/gel.php";
	
	class rule34Api extends GelbooruApi{
		public function __construct(){
			$this->url = "http://rule34.xxx/";
		}
		
		public function get_name(){ return "Rule 34"; }
		public function get_code(){ return "rule34"; }
	}
	
	
?>