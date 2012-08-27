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
	include_once "lib/SiteInfo.php";
	
	//Create site links
	$sites = SiteInfo::sites();
	$links = new htmlList();
	foreach( $sites as $key=>$sub_site ){
		$links->addItem(
				new htmlLink( "/$key/index/", $sub_site )
			);
	}
	
	$page = new htmlPage();
	$page->html->addStylesheet( "/style/main.css" );
	$page->html->title = "BooruSurfer";
	
	$page->html->body->attributes['id'] = "front";
	$page->html->body->content[] = new htmlObject( "h1", "Available sites" );
	$page->html->body->content[] = $links;
	
	$page->write();
?>