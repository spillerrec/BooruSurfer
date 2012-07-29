<?php
	require_once 'lib/header.php';
	require_once 'lib/SiteInfo.php';
	
	$info = new SiteInfo();
	
	$layout = new mainLayout( 'Administration' );
	$layout->page->html->title = 'Administrate sites';
	
	$info->add_site( 'dan', 'DanbooruApi' );
	$info->add_site( 'neko', 'NekobooruApi' );
	$info->add_site( 'vector', 'VectorbooruApi' );
	$info->add_site( 'kona', 'KonachanApi' );
	$info->add_site( 'imouto', 'YandereApi' );
	$info->add_site( 'gel', 'GelbooruApi' );
	$info->add_site( 'rule34', 'rule34Api' );
	$info->add_site( 'xbooru', 'XbooruApi' );
	$info->add_site( 'safe', 'SafebooruApi' );
	$info->add_site( 'furry', 'FurryBooruApi' );
	$info->add_site( 'size', 'SizeBooruApi' );
	
	$sites = SiteInfo::sites();
	foreach( $sites as $code => $site ){
		$layout->main->content[] = new htmlObject( 'p', "$code = $site" );
	}
	
	$layout->page->write();
?>