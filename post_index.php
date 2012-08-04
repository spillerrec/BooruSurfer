<?php
	require_once "lib/header.php";
	require_once "lib/links.php";
	
	require_once "lib/Booru.php";
	$site = new Booru( $_GET['site'] );
	
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
		$thumb = $i->get_image( 'thumb' );
		$image = $i->get_image();
		
		//Add link with thumbnail
		$img = new htmlImage( $thumb->url, null );
		$details = new htmlObject( "section", NULL, toClass("details") );
		$details->content[] = new htmlObject( "p", $image->width . "x" . $image->height, toClass("img_size") );
		$details->content[] = new htmlObject( "p", $image->filesize, toClass("img_filesize") );
		//Add tags
		$tag_details = new htmlObject( "p", NULL, toClass("img_tag") );
		foreach( $i->get_tags() as $tag ){
			$t = new htmlObject( 'span', $tag->name() );
			if( $tag->get_type() )
				$t->addClass( "tagtype" . $tag->get_type() );
			$tag_details->content[] = $t;
			$tag_details->content[] = new fakeObject( " " );
		}
		$details->content[] = $tag_details;
		
		$item = $list->addItem( array( new htmlLink( $site->post_link( $i->id() ), $img ), $details ) );
		if( $i->parent_id() )
			$item->addClass( "has_parent" );
		if( $i->has_children() )
			$item->addClass( "has_children" );
	}
	$layout->main->content[] =& $list;
	
	$page_links = create_page_links( $site, $search, $page );
	if( $page_links )
		$layout->main->content[] = $page_links;
	
	
	//Side-panel
	$layout->sidebar->content[] = new htmlObject( "p", $search );
	$tags = $site->related( $search );
	
	function make_tag_link2( $tag, $limit = 24 ){
		global $site;
		$url = $site->index_link( 1, $tag->name() );
		
		$title = str_replace( "_", " ", $tag->name() );
		$count = $tag->real_count ? $tag->real_count : $tag->get_count();
		if( $count )
			$title .= " (" . $count . ")";
		
		$link = new htmlLink( $url, $title );
		
		if( $tag->get_type() )
			$link->addClass( "tagtype" . $tag->get_type() );
		
		return $link;
	}
	
	if( $tags ){
		$tag_list = new htmlList();
		foreach( $tags as $tag )
			$tag_list->addItem( make_tag_link2( $tag ) );
		$layout->sidebar->content[] = $tag_list;
	}
	
	$layout->page->write();
?>