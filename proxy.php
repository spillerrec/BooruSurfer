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
	
	require_once "lib/Booru.php";
	$site = new Booru( $_GET['site'] );
	
	$post = $site->post( $_GET['id'] );
	
	$site_url = $site->get_api()->get_refferer();
	//TODO: emulate it better ;P
	
	//Get image
	$type = $_GET['type'];
	switch( $type ){
		case 'thumb':
		case 'preview':
		case 'reduced':
			break;
		
		case 'original':
				$type = '';
			break;
		
		default:
			die( "Unknown image type!" );
	}
	$img = $post->get_image( $type );
	$url = $img->real_url;
	
	//Fix caching
	header( "Cache-Control: max-age=" . 60*60*24*365 );
	
	$ch = curl_init( $url );
	
	curl_setopt( $ch, CURLOPT_REFERER, $site_url );
	curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
	
	//Enable HTTPS
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
	
	//Forward content lenght
	curl_setopt( $ch, CURLOPT_HEADERFUNCTION, function( $ch, $header ){
		if( strpos( $header, 'Content-Length:' ) !== false )
			header( $header );
		if( strpos( $header, 'Content-Type:' ) !== false )
			header( $header );
		return strlen( $header );
	}	);
	
	curl_exec( $ch );
	
	curl_close( $ch );
?>