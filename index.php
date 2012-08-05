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