<?php
	include_once "html.php";
	
	function make_tag_link( $tag, $limit = 24 ){
		$url = get_index_link( 1, $tag->name, $limit );
		
		$title = str_replace( "_", " ", $tag->name );
		if( $tag->count )
			$title .= " ($tag->count)";
		
		$link = new htmlLink( $url, $title );
		
		if( $tag->type && $tag->type > 0 )
			$link->addClass( "tagtype" . $tag->type );
		
		return $link;
	}
	
	function create_page_links( $current_page, $amount, $search = NULL, $limit = 24 ){
		$min = $current_page - 3;
		$max = $current_page + 3;
		if( $min < 1 )
			$min = 1;
		if( $max >= $amount )
			$max = $amount - 1;
		
		
		$list = new htmlList();
		if( $min > 1 ){
			$list->addItem( new htmlLink( get_index_link( 1, $search, $limit ), "<<" ) );
			if( $min > 2 )
				$list->addItem( "..." );
		}
		
		for( $i=$min; $i<=$max; $i++ ){
			if( $i == $current_page )
				$list->addItem( $current_page );
			else
				$list->addItem( new htmlLink( get_index_link( $i, $search, $limit ), $i ) );
		}
		
		if( $max < $amount - 1 ){
			$list->addItem( "..." );
			$list->addItem( new htmlLink( get_index_link( $amount - 1, $search, $limit ), ">>" ) );
		}
		
		$nav = new htmlObject( "nav", NULL, array( 'class'=>'page_nav' ) );
		$nav->content[] = $list;
		$nav->content[] = new htmlObject( "div", NULL, array( 'style'=>'clear:both' ) );
		return $nav;
	}
?>