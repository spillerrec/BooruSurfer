<?php
	require_once "plugins/sites/dan.php";
	
	class ThreeDeeBooruApi extends DanbooruApi{
		public function __construct(){
			$this->url = "http://behoimi.org/";
		}
		
		protected function transform_url( &$url, $type ){
			parent::transform_url( $url, $type );
			
			//We need proxy here
			if( ($type == 'preview') || ($type == 'file') ){
				$code = $this->get_code();
				$proxy_url = str_replace( $this->url, "", $url );
				$url = "/$code/proxy/$proxy_url";
			}
		}
		
		public function get_name(){ return "3dbooru"; }
		public function get_code(){ return "threedee"; }
	}
	
	
?>