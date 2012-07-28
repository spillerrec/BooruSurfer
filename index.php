<?php
	include_once "lib/html.php";
	include_once "lib/links.php";
	
	$sites = array(
		'kona'=> array( "Konachan", "http://konachan.com/images/logo.png" ),
		'san'=> array( "Sankaku Channel", "http://chan.sankakustatic.com/logo.png" ),
		'idol'=> array( "Idol Complex", "http://idol.sankakucomplex.com/logo.png" ),
		'imouto'=> array( "oreno.imouto", "https://yande.re/images/logo.png" ),
		'danbooru'=> array( "Danbooru", "/style/danbooru.png" )
	);
	
	$page = new htmlPage();
	$page->html->addStylesheet( "/style/main.css" );
	$page->html->title = "Gateway to epicness";
	
	$page->html->body->attributes['id'] = "front";
	$page->html->body->content[] = new htmlObject( "h1", "Available sites" );
	foreach( $sites as $site=>$details ){
		$page->html->body->content[] = new htmlLink( get_index_link(), new htmlImage( $details[1] ) ); //new htmlLink( $site[1], $site[0] );
	}
	
	$page->write();
?>