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
	
	abstract class Api{
		abstract public function get_name();
		abstract public function get_code();
		public function get_refferer(){ return ''; }
		
	//Post related stuff
		//Retrive a single post based on id
		//TODO: delete and replace functionallity in Index()?
		abstract public function post( $id );
		
		//Retrive several posts based on a search critiria
		abstract public function index( $search = NULL, $page = 1, $limit=NULL );
		
		//Get the count of a index search. If this data is provided
		//by the index() function this should return NULL
		public function index_count( $search = NULL ){ return NULL; }
		
		
	//Tag related stuff
	//	abstract public function tag( $id );
		abstract public function tag_index( $search );
		abstract public function related_tags( $tags );
		
	//Notes stuff
		abstract public function notes( $post_id=NULL );
		
	//Login functionallity
		protected $username = NULL;
		protected $password_hash;
		//Set the user by user name and password hash
		public function set_user( $name, $password ){
			$this->username = $name;
			$this->password_hash = $password;
		}
		//Hashes the password, override this function if logins are used
		public function hash_password( $pass ){ return NULL; }
		
		
	//Support for features
		
		//Specifies how 'limit' works.
		//If result is negative, limit cannot be changed
		//and is equal to abs(result).
		//If result is 0, no limit exists.
		//If result is >0 then there is a upper limit
		//equal to result.
		abstract public function supports_post_limit();
		
		//Returns 0 if post count is unknown.
		//Returns 1 if post count can be found with index_count()
		//Returns 2 if post count is surplied alongside index()
		abstract public function supports_post_count();
		
		//Returns true if it can fetch all tags at once or not.
		abstract public function supports_all_tags();
		
		//Returns true if it can calculate related tags
		abstract public function supports_related_tags();
		
		//Returns true if it is possible to log in
		abstract public function supports_login();
		
		//Returns the default size of thumbnails
		abstract public function thumbnail_size();
		
		
	//Other stuff
		
		protected function get_content( $url ){
			//Setup HTTP
			$opts = array(
			  'http'=>array(
				'method'=>"GET",
				'header'=>"User-Agent: " . $_SERVER['HTTP_USER_AGENT']
			  )
			);

			$context = stream_context_create($opts);

			
			//Try to read the file, try again up to 3 times if unsuccesfull
			$i = 3;
			while( false === ( $result = file_get_contents( $url, false, $context ) ) && $i > 0 )
				$i--;
		
			return $result;
		}
		
		protected function get_json( $url ){
			$result = $this->get_content( $url );
			return $result ? json_decode( $result ) : NULL;
		}
		
		protected function get_xml( $url ){
			$result = $this->get_content( $url );
			return $result ? simplexml_load_string( $result ) : NULL;
		}
		
		
		private function is_xml( $data ){
			return get_class( $data ) == "SimpleXMLElement";
		}
		protected function element_to_array( $data ){
			$arr = array();
			if( $this->is_xml( $data ) ){
				foreach( $data->attributes() as $key => $value )
					$arr[ $key ] = (string)$value;
			}
			else
				return get_object_vars( $data );
			
			return $arr;
		}
		
		protected function transform_array( $arr, $mapping ){
			$result = array();
			foreach( $mapping as $key => $value ){
				$result[ $key ] = isset( $arr[ $value ] ) ? $arr[ $value ] : NULL;
			}
			return $result;
		}
	}
	
?>