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
	
	$site_url = "http://behoimi.org/";
	$url = $site_url . $_GET["url"];
	
	//TODO: emulate referer path
	//TODO: set caching
	
	//Set mimetype
	$mime;
	$ext = pathinfo($url, PATHINFO_EXTENSION);
	switch( $ext ){
		case "jpeg":
		case "jpg": $mime = "image/jpeg"; break;
		case "png": $mime = "image/png"; break;
		case "gif": $mime = "image/gif"; break;
		case "bmp": $mime = "image/bmp"; break; //Not standard!
		case "svg": $mime = "image/svg+xml"; break;
		case "swf": $mime = "application/x-shockwave-flash"; break;
		case "tif": $mime = "image/tiff"; break;
		default: echo "Unknown filetype: $ext!"; die;
	}
	header( "Content-Type: $mime" );
	header( "Cache-Control: max-age=" . 60*60*24*365 );
	
	//Setup HTTP
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"User-Agent: Opera/9.80 (Windows NT 6.1; Win64; x64; U; en) Presto/2.10.289 Version/12.00\r\nReferer: $site_url\r\n"
		)
	);

	$context = stream_context_create($opts);

	readfile( $url, false, $context );
?>