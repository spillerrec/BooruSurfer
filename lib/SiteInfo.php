<?php
	require_once "lib/DataTable.php";
	
	class SiteInfo extends DataTable{
		public function __construct(){
			$this->add( NULL, 'id',	true ); //ID is the code as a string
			$this->add( NULL, 'name',	true ); //Human readable name of the site
			$this->add( NULL, 'class',	true ); //Name of the class
			$this->add( NULL, 'tags_updated',	false ); //Last time tag db was updated (unix time)
			$this->add( NULL, 'username',	false ); //login name
			$this->add( NULL, 'password',	false ); //password hash
			
			parent::__construct( "site_info" );
		}
		
		private static function load_api( $code, $class ){
			require_once "plugins/sites/$code.php";
			return new $class();
		}
		
		public function get_api( $code ){
			//Site not initated
			if( !$this->db_read( $code ) )
				return NULL;
			
			//Include php file and create api
			$code = $this->get( 'id' ); //Safety: Don't allow direct access to the include
			$class = $this->get( 'class' );
			return $this->load_api( $code, $class );
		}
		
		//Warning, this function is unsafe
		public static function add_site( $code, $class ){
			$api = SiteInfo::load_api( $code, $class );
			
			//Add to site_info table
			$info = new SiteInfo();
			$info->set( 'id', $api->get_code() );
			$info->set( 'name', $api->get_name() );
			$info->set( 'class', $class );
			$info->db_save();
			
		}
	}
?>