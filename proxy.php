<?php
	include_once "lib/gateway.php";
	
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

	echo file_get_contents( $url, false, $context );
?>