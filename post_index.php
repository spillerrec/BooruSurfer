<?php
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
	$index = $site->index( $search, $page );
	
	$page_amount = $site->get_page_amount();
	if( $page_amount < 2 ){
		//TODO:
	}
	
	$layout = new mainLayout( "Konachan", "kona" );
	
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
	
	if( $page+1 < $page_amount )
		$layout->page->html->addSequence( "next", $site->index_link( $page+1, $search ) );
	
	//Add links to other sites in the nav bar
	$sites = SiteInfo::sites();
	foreach( $sites as $key=>$sub_site ){
		$layout->navigation->addItem(
				new htmlLink( $site->site_index_link( $key, 1, $search ), $sub_site )
			);
	}
	
	$list = new htmlList();
	
	//Write list
	foreach( $index as $i ){
		$item = $list->addItem( array( $styler->post_thumb( $i ), $styler->post_details( $i ) ) );
		if( $i->parent_id() )
			$item->addClass( "has_parent" );
		if( $i->has_children() )
			$item->addClass( "has_children" );
	}
	$layout->main->content[] =& $list;
	
	//Add page navigation
	$page_links = $styler->page_nav( $search, $page );
	if( $page_links )
		$layout->main->content[] = $page_links;
	
	
	//Side-panel
	$layout->sidebar->content[] = new htmlObject( "p", $search );
	
	//Add related tags
	$tags = $site->related( $search );
	if( $tags )
		$layout->sidebar->content[] = $styler->tag_list( $tags );
	
	$layout->page->write();
?>