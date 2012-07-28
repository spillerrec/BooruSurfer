<?php
	require_once 'plugins/sites/kona.php';
	
	class YandereApi extends KonachanApi{
		public function __construct(){
			$this->url = "http://yande.re/";
		}
		public function get_name(){ return "Yande.re"; }
		public function get_code(){ return "imouto"; }
	}
?>