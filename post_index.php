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
	
	require_once "lib/header.php";
	
	require_once "lib/Booru.php";
	require_once "lib/Styler.php";
	$site = new Booru( $_GET['site'] );
	$styler = new Styler( $site );
	
	//Change limit
	if( isset( $_GET['limit'] ) )
		$site->change_fetch_amount( $_GET['limit'] );
	
	//Get parameters
	$search = $_GET["search"];
	$page = $_GET["page"];
	$post_index = $site->index( $search );
	$index = $post_index->get_page( $page );
	
	$page_amount = $post_index->get_page_amount();
	
	$layout = new mainLayout();
	$layout->navigation = $styler->main_navigation( $search );
	
	//Set title
	$title = "Index";
	if( $search )
		$title .= ": " . $search;
	if( $page > 1 )
		$title .= " - Page " . $page;
	$layout->page->html->title = $title;
	
	$layout->main->attributes['class'] = "post_list";
	$layout->sidebar->attributes['class'] = "post_list_info";
	
	//Set page relationships
	if( $page > 1 )
		$layout->page->html->addSequence( "prev", $site->index_link( $page-1, $search ) );
	
	if( $page+1 <= $page_amount )
		$layout->page->html->addSequence( "next", $site->index_link( $page+1, $search ) );
	
	if( $index ){
		//Write list
		$list = new htmlList();
		foreach( $index as $i )
			$list->addItem( array(
					$styler->post_thumb( $i )
				,	$styler->post_details( $i )
				) );
		
		$layout->main->content[] = $list;
	}
	else	//Nothing to display
		$layout->main->content[] = new htmlObject( 'p', 'No posts found' );
	
	//Add page navigation
	$page_links = $styler->page_index_nav( $post_index, $page );
	if( $page_links )
		$layout->main->content[] = $page_links;
	
	
	//Side-panel
	
	//Add similar tags if low amount of pages
	if( $page_amount < 2 ){
		$tag = new DTTag( $site->get_code() );
		$tags = $tag->similar_tags( $search );
		$layout->sidebar->content[] = $styler->tag_list( $tags, 'Similar tags' );
	}
	
	//Add related tags
	$tags = $post_index->related_tags();
	$layout->sidebar->content[] = $styler->tag_list( $tags, 'Related tags' );
	
	$layout->page->write();
?>