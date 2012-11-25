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
	function start_xml(){
		header( 'Content-type: application/xml' );
		echo '<?xml version="1.0" encoding="UTF-8" ?>', "\r\n";
	}
	function error( $message ){
		start_xml();
		element( 'error', $message );
		die;
	}
	function element( $tag, $text ){
		echo "<$tag>$text</$tag>";
	}
	
	
	require 'lib/DTPost.php';
	require 'lib/Index.php';
	require 'lib/Booru.php';
	
	$post = NULL;
	
	if( isset( $_GET[ 'hash' ] ) ){
		//Try to get from hash
		$hash = $_GET[ 'hash' ];
		
		$dan = new DTPost( 'dan' );
		$gel = new DTPost( 'gel' );
		$san = new DTPost( 'san' );
		
		//Check DB by hash
		if( $dan->db_hash( $hash ) )
			$post = $dan;
		else if( $gel->db_hash( $hash ) )
			$post = $gel;
		else if( $san->db_hash( $hash ) )
			$post = $san;
		
		if( $post === NULL ){
			//If it was not found in the DB,
			//start trying to check the sites
			$site = new Booru( 'dan' );
			if( !( $post = $site->post_hash( $hash ) ) ){
				$site = new Booru( 'san' );
				if( !( $post = $site->post_hash( $hash ) ) ){
					$site = new Booru( 'gel' );
					$post = $site->post_hash( $hash );
				}
			}
		}
	}
	else if( isset( $_GET[ 'site' ] ) && isset( $_GET[ 'id' ] ) ){
		//Get by '$site' + '$id'
		$site = new Booru( $_GET[ 'site' ] );
		$post = $site->post( $_GET[ 'id' ] );
	}
	else
		error( "Wrong or missing paramters surplied" );
	
	if( $post !== NULL ){
		//Post found, create response
		start_xml();
		echo '<post>';
			element( 'id', $post->get( 'id' ) );
			element( 'hash', $post->get( 'hash' ) );
			element( 'width', $post->get( 'width' ) );
			element( 'height', $post->get( 'height' ) );
			element( 'filename', $post->get_filename() );
		echo '</post>';
	}
	else
		error( "No post was found with a matching hash" );
?>