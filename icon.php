<?php
	require "lib/Booru.php";
	
	$site = new Booru( $_GET['site'] );
	$api = $site->get_api();
	
	//Extract colors
	function extract_color( $in, &$r, &$g, &$b, &$a ){
		$a = ( $in & 0xFF000000 ) >> 24;
		$r = ( $in & 0x00FF0000 ) >> 16;
		$g = ( $in & 0x0000FF00 ) >> 8;
		$b = ( $in & 0x000000FF );
	}
	
	//Select file
	$filename = 'invalid.png';
	switch( $_GET['type'] ){
		case 'post': $filename = 'post.png'; break;
		case 'index': $filename = 'index.png'; break;
	}
	
	//Solid background
	$back = imagecreatetruecolor( 16, 16 );
	imagealphablending( $back, false );
	imagesavealpha( $back, true );
	
	//Character
	$char = imagecreatefrompng( $filename );
	imagealphablending( $char, true );
	imagesavealpha( $char, true );
	
	//Set colors
	extract_color( $api->get_front_color(), $r, $g, $b, $a );
	imagefilter( $char, IMG_FILTER_COLORIZE, $r, $g, $b, $a );
	
	extract_color( $api->get_back_color(), $r, $g, $b, $a );
	$back_color = imagecolorallocatealpha( $back, $r, $g, $b, $a );
	imagefill( $back, 0, 0, $back_color );
	
	imagecopy( $back, $char, 0,0, 0,0, 16,16 );
	
	
	header('Content-type: image/png');
	imagepng( $back );
	
?>