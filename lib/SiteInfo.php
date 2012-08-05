<?php
	/*	This file is part of BooruSurfer.

		BooruSurfer is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version.

		BooruSurfer is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with BooruSurfer.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	require_once "lib/DataTable.php";
	
	class SiteInfo extends DataTable{
		public function __construct( $data = NULL){
			parent::__construct( "site_info", $data );
		}
		
		protected function create_data(){
			$this->add( 'id',	true ); //ID is the code as a string
			$this->add( 'name',	true ); //Human readable name of the site
			$this->add( 'class',	true ); //Name of the class
			$this->add( 'tags_updated',	false ); //Last time tag db was updated (unix time)
			$this->add( 'username',	false ); //login name
			$this->add( 'password',	false ); //password hash
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
			$api = $this->load_api( $code, $class );
			
			//Set password
			if( $this->get( 'username' ) ){
				$api->set_user(
						$this->get( 'username' ),
						$this->get( 'password' )
					);
			}
			
			return $api;
		}
		
		//Warning, this function is unsafe
		public static function add_site( $code, $class ){
			$api = SiteInfo::load_api( $code, $class );
			
			//Add to site_info table
			$info = new SiteInfo();
			$info->set( 'id', $api->get_code() );
			$info->set( 'name', $api->get_name() );
			$info->set( 'class', $class );
			$info->db_save( false );
		}
		
		public static function sites(){
			$db = Database::get_instance()->db;
			$stmt = $db->query( 'SELECT * FROM site_info ORDER BY name ASC' );
			
			//Build array
			$sites = array();
			foreach( $stmt as $row )
				$sites[ $row['id'] ] = $row['name'];
			
			return $sites;
		}
	}
?>