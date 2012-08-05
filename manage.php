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
	
	require_once 'lib/header.php';
	require_once 'lib/SiteInfo.php';
	
	$info = new SiteInfo();
	
	$layout = new mainLayout( 'Administration' );
	$layout->page->html->title = 'Administrate sites';
	
	$info->add_site( 'dan', 'DanbooruApi' );
	$info->add_site( 'neko', 'NekobooruApi' );
	$info->add_site( 'vector', 'VectorbooruApi' );
	$info->add_site( 'threedee', 'ThreeDeeBooruApi' );
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