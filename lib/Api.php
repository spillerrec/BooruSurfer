<?php
	abstract class Api{
		abstract public function get_name();
		abstract public function get_code();
		
		abstract public function post( $id ); //Retrive a single post based on id
		abstract public function index( $search = NULL, $page = 1 ); //Retrive several posts based on a search critiria
	//	abstract public function tag( $id );
		abstract public function tag_index( $search );
	//	abstract public function similar_tags( $tag ); //Correct spelling?
		
		
		
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
			else{
				//TODO: do JSON here
				//return $data->{ $name };
				//We need some way of transforming fields into keys
			}
			
			return $arr;
		}
		
		protected function transform_array( $arr, $mapping ){
			$result = array();
			foreach( $mapping as $key => $value ){
				$result[ $key ] = $arr[ $value ];
			}
			return $result;
		}
	}
	
?>